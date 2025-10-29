<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.3
    @build			26th October, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		ImportController.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JCB\Component\Jomodels\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use JCB\Component\Jomodels\Administrator\Helper\JomodelsHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels Import Base Controller
 *
 * @since  1.6
 */
class ImportController extends BaseController
{
    /**
     * Import an spreadsheet.
     *
     * @return  void
     */
    public function import()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $model = $this->getModel('import');
        if ($model->import())
        {
            $cache = Factory::getCache('mod_menu');
            $cache->clean();
            // TODO: Reset the users acl here as well to kill off any missing bits
        }

        $app = Factory::getApplication();
        $redirect_url = $app->getUserState('com_jomodels.redirect_url');
        if (empty($redirect_url))
        {
            $redirect_url = Route::_('index.php?option=com_jomodels&view=import', false);
        }
        else
        {
            // wipe out the user state when we're going to redirect
            $app->setUserState('com_jomodels.redirect_url', '');
            $app->setUserState('com_jomodels.message', '');
            $app->setUserState('com_jomodels.extension_message', '');
        }
        $this->setRedirect($redirect_url);
    }
}
