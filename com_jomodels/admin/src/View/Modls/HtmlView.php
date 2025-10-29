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
namespace JCB\Component\Jomodels\Administrator\View\Modls;

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
use JCB\Joomla\Utilities\ArrayHelper;
use JCB\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels Html View class for the Modls
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
    /**
     * The items from the model
     *
     * @var    mixed
     * @since  3.10.11
     */
    public mixed $items;

    /**
     * The state object
     *
     * @var    mixed
     * @since  3.10.11
     */
    public mixed $state;

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
     * The return here base64 url
     *
     * @var    string
     * @since  3.10.11
     */
    public string $return_here;

    /**
     * The title key used in modal
     *
     * @var    string
     * @since  5.2.1
     */
    public string $modalTitleKey;

    /**
     * The modal state
     *
     * @var    bool
     * @since  5.2.1
     */
    public bool $isModal;

    /**
     * The empty state
     *
     * @var    bool
     * @since  5.2.1
     */
    protected bool $isEmptyState;

    /**
     * The user object.
     *
     * @var    User
     * @since  3.10.11
     */
    public User $user;

    /**
     * Modls view display method
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     * @throws \Exception
     * @since  1.6
     */
    public function display($tpl = null): void
    {
        // Load module values
        $model = $this->getModel();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->styles = $model->getStyles();
        $this->scripts = $model->getScripts();
        $this->user ??= $this->getCurrentUser();
        // Load the filter form from xml for searchtools.
        $this->filterForm = $model->getFilterForm();
        // Load the active filters for searchtools.
        $this->activeFilters = $model->getActiveFilters();
        // Add the list ordering clause.
        $this->listOrder = $this->escape($this->state->get('list.ordering', 'a.id'));
        $this->listDirn = $this->escape($this->state->get('list.direction', 'DESC'));
        $this->saveOrder = $this->listOrder == 'a.ordering';
        // set the return here value
        $this->return_here = urlencode(base64_encode((string) Uri::getInstance()));
        // get global action permissions
        $this->canDo = JomodelsHelper::getActions('modl');
        $this->canEdit = $this->canDo->get('core.edit');
        $this->canState = $this->canDo->get('core.edit.state');
        $this->canCreate = $this->canDo->get('core.create');
        $this->canDelete = $this->canDo->get('core.delete');
        $this->canBatch = ($this->canDo->get('modl.batch') && $this->canDo->get('core.batch'));

        // If we don't have items we load the empty state
        if (is_array($this->items) && !count((array) $this->items) && $this->isEmptyState = $model->getIsEmptyState())
        {
            $this->setLayout('emptystate');
        }

        // We don't need toolbar in the modal window.
        $this->isModal = true;
        if ($this->getLayout() !== 'modal')
        {
            $this->isModal = false;
            $this->addToolbar();
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
     * @since   1.6
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_JOMODELS_MODLS'), 'joomla');

        if ($this->canCreate)
        {
            ToolbarHelper::addNew('modl.add');
        }

        // Only load if there are items
        if (ArrayHelper::check($this->items))
        {
            if ($this->canEdit)
            {
                ToolbarHelper::editList('modl.edit');
            }

            if ($this->canState)
            {
                ToolbarHelper::publishList('modls.publish');
                ToolbarHelper::unpublishList('modls.unpublish');
                ToolbarHelper::archiveList('modls.archive');

                if ($this->canDo->get('core.admin'))
                {
                    ToolbarHelper::checkin('modls.checkin');
                }
            }

            if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
            {
                ToolbarHelper::deleteList('', 'modls.delete', 'JTOOLBAR_EMPTY_TRASH');
            }
            elseif ($this->canState && $this->canDelete)
            {
                ToolbarHelper::trash('modls.trash');
            }
        }

        // set help url for this view if found
        $this->help_url = JomodelsHelper::getHelpUrl('modls');
        if (StringHelper::check($this->help_url))
        {
            ToolbarHelper::help('COM_JOMODELS_HELP_MANAGER', false, $this->help_url);
        }

        // add the options comp button
        if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
        {
            ToolbarHelper::preferences('com_jomodels');
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
    public function escape($var, bool $shorten = true, int $length = 50)
    {
        if (!is_string($var))
        {
            return $var;
        }

        return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
    }

    /**
     * Get the modal data/title key
     *
     * @return  string  The key value.
     * @since   5.2.1
     */
    public function getModalTitleKey(): string
    {
        return $this->modalTitleKey ?? 'id';
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array   containing the field name to sort by as the key and display text as value
     * @since   1.6
     */
    protected function getSortFields()
    {
        return array(
            'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
            'a.published' => Text::_('JSTATUS'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        );
    }
}
