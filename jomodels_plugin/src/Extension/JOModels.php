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
use Joomla\Registry\Registry;
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
    var $logs = false;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare'  => 'onContentPrepare'
        ];
    }


    private function addLogger() {
        Log::addLogger(
            array(
             // Sets file name.
             'text_file' => 'plg_content_jomodels.php',
             // Sets the format of each line.
             'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}'
            ),
            // Sets all but DEBUG log level messages to be sent to the file.
            Log::ALL,
            // The log category which should be recorded in this file.
            array('plg_content_jomodels')
        );
    }

    public function Log($msg, $type = Log::WARNING ){
        if ($this->logs) {
            Log::add($msg, $type, 'plg_content_jomodels');
        }
    }

    function onContentPrepare(ContentPrepareEvent $event)
    {
        // Escape fast if not front-end
        if (!Factory::getApplication()->isClient('site')) {
            return;
        }

        if (!$this->params->get('enabled', 1)) {
            return true;
        }
        $params = new Registry($this->params);

        // Valeur du paramètre, avec une valeur par défaut éventuelle
        $this->logs = (int)$params->get('logs', '0');
        if ($this->logs) {
            $this->addLogger();
        }
        Log::add("log:" . print_r($this->logs, true) . ":", Log::WARNING , '');
        // use this format to get the arguments for both Joomla 4 and Joomla 5
        // In Joomla 4 a generic Event is passed
        // In Joomla 5 a concrete ContentPrepareEvent is passed
        [$context, $row, $params, $page] = array_values($event->getArguments());
        if ($context == 'com_jomodels.jomodels.text') return;
        JOModelsHelper::init();
        $this->Log("OnContentPrepare $context", Log::DEBUG);
        if (!count($this->allmodels)) {
            //retrieves all models present in /files/jcodes
            foreach (glob( JPATH_ROOT . '/files/jomodels/' . '*.tmpl') as $file)
            {
                $splitar = preg_split("/\./", basename($file));
                $this->allmodels[$splitar[0]] = new JOFileModel($splitar[0], $file);
            }
            //retrieves all Models
            $factory = Factory::getApplication()->bootComponent('com_jomodels')->getMVCFactory();
            if ($factory) {
                $jarticles = $factory->createModel('Jomodels', 'Site', ['ignore_request' => true]);
                $appParams = Factory::getApplication()->getParams();
                $jarticles->setState('params', $appParams);
                $jarticles->setState('filter.published', 1);
                $articles = $jarticles->getItems();
                //Log::add("articles found ".print_r($articles, true), Log::DEBUG, "jomodels");
                foreach ($articles as $article) {
                    $this->allmodels[$article->alias] = new JOModel($article->alias, $article->text, $article->type);
                }
            }
            else {
                 $this->Log("no factory found ", Log::DEBUG);
            }
            $this->Log("models found ".print_r($this->allmodels, true), Log::DEBUG);
        }
        JOModelsHelper::replaceModels($this, $row->text, $this->allmodels); 
    }
}
