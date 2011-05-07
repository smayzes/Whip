<?
/**
 *  Pagination plugin.
 *
 *  Whip : A non-restrictive PHP framework
 *  Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 *  Released under the GNU General Public License, Version 3
 *
 *  Configuration options:
 *      - max_display
 *      - max_adjecent
 *      - max_outside
 *      - results_per_page
 *
 *      - url
 *      - num_results
 *
 * @extends WhipPlugin
 */
class Pagination extends WhipPlugin {
    private $page           = 0;
    private $num_pages      = 0;
    private $is_ready       = false;
    private $pages          = array();
    
    /**
     * __construct function.
     *
     * Prevent users from instantiating the plugin directly
     * 
     * @access private
     * @return void
     */
    protected function __construct($config=null) {
    //  merge configuration array
        $this->set_config($config);
        $this->generate();
    }   //  __construct


/*
**  Constructor
*/
/*
	public function __construct(Whip &$whip, $num_results=0, $page=0, $url='') {
        global $config;
    #   Set config variables if available
        if (isset($config['pagination']) && is_array($config['pagination'])) {
            foreach ($config['pagination'] as $key=>$value) {
                $this->_config[$key] = $value;
            }
        }   //  if config
    #   Set total number of results and page if provided
        $this->_config['num_results']      = $num_results;
        $this->url              = $url;
        if ($page>0) {
            $this->page             = $page;
        }   //  if page
    #   Set Whip
        parent::__construct($whip);
	}   //  construct
*/
  
/*
**  Property get
*/
    public function __get($property) {
    #   Make sure pages array has been generated
        if (!$this->is_ready) {
            $this->_generate();
        }
    #   Return the requested property
        switch($property) {
        case 'prev':
        case 'previous':
        #   Get the previous page (or false)
            if ($this->page>1) {
                return $this->page-1;
            }
            else {
                return false;
            }
            break;
            
        case 'next':
        #   Get the next page (or false)
            if ($this->page<$this->num_pages) {
                return $this->page+1;
            }
            else {
                return false;
            }
            break;
            
        case 'page':
        #   Get current page
            return $this->_config['page'];
            break;
            
        case 'pages':
        #   Get array of pages
            return $this->pages;
            break;
        
        case 'num_pages':
        #   Get number of pages
            return $this->num_pages;
            break;
            
        case 'num_results':
        #   Get number of results
            return $this->_config['num_results'];
            break;
            
        case 'results_per_page':
        case 'sql_limit':
        case 'limit':
        #   Results per page
        #   (SQL limit)
            return $this->_config['results_per_page'];
            break;
            
        case 'sql_offset':
        case 'offset':
        #   SQL offset
            return ($this->_config['results_per_page'] * ($this->page-1));
            break;
            
        case 'url':
        #   Get base url
            return $this->_config['url'];
            break;
            
        case 'display_from':
        #   "Displaying results 1 to 9 of 373"
            return ($this->_config['results_per_page'] * ($this->page-1)) + 1;
            break;
            
        case 'display_to':
        #   "Displaying results 1 to 9 of 373"
            $to     = ($this->_config['results_per_page'] * $this->page) + 1;
            if ($to > $this->_config['num_results']) {
                $to     = $this->_config['num_results'];
            }
            return $to;
            break;
            
            
        }   //  switch property
    }   //  __get
    
