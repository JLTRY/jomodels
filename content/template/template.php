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
use Joomla\CMS\Factory;
//jimport( 'joomla.plugin.plugin' );
define('PF_REGEX_SEARCH_PATTERN', "{{%s");
define('PF_REGEX_TEMPLATE_PATTERN', "#{{%s([^}]*)}}#s");
define('PF_REGEX_VARIABLE_PATTERN',  "/{{{[^|]+\|+([^}|]+)}}}/");


/**
* Template generic class
*
*/
class Template
{
    function __construct( $name, $content )
	{
        $this->name = $name;
        $this->content = str_replace("</pre>", "", str_replace("<pre>", "", $content));
    }
}


class FileTemplate extends Template
{
    function __construct( $name, $filepath )
    {
        $content = file_get_contents($filepath);
        parent::__construct($name, $content);
    }        
}


/**
* Template Content Plugin
*
*/
class PlgContentTemplate extends JPlugin
{
    
    var $alltemplates = [];
    
	/**
	* Constructor
	*
	* @param object $subject The object to observe
	* @param object $params The object that holds the plugin parameters
	*/
	function __construct( &$subject, $params )
	{
        
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
	function _template($template,  $params)
	{
		$html_content = $template->content;
        foreach($params as $param => $value) {
            $html_content = preg_replace("/{{{". $param . "[^}]*}}}/", $value, $html_content);
        }
        //sub templates
        foreach($this->alltemplates  as $template) {
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $template->name);
            if (!strpos( $html_content, $searchexp) === false ) {
                $html_content = $this->replace_template($html_content, $template, $params);
            }
        }
        //variables
        $matches= array();
        while (preg_match(PF_REGEX_VARIABLE_PATTERN, $html_content, $matches)){
            $html_content = preg_replace(PF_REGEX_VARIABLE_PATTERN, '\1', $html_content);
        }
		return $html_content;
	}

    function replace_template($text, $template, $topparams)
	{
        preg_match_all(sprintf(PF_REGEX_TEMPLATE_PATTERN, $template->name), $text, $matches);
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
                        /* foreach ($this->alltemplates  as $template) {            
                            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $template);
                            if (!strpos( $value, $searchexp) === false ) {
                                $params[$key] = $this->replace_template($value, $template, $params);
                            }
                        }*/
                    }
                }
                $p_content = $this->_template($template, $params); 
                if (@$matches[1][$i]) { 
                    $text = str_replace(sprintf("{{%s" . $matches[1][$i] . "}}", $template->name), $p_content, $text);                	                   
                } else {
                    $text = str_replace(sprintf("{{%s}}", $template->name), $p_content, $text); 
                }
            }                
        }
        else
        {
            $text = str_replace(sprintf("{{%s ", $template->name), sprintf("erreur de syntaxe: {%s parameters}", $template->name), $text);
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
        if (!count($this->alltemplates)) {
            foreach (glob(dirname(__FILE__) . '/*.tmpl') as $file)
            {
                $splitar = preg_split("/\./", basename($file));
                $this->alltemplates[] = new FileTemplate($splitar[0], $file);            
            }
            $catId = $this->params->get('catid');
            if ($catId) {
                $app     = Factory::getApplication();
                $factory = $app->bootComponent('com_content')->getMVCFactory();
                // Get an instance of the generic articles model
                $jarticles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);
                $jarticles->setState('filter.category_id', array($catId));
                $appParams = $app->getParams();
                $jarticles->setState('params', $appParams);
                //$jarticles->setState('list.limit', 10);
                $jarticles->setState('filter.published', 1);
                $articles= $jarticles->getItems();                 
                foreach ($articles as $article) {    
                    $this->alltemplates[] = new Template($article->alias, $article->introtext); 
                }
            }
        }
        foreach ($this->alltemplates  as $pattern) {            
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $pattern->name);
            if (!strpos( $row->text, $searchexp) === false ) {
                $match = true;
            }
        }		
        if ($match == false) {            
            return true;
        }
        foreach($this->alltemplates  as $template) {
            $searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $template->name);
            if (!strpos( $row->text, $searchexp) === false ) {
                $params= array("ROOTURI" =>JURI::root() );
                $row->text = $this->replace_template($row->text, $template, $params);
            }
        }        
    }    
 	
}
