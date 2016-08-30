<?php
 include('tc.php');
 include('bc.php');
 include('nc.php');
 include('image.php');
 include_once('common.func.php');
class makecode
{
	var $str, $length;       //需要编码的字符串,及字符串长度；
	var $row;       //行数；
	var $i;         //当前行数；
	var $s;         //纠错等级；
	var $cow;       //数据区列数；
	var $correct_c;  //纠错码字个数
	var $padding_c;  //填充码字个数；
	var $curstr ;      //字符当前位置标识；
	var $curmode;      //当前的压缩模式,0:数字压缩 1：文本压缩 2：字节压缩
	var $n ,$t, $b;   //$n 为连续数字个数 ； $t 为连续文本个数； $b 为连续字节个数； $p 指示当前位置； 
	var $data;        //数据区；
	var $nextmode = '';
	private $img;
	function __construct($string='', $s = -1) //需要编码的字符串，和纠错等级
	{
		//echo "<h1>需要编码的字符串：<br />".$string."</h1>";
		$this->str = $string;
		$this->length = strlen($this->str); 
		$this->s = $s;
		$this->strarray = str_split($string);
		$this->curstr = 0;          //读取标识 ；
		$this->curmode = 1;         //默认为文本压缩
		$this->img = new createimg();
		$this->data = $this->getdata();  //获得数据区数据码字,获得推荐纠错级别；
		$this->correct_c = Exponentiation(2,$this->s + 1); //计算纠错码字个数；
		$this->countrow();          //计算行数；
		$this->countpadinglength(); //计算填充字符个数；
		$this->getcorrect();        //计算纠错码字；
		$this->img->setrow($this->row, $this->cow, $this->s);   //生成图片实例；
		$this->createimg();
	}

	function countDataLength()   //计算码字个数； 包括：符号长度码字 + 数据区码字 + 纠错码字；
     {
       $count = count($this->data);
	   return $count + 1 + $this->correct_c; 
     }

     function countpadinglength()  //计算填充码字个数；
      {
		 $this->padding_c = $this->cow * $this->row - $this->countDataLength();
		 $str = "\r\n填充字符个数为：".$this->padding_c ; 
		 $str .= "\r\n纠错等级为：".$this->s ;
		 $str .= "\r\n行数：".$this->row ; 
		 $str .= "\r\n列数：".$this->cow ;
	     fwrite($this->img->f, $str);
      }
 
	function countrow()  //设置宽高比为3：1 ，根据数据码字个数计算宽，高；
	{
	   $row = intval(sqrt($this->countDataLength()));    //计算行数 
	   $cow =  $row;                                     //计算列数
	   if( $this->countDataLength() - $row * $row  > 0 ) $row +=  (intval(($this->countDataLength() - $cow * $cow  - 1) / $cow) + 1);  
	   if($row < 3) 
		{
		   $row = 3;
		   $cow = 3;
		}
	   else if($cow > 30) 
		   {
		      $cow = 30;
			  $row = (intval(($this->countDataLength() - 1) / $cow) + 1); 
		   }
      //echo "<br />row:".$row."   cow:".$cow."<br />"; exit();
	  $this->row = $row;
	  $this->cow = $cow; 
	}
	
	function getcorrect()
	{
		$count = count($this->data) + 1 + $this->padding_c;  //计算码字个数； 包括：符号长度码字 + 数据区码字 + 填充码字；
		array_unshift($this->data, $count);
		$this->data = array_pad($this->data, $count, 900);
		$this->data = array_merge($this->data, ErrorCorrection($this->data, $this->s));
		foreach($this->data as $key => $value)
			$data[$key/$this->cow][] = $value; 
		return $this->data = $data;
	 
	}

   function createimg()
	{
	   // print_r($this->data); 
	   $str = "\r\n\r\n编码内容为：".$this->str; 
	   fwrite($this->img->f, $str);
	   foreach($this->data as $key)
		{
		    fwrite($this->img->f, "\r\n");
			foreach($key as $value)
				fwrite($this->img->f, $value."   ");
		}
	   $str = "\r\n\r\n转换成01序列为:"; 
	   fwrite($this->img->f, $str);
	   foreach($this->data as $i => $row )
		{
		   $this->img->leftline($i + 1);
		   foreach ($row as $key => $value)
			{
			    $this->img->makepic(getbs($i % 3 *3, $value));
			}
		   $this->img->rightline($i + 1);
		   //exit();
		}
		fclose($this->img->f);
	    $this->img->echoimg();
	}
	
    //返回当前字符,若为多个字符，则为字符数组；$setp 为读取字符个数；reread = 0表示不是预读(即：指示符curstr不增加) ,$start为开始读取位置；
	function getchar($step = 1, $reread = 0 , $start = -1 )
	{
		if($start == -1 ) $start = $this->curstr;
		if(($start + $step) > strlen($this->str))  return false;  
		for($i = $start; $i < $start + $step; $i++)
		   $ret .= $this->strarray[$i];
        if(!$reread)
		{
		   $this->curstr += $step;
		} 
        return $ret;
	}