    public function __isset($name) {
        $properties = array(
            'prev',
            'previous',
            'next',
            'page',
            'pages',
            'num_pages',
            'num_results',
            'results_per_page',
            'sql_limit',
            'limit',
            'sql_offset',
            'offset',
            'url',
            'display_from',
            'display_to',
        );
        if (in_array($name, $properties)) {
            return true;
        }
        return false;
    }

/*
**  Property set
*/
    public function __set($property, $value) {
        switch($property) {
        case 'page':
        #   Set current page
            if ($value > 1 && $value <= $this->num_pages) {
                $this->page = $value;
            }
            $this->is_ready = false;
            break;
            
        case 'num_results':
            $this->_config['num_results'] = $value;
            $this->is_ready = false;
            break;
        
        case 'results_per_page':
        case 'sql_limit':
        case 'limit':
        #   Set results per page
            if ($value > 1) {
                $this->_config['results_per_page'] = $value;
            }
            $this->is_ready = false;
            break;
            
        case 'url':
        #   Set base url
            $this->url = $value;
            break;
            
        }   //  switch property
    }   //  __set
    
    
/*
**  Generate the pages array
*/
    public function generate() {
    #   Generate paging
        $this->pages = array();
        $this->num_pages = ceil($this->_config['num_results'] / $this->_config['results_per_page']);
    #   Empty paging?
        if ($this->num_pages<=1) {
            $this->num_pages = 1;
            $this->pages[] = 1;
            $this->_config['page'] = 1;
        #   READY!
            $this->is_ready = true;
            return;
        }
    #   Page must be in range
        if (!is_numeric($this->_config['page'])) {
            $this->_config['page'] = 1;
        }
        elseif ($this->_config['page']<1) {
            $this->_config['page'] = 1;
        }
        elseif ($this->_config['page']>$this->num_pages) {
            $this->_config['page'] = $this->num_pages;
        }
    #   Left segment
        $page = 1;
        while (
            $page<=$this->_config['max_outside']
            &&
            $page<$this->num_pages
        ) {
            $this->pages[] = $page;
            ++$page;
        }
        if ($this->_config['page']>$this->_config['max_outside']) {
        #   Middle segment
            
            if ($page < ($this->_config['page']-$this->_config['max_adjecent']-1)) {
                $this->pages[] = false;
                $page = $this->_config['page']-$this->_config['max_adjecent'];
            }
            while ($page<=($this->_config['page']+$this->_config['max_adjecent']) && $page<$this->num_pages) {
                $this->pages[] = $page;
                ++$page;
            }
        }
        else {
        #   Longer left segment
            while (
                $page<=(max($this->_config['page']+$this->_config['max_adjecent'],$this->_config['max_outside']))
                &&
                $page<$this->num_pages
            ) {
                $this->pages[] = $page;
                ++$page;
            }
        }
    #   Right segment
        if ($page<($this->num_pages-$this->_config['max_outside'])) {
            $this->pages[] = false;
            $page = $this->num_pages-$this->_config['max_outside']+1;
        }
        while ($page<=$this->num_pages) {
            $this->pages[] = $page;
            ++$page;
        }
    #   READY!
        $this->is_ready = true;
    }   //  function generate
    
    
    
/*
**  Generate the pages array
*/
/*
    private function _generate() {
    #   Generate paging
        $this->pages        = array();
        $this->num_pages    = ceil($this->_config['num_results'] / $this->_config['results_per_page']);
    #   Empty paging?
        if ($this->num_pages<=1) {
            $this->num_pages    = 1;
            $this->pages[]      = 1;
            $this->page         = 1;
        #   READY!
            $this->is_ready     = true;
            return;
        }
    #   Page must be in range
        if (!is_numeric($this->page)) {
            $this->page = 1;
        }
        elseif ($this->page<1) {
            $this->page = 1;
        }
        elseif ($this->page>$this->num_pages) {
            $this->page = $this->num_pages;
        }
    #   Left segment
        $page = 1;
        while (
            $page<=$this->_config['max_outside']
            &&
            $page<$this->num_pages
        ) {
            $this->pages[] = $page;
            ++$page;
        }
        if ($this->page>$this->_config['max_outside']) {
        #   Middle segment
            
            if ($page < ($this->page-$this->_config['max_adjecent']-1)) {
                $this->pages[] = false;
                $page = $this->page-$this->_config['max_adjecent'];
            }
            while ($page<=($this->page+$this->_config['max_adjecent']) && $page<$this->num_pages) {
                $this->pages[] = $page;
                ++$page;
            }
        }
        else {
        #   Longer left segment
            while (
                $page<=(max($this->page+$this->_config['max_adjecent'],$this->_config['max_outside']))
                &&
                $page<$this->num_pages
            ) {
                $this->pages[] = $page;
                ++$page;
            }
        }
    #   Right segment
        if ($page<($this->num_pages-$this->_config['max_outside'])) {
            $this->pages[] = false;
            $page = $this->num_pages-$this->_config['max_outside']+1;
        }
        while ($page<=$this->num_pages) {
            $this->pages[] = $page;
            ++$page;
        }
    #   READY!
        $this->is_ready = true;
    }   //  _generate
*/
    
}   // class pagination
