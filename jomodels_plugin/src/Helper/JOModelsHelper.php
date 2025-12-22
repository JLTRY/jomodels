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

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

define('JM_REGEX_MODEL_SEARCH_PATTERN', "{model:%s");
define('JM_REGEX_JOMODEL_SEARCH_PATTERN', "{jomodel:%s");
define('JM_REGEX_VARIABLES', '((?:\s?[a-zA-Z0-9_-]+=\"[^\"]+\")+|(?:\|?[a-zA-Z0-9_-]+=[^\"}]+)+|(?:\s*))');
define('JM_REGEX_JOMODEL_PATTERN', "#{(?:jo)?model:%s\s?" . JM_REGEX_VARIABLES ."\s*}#s");
define('JM_REGEX_JOMODEL_FULL_PATTERN', "#{(?:jo)?model:%s\s?" . JM_REGEX_VARIABLES ."\s*}(.*?){/(?:jo)?model:%s}#s");
define('JM_REGEX_DEFAULT_VARIABLE_PATTERN', "/%{[^|}]+\|+([^}|]+)}/");
define('JM_REGEX_VARIABLE_PATTERN', "/%{([^}]*?)}/");
define('JM_REGEX_SUBMODEL_PATTERN', "#{model\:(?P<key>[a-zA-Z0-9_-]*)\s*#s");
define('JM_REGEX_SUBJOMODEL_PATTERN', "#{jomodel\:(?P<key>[a-zA-Z0-9_-]*)\s*#s");

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
        \preg_replace_callback(JM_REGEX_SUBMODEL_PATTERN, function($match) use(&$vars){
            $vars[] = $match["key"];
        }, $template);
         \preg_replace_callback(JM_REGEX_SUBJOMODEL_PATTERN, function($match) use(&$vars){
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
    * Function to get UserFielsValues for current user
    *
    * @param &$values : values filled
    */
    private static function _getUserFielsValues(&$values)
    {
        $user = Factory::getUser();
        if ($user) {
            $fields = FieldsHelper::getFields('com_users.user', $user, true);
            foreach ($fields as $field) {
                $values["ud:" . $field->name] = $field->value;
            }
            $fields = FieldsHelper::getFields('com_users.user', $user, false);
            foreach ($fields as $field) {
                $values["u:" . $field->name] = $field->value;
            }
        }
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
            if (is_string($value) && !(strpos($value,"%{") === false)) {
                $params[$param] = preg_replace_callback(JM_REGEX_VARIABLE_PATTERN,
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
            $html_content = preg_replace("/%{". $param . "[^}]*?}/", print_r($value, 1), $html_content);
        }
        //default variables
        $matches= array();
        if ($html_content) {
            while (preg_match(JM_REGEX_DEFAULT_VARIABLE_PATTERN, $html_content, $matches)){
                $html_content = preg_replace(JM_REGEX_DEFAULT_VARIABLE_PATTERN, '\1', $html_content);
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
                    if ( strpos( $matches[1], "\"") === false ) {
                        $localparams = array();
                        self::parseAttributes($matches[1], $localparams);
                    } else {
                        $localparams = Utility::parseAttributes($matches[1]);
                    }
                    $params = array_merge($topparams, $localparams);
                } else {
                    $params = $topparams;
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
        //get the user fields values if user is connected
        $fieldsvalues = array();
        self::_getUserFielsValues($fieldsvalues);
        $submodels = array();
        foreach($allmodels  as $name => $model) {
            if (($filter == null) || in_array($name, $filter)) {
                $params= $fieldsvalues;
                $params["ROOTURI"] = Uri::root();
                $searchexp = sprintf(JM_REGEX_MODEL_SEARCH_PATTERN, $model->name);
                $josearchexp = sprintf(JM_REGEX_JOMODEL_SEARCH_PATTERN, $model->name);
                Log::add('replaceModels ?:=>:'. $name . ":" . $searchexp, Log::WARNING, 'jomodels');
                if (! (strpos( $text, $searchexp) === false) ||
                    ! (strpos( $text, $josearchexp) === false) )
                {
                    Log::add('replaceModels:=>:' . $text, Log::WARNING, 'jomodels');
                    if ( $model->prio == COM_JOMODELS_NORMAL ) {
                        self::replaceModel(sprintf(JM_REGEX_JOMODEL_PATTERN, $model->name), $text, $allmodels, $model, $params);
                    }
                    if ( $model->prio == COM_JOMODELS_FULL) {
                        self::replaceModel(sprintf(JM_REGEX_JOMODEL_FULL_PATTERN, $model->name, $model->name), $text, $allmodels, $model, $params);
                    }
                    $submodels = array_merge($model->allmodels, $submodels);
                    Log::add('replaceModels:<=:'. $text, Log::WARNING, 'jomodels');
                }
            }
        }
        if (count($submodels) && $recurse < 5) {
            self::replaceModels($text, $allmodels, $submodels, $recurse +1);
        }
    }
}


