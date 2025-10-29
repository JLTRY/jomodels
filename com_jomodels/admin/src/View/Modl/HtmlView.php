<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.3
    @build			26th October, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		HtmlView.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JCB\Component\Jomodels\Administrator\View\Modl;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use JCB\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use JCB\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Modl Html View class
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
    /**
     * The app class
     *
     * @var    CMSApplicationInterface
     * @since  5.2.1
     */
    public CMSApplicationInterface $app;

    /**
     * The input class
     *
     * @var    Input
     * @since  5.2.1
     */
    public Input $input;

    /**
     * The params registry
     *
     * @var    Registry
     * @since  5.2.1
     */
    public Registry $params;

    /**
     * The item from the model
     *
     * @var    mixed
     * @since  3.10.11
     */
    public mixed $item;

    /**
     * The state object
     *
     * @var    mixed
     * @since  3.10.11
     */
    public mixed $state;

    /**
     * The form from the model
     *
     * @var    mixed
     * @since  3.10.11
     */
    public mixed $form;

    /**
     * The toolbar object
     *
     * @var    Toolbar
     * @since  3.10.11
     */
    public Toolbar $toolbar;

    /**
     * The styles url array
     *
     * @var    array
     * @since  5.0.0
     */
    protected array $styles;

    /**
     * The scripts url array
     *
     * @var    array
     * @since  5.0.0
     */
    protected array $scripts;

    /**
     * The actions object
     *
     * @var    object
     * @since  3.10.11
     */
    public object $canDo;

    /**
     * The origin referral view name
     *
     * @var    string
     * @since  3.10.11
     */
    public string $ref;

    /**
     * The origin referral item id
     *
     * @var    int
     * @since  3.10.11
     */
    public int $refid;

    /**
     * The referral url suffix values
     *
     * @var    string
     * @since  3.10.11
     */
    public string $referral;

    /**
     * The modal state
     *
     * @var    bool
     * @since  5.2.1
     */
    public bool $isModal;

    /**
     * Modl view display method
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     * @throws \Exception
     * @since  1.6
     */
    public function display($tpl = null): void
    {
        // get application
        $this->app ??= Factory::getApplication();
        // get input
        $this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
        // set params
        $this->params ??= method_exists($this->app, 'getParams')
            ? $this->app->getParams()
            : ComponentHelper::getParams('com_jomodels');
        $this->useCoreUI = true;
        // Load module values
        $model = $this->getModel();
        $this->form ??= $model->getForm();
        $this->item = $model->getItem();
        $this->styles = $model->getStyles();
        $this->scripts = $model->getScripts();
        $this->state = $model->getState();
        // get action permissions
        $this->canDo = JomodelsHelper::getActions('modl', $this->item);
        // get return referral details
        $this->ref = $this->input->get('ref', 0, 'word');
        $this->refid = $this->input->get('refid', 0, 'int');
        $return = $this->input->get('return', null, 'base64');
        // set the referral string
        $this->referral = '';
        if ($this->refid && $this->ref)
        {
            // return to the item that referred to this item
            $this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
        }
        elseif($this->ref)
        {
            // return to the list view that referred to this item
            $this->referral = '&ref=' . (string) $this->ref;
        }
        // check return value
        if (!is_null($return))
        {
            // add the return value
            $this->referral .= '&return=' . (string) $return;
        }

        // Set the toolbar
        if ($this->getLayout() !== 'modal')
        {
            $this->isModal = false;
            $this->addToolbar();
        }
        else
        {
            $this->isModal = true;
            $this->addModalToolbar();
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode("\n", $errors), 500);
        }

        // Set the html view document stuff
        $this->_prepareDocument();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     * @throws  \Exception
     * @since   1.6
     */
    protected function addToolbar(): void
    {
        $this->input->set('hidemainmenu', true);
        $user = $this->getCurrentUser();
        $userId = $user->id;
        $isNew = $this->item->id == 0;

        ToolbarHelper::title( Text::_($isNew ? 'COM_JOMODELS_MODL_NEW' : 'COM_JOMODELS_MODL_EDIT'), 'pencil-2 article-add');
        // Built the actions for new and existing records.
        if (StringHelper::check($this->referral))
        {
            if ($this->canDo->get('core.create') && $isNew)
            {
                // We can create the record.
                ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
            }
            elseif ($this->canDo->get('core.edit'))
            {
                // We can save the record.
                ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
            }
            if ($isNew)
            {
                // Do not creat but cancel.
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CANCEL');
            }
            else
            {
                // We can close it.
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CLOSE');
            }
        }
        else
        {
            if ($isNew)
            {
                // For new records, check the create permission.
                if ($this->canDo->get('core.create'))
                {
                    ToolbarHelper::apply('modl.apply', 'JTOOLBAR_APPLY');
                    ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
                    ToolbarHelper::custom('modl.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
                };
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CANCEL');
            }
            else
            {
                if ($this->canDo->get('core.edit'))
                {
                    // We can save the new record
                    ToolbarHelper::apply('modl.apply', 'JTOOLBAR_APPLY');
                    ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
                    // We can save this record, but check the create permission to see
                    // if we can return to make a new one.
                    if ($this->canDo->get('core.create'))
                    {
                        ToolbarHelper::custom('modl.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
                    }
                }
                $canVersion = ($this->canDo->get('core.version') && $this->canDo->get('modl.version'));
                if ($this->state->params->get('save_history', 1) && $this->canDo->get('core.edit') && $canVersion)
                {
                    ToolbarHelper::versions('com_jomodels.modl', $this->item->id);
                }
                if ($this->canDo->get('core.create'))
                {
                    ToolbarHelper::custom('modl.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
                }
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CLOSE');
            }
        }
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
        // set help url for this view if found
        $this->help_url = JomodelsHelper::getHelpUrl('modl');
        if (StringHelper::check($this->help_url))
        {
            ToolbarHelper::help('COM_JOMODELS_HELP_MANAGER', false, $this->help_url);
        }
    }

    /**
     * Add the modal toolbar.
     *
     * @return  void
     * @throws  \Exception
     * @since   5.0.0
     */
    protected function addModalToolbar()
    {
        $this->input->set('hidemainmenu', true);
        $user = $this->getCurrentUser();
        $userId = $user->id;
        $isNew = $this->item->id == 0;

        ToolbarHelper::title( Text::_($isNew ? 'COM_JOMODELS_MODL_NEW' : 'COM_JOMODELS_MODL_EDIT'), 'pencil-2 article-add');
        // Built the actions for new and existing records.
        if (StringHelper::check($this->referral))
        {
            if ($this->canDo->get('core.create') && $isNew)
            {
                // We can create the record.
                ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
            }
            elseif ($this->canDo->get('core.edit'))
            {
                // We can save the record.
                ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
            }
            if ($isNew)
            {
                // Do not creat but cancel.
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CANCEL');
            }
            else
            {
                // We can close it.
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CLOSE');
            }
        }
        else
        {
            if ($isNew)
            {
                // For new records, check the create permission.
                if ($this->canDo->get('core.create'))
                {
                    ToolbarHelper::apply('modl.apply', 'JTOOLBAR_APPLY');
                    ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
                    ToolbarHelper::custom('modl.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
                };
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CANCEL');
            }
            else
            {
                if ($this->canDo->get('core.edit'))
                {
                    // We can save the new record
                    ToolbarHelper::apply('modl.apply', 'JTOOLBAR_APPLY');
                    ToolbarHelper::save('modl.save', 'JTOOLBAR_SAVE');
                }
                ToolbarHelper::cancel('modl.cancel', 'JTOOLBAR_CLOSE');
            }
        }
    }

    /**
     * Prepare some document related stuff.
     *
     * @return  void
     * @since   1.6
     */
    protected function _prepareDocument(): void
    {
        // Load jQuery
        Html::_('jquery.framework');
        $isNew = ($this->item->id < 1);
        // add styles
        foreach ($this->styles as $style)
        {
            Html::_('stylesheet', $style, ['version' => 'auto']);
        }
        // add scripts
        foreach ($this->scripts as $script)
        {
            Html::_('script', $script, ['version' => 'auto']);
        }
    }

    /**
     * Escapes a value for output in a view script.
     *
     * @param   mixed  $var     The output to escape.
     * @param   bool   $shorten The switch to shorten.
     * @param   int    $length  The shorting length.
     *
     * @return  mixed  The escaped value.
     * @since   1.6
     */
    public function escape($var, bool $shorten = true, int $length = 30)
    {
        if (!is_string($var))
        {
            return $var;
        }

        return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
    }
}