	function getdata()
	{
		$code = array();
		$p = 0;  //已编码位置标识
		while($p < $this->length && count($code) < 900)  //判断是否到最后；
		{
			$this->n = 0; $this->t = 0; $this->b = 0; 
			//echo "curstr:".$this->curstr;
			//$n 为连续数字个数 ； $t 为连续文本个数； $b 为连续字节个数； 
			while( $this->curstr < $this->length & is_number($this->getchar()))
			   $this->n++;  
			if($this->n >= 13) 
				{
				if($this->curmode != 0) 
					{
						$this->curmode = 0;
						$code[] = 902;
					}
				
				$ncarr = new Nccode($this->getchar($this->n,1,$p));
				$code = array_merge($code, $ncarr->getcode());
				$str = "\r\nNC模式编码 ：".$this->getchar($this->n,1,$p)."\r\n"; 
				$result = "编码结果： ".var_export($ncarr->getcode(),TRUE)."\r\n";
	            fwrite($this->img->f, $str.$result);
				$p += $this->n;
				$this->curstr = $p;
				}
			else 
				{
					$this->curstr = $p; $this->t = 0;
					while( $this->curstr < $this->length & $this->is_text($this->getchar()))
						$this->t ++;
					if($this->t >= 5)
					{
						if(!($this->curmode == 1)) 
							{
								$this->curmode = 1;
								$code[] = 900;
							}
						
						$tcarr = new Tccode($this->getchar($this->t,1,$p));
						$tcresult = $tcarr->getcode();
						$str = "\r\nTC模式编码 ：".$this->getchar($this->t,1,$p)."\r\n"; 
						$result = "编码结果： ".var_export($tcresult,TRUE)."\r\n";
						$code = array_merge($code, $tcresult);
						fwrite($this->img->f, $str.$result);
						$p += $this->t;
						$this->curstr = $p;	
					}
					else
					{ 
						$this->curstr = $p;
						while($this->curstr < $this->length & $this->is_bc($this->getchar()) )
							$this->b ++;
						if($this->b == 1 & $this->curmode == 1 )
						{
							$code[] = 913;  
						}
						else 
						{
							if($this->curmode != 2) 
								{
									$this->curmode = 2;
									if($this->b % 6 ) $code[] = 901;
									else $code[] = 924;
								}
						}
						$bcarr = new Bccode($this->getchar($this->b,1,$p));
						$code = array_merge($code, $bcarr->getcode());
						$str = "\r\nBC模式编码 ：".$this->getchar($this->b,1,$p)."\r\n"; 
						$result = "编码结果： ".var_export($bcarr->getcode(),TRUE)."\r\n";
						fwrite($this->img->f, $str.$result);
						$p += $this->b;
						$this->curstr = $p;
					}
				}
		}
 
        if(count($code) > 898 ) 
		{
			$beyond  = count($code) - 898;
			for($i =1; $i <=$beyond; $i++ )
			{
				array_pop($code);
			}
		}	 
		if( $this->s == -1 ) $this->s = $this->gets($code);
	    return $code;		
	}
	function gets($data)
	{
		 $count = count($data);
		 if($count < 40 ) $s = 2;
		 else if( $count < 160 ) $s = 3;
		 else if( $count < 320 ) $s = 4;
		 else if( $count < 863 ) $s = 5;
		 else $s = 6;
		 return $s;
	}

 
	function is_text($str)
	{
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //混合型字符ASCII列表
		$punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //标点型字符ASCII列表；
		$rep  = array(13,09,44,58,45,46,36,47,42,32); //混合型与标点型 重复字符列表； 另外加32（空格）
		$alpha = range(65,90);
		$lower = range(97,122);
		if(is_number($str)) 
		{
			if(is_number($this->getchar(12,1))) return false;  
		}
		
		$ascii = ord($str);
		if(($ascii >= 65 & $ascii <= 90) || ($ascii >= 97 & $ascii <= 122) || in_array($ascii, $mix) || in_array($ascii, $punc) || in_array($ascii, $rep)) 
		   return true;  
		else  return false;  
	}


	function is_bc($str)
	{
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //混合型字符ASCII列表
		$punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //标点型字符ASCII列表；
		$rep  = array(13,09,44,58,45,46,36,47,42,32); //混合型与标点型 重复字符列表； 另外加32（空格）
		$alpha = range(65,90);
		$lower = range(97,122);
		$text = array_merge($mix, $punc, $rep, $alpha, $lower);
		if(is_number($str)) 
		{
			if(is_number($this->getchar(12,1))) { $this->nextmode = 'NC'; return false; } 
		}
		if(in_array(ord($str), $text))
		{
			
			$this->t = 1;  
			$p = $this->curstr;
			for($i = 1; $i < 5; $i ++)
			{
				if($this->curstr < $this->length)
				{
					$st = $this->getchar();
					if(in_array(ord($st), $text) )
					{
						$this->t++;
					}
					if(is_number($st))
					{
						if(is_number($this->getchar(12,1))) 
							{ $this->t--; break; } 
					}
				}
			}
			$this->curstr = $p;
			if($this->t++ >= 5 )
				{
					$this->nextmode = 'TC'; return false; 
			    }
			else return true;
		}
		else  return true;
	} 
}

if($_POST['submit'] & $_POST['cont']) 
	{
		$cont = $_POST['cont']; 
		echo "<title>PDF417编码成功！</title>";
		$code = new makecode($cont, 1);
	}
	else 
	{
		header("location:./index.html");
	}
?>
