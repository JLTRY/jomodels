<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.5
    @build			26th October, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		JomodelsModel.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JLTRY\Component\Jomodels\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use JLTRY\Component\Jomodels\Site\Helper\JomodelsHelper;
use JLTRY\Component\Jomodels\Site\Helper\RouteHelper;
use Joomla\CMS\Helper\TagsHelper;
use JLTRY\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JLTRY\Joomla\Utilities\JsonHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels List Model for Jomodels
 *
 * @since  1.6
 */
class JomodelsModel extends ListModel
{
    /**
     * Represents the current user object.
     *
     * @var   User  The user object representing the current user.
     * @since 3.2.0
     */
    protected User $user;

    /**
     * The unique identifier of the current user.
     *
     * @var   int|null  The ID of the current user.
     * @since 3.2.0
     */
    protected ?int $userId;

    /**
     * Flag indicating whether the current user is a guest.
     *
     * @var   int  1 if the user is a guest, 0 otherwise.
     * @since 3.2.0
     */
    protected int $guest;

    /**
     * An array of groups that the current user belongs to.
     *
     * @var   array|null  An array of user group IDs.
     * @since 3.2.0
     */
    protected ?array $groups;

    /**
     * An array of view access levels for the current user.
     *
     * @var   array|null  An array of access level IDs.
     * @since 3.2.0
     */
    protected ?array $levels;

    /**
     * The application object.
     *
     * @var   CMSApplicationInterface  The application instance.
     * @since 3.2.0
     */
    protected CMSApplicationInterface $app;

    /**
     * The input object, providing access to the request data.
     *
     * @var   Input  The input object.
     * @since 3.2.0
     */
    protected Input $input;

    /**
     * The styles array.
     *
     * @var    array
     * @since  4.3
     */
    protected array $styles = [
        'components/com_jomodels/assets/css/site.css',
        'components/com_jomodels/assets/css/jomodels.css'
    ];

    /**
     * The scripts array.
     *
     * @var    array
     * @since  4.3
     */
    protected array $scripts = [
        'components/com_jomodels/assets/js/site.js'
    ];

    /**
     * A custom property for UIKit components. (not used unless you load v2)
     */
    protected $uikitComp;

    /**
     * Constructor
     *
     * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->app ??= Factory::getApplication();
        $this->input ??= $this->app->getInput();

        // Set the current user for authorisation checks (for those calling this model directly)
        $this->user ??= $this->getCurrentUser();
        $this->userId = $this->user->get('id');
        $this->guest = $this->user->get('guest');
        $this->groups = $this->user->get('groups');
        $this->authorisedGroups = $this->user->getAuthorisedGroups();
        $this->levels = $this->user->getAuthorisedViewLevels();

        // will be removed
        $this->initSet = true;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return   string  An SQL query
     * @since    1.6
     */
    protected function getListQuery()
    {
        // Get a db connection.
        $db = $this->getDatabase();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Get from #__jomodels_modl as a
        $query->select($db->quoteName(
            array('a.id','a.alias','a.type','a.text','a.published'),
            array('id','alias','type','text','published')));
        $query->from($db->quoteName('#__jomodels_modl', 'a'));

        // return the query object
        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     * @since   1.6
     */
    public function getItems()
    {
        $user = $this->user;
        // load parent items
        $items = parent::getItems();

        // Get the global params
        $globalParams = ComponentHelper::getParams('com_jomodels', true);

        // Insure all item fields are adapted where needed.
        if (UtilitiesArrayHelper::check($items))
        {
            // Load the Event Dispatcher
            PluginHelper::importPlugin('content');
            foreach ($items as $nr => &$item)
            {
                // Always create a slug for sef URL's
                $item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
                // Check if item has params, or pass whole item.
                $params = (isset($item->params) && JsonHelper::check($item->params)) ? json_decode($item->params) : $item;
                // Make sure the content prepare plugins fire on text
                $_text = new \stdClass();
                $_text->text =& $item->text; // value must be in text
                // Since all values are now in text (Joomla Limitation), we also add the field name (text) to context
                // onContentPrepare Event Trigger
                $this->getDispatcher()->dispatch('onContentPrepare',
                    new ContentPrepareEvent(
                        'onContentPrepare',
                        [
                            'context' => 'com_jomodels.jomodels.text',
                            'subject' => $_text,
                            'params' => $params,
                            'page' => 0
                        ]
                    )
                );
            }
        }

        // return items
        return $items;
    }

    /**
     * Method to get the styles that have to be included on the view
     *
     * @return  array    styles files
     * @since   4.3
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * Method to set the styles that have to be included on the view
     *
     * @return  void
     * @since   4.3
     */
    public function setStyles(string $path): void
    {
        $this->styles[] = $path;
    }

    /**
     * Method to get the script that have to be included on the view
     *
     * @return  array    script files
     * @since   4.3
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * Method to set the script that have to be included on the view
     *
     * @return  void
     * @since   4.3
     */
    public function setScript(string $path): void
    {
        $this->scripts[] = $path;
    }
}
