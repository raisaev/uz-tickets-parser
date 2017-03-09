<?php

namespace RAIsaev\UzTicketsParser\Rest;

class Client
{
    const REQUEST_TYPE_POST   = 'POST';
    const REQUEST_TYPE_GET    = 'GET';
    const REQUEST_TYPE_DELETE = 'DELETE';
    const REQUEST_TYPE_PUT    = 'PUT';
    const REQUEST_TYPE_PATCH  = 'PATCH';

    protected $params  = [];
    protected $headers = [];
    protected $post    = [];
    protected $get     = [];
    protected $cookies = [];

    protected $responseHeaders;
    protected $responseBody;
    protected $responseInfo;
    protected $responseCookies;

    // ########################################

    public function sendRequest($url,
                                $requestType = self::REQUEST_TYPE_GET,
                                $useMultiPartForm = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($curl, CURLOPT_HEADER, true);

        if (!empty($this->getPost())) {

            curl_setopt($curl, CURLOPT_POST, true);

            if ($useMultiPartForm) {

                $boundary    = $this->buildBoundary();
                $rawPostData = $this->buildRawPostData($this->getPost(), $boundary);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $rawPostData);

                $this->headers['Content-Type']   = 'multipart/form-data; boundary=' . $boundary;
                $this->headers['Content-Length'] = strlen($rawPostData);
                $this->headers['Expect']         = '100-continue';
            } else {

                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post);
            }
        }

        if (!empty($this->getGet())) {
            $url .= '?' . http_build_query($this->getGet());
        }
        curl_setopt($curl, CURLOPT_URL, $url);

        $preparedHeaders = [];
        foreach ($this->headers as $headerName => $headerValue) {
            $preparedHeaders[] = $headerName .': '. $headerValue;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $preparedHeaders);

        $cookieString = '';
        foreach ($this->cookies as $key => $value) {
            $cookieString .= $key.'='.$value.';';
        }
        curl_setopt($curl, CURLOPT_COOKIE, $cookieString);

        if (in_array($requestType, [self::REQUEST_TYPE_DELETE, self::REQUEST_TYPE_PUT, self::REQUEST_TYPE_PATCH])) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestType);
        }

        $curlResponse = curl_exec($curl);
        $headerSize   = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $this->responseBody = substr($curlResponse, $headerSize);
        $this->responseInfo = curl_getinfo($curl);

        $rawHeader = substr($curlResponse, 0, $headerSize);
        $this->responseHeaders = $this->parseResponseHeaders($rawHeader);
        $this->responseCookies = $this->parseResponseCookies($rawHeader);
        $this->validateLastResponse($curl);

        curl_close($curl);
        return $this->responseBody;
    }

    // ########################################

    protected function validateLastResponse($curl)
    {
        if ($this->responseBody === false) {

            $curlError = curl_error($curl);
            throw new \Exception("An connection error occurred. {$curlError}.");
        }

        if (!empty($this->responseInfo['http_code']) &&
            in_array((int)$this->responseInfo['http_code'], [500, 400])) {

            $httpCode = $this->responseInfo['http_code'];
            throw new \Exception("An error occurred. HTTP Code: {$httpCode}");
        }
    }

    protected function parseResponseHeaders($rawHeaders)
    {
        $headers = [];
        foreach (explode("\r\n", $rawHeaders) as $headerLine) {

            if (strpos($headerLine, ': ') === false) {
                continue;
            }

            list($key, $value) = explode(': ', $headerLine);
            if (strtolower($key) === 'set-cookie') {
                continue;
            }

            $headers[$key] = $value;
        }

        return $headers;
    }

    protected function parseResponseCookies($rawHeaders)
    {
        $cookies = [];
        preg_match_all('/Set\-Cookie:\s((.)*?=(.)*?);/', $rawHeaders, $matches);

        foreach ($matches[1] as $cookieMatch) {
            $cookie = explode('=', $cookieMatch);
            if (empty($cookie[0]) || empty($cookie[1])) {
                continue;
            }
            $cookies[$cookie[0]] = $cookie[1];
        }

        return $cookies;
    }

    // ########################################

    protected function buildRawPostData($postFields, $boundary)
    {
        $preparedBody   = [];
        $preparedFields = $this->convertPostDataToFlatArray($postFields);

        foreach ($preparedFields as $field) {

            list($key, $value) = $field;

            if (strpos($value, '@') === 0) {

                preg_match('/^@(.*?)$/', $value, $matches);
                list($dummy, $filename) = $matches;
                $preparedBody[] = '--' . $boundary;
                $preparedBody[] = 'Content-Disposition: form-data; name="' .$key. '"; filename="' .basename($filename). '"';
                $preparedBody[] = 'Content-Type: application/octet-stream';
                $preparedBody[] = '';
                $preparedBody[] = file_get_contents($filename);
            } else {

                $preparedBody[] = '--' . $boundary;
                $preparedBody[] = 'Content-Disposition: form-data; name="' . $key . '"';
                $preparedBody[] = '';
                $preparedBody[] = is_bool($value) ? json_encode($value) : $value;
            }
        }

        $preparedBody[] = '--' .$boundary. '--';
        $preparedBody[] = '';
        $content = join("\r\n", $preparedBody);

        return $content;
    }

    protected function buildBoundary()
    {
        return str_repeat('-', 28) . substr(md5('cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);
    }

    protected function convertPostDataToFlatArray($postData, $postKeyPrefix = null)
    {
        $preparedFields = [];

        foreach ($postData as $postKey => $postValue) {

            if ($postKeyPrefix) {
                $postKey = is_int($postKey) ? $postKeyPrefix . '[]'
                                            : $postKeyPrefix . '[' .$postKey. ']';
            }

            if (is_array($postValue)) {
                $preparedFields = array_merge($preparedFields,
                                              $this->convertPostDataToFlatArray($postValue, $postKey));
            } else {
                $preparedFields[] = array($postKey, $postValue);
            }
        }

        return $preparedFields;
    }

    // ########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param array $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * @param array $get
     */
    public function setGet($get)
    {
        $this->get = $get;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param array $cookies
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
    }

    // ----------------------------------------

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getResponseInfo()
    {
        return $this->responseInfo;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    public function getResponseCookies()
    {
        return $this->responseCookies;
    }

    // ########################################
}