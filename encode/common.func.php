<?php
function switch256to900($arr)  //将6个字节转换成5个码字,参数为包含最多6个元素的数组；
{
     $dec=0;
     $return = array();
    if (count($arr)<6) {
        return $arr;   //如果传递数组 不足6个元素，返回原来数组；
    }    if (count($arr)>6) {
        echo "参数错误！";
        exit();
    } //如果传递数组超过6个元素，错误提示，终止程序；
    foreach ($arr as $key => $val) {
        $dec = bcadd($dec, bcmul($val, Exponentiation(256, 5-$key)));
    }
    for ($i=0; $i < 5; $i++) {
        $return[$i]= bcmod($dec, 900);
        $dec= bcdiv($dec, 900);
    }
    return array_reverse($return);
}

function switch900to256($arr)  //将5个码字转换成6个字节,参数为包含最多5个元素的数组；
{
     $dec=0;
     $return = array();
    if (count($arr) < 5) {
        return $arr;   //如果传递数组 不足6个元素，返回原来数组；
    }    if (count($arr) > 5) {
        echo "参数错误！";
        exit();
    } //如果传递数组超过6个元素，错误提示，终止程序；
    foreach ($arr as $key => $val) {
        $dec = bcadd($dec, bcmul($val, Exponentiation(900, 4-$key)));
    }
    for ($i=0; $i < 6; $i++) {
        $return[$i]= bcmod($dec, 256);
        $dec= bcdiv($dec, 256);
    }
    return array_reverse($return);
}
 
function switch900to10($arr) //将15位900进制数字转换成45个10进制数,参数为包含最多15个元素的数组； 转换成10进制数后去掉前置1；
{
  // $str="000213298174000";  //测试数据；
  //$arr = str_split($str);
    $length = count($arr) - 1 ;
    foreach ($arr as $key => $val) {
        $dec = bcadd($dec, bcmul($val, Exponentiation(900, $length - $key)));
    }
    return substr($dec, 1);
}

function Exponentiation($x, $s) //幂运算,x的s次幂；
{
    $r=1.0;
    if ($x==1 || $x==0 || $s ==0) {
        return 1;
    }
    for ($i=0; $i<$s; $i++) {
        $r = bcmul($r, $x, 50);
    }
    return $r;
}

function is_number($str)
{
    if (strlen($str)>1) {
        $arr = str_split($str);
        $strint = '';
        foreach ($arr as $key => $value) {
            $strint .= intval($value).'';
        }
        if ($str == $strint) {
            return true;
        } else {
            return false;
        }
    } else {
        if (trim($str) === '') {
            return false;
        }
        $strint = intval($str).'';
        if ($str == $strint) {
            return true;
        } else {
            return false;
        }
    }
        
}

function asciitotc($str, $mode)
{
    
    $mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //混合型字符ASCII列表
    $Punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //标点型字符ASCII列表；
    $rep  = array(13,09,44,58,45,46,36,47,42); //混合型与标点型 重复字符列表；

    $mixcode  = array(0,1,2,3,4,5,6,7,8,9,10,15,20,21,23,24);
    $punccode = array(0,1,2,3,4,5,6,7,8,9,10,15,20,21,23,24,25,26,27,28);
    $repcode  = array(11,12,13,14,16,17,18,19,22);
    $ascii = ord($str);
    if ($mode == 'Alpha') {
        if ($ascii == 32) {
            $ret = 26;
        } else {
            $ret = ord($str) - 65 ;
        }
    } else if ($mode == 'Lower') {
        if ($ascii == 32) {
            $ret = 26;
        } else {
            $ret = ord($str) - 97 ;
        }
    } else if ($mode == 'Mix') {
        if ($ascii == 32) {
            $ret = 26;
        } else if (in_array($ascii, $mix)) {
            $key =  array_keys($mix, $ascii);
            $ret = $mixcode[$key['0']];
        } else if (in_array($ascii, $rep)) {
            $key =  array_keys($rep, $ascii);
            $ret = $repcode[$key['0']];
        }
    } else if ($mode == "Punc") {
        if (in_array($ascii, $Punc)) {
            $key =  array_keys($Punc, $ascii);
            $ret = $punccode[$key[0]];
        } else if (in_array($ascii, $rep)) {
            $key =  array_keys($rep, $ascii);
                $ret = $repcode[$key];
        }
    }
    return $ret;
}

function bushu($m)
{
    return 929 - $m;
}
function isright(&$str)
{
    $mode = "/11111111010101000(.*)111111101000101001/";
    preg_match($mode, $str, $match);
    $str = trim($match[1]);
    if (strlen($str) % 17 == 0) {
        $str = str_split($str, 17);
    } else {
        echo "码字 模块个数错误！";
        exit();
    }
}
function getmodefrombs($bs)
{
    $bs = explode();
}
function ErrorCorrection($codearray, $correctleval)  //数据，纠错级别；
{
     $array = array();
     $k = Exponentiation(2, $correctleval+1);    //纠错码个数；
     $c = array_pad($array, $k, 0);  //纠错码数组
     $t1 = $t2 = $t3 = 0;                       //临时变量
     $a = geta($correctleval);            //根据纠错级别，得出的g(x)展开后各项的系数；
    foreach ($codearray as $key => $data) {
        $t1 = ( $data + $c[$k - 1] ) % 929 ;
        // echo "    t1:$t1\n";
        foreach ($c as $keyc => $correct) {
            $t2 = ($t1 * $a[$k - $keyc - 1]) % 929;
            $t3 =  929 - $t2;
            if ($k - $keyc - 1 == 0) {
                $m = $t3;
            } else {
                $m = $c[$k - $keyc - 2] + $t3;
            }
            $c[$k - $keyc - 1] =  $m % 929;
            //echo "t2:".$t2."      t3:".$t3."        c[".($k - $keyc - 1)."]:".$c[$k - $keyc - 1]."\n";
        }
    }
    $c = array_map("bushu", array_reverse($c));
    //print_r($c);echo "<hr />";
    return $c;
}
