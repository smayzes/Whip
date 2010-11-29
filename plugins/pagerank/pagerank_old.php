<?php

/**
 * Pagerank plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Pagerank extends SingletonWhipPlugin {
//  Google's magic number
    const GOOGLE_MAGIC = 0xE6359A60;
    const CRLF = "\r\n";

	private static function _zero_fill($a, $b){
		$z = hexdec(80000000);
		if ($z & $a){
			$a = ($a>>1);
			$a &= (~$z);
			$a |= 0x40000000;
			$a = ($a>>($b-1));
		}else
			$a = ($a>>$b);
		return $a;
	}  //  function _zero_fill

	private static function _mix($a,$b,$c){
		$a -= $b; $a -= $c; $a ^= (self::_zero_fill($c,13));
		$b -= $c; $b -= $a; $b ^= ($a<<8);
		$c -= $a; $c -= $b; $c ^= (self::_zero_fill($b,13));
		$a -= $b; $a -= $c; $a ^= (self::_zero_fill($c,12));
		$b -= $c; $b -= $a; $b ^= ($a<<16);
		$c -= $a; $c -= $b; $c ^= (self::_zero_fill($b,5));
		$a -= $b; $a -= $c; $a ^= (self::_zero_fill($c,3));
		$b -= $c; $b -= $a; $b ^= ($a<<10);
		$c -= $a; $c -= $b; $c ^= (self::_zero_fill($b,15));
		return array($a,$b,$c);
	}  //  function _mix

	private static function _google_ch($url, $length=null, $init=self::GOOGLE_MAGIC){
		if(is_null($length))
			$length = sizeof($url); 
		$a = $b = 0x9E3779B9;
		$c = $init;
		$k = 0;
		$len = $length;
		while($len >= 12){
			$a += ($url[$k+0] +($url[$k+1]<<8) +($url[$k+2]<<16) +($url[$k+3]<<24));
			$b += ($url[$k+4] +($url[$k+5]<<8) +($url[$k+6]<<16) +($url[$k+7]<<24));
			$c += ($url[$k+8] +($url[$k+9]<<8) +($url[$k+10]<<16)+($url[$k+11]<<24));
			$_mix = self::_mix($a,$b,$c);
			$a = $_mix[0]; $b = $_mix[1]; $c = $_mix[2];
			$k += 12;
			$len -= 12;
		}
		$c += $length;
		switch($len){
			case 11: $c+=($url[$k+10]<<24);
			case 10: $c+=($url[$k+9]<<16);
			case 9 : $c+=($url[$k+8]<<8);
			case 8 : $b+=($url[$k+7]<<24);
			case 7 : $b+=($url[$k+6]<<16);
			case 6 : $b+=($url[$k+5]<<8);
			case 5 : $b+=($url[$k+4]);
			case 4 : $a+=($url[$k+3]<<24);
			case 3 : $a+=($url[$k+2]<<16);
			case 2 : $a+=($url[$k+1]<<8);
			case 1 : $a+=($url[$k+0]);
		}
		$_mix = self::_mix($a,$b,$c);
		return $_mix[2];
	}  //  function _google_ch

	private static function _strord($string){
		for($i=0;$i<strlen($string);$i++)
			$result[$i] = ord($string{$i});
		return $result;
	}  //  function _strord
	

	/**
	 * get function.
	 *
	 * Gets a site's pagerank
	 * 
	 * @access public
	 * @param mixed $url
	 */
	public function get($url){
        
        
        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }
        
	   
		$pagerank = -1;
		$ch = '6'.self::_google_ch(self::_strord('info:'.$url));
		$fp = fsockopen('toolbarqueries.google.com', 80, $errno, $errstr, 30);
		if($fp){
			$out =
    			'GET /search?client=navclient-auto&ch='.$ch.'&features=Rank&q=info:'.$url.' HTTP/1.1'.self::CRLF.
    			'Host: www.google.com'.self::CRLF.
    			'Connection: Close'.self::CRLF.self::CRLF;
			fwrite($fp, $out);
			while (!feof($fp)){
				$data = fgets($fp, 512);
				$pos = strpos($data, 'Rank_');
				if($pos !== false) {
				    $pagerank = substr($data, $pos + 9);
				}
			}
			fclose($fp);
		}
		return $pagerank;
	}  //  function get
    
}   //  class Pagerank

