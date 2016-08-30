<?php
include('common.func.php');
class Bccode
{
	var $str;    //ะ่าชBCันห๕ตฤืึท๛ดฎฃป
	function __construct($str)
	{
	  $this->str = $str;
	}
	function getcode()
	{
	  $codeswitch = array();
      	  $arr=str_split($this->str);
	  $code = array();
	  foreach($arr as $key=>&$value)
		{
		  $value=ord($value);
		  $code[intval($key / 6)][]=$value;
		} 
	 foreach($code as $key=>$value)
		$codeswitch = array_merge($codeswitch, switch256to900($value));
	  return $codeswitch;
	}
}
?>