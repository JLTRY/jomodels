<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
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
namespace JLTRY\Component\Jomodels\Administrator\View\Import;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use JLTRY\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use JLTRY\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels Import Html View
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    protected $headerList;
    protected $hasPackage = false;
    protected $headers;
    protected $hasHeader = 0;
    protected $dataType;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     * @since  1.6
     */
    public function display($tpl = null)
    {
        $paths = new \StdClass;
        $paths->first = '';
        $state = $this->get('state');

        $this->paths = &$paths;
        $this->state = &$state;
        // get global action permissions
        $this->canDo = JomodelsHelper::getActions('import');

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal')
        {
            $this->addToolbar();
        }

        // get the session object
        $session = Factory::getSession();
        // check if it has package
        $this->hasPackage     = $session->get('hasPackage', false);
        $this->dataType     = $session->get('dataType', false);
        if($this->hasPackage && $this->dataType)
        {
            $this->headerList     = json_decode($session->get($this->dataType.'_VDM_IMPORTHEADERS', false),true);
            $this->headers         = JomodelsHelper::getFileHeaders($this->dataType);
            // clear the data type
            $session->clear('dataType');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode("\n", $errors), 500);
        }

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
        ToolbarHelper::title(Text::_('COM_JOMODELS_IMPORT_TITLE'), 'upload');

        if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
        {
            ToolbarHelper::preferences('com_jomodels');
        }

        // set help url for this view if found
        $this->help_url = JomodelsHelper::getHelpUrl('import');
        if (StringHelper::check($this->help_url))
        {
            ToolbarHelper::help('COM_JOMODELS_HELP_MANAGER', false, $this->help_url);
        }
    }
}
