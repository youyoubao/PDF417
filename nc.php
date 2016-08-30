<?php
class Nccode
{
	var $str;
	function __construct($str)
	{
		$this->str = $str;
	}
	function getcode()
	{
	  $codeswitch = array();
      $arr = str_split($this->str);
	  foreach($arr as $key => &$value)
		{
		  $code[intval($key / 44)][] = $value;
		}
	 foreach($code as $key=>$value)
		$codeswitch = array_merge($codeswitch, switch10to900($value));
	 //echo "<hr />NC code  str:{$this->str}<br />";
	 //print_r($codeswitch);
	 return $codeswitch;
	 //echo "<hr />";
	}
}
 
?>