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


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

define('PF_REGEX_SEARCH_PATTERN', "{model:%s");
define('PF_REGEX_MODEL_PATTERN', "#{model:%s([^}]*)}#s");
define('PF_REGEX_FULL_MODEL_PATTERN', "#{model:%s([^}]*)}(.*){/model:%s}#s");
define('PF_REGEX_VARIABLE_PATTERN', "/%{[^|]+\|+([^}|]+)}/");
define('COM_JOMODELS_MODEL_NORMAL', 1);
define('COM_JOMODELS_MODEL_FULL', 0);

/**
* Model generic class
*
*/
class JOModel
{
	const _PRIO0="PRIO0";
    const _PRIO1="PRIO1";
    public $name;
    public $prio;
    public $content;
	function __construct( $name, $content, $metakey=COM_JOMODELS_MODEL_FULL)
	{
		$this->name = $name;
		$this->prio = strlen($metakey)? $metakey : COM_JOMODELS_MODEL_FULL;
		$this->content = str_replace("</pre>", "", str_replace("<pre>", "", $content));
	}
}


class JOFileModel extends JOModel
{
	function __construct( $name, $filepath)
	{
		$content = file_get_contents($filepath);
		parent::__construct($name, $content);
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
	private static function _model($allmodels, $model, $params)
	{
		$html_content = $model->content;
		foreach($params as $param => $value) {
            $html_content = preg_replace("/%{". $param . "[^}]*}/", $value, $html_content);
		}
		//sub models
		foreach($allmodels  as $modeli) {
			$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $modeli->name);
			if (! (strpos( $html_content, $searchexp) === false) ) {
				JOModelsHelper::replaceModel($html_content, $allmodels, $modeli, $params);
			}
		}
		//default variables
		$matches= array();
        if ($html_content) {
            while (preg_match(PF_REGEX_VARIABLE_PATTERN, $html_content, $matches)){
                $html_content = preg_replace(PF_REGEX_VARIABLE_PATTERN, '\1', $html_content);
            }
        }
		return $html_content;
	}

    /**
     * Method to extract key/value pairs out of a string with XML style attributes
     *
     * @param   string  $string  String containing XML style attributes
     *
     * @return  array  Key/Value pairs for the attributes
     *
     * @since   1.7.0
     */
    /* public static function parseAttributes($string, &$retarray)
    {
        $attr     = [];
        // Let's grab all the key/value pairs using a regular expression
        preg_match_all('/([\s\|]*[\w:-]+)=([^|}]*)[\s]?/i', $string, $attr);

        if (\is_array($attr)) {
            $numPairs = \count($attr[1]);

            for ($i = 0; $i < $numPairs; $i++) {
                $retarray[$attr[1][$i]] = $attr[2][$i];
            }
        }
    } */
    
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

    public static function replaceModel(&$text, $allmodels, $model, $topparams)
    {
        $text = preg_replace_callback(sprintf(PF_REGEX_MODEL_PATTERN, $model->name, $model->name),
            function($matches) use ($topparams, $model, $allmodels){
				$params = array_replace([], $topparams);
				if (@$matches[1]) {
					self::parseAttributes($matches[1], $params);
				}
				return self::_model($allmodels, $model, $params);
            }, $text);
    }


    public static function replaceFullModel(&$text, $allmodels, $model, $topparams)
    {
        $text = preg_replace_callback(sprintf(PF_REGEX_FULL_MODEL_PATTERN, $model->name, $model->name),
            function($matches) use ($topparams, $model, $allmodels){
				$params = array_replace([], $topparams);
				if (@$matches[1]) {
					self::parseAttributes($matches[1], $params);
				}
                if (@$matches[2]) {
                    $params['content'] = $matches[2];
                }
				return self::_model($allmodels, $model, $params);
            }, $text);
    }

    public static function replaceModels(&$text, $allmodels)
    {
        foreach($allmodels  as $model) {
			if (! strcmp($model->prio, COM_JOMODELS_MODEL_NORMAL)) {
				$params= array("ROOTURI" =>Uri::root());
				$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model->name);
				if (! (strpos( $text, $searchexp) === false) ){
					self::replaceModel($text, $allmodels, $model, $params);
				}
			}
		}
		foreach($allmodels  as $model) {
			if (! strcmp($model->prio, COM_JOMODELS_MODEL_FULL)) {
				$params= array("ROOTURI" =>Uri::root() );
				$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model->name);
				if (! (strpos( $text, $searchexp) === false) ){
					self::replaceFullModel($text, $allmodels, $model, $params);
				}
			}
		}
    }
}


