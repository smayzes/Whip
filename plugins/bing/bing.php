<?php
/**
 * Bing plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Bing extends SingletonWhipPlugin {
	
	
	
	
	
	/**
	 * search_images function.
	 * 
	 * @access public
	 * @param mixed $q
	 * @param int $limit. (default: 10)
	 * @param string $adult. (default: 'Off')
	 * @param mixed $filters. (default: null)
	 * @param mixed $market. (default: null)
	 * @return void
	 */
	public function search_images($q, $limit=10, $adult='Off', $filters=null, $market=null) {
	//	Check if user/pass are set
		if (!isset($this->_config['username']) OR !isset($this->_config['key'])) {
			throw new WhipPluginException('Bing plugin not configured with your username and key.');
		}
	//	Build url
		$url =
			'https://api.datamarket.azure.com/Data.ashx/Bing/Search/Image'.
				'?Query=%27'.urlencode($q).'%27'.
				'&$top='.$limit.
				'&$format=Json';
		if (null !== $adult) {
			$url .= '&Adult=%27'.urlencode($adult).'%27';
		}
		if (null !== $filters) {
			$url .= '&ImageFilters=%27'.urlencode($filters).'%27';
		}
				
	//	Download
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->_config['username'].':'.$this->_config['key']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$data = curl_exec($ch);
		curl_close($ch);
	//	Get data and decode JSON
		$json = json_decode($data);
		if (!isset($json->d) OR !isset($json->d->results)) {
			throw new WhipPluginException('Incorrect data response from Bing');
		}
		return $json->d->results;
	}	//	function search_images
	
	

}	//	class Bing

