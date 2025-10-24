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
namespace JLTRY\Plugin\Content\JOModels\Extension;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

define('PF_REGEX_SEARCH_PATTERN', "{model:%s");
define('PF_REGEX_MODEL_PATTERN', "#{model:%s([^}]*)}#s");
define('PF_REGEX_VARIABLE_PATTERN', "/{var:([^}|+)}/");


/**
* Model generic class
*
*/
class Model
{
	const _PRIO0="PRIO0";
    const _PRIO1="PRIO0";
	function __construct( $name, $content, $metakey=Model::_DEFAULT)
	{
		$this->name = $name;
		$this->prio = strlen($metakey)? $metakey : Model::_DEFAULT;
		$this->content = str_replace("</pre>", "", str_replace("<pre>", "", $content));
	}
}


class FileModel extends Model
{
	function __construct( $name, $filepath)
	{
		$content = file_get_contents($filepath);
		parent::__construct($name, $content);
	}
}


/**
 * JOModels Content Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.JOModels
 */
class JOModels extends CMSPlugin implements SubscriberInterface
{
	var $allmodels = [];

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare'  => 'onContentPrepare'
        ];
    }


	/**
	* Function to insert model model
	*
	* @param $model : the model
	* @param $params : paramters to replace
	*/
	function _model($model,  $params)
	{
		$html_content = $model->content;
		foreach($params as $param => $value) {
			$html_content = preg_replace("/{{{". $param . "[^}]*}}}/", $value, $html_content);
		}
		//sub models
		foreach($this->allmodels  as $model) {
			$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model->name);
			if (!strpos( $html_content, $searchexp) === false ) {
				$html_content = $this->replace_model($html_content, $model, $params);
			}
		}
		//default variables
		$matches= array();
		while (preg_match(PF_REGEX_VARIABLE_PATTERN, $html_content, $matches)){
			$html_content = preg_replace(PF_REGEX_VARIABLE_PATTERN, '\1', $html_content);
		}
		return $html_content;
	}

	function replace_model($text, $model, $topparams)
	{
		preg_match_all(sprintf(PF_REGEX_MODEL_PATTERN, $model->name), $text, $matches);
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
						/* foreach ($this->allmodels  as $model) {
							$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model);
							if (!strpos( $value, $searchexp) === false ) {
								$params[$key] = $this->replace_model($value, $model, $params);
							}
						}*/
					}
				}
				$p_content = $this->_model($model, $params);
				if (@$matches[1][$i]) {
					$text = str_replace(sprintf("{{%s" . $matches[1][$i] . "}}", $model->name), $p_content, $text);
				} else {
					$text = str_replace(sprintf("{{%s}}", $model->name), $p_content, $text);
				}
			}
		}
		else
		{
			$text = str_replace(sprintf("{{%s ", $model->name), sprintf("erreur de syntaxe: {%s parameters}", $model->name), $text);
		}
		return $text;
	}

	function onContentPrepare(ContentPrepareEvent $event)
    {
        //Escape fast
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        if (!$this->params->get('enabled', 1)) {
            return true;
        }
        // use this format to get the arguments for both Joomla 4 and Joomla 5
        // In Joomla 4 a generic Event is passed
        // In Joomla 5 a concrete ContentPrepareEvent is passed
        [$context, $article, $params, $page] = array_values($event->getArguments());		$match = false;
		if (!count($this->allmodels)) {
			foreach (glob( JPATH_ROOT . '/files/' . '*.tmpl') as $file)
			{
				$splitar = preg_split("/\./", basename($file));
				$this->allmodels[] = new FileModel($splitar[0], $file);
			}
			$catId = $this->params->get('catid');
			if ($catId) {
				$factory = $app->bootComponent('com_content')->getMVCFactory();
				// Get an instance of the generic articles model
				$jarticles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);
				$jarticles->setState('filter.category_id', array($catId));
				$appParams = $app->getParams();
				$jarticles->setState('params', $appParams);
				$jarticles->setState('filter.published', 1);
				$articles= $jarticles->getItems();
				foreach ($articles as $article) {
					$this->allmodels[] = new Model($article->alias, $article->introtext, $article->metakey);
				}
			}
		}
		foreach($this->allmodels  as $model) {
			if (! strcmp($model->prio, Model::_PRIO1)) {
				$params= array("ROOTURI" =>JURI::root() );
				$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model->name);
				if (! strpos( $row->text, $searchexp) === false ){
					$row->text = $this->replace_model($row->text, $model, $params);
				}
			}
		}
		foreach($this->allmodels  as $model) {
			if (! strcmp($model->prio, Model::_PRIO0)) {
				$params= array("ROOTURI" =>JURI::root() );
				$searchexp = sprintf(PF_REGEX_SEARCH_PATTERN, $model->name);
				if (! strpos( $row->text, $searchexp) === false ){
					$row->text = $this->replace_model($row->text, $model, $params);
				}
			}
		}
	}
 
}
