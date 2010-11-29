<?php

/**
 * Pagerank plugin.
 *
 * Based on Emre Odabas' PageRank class,
 * which in turn was based on Raistlin Majere's google_pagerank function
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends SingletonWhipPlugin
 */
class Pagerank extends SingletonWhipPlugin {
//  8 second timeout
    const TIMEOUT = 8;
    const CRLF = "\r\n";
//  Private variables
    private $google_domains = Array(
        'toolbarqueries.google.com',
        'www.google.com',
        '64.233.187.99',
        '72.14.207.99',
    );
    private $user_agent = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20041107 Firefox/3.0';
    private $page_rank = -1;
    
    
    /**
     * get function.
     *
     * Get the PageRank value for an URL.
     * 
     * @access public
     * @param mixed $url
     * @return void
     */
    public function get($url) {
        $result = array('', -1);
    //  TODO: check if url is an url
    
        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

    //  check for protocol
        $url_ = $url;
        $host = $this->google_domains[mt_rand(0,count($this->google_domains)-1)];
        $target = '/search';
        $querystring = sprintf(
            "client=navclient-auto&ch=%s&features=Rank&q=%s",
            $this->check_hash($this->hash_url($url_)),urlencode("info:".$url_)
        );
        $contents = '';
        
    //  Grab PageRank from Google
        if (@function_exists('curl_init')) {
        //  use curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://'.$host.$target.'?'.$querystring);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
            if (!($contents = trim(@curl_exec($ch)))) {
                return false;
            }
            curl_close ($ch);
        } else {
        //  use sockets
            if ($socket  = @fsockopen($host, 80, $errno, $errstr, self::TIMEOUT)) {
                $request =
                    'GET '.$target.'?'.$querystring.' HTTP/1.0'.self::CRLF.
                    'Host: '.$host.self::CRLF.
                    'User-Agent: '.$this->user_agent.self::CRLF.
                    'Accept-Language: en-us, en;q=0.50'.self::CRLF.
                    'Accept-Charset: ISO-8859-1, utf-8;q=0.66, *;q=0.66'.self::CRLF.
                    'Accept: text/xml,application/xml,application/xhtml+xml,text/html;'.
                        'q=0.9,text/plain;q=0.8,video/x-mng,image/png,image/jpeg,image/gif;'.
                        'q=0.2,text/css,*/*;q=0.1'.self::CRLF.
                    'Connection: close'.self::CRLF.
                    'Cache-Control: max-age=0'.self::CRLF.self::CRLF;
                stream_set_timeout ( $socket, self::TIMEOUT);
                fwrite( $socket, $request );
                $ret = '';
                while (!feof($socket)) {
                    $ret .= fread($socket, 4096);
                }
                fclose($socket);
                $contents = trim(substr($ret,strpos($ret, self::CRLF.self::CRLF) + 4));
            } else {
            //  TODO: throw exception
                throw new WhipPluginException('Cannot open socket');
                return false;
            }
        }
        $result[0]=$contents;
        // Rank_1:1:0 = 0
        // Rank_1:1:5 = 5
        // Rank_1:1:9 = 9
        // Rank_1:2:10 = 10 etc
        $p = explode(":",$contents);
        if (isset($p[2])) $result[1]=$p[2];

        if($result[1] == -1) $result[1] = 0;
        $this->page_rank = (int)$result[1];
        return $this->page_rank;
    }   //  function get
    
    
    
	/**
	 * str_to_num function.
	 *
	 * convert a string to a 32-bit integer
	 * 
	 * @access private
	 * @param mixed $Str
	 * @param mixed $Check
	 * @param mixed $Magic
	 */
	private function str_to_num($Str, $Check, $Magic) {
		$Int32Unit = 4294967296;  // 2^32
		$length = strlen($Str);
		for ($i = 0; $i < $length; $i++) {
			$Check *= $Magic; 	
			//If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
			//  the result of converting to integer is undefined
			//  refer to http://www.php.net/manual/en/language.types.integer.php
			if ($Check >= $Int32Unit) {
				$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
				//if the check less than -2^31
				$Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
			}
			$Check += ord($Str{$i}); 
		}
		return $Check;
	}  //  function str_to_num
	

	/**
	 * hash_url function.
	 *
	 * genearate a hash for a url
	 * 
	 * @access private
	 * @param mixed $String
	 */
	private function hash_url($String) {
		$Check1 = $this->str_to_num($String, 0x1505, 0x21);
		$Check2 = $this->str_to_num($String, 0, 0x1003F);
		$Check1 >>= 2; 	
		$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
		$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
		$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);	
		
		$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
		$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
		
		return ($T1 | $T2);
	}  //  function hash_url
	
	
	/**
	 * check_hash function.
	 *
	 * genearate a checksum for the hash string
	 * 
	 * @access private
	 * @param mixed $Hashnum
	 */
	private function check_hash($Hashnum) {
		$CheckByte = 0;
		$Flag = 0;
		$HashStr = sprintf('%u', $Hashnum) ;
		$length = strlen($HashStr);
		for ($i = $length - 1;  $i >= 0;  $i --) {
			$Re = $HashStr{$i};
			if (1 === ($Flag % 2)) {			  
				$Re += $Re;	 
				$Re = (int)($Re / 10) + ($Re % 10);
			}
			$CheckByte += $Re;
			$Flag ++;	
		}
		$CheckByte %= 10;
		if (0 !== $CheckByte) {
			$CheckByte = 10 - $CheckByte;
			if (1 === ($Flag % 2) ) {
				if (1 === ($CheckByte % 2)) {
					$CheckByte += 9;
				}
				$CheckByte >>= 1;
			}
		}
		return '7'.$CheckByte.$HashStr;
	}  //  function check_hash
    
}   //  class Pagerank
