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
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;
use JLTRY\Plugin\Content\JOModels\Helper\JOModel;
use JLTRY\Plugin\Content\JOModels\Helper\JOFileModel;
use JLTRY\Plugin\Content\JOModels\Helper\JOModelsHelper;



// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects



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

    function onContentPrepare(ContentPrepareEvent $event)
    {
        //Escape fast
        if (!Factory::getApplication()->isClient('site')) {
            return;
        }

        if (!$this->params->get('enabled', 1)) {
            return true;
        }
        // use this format to get the arguments for both Joomla 4 and Joomla 5
        // In Joomla 4 a generic Event is passed
        // In Joomla 5 a concrete ContentPrepareEvent is passed
        [$context, $row, $params, $page] = array_values($event->getArguments());
        if ($context == 'com_jomodels.jomodels.text') return;
        JOModelsHelper::init();
        Log::add("OnContentPrepare $context", Log::DEBUG, "jomodels");
        if (!count($this->allmodels)) {
            //retrieves all models present in /files/jcodes
            foreach (glob( JPATH_ROOT . '/files/jomodels/' . '*.tmpl') as $file)
            {
                $splitar = preg_split("/\./", basename($file));
                $this->allmodels[$splitar[0]] = new JOFileModel($splitar[0], $file);
            }
            //retrieves all articles of "models" category
            /*$catId = $this->params->get('catid');
            if ($catId) {
                $factory = Factory::getApplication()->bootComponent('com_content')->getMVCFactory();
                // Get an instance of the generic articles model
                $jarticles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);
                $jarticles->setState('filter.category_id', array($catId));
                $appParams = Factory::getApplication()->getParams();
                $jarticles->setState('params', $appParams);
                $jarticles->setState('filter.published', 1);
                $articles= $jarticles->getItems();
                foreach ($articles as $article) {
                    $this->allmodels[$article->alias] = new JOModel($article->alias, $article->introtext, $article->metakey);
                }
            }*/
            //retrieves all Models
            $factory = Factory::getApplication()->bootComponent('com_jomodels')->getMVCFactory();
            if ($factory) {
                $jarticles = $factory->createModel('Jomodels', 'Site', ['ignore_request' => true]);
                $appParams = Factory::getApplication()->getParams();
                $jarticles->setState('params', $appParams);
                $jarticles->setState('filter.published', 1);
                $articles = $jarticles->getItems();
                Log::add("articles found ".print_r($articles, true), Log::DEBUG, "jomodels");
                foreach ($articles as $article) {
                    $this->allmodels[$article->alias] = new JOModel($article->alias, $article->text, $article->type);
                }
            }
            else {
                 Log::add("no factory found ", Log::DEBUG, "jomodels");
            }
            Log::add("models found ".print_r($this->allmodels, true), Log::DEBUG, "jomodels");
        }
        JOModelsHelper::replaceModels($row->text, $this->allmodels); 
    }
}
