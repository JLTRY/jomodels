<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		ModlsController.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JLTRY\Component\Jomodels\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use JLTRY\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use JLTRY\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JLTRY\Joomla\Utilities\ObjectHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Modls Admin Controller
 *
 * @since  1.6
 */
class ModlsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_JOMODELS_MODLS';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Modl', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function exportData()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        // check if export is allowed for this user.
        $user = Factory::getApplication()->getIdentity();
        if ($user->authorise('modl.export', 'com_jomodels') && $user->authorise('core.export', 'com_jomodels'))
        {
            // Get the input
            $input = Factory::getApplication()->input;
            $pks = $input->post->get('cid', array(), 'array');
            // Sanitize the input
            $pks = ArrayHelper::toInteger($pks);
            // Get the model
            $model = $this->getModel('Modls');
            // get the data to export
            $data = $model->getExportData($pks);
            if (UtilitiesArrayHelper::check($data))
            {
                // now set the data to the spreadsheet
                $date = Factory::getDate();
                JomodelsHelper::xls($data,'Modls_'.$date->format('jS_F_Y'),'Modls exported ('.$date->format('jS F, Y').')','modls');
            }
        }
        // Redirect to the list screen with error.
        $message = Text::_('COM_JOMODELS_EXPORT_FAILED');
        $this->setRedirect(Route::_('index.php?option=com_jomodels&view=modls', false), $message, 'error');
        return;
    }


    public function importData()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        // check if import is allowed for this user.
        $user = Factory::getApplication()->getIdentity();
        if ($user->authorise('modl.import', 'com_jomodels') && $user->authorise('core.import', 'com_jomodels'))
        {
            // Get the import model
            $model = $this->getModel('Modls');
            // get the headers to import
            $headers = $model->getExImPortHeaders();
            if (ObjectHelper::check($headers))
            {
                // Load headers to session.
                $session = Factory::getSession();
                $headers = json_encode($headers);
                $session->set('modl_VDM_IMPORTHEADERS', $headers);
                $session->set('backto_VDM_IMPORT', 'modls');
                $session->set('dataType_VDM_IMPORTINTO', 'modl');
                // Redirect to import view.
                $message = Text::_('COM_JOMODELS_IMPORT_SELECT_FILE_FOR_MODLS');
                $this->setRedirect(Route::_('index.php?option=com_jomodels&view=import', false), $message);
                return;
            }
        }
        // Redirect to the list screen with error.
        $message = Text::_('COM_JOMODELS_IMPORT_FAILED');
        $this->setRedirect(Route::_('index.php?option=com_jomodels&view=modls', false), $message, 'error');
        return;
    }
}