<?php

/**
 * Amazon plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Amazon extends SingletonWhipPlugin {
//  Newline
    const NEWLINE               = "\n";
//  Product search methods
    const SEARCH_METHOD_ARTIST  = 'Artist';
    const SEARCH_METHOD_KEYWORDS= 'Keywords';
    const SEARCH_METHOD_TITLE   = 'Title';
    private static $_search_methods = array(
        self::SEARCH_METHOD_ARTIST,
        self::SEARCH_METHOD_KEYWORDS,
        self::SEARCH_METHOD_TITLE,
    );
//  Product lookup methods
    const LOOKUP_METHOD_ASIN    = 'ASIN';
    const LOOKUP_METHOD_EAN     = 'EAN';
    const LOOKUP_METHOD_ISBN    = 'ISBN';
    const LOOKUP_METHOD_SKU     = 'SKU';
    const LOOKUP_METHOD_UPC     = 'UPC';
    private static $_lookup_methods = array(
        self::LOOKUP_METHOD_ASIN,
        self::LOOKUP_METHOD_EAN,
        self::LOOKUP_METHOD_ISBN,
        self::LOOKUP_METHOD_SKU,
        self::LOOKUP_METHOD_UPC,
    );
    
    /**
     * _categories
     * Product search categories
     *
     * More details available at:
     * http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/APPNDX_SearchIndexValues.html
     * 
     * @var mixed
     * @access private
     * @static
     */
    private static $_categories = array(
        'All',
        'Apparel',
        'Appliances',
        'ArtsAndCrafts',
        'Automotive',
        'Baby',
        'Beauty',
        'Blended',
        'Books',
        'Classical',
        'DigitalMusic',
        'DVD',
        'Electronics',
        'ForeignBooks',
        'GourmetFood',
        'Grocery',
        'HealthPersonalCare',
        'Hobbies',
        'HomeGarden',
        'HomeImprovement',
        'Industrial',
        'Jewelry',
        'KindleStore',
        'Kitchen',
        'Lighting',
        'Magazines',
        'Merchants',
        'Miscellaneous',
        'MobileApps',
        'MP3Downloads',
        'Music',
        'MusicalInstruments',
        'MusicTracks',
        'OfficeProducts',
        'OutdoorLiving',
        'Outlet',
        'PCHardware',
        'PetSupplies',
        'Photo',
        'Shoes',
        'Software',
        'SoftwareVideoGames',
        'SportingGoods',
        'Tools',
        'Toys',
        'UnboxVideo',
        'VHS',
        'Video',
        'VideoGames',
        'Watches',
        'Wireless',
        'WirelessAccessories',
    );
    
    /**
     * _response_groups
     * Specifies the types of values to return.
     * 
     * @var mixed
     * @access private
     * @static
     */
    private static $_response_groups = array(
        'Accessories',
        'BrowseNodes',
        'EditorialReview',
        'Images',
        'ItemAttributes',
        'ItemIds',
        'Large',
        'Medium',
        'OfferFull',
        'Offers',
        'OfferSummary',
        'PromotionSummary',
        'RelatedItems',
        'Reviews',
        'SalesRank',
        'Similarities',
        'Tracks',
        'VariationImages',
        'Variations',
        'VariationSummary',
    );
    
    /**
     * search function.
     * Amazon product search
     * 
     * @access public
     * @param mixed $keyword
     * @param string $category. (default: 'All')
     * @param mixed $method. (default: self::SEARCH_METHOD_KEYWORDS)
     * @param string $response_group. (default: 'Medium')
     * @return Object xml
     */
    public function search($keywords, $category='All', $method=self::SEARCH_METHOD_KEYWORDS, $response_group='Medium') {
    //  Check category
        if (!in_array($category, self::$_categories, true)) {
            throw new WhipPluginException('Invalid category specified');
        }
    //  Check search method
        if (!in_array($method, self::$_search_methods, true)) {
            throw new WhipPluginException(
                'Invalid search method specified. '.
                'Search method should be one of: Artist, Keywords, Title'
            );
        }
    //  Check response group
        if (!in_array($response_group, self::$_response_groups, true)) {
            throw new WhipPluginException('Invalid response group specified');
        }
    //  Search by artist, keywords or title
        $parameters = array(
            'Operation'     => 'ItemSearch',
            $method         => $keywords,
            'SearchIndex'   => $category,
            'ResponseGroup' => $response_group,
        );
    //  Query the Amazon API
        return $this->_request($parameters);
    }   //  function search
    
    
    /**
     * lookup function.
     * Look up a product by its ASIN, EAN, ISBN, SKU or UPC code.
     * 
     * @access public
     * @param string $item_id
     * @param string $category. (default: 'All')
     * @param string $method. (default: self::LOOKUP_METHOD_ISBN)
     * @param string $response_group. (default: 'Medium')
     * @return Object xml
     */
    public function lookup($item_id, $category='All', $method=self::LOOKUP_METHOD_ISBN, $response_group='Medium') {
    //  Check category
        if (!in_array($category, self::$_categories, true)) {
            throw new WhipPluginException('Invalid category specified');
        }
    //  Check lookup method
        if (!in_array($method, self::$_lookup_methods, true)) {
            throw new WhipPluginException(
                'Invalid lookup method specified. '.
                'Lookup method should be one of: ASIN, EAN, ISBN, SKU, UPC'
            );
        }
    //  Check response group
        if (!in_array($response_group, self::$_response_groups, true)) {
            throw new WhipPluginException('Invalid response group specified');
        }
    //  Look up the item
        $parameters = array(
            'IdType'        => $method,
            'ItemId'        => $item_id,
            'Operation'     => 'ItemLookup',
            'ResponseGroup' => $response_group,
            'SearchIndex'   => $category,
        );
    //  Query the Amazon API
        return $this->_request($parameters);
    }   //  function lookup
    
    /**
     * _request function.
     * 
     * @access private
     * @param array $params
     * @return Object xml
     */
    private function _request(array $params) {
    //  Configure request
        if (!isset($this->_config['region'])) {
            $this->_config['region'] = 'com';
        }
        $http   = array(
            'method'    => 'GET',
            'host'      => 'ecs.amazonaws.'.strtolower($this->_config['region']),
            'uri'       => '/onca/xml',
        );
        $params['Service']          = 'AWSECommerceService';
        $params['AWSAccessKeyId']   = $this->_config['public_key'];
        $params['Timestamp']        = gmdate('Y-m-d\TH:i:s\Z');
        $params['Version']          = '2009-03-31';
    //  Sort parameters by key.
    //  This is a necessary step in Amazon's request signing process.
        ksort($params);
    //  Urlencode parameters
        $query = array();
        foreach ($params as $param=>$value) {
            $query[] =
                $this->_encode($param).
                '='.
                $this->_encode($value);
        }   //  each param
    //  Prepare the request
        $string_to_sign =
            $http['method'].self::NEWLINE.
            $http['host'].self::NEWLINE.
            $http['uri'].self::NEWLINE.
            implode('&', $query);
    //  Sign and encode
        $signature = $this->_encode(
            base64_encode(
                hash_hmac('sha256', $string_to_sign, $this->_config['private_key'], true)
            )
        );
    //  Execute the request
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            'http://'.$http['host'].$http['uri'].'?'.implode('&', $query).'&Signature='.$signature
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
    //  Verify response
        if (false === $response) {
            throw new WhipPluginException('Could not retrieve results from Amazon');
        }
    //  Parse and verify Amazon XML
        $xml = @simplexml_load_string($response);
        if (!isset($xml->Items->Item->ItemAttributes->Title)) {
        //  XML is invalid.
        //  Check for error.
            if (isset($xml->Error->Message)) {
                throw new WhipPluginException($xml->Error->Message);
            }
            throw new WhipPluginException('Invalid XML received from Amazon');
        }
    //  Return valid XML object
        return $xml;
    }   //  function _request
    
    /**
     * _encode function.
     * URL encode a string for use with the Amazon API
     * 
     * @access private
     * @param string $string
     * @return string
     */
    private function _encode($string) {
        return str_replace('%7E', '~', rawurlencode($string));
    }   //  function _encode
    
    
}   //  class Amazon
