<?php

/**
 * Template class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
require_once('classes.php');

class Template extends WhipPlugin {
    const TOKEN_TAG                 = '`';
    
    const TOKEN_FUNCTION            = '%';
    
    const TOKEN_VARIABLE            = '$';
    const TOKEN_VARIABLE_SEPARATOR  = '.';
    const TOKEN_VARIABLE_VARIABLE_O = '{';
    const TOKEN_VARIABLE_VARIABLE_C = '}';
    const TOKEN_VARIABLE_DEFAULT    = '?';
    const TOKEN_VARIABLE_MODIFIER   = '|';
    const TOKEN_VARIABLE_KEYPAIR    = '=>';
    
//  Possible functions in the template
    const TOKEN_FUNCTION_IF         = 'if';
    const TOKEN_FUNCTION_ELSE       = 'else';
    const TOKEN_FUNCTION_FOR        = 'for';
    const TOKEN_FUNCTION_FOREACH    = 'foreach';
    const TOKEN_FUNCTION_FOR_IN     = 'in';
    const TOKEN_FUNCTION_FOR_AS     = 'as';
    const TOKEN_FUNCTION_INCLUDE    = 'include';
    const TOKEN_FUNCTION_I18N       = 'i18n';       //  experimental!
//  Prefix for function end tags
    const TOKEN_FUNCTION_END        = 'end';
    
//  3 arrays of preg matches on the template data
//  before it gets converted to a token tree
    const TOKEN_MATCH_INCLUSIVE     = 0x00;
    const TOKEN_MATCH_EXCLUSIVE     = 0x01;
    const TOKEN_MATCH_TAG           = 0x02;
    
    const TREE_IF_TRUEPART          = 0;
    const TREE_IF_FALSEPART         = 1;
    
//  Fields for each token in the token tree
    const TREE_START                = 0x00;
    const TREE_END                  = 0x01;
    const TREE_TAG                  = 0x02;
    const TREE_CHILDREN             = 0x03;
    const TREE_TYPE                 = 0x04;
    
//  Types of TREE_TYPE
    const TREE_TYPE_ROOT            = 0x00;
    const TREE_TYPE_VARIABLE        = 0x01;
    const TREE_TYPE_FUNCTION        = 0x02;
    
    protected $_template_filename   = '';
    protected $_template_path       = '';
    protected $_template_data       = '';
    protected $_context             = array();
    protected $_context_cache       = array();
    
    /**
     * load function.
     *
     * Load a template file
     * 
     * @access public
     * @param mixed $filename
     * @param array &$context. (default: array())
     */
    public function load($template_filename, &$context=array()) {
    //  Fix path if necessary
        $this->config['path'] = Whip::real_path($this->_config['path']);
    //  Separate real path and filename
        $template_filename = realpath($this->_config['path'].$template_filename);
        $this->_template_filename = pathinfo($template_filename, PATHINFO_BASENAME);
        $this->_template_path = pathinfo($template_filename, PATHINFO_DIRNAME).'/';
    //  Check if path exists
        if (false == $this->_template_filename || false == $this->_template_path) {
            throw new WhipPluginException('Template file not found');
            return false;
        }
    //  And stick them back together to check if the file exists
        $template_filename = $this->_template_path.$this->_template_filename;
    //  Check if file exists
        if (!file_exists($template_filename)) {
            $this->_template_filename = '';
            $this->_template_path = '';
            throw new WhipPluginException('Template file not found');
            return false;
        }
    //  Load template file
        $this->_template_data = file_get_contents($template_filename);
        if (false === $this->_template_data) {
            throw new WhipPluginException(
                'Template file '.$this->_template_filename.' exists but could not be loaded.'.
                'Check file permissions.'
            );
            return false;
        }
    //  Set context
        $this->_context = $context;
        return true;
    }   //  function load
    
    
    /**
     * load_string function.
     *
     * Loads a string of template data.
     * 
     * @access public
     * @param string $string
     * @param array &$context. (default: array())
     * @return void
     */
    public function load_string($string, array $context=array()) {
    //  Load template data
        $this->_template_data = $string;
    //  Set context
        $this->_context = $context;
        return true;
    }   //  function load_string
    
    
    /**
     * render function.
     * 
     * @access public
     * @param mixed $template. (default: null)
     * @param mixed $context. (default: null)
     */
    public function render($template_filename=null, array $context=array(), $return=false) {
    //  Load template if necessary
        if (null != $template_filename) {
            $this->load($template_filename, $context);
        }
        elseif (null != $context) {
        //  Set context
            $this->_context = $context;
        }
        
    //  Start output buffering
        ob_start();
    //  Build a TemplateBlock tree
        $tree = $this->_build_tree();
        if (false === $tree) {
        //  Plain text / html
            echo $this->_template_data;
        }
        else {
        //  Has Whip Template tags; Render tree
            $this->_render_tree($tree);
        }
    //  Finish output buffering
        if (true == $return) {
        //  Return output buffer contents
            return ob_get_clean();
        }
        else {
        //  Send output buffer to client
            ob_end_flush();
            return true;
        }
    }   //  function render
    
    
    /**
     * _render_tree function.
     * Render (part of) the TemplateBlock tree
     * 
     * @access private
     */
    private function _render_tree(&$node) {
        if (is_string($node)) {
        //  Plain text / html
        //  Trimm off the fat before outputting
            //$node = preg_replace('/[ ]{2,16}/s', '', $node);  //  a bit overkill
            echo trim($node, "\r\n\t");
            return true;
        }
        
        if ($node instanceof TemplateBlockVariable) {
        //  Variable
            $this->_render_variable($node->variable, $node->parameters, $node->default);
        }   //  function
        elseif ($node instanceof TemplateBlockFunction) {
        //  Function
        //@TODO
        //  Different functionality depending on function name
            switch($node->function) {
            case self::TOKEN_FUNCTION_IF:
            //  IF
                $this->_render_if($node);
                break;
                
            case self::TOKEN_FUNCTION_FOR:
            case self::TOKEN_FUNCTION_FOREACH:
            //  FOR
                $this->_render_for($node);
                break;
                
            case self::TOKEN_FUNCTION_INCLUDE:
            //  INCLUDE
                $this->_render_include($node);
                break;
                
            case self::TOKEN_FUNCTION_I18N:
            //  INTERNATIONALIZATION
                $this->_render_i18n($node);
                break;
                
            default:
            //  ELSE / other
                if (isset($node->children)) {
                    foreach($node->children as &$child) {
                        $this->_render_tree($child);
                    }
                }
                
            }   //  switch function name
        
        }   //  function
        else {
        //  Root
            if (isset($node->children)) {
                foreach($node->children as &$child) {
                    $this->_render_tree($child);
                }
            }
        }   //  if instanceof TemplateBlockVariable / TemplateBlockFunction
        
    }   //  function _render_tree
    
    
    
    /**
     * _render_if function.
     * 
     * @access private
     * @param mixed &$node
     */
    private function _render_if(&$node) {
    //  Check what kind of IF statement this is
        switch (count($node->parameters)) {
        case 1:
        //  Simple IF:      IF $var
            $variable   = $node->parameters[0];
            if ('!' == $variable[0]) {
            //  Negate the value
                $if_bool = !(bool)$this->_render_variable(substr($variable, 1), null, null, true);
            }
            else {
            //  Straight up variable
                $if_bool = (bool)$this->_render_variable($variable, null, null, true);
            }
            break;
            
        case 2:
        //TODO: throw exception
            break;
            
        case 3:
        //  Comparitive IF:      IF $var > 5
            $value1     = $this->_render_variable($node->parameters[0], null, null, true);
            $value2     = $this->_render_variable($node->parameters[2], null, null, true);
            $operator   = strtolower($node->parameters[1]);
            
            switch($operator) {
            case '==':
            case 'eq':
            case 'is':
            //  Equals
                $if_bool = (bool)($value1 == $value2);
                break;
                
            case '!=':
            case 'ne':
            case 'neq':
            case 'not':
            //  NOT Equals
                $if_bool = (bool)($value1 != $value2);
                break;
            
            case '>=':
            case '=>':
            case 'gte':
            //  Greater than or equal to
                $if_bool = (bool)($value1 >= $value2);
                break;
                
            case '<=':
            case '=<':
            case 'lte':
            //  Less than or equal to
                $if_bool = (bool)($value1 <= $value2);
                break;
                
            case '>':
            case 'gt':
            //  Greater than
                $if_bool = (bool)($value1 > $value2);
                break;
                
            case '<':
            case 'lt':
            //  Less than
                $if_bool = (bool)($value1 < $value2);
                break;
                
            case 'in':
            //  In (array)
                if (is_array($value2)) {
                    $if_bool = in_array($value1, $value2);
                }
                else {
                    $if_bool = false;
                }
                break;
                
            case 'nin':
            //  NOT in (array)
                if (is_array($value2)) {
                    $if_bool = !in_array($value1, $value2);
                }
                else {
                    $if_bool = true;
                }
                break;
                
            case 'contains':
            //  Contains (array)
                if (is_array($value1)) {
                    $if_bool = in_array($value2, $value1);
                }
                else {
                    $if_bool = false;
                }
                break;
            
            default:
            //  Unsupported operator
                throw new WhipPluginException('Unsupported IF tag operator: '.$operator);
                break;
            }
            break;
        
        default:
        //  Unexpected number of parameters
            throw new WhipPluginException('Unexpected number of parameters in an IF tag');
        
        }   //  switch number of parameters
        
        if ($if_bool) {
        //  TRUE part
            if (isset($node->children)) {
                foreach($node->children as &$child) {
                    $this->_render_tree($child);
                }
            }
        }
        else {
        //  FALSE part
            $this->_render_tree($node->else);
        }
    }   //  function _render_if
    
    
    /**
     * _render_for function.
     * 
     * @access private
     * @param mixed &$node
     */
    private function _render_for(&$node) {
        if (!isset($node->children) || !$node->children || !count($node->children)) {
        //  Nothing to render
            return false;
        }
        $num_parameters = count($node->parameters);
        switch ($num_parameters) {
        case 3:
        //  Check if we need to use the "for .. in" or "for .. as" syntax
            if (self::TOKEN_FUNCTION_FOR_IN == $node->parameters[1]) {
            //  for .. in
                $for_values = $this->_context($node->parameters[2]);
                $for_variable_name = $node->parameters[0];
                if (self::TOKEN_VARIABLE == $for_variable_name[0]) {
                    $for_variable_name = substr($for_variable_name, 1);
                }
            }
            elseif (self::TOKEN_FUNCTION_FOR_AS == $node->parameters[1]) {
            //  for .. as
                $for_values = $this->_context($node->parameters[0]);
                $for_variable_name = $node->parameters[2];
                if (self::TOKEN_VARIABLE == $for_variable_name[0]) {
                    $for_variable_name = substr($for_variable_name, 1);
                }
            }   //  switch syntax
        //  Loop through the values
            if (is_numeric($for_values)) {
            //  Numeric for... loop
                for ($idx_for=1; $idx_for<=$for_values; ++$idx_for) {
                    $this->_context[$for_variable_name] = $idx_for;
                    foreach($node->children as &$child) {
                        $this->_render_tree($child);
                    }   //  render each  child
                }   //  each value
            }
            else {
            //  Normal for... loop
                foreach ($for_values as &$for_value) {
                    $this->_context[$for_variable_name] = $for_value;
                    foreach($node->children as &$child) {
                        $this->_render_tree($child);
                    }   //  render each  child
                }   //  each value
            }
            break;
            
        case 4:
        //  Check if we need to use the "for .. in" or "for .. as" syntax
            if (self::TOKEN_FUNCTION_FOR_AS == $node->parameters[1]) {
            //  for .. as
                $for_values = $this->_context($node->parameters[0]);
                $for_key_name       = $node->parameters[2];
                $for_variable_name  = $node->parameters[3];
                if (self::TOKEN_VARIABLE == $for_variable_name[0]) {
                    $for_variable_name = substr($for_variable_name, 1);
                }
                if (self::TOKEN_VARIABLE == $for_key_name[0]) {
                    $for_key_name = substr($for_key_name, 1);
                }
            //  Loop through the values
                foreach ($for_values as $for_key => &$for_value) {
                    $this->_context[$for_variable_name] = $for_value;
                    $this->_context[$for_key_name] = $for_key;
                    foreach($node->children as &$child) {
                        $this->_render_tree($child);
                    }   //  render each  child
                }   //  each value
            }   //  switch syntax
            else {
            //@TODO: Incorrect syntax Exception?
            }
            break;
            
        default:
        //@TODO: Incorrect syntax Exception?
        
        }   //  switch syntax
        
    }   //  function _render_for
    
    
    /**
     * _render_include function.
     * 
     * @access private
     * @param mixed &$node
     */
    private function _render_include(&$node) {
    //  Resolve any variables
        $filename = $this->_render_variable(
            $node->parameters[0],
            null,
            null,
            true
        );
    //  If the filename starts with /,
    //  assume base path.
        if ('/' === $filename[0]) {
        //  Use base path
            $filename = substr($filename, 1);
            $path = $this->_config['path'];
        }
        else {
        //  Use current path
            $path = $this->_template_path;
        }
    //  Build the complete path from which to load the template
        $template_filename = Whip::real_path($path.$filename);
    //  Make sure this path is inside the config template path
        $include_path = Whip::real_path(dirname($template_filename));
        $len_config_path = strlen($this->_config['path']);
        if (substr($include_path, 0, $len_config_path) != $this->_config['path']) {
            throw new WhipPluginException('Cannot load a template outside of the template path: "'.$filename.'"');
        }
        elseif (file_exists($template_filename)) {
        //  Template exists
        //  Include the template file
            Whip::Template($filename)->render(
                substr($template_filename, $len_config_path),
                $this->_context
            );
        }
        else {
        //  Template does not exist
            throw new WhipPluginException('Template not found: "'.$filename.'"');
        }
    }   //  function _render_include
    
    
    /**
     * _render_include function.
     * 
     * @access private
     * @param mixed &$node
     */
    private function _render_i18n(&$node) {
    //  NOTE:   The i18n modifier DOES NOT CURRENTLY EXIST outside of a private website!
    //          A Whip plugin is currently being developed.
        $text = implode(' ', $node->parameters);
        $modifier_class_name = $this->_load_modifier('i18n');
    //  Execute the modifier
        $value = call_user_func_array($modifier_class_name.'::run', array($text));
        echo $value;
    }   //  function _render_i18n
    
    
    /**
     * _build_tree function.
     * 
     * @access private
     */
    private function _build_tree() {
    //  Check if the data contains any tags at all
        $data_contains_tags = strpos($this->_template_data, self::TOKEN_TAG);
        if (false === $data_contains_tags) {
        //  No start token found.
        //  Render all the data as-is.
            return false;
        }
    //  Tokenize the data
        $tokens = array();
        if (
            false == preg_match_all(
                '/'.self::TOKEN_TAG.'('.
                //  Function
                    self::TOKEN_FUNCTION.
                        '[^'.self::TOKEN_TAG.self::TOKEN_FUNCTION.']+'.
                    self::TOKEN_FUNCTION.
                    '|'.
                //  Variable
                    '[\s]*\\'.self::TOKEN_VARIABLE.'[^'.self::TOKEN_TAG.']+'.
                ')'.self::TOKEN_TAG.'/',
                $this->_template_data,
                $tokens,
                PREG_OFFSET_CAPTURE
            )
        ) {
        //  No tags found.
        //  Render all the data as-is.
            return false;
        }
        
    //  We have tokens.
    //  Build a tree of TemplateBlocks.
        $stack      = array( new TemplateBlockRoot() );
        $cursor     = 0;
        $num_tokens = count($tokens[self::TOKEN_MATCH_INCLUSIVE]);
        
        for($idx_token=0; $idx_token<$num_tokens; ++$idx_token) {
        //  Grab (any) content before this token.
            $content = substr(
                $this->_template_data,
                $cursor,
                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][1] - $cursor
            );
            if (strlen($content)) {
            //  Push into current block's children
                //$stack[0]->children[]   = $content;
                $stack[0]->add_child($content);
            }
            
            if ($tokens[self::TOKEN_MATCH_EXCLUSIVE][$idx_token][0][0] == self::TOKEN_FUNCTION) {
            //  Function
                $block = new TemplateBlockFunction($tokens[self::TOKEN_MATCH_EXCLUSIVE][$idx_token][0]);
            //  Different functionality depending on function name
                switch($block->function) {
                case self::TOKEN_FUNCTION_IF:
                //  IF      (one level deeper)
                    array_unshift($stack, $block);
                    break;
                
                case self::TOKEN_FUNCTION_ELSE:
                //  ELSE
                //  Check if we are in an IF-block
                    if (!($stack[0] instanceof TemplateBlockFunction) ||
                        self::TOKEN_FUNCTION_IF != $stack[0]->function) {
                    //  We are NOT in an IF-block.
                        throw new WhipDataException(
                            'Unexpected template syntax near: "'.
                                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                            '"'
                        );
                    }   //  if in IF-block
                //  Push me to the stack
                    array_unshift($stack, $block);
                    break;
                    
                case self::TOKEN_FUNCTION_END.self::TOKEN_FUNCTION_IF:
                //  Check if we are in a function
                    if (!($stack[0] instanceof TemplateBlockFunction)) {
                    //  We are NOT in a function
                        throw new WhipDataException(
                            'Unexpected template syntax near: "'.
                                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                            '"'
                        );
                    }   //  if in FUNCTION
                    if (self::TOKEN_FUNCTION_IF == $stack[0]->function) {
                    //  End the IF block
                        $if_block = array_shift($stack);
                        $stack[0]->add_child($if_block);
                    }
                    elseif (self::TOKEN_FUNCTION_ELSE == $stack[0]->function) {
                    //  End the ELSE block, give it to the IF
                        $else_block = array_shift($stack);
                        $stack[0]->add_child($else_block);
                    //  End the IF block
                        $if_block = array_shift($stack);
                        $stack[0]->add_child($if_block);
                    }
                    else {
                    //  ENDIF without being in an IF-block
                        throw new WhipDataException(
                            'Unexpected template syntax near: "'.
                                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                            '"'
                        );
                    }
                    break;
                    
                case self::TOKEN_FUNCTION_FOR:
                case self::TOKEN_FUNCTION_FOREACH:
                //  FOR     (one level deeper)
                    array_unshift($stack, $block);
                    break;
                    
                case self::TOKEN_FUNCTION_END.self::TOKEN_FUNCTION_FOR:
                case self::TOKEN_FUNCTION_END.self::TOKEN_FUNCTION_FOREACH:
                //  Check if we are in a function
                    if (!($stack[0] instanceof TemplateBlockFunction)) {
                    //  We are NOT in a function
                        throw new WhipDataException(
                            'Unexpected template syntax near: "'.
                                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                            '"'
                        );
                    }   //  if in FUNCTION
                    if (self::TOKEN_FUNCTION_FOR == $stack[0]->function ||
                        self::TOKEN_FUNCTION_FOREACH == $stack[0]->function
                    ) {
                    //  End the FOR block
                        $for_block = array_shift($stack);
                        $stack[0]->add_child($for_block);
                    }
                    else {
                    //  ENDFOR without being in a FOR-block
                        throw new WhipDataException(
                            'Unexpected template syntax near: "'.
                                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                            '"'
                        );
                    }
                    break;
                                        
                case self::TOKEN_FUNCTION_INCLUDE:
                //  INCLUDE (same level)
                    //$stack[0]->children[]   = $block;
                    $stack[0]->add_child($block);
                    break;
                    
                case self::TOKEN_FUNCTION_I18N:
                //  INCLUDE (same level)
                    //$stack[0]->children[]   = $block;
                    $stack[0]->add_child($block);
                    break;
                
                default:
                //  Unknown function
                    throw new WhipDataException(
                        'Unexpected template syntax near: "'.
                            $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0].
                        '"'
                    );
                }   //  switch function name
                
            }
            else {
            //  Variable
                $block = new TemplateBlockVariable($tokens[self::TOKEN_MATCH_EXCLUSIVE][$idx_token][0]);
                //$stack[0]->children[] = $block;
                $stack[0]->add_child($block);
            }   //  if function or var
            
        //  Move cursor after this token
            $cursor =
                $tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][1] +
                strlen($tokens[self::TOKEN_MATCH_INCLUSIVE][$idx_token][0]);
                
        }   //  each token
        
    //  Grab (any) data after the last token
        if ($cursor < strlen($this->_template_data)) {
        //  Push into current block's children
            //$stack[0]->children[]   = substr($this->_template_data, $cursor);;
            $plain_content_block = substr($this->_template_data, $cursor);
            $stack[0]->add_child($plain_content_block);
        }
    //  Return the tree of TemplateBlocks
        return $stack[0];
    }   //  function _build_tree
    
    
    /**
     * _render_variable function.
     * 
     * @access private
     * @param mixed &$node
     * @param bool $return. (default: false)
     */
    private function _render_variable($variable, $parameters=array(), $default=null, $return=false) {
    //  Render all {encapsulated} variables first
        $variable = preg_replace_callback(
            '/{([^'.
                self::TOKEN_TAG.
                self::TOKEN_VARIABLE_VARIABLE_O.
                self::TOKEN_VARIABLE_VARIABLE_C.
            ']+)}/',
            array($this, '_render_variable_callback'),
            $variable
        );
    //  Get value
        $value = $this->_context($variable);
        if (null === $value && null !== $default) {
        //  Use default value
            $value = $default;
        }
        
    //  Run any modifiers
        $num_modifiers = count($parameters);
        if ($num_modifiers) {
            for ($i_modifier=0; $i_modifier<$num_modifiers; ++$i_modifier) {
            //  Make sure modifier is loaded
                $parameter = preg_split('/[\s]+/', $parameters[$i_modifier]);
                $modifier_class_name = array_shift($parameter);
                array_unshift($parameter, $value);
                if (strpos($modifier_class_name, self::TOKEN_VARIABLE_SEPARATOR)) {
                //  Custom modifier function name.
                //  (separated by period, like: modifiername.functionname)
                    list($modifier_class_name, $modifier_function_name) = explode(
                        self::TOKEN_VARIABLE_SEPARATOR,
                        $modifier_class_name,
                        2
                    );
                }
                else {
                //  Default modifier function name.
                    $modifier_function_name = 'run';
                }
                $modifier_class_name = $this->_load_modifier($modifier_class_name);
            //  Execute the modifier
                $value = call_user_func_array($modifier_class_name.'::'.$modifier_function_name, $parameter);
            }
        }   //  if modifiers
        
        
    //  Render / Return
        if (true === $return) {
            return $value;
        }
        echo $value;
    }   //  function _render_variable
    
    /**
     * _render_variable_callback function.
     * 
     * @access private
     * @param mixed $matches
     */
    private function _render_variable_callback($matches) {
        $block = new TemplateBlockVariable($matches[1]);
        return $this->_render_variable(
            $block->variable,
            $block->parameters,
            $block->default,
            true
        );
    }   //  function _render_variable_callback
    
    
    /**
     * _context function.
     * 
     * @access private
     * @param mixed $variable
     */
    private function _context($variable) {
    //  If not a variable, return literal
        if (self::TOKEN_VARIABLE != $variable[0]) {
            return $variable;
        }
    //  Remove initial "$"
        $variable = substr($variable, 1);
        if (false === strpos($variable, self::TOKEN_VARIABLE_SEPARATOR)) {
        //  Simple variable
            if (!isset($this->_context[$variable])) {
                return null;
            }
        //  Return value
            return $this->_context[$variable];
        }   //  if simple variable
        
    //  Complex variable
    //  Split into parts
        $variable_parts = explode('.', $variable);
        if (!isset($this->_context[$variable_parts[0]])) {
            return null;
        }
    //  Get the first part's value (object or array)
        $value =& $this->_context[$variable_parts[0]];
        $num_variable_parts = count($variable_parts);
        for ($idx_variable=1; $idx_variable<$num_variable_parts; ++$idx_variable) {
            if (is_array($value)) {
            //  Array
                if (!isset($value[$variable_parts[$idx_variable]])) {
                    return null;
                }
                $value =& $value[$variable_parts[$idx_variable]];
                continue;
            }
            elseif (is_object($value)) {
            //  Object
                if (!isset($value->{$variable_parts[$idx_variable]})) {
                    return null;
                }
                @$value =& $value->{$variable_parts[$idx_variable]};
                continue;
            }
        //  Object is not traversable
            return null;
        }   //  each variable part
    //  Return value
        return $value;
    }   //  function _context
    
    
    /**
     * _load_modifier function.
     * 
     * @access private
     * @param mixed $name
     * @return void
     */
    private function _load_modifier($name) {
    //  Get modifier class name
        $modifier_class_name = 'TemplateModifier'.ucfirst($name);
        if (!class_exists($modifier_class_name)) {
        //  Check name for security
            if (!preg_match('/^[a-z0-9_\.-]+$/i', $name)) {
                throw new WhipPluginException('Unsafe modifier used: '.$name);
                return false;
            }
        //  Load modifier file
            $modifier_file_name = Whip::real_path(__DIR__).'modifiers/'.$name.'.php';
            if (!file_exists($modifier_file_name)) {
                throw new WhipPluginException('Modifier not found: '.$name);
                return false;
            }
            include_once($modifier_file_name);
        }
        return $modifier_class_name;
    }   //  function _load_modifier
    
    
    
}   //  class Template

