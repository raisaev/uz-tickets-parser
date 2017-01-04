<?php

namespace JJDecode;

class JJDecode
{
    protected $glob_var=null;

    function Decode($str){
        while(preg_match('#=\~\[\];\s*(.+?)\s*\=\{#is', $str, $mth)){
            $this->glob_var=$mth[1];
            $str=$this->ParseJs($str);
        }

        return $str;
    }

    /** Парсинг всех участков зашифрованных JJEncode
     *
     * @param string $str
     * @return string
     */
    protected function ParseJs($str){
        $preg='#'.preg_quote($this->glob_var, '#').'=\~\[\];\s*'.
            ''.preg_quote($this->glob_var, '#').'={\s*([\s|\S]+?)\s*};\s*'.
            '[\s|\S]+?'.
            '\s*'.preg_quote($this->glob_var).'.\$\('.preg_quote($this->glob_var, '#').'.\$\(([\s|\S]+?)\)\(\)\)\(\);#is';
        $newstr=preg_replace_callback($preg, array($this, 'ParseStr'), $str);
        return $newstr;
    }

    /** Функция колбека для ParseJs($str)
     *
     * @param array $mathes
     * @return string
     */
    private function ParseStr($mathes){
        $obufstr=$mathes[2];
        $alpha=$mathes[1];
        //Выделяем начальный алфавит
        $alphabet=$this->ParseAlphabet($alpha);
        if(!is_array($alphabet))return '';
        $alphabet=array_merge($alphabet, $this->ParseAlphabetAdd());
        //Деобусфицируем строку
        $newstr=$this->ParseObufStr($obufstr, $alphabet);
        //Приводим строку к нормальному виду (без escape последовательностей)
        $newstr=$this->ParseDecodeStr($newstr);
        return $newstr;
    }

    /** Очищаем строку от escape-последовательностей
     *
     * @param string $str деобфусцированная строка
     * @return string
     */
    protected function ParseDecodeStr($str){
        $str=preg_replace_callback('#\\\\([0-7]{1,3})#i', array($this, 'ParseDecodeStrCallback'), $str);
        return $str;
    }

    /** Колбэк для ParseDecodeStr. Преобразование каждой escape - последовательности с учетом восьмиричной системы
     *
     * @param array $mathes
     * @return string
     */
    private function ParseDecodeStrCallback($mathes){
        $int=$mathes[1];
        $add='';
        while(($dec=octdec($int))>255){
            $add=substr($int, -1);
            $int=substr($int, 0, -1);
            if(strlen($int)<1)break;
        }
        return chr(octdec($int)).$add;
    }

    /** Приведение обфусцированной строки к упрощенному виду для облегчения разделения на "слагаемые"
     *
     * @param string $str обфусцированная строка
     * @return string упрощенный вид
     */
    private function ParseObufStrRaw($str){
        $nstr='';
        $incnt=0;
        $quote=false;
        for($i=0, $x=strlen($str); $i<$x; $i++){
            $char=$str{$i};
            if($char!='+'){
                if($char=='"'){
                    $quote=!$quote;
                    $nstr.='x';
                    continue;
                }
                if($quote){
                    if($char=='\\'){
                        $i++;
                        $nstr.='xx';
                        continue;
                    }else{
                        $nstr.='x';
                        continue;
                    }
                }
                if(in_array($char, array('{', '[', '('))){
                    $incnt++;
                }elseif(in_array($char, array('}', ']', ')'))){
                    $incnt--;
                }
                $nstr.='x';
            }else{
                if($quote){
                    $nstr.='x';
                }else{
                    if($incnt==0){
                        $nstr.='+';
                    }else{
                        $nstr.='x';
                    }
                }
            }
        }
        $arr=array();
        $words=explode('+', $nstr);
        $pos=0;
        foreach($words as $word){
            $len=strlen($word);
            $arr[]=substr($str, $pos, $len);
            $pos+=($len+1);
        }
        return $arr;
    }

    /** Деобфусцирование строк
     *
     * @param string $str обфусцированная строка
     * @param array $alphabet алфавит
     * @return string
     */
    protected function ParseObufStr($str, $alphabet){
        $array=$this->ParseObufStrRaw($str);
        $nstr='';
        $unk=array();
        foreach($array as $val){
            $val=trim($val);
            if(empty($val))continue;
            if(preg_match('#^'.preg_quote($this->glob_var).'\.([\_\$]{1,4})$#i', $val, $mth)){
                if(array_key_exists($mth[1], $alphabet)){
                    $nstr.=$alphabet[$mth[1]];
                }else{
                    $unk[]=$val;
                    $nstr.='?';
                }
            }elseif(preg_match('#^"(.*)"$#i', $val, $mth)){
                $nstr.=str_replace('\"', '"', stripslashes($mth[1]));
            }elseif(preg_match('#\((.+)\)\['.preg_quote($this->glob_var).'\.([\_\$]{1,4})\]#i', $val, $mth)){
                if(array_key_exists($mth[2], $alphabet)){
                    if(strpos($mth[1], '![]+""')!==false){
                        $tmp='false';
                        $nstr.=$tmp{$alphabet[$mth[2]]};
                    }else{
                        $unk[]=$val;
                        $nstr.='?';
                    }
                }else{
                    $unk[]=$val;
                    $nstr.='?';
                }
            }else{
                $unk[]=$val;
                $nstr.='?';
            }
        }
        if(count($unk)>0){
        }
        if(preg_match('#return\s*"(.+)"#i', $nstr, $mth)){
            $nstr=$mth[1];
            return $nstr;
        }else{
            return false;
        }
    }

    protected function ParseAlphabetAdd(){
        return array(
            '$_'=>'constructor',
            '$$'=>'return',
            '$'=>'function Function() { [native code] }',
            '__'=>'t',
            '_$'=>'o',
            '_'=>'u',
        );
    }

    /** Парсинг участка с алфавитом
     *
     * @param string $str участок строки с алфавитом
     * @return array
     */
    protected function ParseAlphabet($str){
        if(!preg_match_all('#([\_|\$]{2,4})\:(.+?),#i', $str.',', $mth)){
            return false;
        }
        $newarr=array();
        $val_o=0;
        for($i=0, $x=count($mth[0]); $i<$x; $i++){
            $key=$mth[1][$i];
            $val=$mth[2][$i];
            if($val=='++'.$this->glob_var.''){
                $newarr[$key]=$val_o;
                $val_o++;
            }elseif(strpos($val, '(![]+"")')!==false){
                $tmp='false';
                $newarr[$key]=$tmp{($val_o-1)};
            }elseif(strpos($val, '({}+"")')!==false){
                $tmp='[object Object]';
                $newarr[$key]=$tmp{($val_o-1)};
            }elseif(strpos($val, '('.$this->glob_var.'['.$this->glob_var.']+"")')!==false){
                $tmp='undefined';
                $newarr[$key]=$tmp{($val_o-1)};
            }elseif(strpos($val, '(!""+"")')!==false){
                $tmp='true';
                $newarr[$key]=$tmp{($val_o-1)};
            }
        }
        if(count($newarr)!==16){
            return false;
        }
        return $newarr;
    }
}