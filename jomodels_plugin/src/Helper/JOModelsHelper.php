<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.JoModels
 * @copyright   (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
                (C) 2025 JL TRYOEN <https://www.jltryoen.fr>
 *
 * Version 1.0.0
 *
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.jltryoen.fr
*/
namespace JLTRY\Plugin\Content\JOModels\Helper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

define('PF_REGEX_MODEL_SEARCH_PATTERN', "{model:%s");
define('PF_REGEX_JOMODEL_SEARCH_PATTERN', "{jomodel:%s");
define('PF_REGEX_MODEL_PATTERN', "#{model:%s\s?([^}]*?)}#s");
define('PF_REGEX_MODEL_FULL_PATTERN', "#{model:%s([^}]*)}(.*?){/model:%s}#si");
define('PF_REGEX_JOMODEL_PATTERN', "#{jomodel:%s\s?((?:[a-zA-Z0-9_-]*=\"[^\"].*?\")*)}#s");
define('PF_REGEX_JOMODEL_FULL_PATTERN', "#{jomodel:%s\s?((?:[a-zA-Z0-9_-]*=\"[^\"].*?\")*)}(.*?){/jomodel:%s}#si");
define('PF_REGEX_DEFAULT_VARIABLE_PATTERN', "/%{[^|}]+\|+([^}|]+)}/");
define('PF_REGEX_VARIABLE_PATTERN', "/%{([^}]*?)}/");
define('PF_REGEX_SUBMODEL_PATTERN', "#{model\:(?P<key>[a-zA-Z0-9_-]*)\s*#s");
define('PF_REGEX_SUBJOMODEL_PATTERN', "#{jomodel\:(?P<key>[a-zA-Z0-9_-]*)\s*#s");

define('COM_JOMODELS_NORMAL', 1);
define('COM_JOMODELS_FULL', 0);

/**
* Model generic class
*
*/
class JOModel
{
    public $name;
    public $prio;
    public $content;
    public $allmodels;
    
    function extractmodel($template, &$vars) {
        \preg_replace_callback(PF_REGEX_SUBMODEL_PATTERN, function($match) use(&$vars){
            $vars[] = $match["key"];
        }, $template);
         \preg_replace_callback(PF_REGEX_SUBJOMODEL_PATTERN, function($match) use(&$vars){
            $vars[] = $match["key"];
        }, $template);
    }

    function __construct( $name, $content, $metakey=COM_JOMODELS_FULL)
    {
        $this->name = $name;
        $this->allmodels = array();
        if (is_string($metakey)) {
            if (!strcmp($metakey, "PRIO1") || ($metakey == COM_JOMODELS_NORMAL)) {
                $this->prio = COM_JOMODELS_NORMAL;
            }
            elseif (!strcmp($metakey, "PRIO1")|| !strlen($metakey)  || ($metakey == COM_JOMODELS_FULL)) {
                $this->prio = COM_JOMODELS_FULL;
            }
         } else {
            $this->prio = $metakey;
        }
        $this->content = str_replace("</pre>", "", str_replace("<pre>", "", $content));
        $this->extractmodel($this->content, $this->allmodels);
    }
}


class JOFileModel extends JOModel
{
    function __construct( $name, $filepath)
    {
        $content = file_get_contents($filepath);
        parent::__construct($name, $content, COM_JOMODELS_NORMAL);
    }
}

class JOModelsHelper
{
    // dummy function to load file
    public static function init()
    {
    }
    /**
    * Function to insert model model
    *
    * @param $model : the model
    * @param $params : paramters to replace
    */
    private static function _model($model, $params)
    {
        $html_content = $model->content;
        foreach($params as $param => $value) {
            if ( !(strpos($value,"%{") === false)) {
                $params[$param] = preg_replace_callback(PF_REGEX_VARIABLE_PATTERN,
                    function($matches) use ($params){
                        if (@$matches[1] && array_key_exists($matches[1], $params)) {
                            return($params[$matches[1]]);
                        } else {
                            return $matches[0];
                        }
                    }, $value);
            }
        }
        foreach($params as $param => $value) {
            $html_content = preg_replace("/\|%{". $param . "[^}]*?}/", "|". $value, $html_content);
        }
        foreach($params as $param => $value) {
            $html_content = preg_replace("/%{". $param . "[^}]*?}/", $value, $html_content);
        }
        //default variables
        $matches= array();
        if ($html_content) {
            while (preg_match(PF_REGEX_DEFAULT_VARIABLE_PATTERN, $html_content, $matches)){
                $html_content = preg_replace(PF_REGEX_DEFAULT_VARIABLE_PATTERN, '\1', $html_content);
            }
        }
        return $html_content;
    }

    public static function parseAttributes($string, &$retarray)
    {
        $pairs = explode('|', trim($string));
        foreach ($pairs as $pair) {
            if ($pair == "") {
                continue;
            }
            $pos = strpos($pair, "=");
            $key = substr($pair, 0, $pos);
            $value = substr($pair, $pos + 1);
            $retarray[$key] = $value;
        }
    }

    public static function replaceModel($regexp, &$text, $allmodels, $model, $topparams)
    {
        $text = preg_replace_callback($regexp,
            function($matches) use ($topparams, $model, $allmodels){
                if (@$matches[1]) {
                    if ( strpos( $matches[1], "\"") == false ) {
                        $localparams = array();
                        self::parseAttributes($matches[1], $localparams);
                    } else {
                        $localparams = Utility::parseAttributes($matches[1]);
                    }
                    $params = array_merge($topparams, $localparams);
                } else {
                    $params = array();
                }
                if (@$matches[2]) {
                    $params['content'] = $matches[2];
                }
                return self::_model($model, $params);
            }, $text);
    }

    public static function replaceModels(&$text, $allmodels, $filter = null, $recurse = 0)
    {
        if ($recurse >= 10) {
            $text .= "recrusion!!!";
            return;
        }
        $submodels = array();
        foreach($allmodels  as $name => $model) {
            if (($filter == null) || in_array($name, $filter)) {
                $params= array("ROOTURI" =>Uri::root());
                $searchexp = sprintf(PF_REGEX_MODEL_SEARCH_PATTERN, $model->name);
                $josearchexp = sprintf(PF_REGEX_JOMODEL_SEARCH_PATTERN, $model->name);
                if (! (strpos( $text, $searchexp) === false) ||
                    ! (strpos( $text, $josearchexp) === false) )
                {
                    //Log::add('replaceModels:=>:'. $name, Log::WARNING, 'jomodels');
                    if ( $model->prio == COM_JOMODELS_NORMAL ) {
                        self::replaceModel(sprintf(PF_REGEX_JOMODEL_PATTERN, $model->name, $model->name), $text, $allmodels, $model, $params);
                        self::replaceModel(sprintf(PF_REGEX_MODEL_PATTERN, $model->name, $model->name), $text, $allmodels, $model, $params);
                    }
                    if ( $model->prio == COM_JOMODELS_FULL) {
                        self::replaceModel(sprintf(PF_REGEX_JOMODEL_FULL_PATTERN, $model->name, $model->name), $text, $allmodels, $model, $params);
                         self::replaceModel(sprintf(PF_REGEX_MODEL_FULL_PATTERN, $model->name, $model->name), $text, $allmodels, $model, $params);
                    }
                    $submodels = array_merge($model->allmodels, $submodels);
                    //Log::add('replaceModels:<=:'. $name, Log::WARNING, 'jomodels');
                }
            }
        }
        if (count($submodels) && $recurse < 5) {
            self::replaceModels($text, $allmodels, $submodels, $recurse +1);
        }
    }
}


