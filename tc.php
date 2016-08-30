<?php

class Tccode
{
	var $str;  //需要使用文本压缩的字符串；
	var $cur;  //当前压缩位置；
	var $length; //字符串长度
    var $Tccurmode; //当前子模式；取值范围 Alpha , Lower ,Mix , Punc
	function __construct($str)
	{
		$this->str = str_split($str);
		$this->string = $str;
		$this->cur = 0;
		$this->length = count($this->str);
		$this->Tccurmode = 'Alpha';   //设置TC压缩的默认子模式为大写字母型；
	}

	//返回字符串
	function getnextchar($step = 1, $reread = 0)
	{
		$ret = array();
		//echo $this->str[0];
		for($i = $this->cur; $i < $this->cur + $step; $i ++)
		   $ret[] = $this->str[$i];
        if(!$reread)
		{
		   $this->cur += $step;
		}
		return implode('', $ret);
	}
	function getcode()
	{
	   //print_r($this->str);
	   $Tcarr = array();
       while( $this->cur < $this->length )
		{
		  $str = $this->getnextchar();  //读取1个字符；
		  $child = $this->getChildMode($str); //获取字符子模式；
          if( $child  == $this->Tccurmode )  $Tcarr[] = asciitotc($str,$child);   //和当前子模式相同
		  else  
			{
			  $switch = $this->getSwitchSymbol( $this->Tccurmode , $child );  //获取切换符数组，数组包括：切换符（有可能为多个，），锁定或非锁定
			  if(@array_pop($switch) == 1 )  //锁定；
				{
				  $this->Tccurmode = $child;
				}
				$Tcarr = array_merge($Tcarr, $switch);
				$Tcarr[] = asciitotc($str,$child);
			} 
		}
		// print_r($Tcarr);
		if(count($Tcarr)%2) $Tcarr[] = 29; 
		for($i = 0; $i < count($Tcarr)/2; $i++)
		{
			$code[$i] = $Tcarr[$i*2] * 30 + $Tcarr[$i * 2 + 1];
		}
		//echo "<hr />TC code str:{$this->string}<br />";
	 // print_r($code);  
	  return $code;
	}
 
    //返回一个字符的子模式; 由于标点型字符出现较少，当一个字符同时属于混合型和标点型时，将字符纳入混合型。
	function getChildMode($str) 
	{
		$return = '';
		$ascii = ord($str);
		//echo $str."  ascii:".$ascii."  ";
		//echo "curmode:".$this->Tccurmode."  ";
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //混合型字符ASCII列表
		$Punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //标点型字符ASCII列表；
		$rep  = array(13,09,44,58,45,46,36,47,42); //混合型与标点型 重复字符列表；
		if( ($ascii >= 65 & $ascii <= 90) ||  ($this->Tccurmode == 'Alpha' & $ascii == 32 )  )  $return = "Alpha";  
		else if( ($ascii >= 97 & $ascii <= 122) || ($this->Tccurmode =='Lower' & $ascii== 32))  $return = "Lower";  
		else if( in_array($ascii,$mix) || in_array($ascii,$rep) || ($this->Tccurmode =='Mix' & $ascii== 32))
		   $return = "Mix";  
		else if(in_array($ascii,$Punc))
		   $return = "Punc"; 
		else if( $ascii == 32 ) return "Lower";
		else $return = "No"; 
		 return $return;
	}


	
    //返回从子模式1 切换到子模式2需添加的切换字符数组；数组格式：
	// array (
    //         切换符1，
	//         切换符2，。。
	//         锁定   )  (锁定值： 1：锁定   0：转移   2：Error)
	function getSwitchSymbol ( $mode1 , $mode2 ) 
	{
		$curmode = array();
		$lock = 1;   //默认为模式锁定
	  if( $mode1 == 'Alpha' )  //大写子母型子处理过程
		{
           if(  $mode2 == "Lower" ) $curmode[] = 27;
		   else if( $mode2 == "Mix" ) $curmode[] = 28;
		   else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //预读取1个字符；
		       $child = $this->getChildMode($str); //获取字符子模式；
			   if( $child  == "Punc" )
				{
				   $curmode[] = 28;
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] =29;
					$lock = 0;    //表示模式转换；
				}
			}
		   else  $curmode[] = "error";
		   $curmode[] = $lock;
		   return $curmode;
		}
		else if( $mode1 == 'Lower')  //小写子母型子处理过程
		{
		   if(  $mode2 == "Mix" ) $curmode[] = 28;
		   else if( $mode2 == "Alpha" )
			{
               $str = $this->getnextchar(1,1);  //预读取1个字符；
		       $child = $this->getChildMode($str); //获取字符子模式；
			   if( $child  == "Alpha" )
				{
				   $curmode[] = 28;  
				   $curmode[] = 28;   //表示模式锁定；
				}
				else 
				{
					$curmode[] =27;
					$lock = 0;    //表示模式转换；
				}
			}
		   else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //预读取1个字符；
		       $child = $this->getChildMode($str); //获取字符子模式；
			   if( $child  == "Punc" )
				{
				   $curmode[] = 28;
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] =29;
					$lock = 0;    //表示模式转换；
				}
			}
		   else  $curmode[] = 2;
		   $curmode[] = $lock;
		   return $curmode;
		}
		else if( $mode1 == 'Mix') //混全型子处理过程
		{
			if(  $mode2 == "Lower" ) $curmode[] = 27;
			else if ( $mode2 == "Alpha" ) $curmode[] = 28;
			else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //预读取1个字符；
		       $child = $this->getChildMode($str); //获取字符子模式；
			   if( $child  == "Punc" )
				{
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] = 29;
					$lock = 0;    //表示模式转换；
				}
			}
			else  $curmode[] = "no switch!";
			$curmode[] = $lock;
		    return $curmode;
		}
		else if( $mode1 == 'Punc') //标点型子处理过程
		{
			 $curmode[] = 29;
			 if ( $mode2 == "Alpha" ) ;
			 else if($mode2 == "Lower") $curmode[] = 27;
			 else if( $mode2 == "Mix" ) $curmode[] = 28;
			 else  $curmode[] = "no switch!";
			 $curmode[] = $lock;
		     return $curmode;
		}
	}
}

?>