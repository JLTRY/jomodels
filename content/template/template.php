<?php
/**
 * 
* @copyright Copyright (C) 2012 Jean-Luc TRYOEN. All rights reserved.
* @license GNU/GPL
*
* Version 1.0
*
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Uri\Uri;
//jimport( 'joomla.plugin.plugin' );
define('PF_REGEX_SEARCH_PATTERN', "{{%s");
define('PF_REGEX_TEMPLATE_PATTERN', "#{{%s([^}]*)}}#s");
define('PF_REGEX_VARIABLE_PATTERN',  "/{{{[^|]+\|+([^}|]+)}}}/");

/**
* Template Content Plugin
*
*/
class plgContentTemplate extends JPlugin
{
    
    var $TEMPLATE_TEMPLATES = [];
    
	/**
	* Constructor
	*
	* @param object $subject The object to observe
	* @param object $params The object that holds the plugin parameters
	*/
	function __construct( &$subject, $params )
	{
        foreach (glob(dirname(__FILE__) . '/*.tmpl') as $file)
        {
            $splitar = preg_split("/\./", basename($file));
            $this->TEMPLATE_TEMPLATES[] = $splitar[0];            
        }
		parent::__construct( $subject, $params );
	}

	
 	/**
	* Example prepare content method in Joomla 1.6/1.7/2.5
	*
	* Method is called by the view
	*
	* @param object The article object. Note $article->text is also available
	* @param object The article params
	*/   
	function onContentPrepare($context, &$row, &$params, $page = 0){
		return $this->OnPrepareRow($row);
	}

    /**
	* Function to insert template template
	*
	* Method is called by the onContentPrepare or onPrepareContent
	*
	* @param string The text string to find and replace
	*/	   
	function _template($pattern,  $params)
	{
		$html_content = file_get_contents(dirname(__FILE__) . '/' . $pattern . '.tmpl');
        foreach($params as $param => $value) {
            $html_content = preg_replace("/{{{". $param . "[^}]*}}}/", $value, $html_content);
        }
        //variables
        $matches= array();
        while (preg_match(PF_REGEX_VARIABLE_PATTERN, $html_content, $matches)){
            $html_content = preg_replace(PF_REGEX_VARIABLE_PATTERN, '\1', $html_content);
        }
        //sub templates
        foreach($this->TEMPLATE_TEMPLATES  as $template) {
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $template);
            if (!strpos( $html_content, $searchexp) === false ) {
                $html_content = $this->replace_template($html_content, $template, $params);
            }
        }
		return $html_content;
	}

    function replace_template($text, $pattern, $topparams)
	{
        preg_match_all(sprintf(PF_REGEX_TEMPLATE_PATTERN, $pattern), $text, $matches);
        // Number of plugins
        $count = count($matches[0]);            
        // plugin only processes if there are any instances of the plugin in the text
        if ($count) {               
            for ($i = 0; $i < $count; $i++)
            {
                $params = array_replace([], $topparams); 
                if (@$matches[1][$i]) {
                    $inline_params = $matches[1][$i];                       
                    $pairs = explode('|', trim($inline_params));
                    foreach ($pairs as $pair) {
                        if ($pair == "") {
                            continue;
                        }
                        $pos = strpos($pair, "=");
                        $key = substr($pair, 0, $pos);
                        $value = substr($pair, $pos + 1);
                        $params[$key] = $value;
                    }
                    $p_content = $this->_template($pattern, $params);                                                
                    $text = str_replace(sprintf("{{%s" . $matches[1][$i] . "}}", $pattern), $p_content, $text);
                }	                   
            }                
        }
        else
        {
            $text = str_replace(sprintf("{{%s ", $pattern), sprintf("erreur de syntaxe: {%s parameters}", $pattern), $text);
        }		
        return $text;
    }
    
    
	
	function onPrepareRow(&$row) 
	{
		//Escape fast
		if (!$this->params->get('enabled', 1)) {
			return true;
		}
        $match = false;
              
        foreach ($this->TEMPLATE_TEMPLATES  as $pattern) {            
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $pattern);
            if (!strpos( $row->text, $searchexp) === false ) {
                $match = true;
            }
		}		
        if ($match == false) {            
            return true;
        }
        foreach($this->TEMPLATE_TEMPLATES  as $template) {
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $template);
            if (!strpos( $row->text, $searchexp) === false ) {
                $params= array("ROOTURI" => Uri::root(true));
                $row->text = $this->replace_template($row->text, $template, $params);
            }
        }
		return true;
	}



 	
}
