<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.3
    @build			26th October, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		ModlsModel.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JCB\Component\Jomodels\Administrator\Model;

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
use JCB\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use Joomla\CMS\Helper\TagsHelper;
use JCB\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JCB\Joomla\Utilities\StringHelper;
use JCB\Joomla\Utilities\ObjectHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Modls List Model
 *
 * @since  1.6
 */
class ModlsModel extends ListModel
{
    /**
     * The application object.
     *
     * @var   CMSApplicationInterface  The application instance.
     * @since 3.2.0
     */
    protected CMSApplicationInterface $app;

    /**
     * The styles array.
     *
     * @var    array
     * @since  4.3
     */
    protected array $styles = [
        'administrator/components/com_jomodels/assets/css/admin.css',
        'administrator/components/com_jomodels/assets/css/modls.css'
    ];

    /**
     * The scripts array.
     *
     * @var    array
     * @since  4.3
     */
    protected array $scripts = [
        'administrator/components/com_jomodels/assets/js/admin.js'
    ];

    /**
     * Constructor
     *
     * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'a.id','id',
                'a.published','published',
                'a.access','access',
                'a.ordering','ordering',
                'a.created_by','created_by',
                'a.modified_by','modified_by'
            );
        }

        parent::__construct($config, $factory);

        $this->app ??= Factory::getApplication();
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     * @since   1.7.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->app;
        $input = $this->app->getInput();

        // Adjust the context to support modal layouts.
        if ($layout = $input->get('layout'))
        {
            $this->context .= '.' . $layout;
        }

        // Check if the form was submitted
        $formSubmited = $input->post->get('form_submited');

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        if ($formSubmited)
        {
            $access = $input->post->get('access');
            $this->setState('filter.access', $access);
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
        $this->setState('filter.created_by', $created_by);

        $created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
        $this->setState('filter.created', $created);

        $sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
        $this->setState('filter.sorting', $sorting);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     * @since   1.6
     */
    public function getItems()
    {
        // Check in items
        $this->checkInNow();

        // load parent items
        $items = parent::getItems();

        // set selection value to a translatable value
        if (UtilitiesArrayHelper::check($items))
        {
            foreach ($items as $nr => &$item)
            {
                // convert type
                $item->type = $this->selectionTranslation($item->type, 'type');
            }
        }


        // return items
        return $items;
    }

    /**
     * Method to convert selection values to translatable string.
     *
     * @return  string   The translatable string.
     */
    public function selectionTranslation($value,$name)
    {
        // Array of type language strings
        if ($name === 'type')
        {
            $typeArray = array(
                1 => 'COM_JOMODELS_MODL_NORMAL',
                0 => 'COM_JOMODELS_MODL_FULL'
            );
            // Now check if value is found in this array
            if (isset($typeArray[$value]) && StringHelper::check($typeArray[$value]))
            {
                return $typeArray[$value];
            }
        }
        return $value;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string    An SQL query
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Get the user object.
        $user = $this->getCurrentUser();
        // Create a new query object.
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the jomodels_item table
        $query->from($db->quoteName('#__jomodels_modl', 'a'));

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published))
        {
            $query->where('a.published = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(a.published = 0 OR a.published = 1)');
        }

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
        // Filter by access level.
        $_access = $this->getState('filter.access');
        if ($_access && is_numeric($_access))
        {
            $query->where('a.access = ' . (int) $_access);
        }
        elseif (UtilitiesArrayHelper::check($_access))
        {
            // Secure the array for the query
            $_access = ArrayHelper::toInteger($_access);
            // Filter by the Access Array.
            $query->where('a.access IN (' . implode(',', $_access) . ')');
        }
        // Implement View Level Access
        if (!$user->authorise('core.options', 'com_jomodels'))
        {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Add the list ordering clause.
        $orderCol = $this->getState('list.ordering', 'a.id');
        $orderDirn = $this->getState('list.direction', 'desc');
        if ($orderCol != '')
        {
            // Check that the order direction is valid encase we have a field called direction as part of filers.
            $orderDirn = (is_string($orderDirn) && in_array(strtolower($orderDirn), ['asc', 'desc'])) ? $orderDirn : 'desc';
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get list export data.
     *
     * @param   array  $pks  The ids of the items to get
     * @param   JUser  $user  The user making the request
     *
     * @return mixed  An array of data items on success, false on failure.
     */
    public function getExportData($pks, $user = null)
    {
        // setup the query
        if (($pks_size = UtilitiesArrayHelper::check($pks)) !== false || 'bulk' === $pks)
        {
            // Set a value to know this is export method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
            $_export = true;
            // Get the user object if not set.
            if (!isset($user) || !ObjectHelper::check($user))
            {
                $user = $this->getCurrentUser();
            }
            // Create a new query object.
            $db = $this->getDatabase();
            $query = $db->getQuery(true);

            // Select some fields
            $query->select('a.*');

            // From the jomodels_modl table
            $query->from($db->quoteName('#__jomodels_modl', 'a'));
            // The bulk export path
            if ('bulk' === $pks)
            {
                $query->where('a.id > 0');
            }
            // A large array of ID's will not work out well
            elseif ($pks_size > 500)
            {
                // Use lowest ID
                $query->where('a.id >= ' . (int) min($pks));
                // Use highest ID
                $query->where('a.id <= ' . (int) max($pks));
            }
            // The normal default path
            else
            {
                $query->where('a.id IN (' . implode(',',$pks) . ')');
            }
            // Implement View Level Access
            if (!$user->authorise('core.options', 'com_jomodels'))
            {
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('a.access IN (' . $groups . ')');
            }

            // Order the results by ordering
            $query->order('a.ordering  ASC');

            // Load the items
            $db->setQuery($query);
            $db->execute();
            if ($db->getNumRows())
            {
                $items = $db->loadObjectList();

                // Set values to display correctly.
                if (UtilitiesArrayHelper::check($items))
                {
                    foreach ($items as $nr => &$item)
                    {
                        // unset the values we don't want exported.
                        unset($item->asset_id);
                        unset($item->checked_out);
                        unset($item->checked_out_time);
                    }
                }
                // Add headers to items array.
                $headers = $this->getExImPortHeaders();
                if (ObjectHelper::check($headers))
                {
                    array_unshift($items,$headers);
                }
                return $items;
            }
        }
        return false;
    }

    /**
    * Method to get header.
    *
    * @return mixed  An array of data items on success, false on failure.
    */
    public function getExImPortHeaders()
    {
        // Get a db connection.
        $db = Factory::getDbo();
        // get the columns
        $columns = $db->getTableColumns("#__jomodels_modl");
        if (UtilitiesArrayHelper::check($columns))
        {
            // remove the headers you don't import/export.
            unset($columns['asset_id']);
            unset($columns['checked_out']);
            unset($columns['checked_out_time']);
            $headers = new \stdClass();
            foreach ($columns as $column => $type)
            {
                $headers->{$column} = $column;
            }
            return $headers;
        }
        return false;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * @return  string  A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.id');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        // Check if the value is an array
        $_access = $this->getState('filter.access');
        if (UtilitiesArrayHelper::check($_access))
        {
            $id .= ':' . implode(':', $_access);
        }
        // Check if this is only an number or string
        elseif (is_numeric($_access)
         || StringHelper::check($_access))
        {
            $id .= ':' . $_access;
        }
        $id .= ':' . $this->getState('filter.ordering');
        $id .= ':' . $this->getState('filter.created_by');
        $id .= ':' . $this->getState('filter.modified_by');

        return parent::getStoreId($id);
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

    /**
     * Build an SQL query to check in all items left checked out longer then a set time.
     *
     * @return void
     * @throws \DateMalformedStringException
     * @since 3.2.0
     */
    protected function checkInNow(): void
    {
        // Get set check in time
        $time = ComponentHelper::getParams('com_jomodels')->get('check_in');

        if ($time)
        {
            // Get a db connection.
            $db = $this->getDatabase();
            // Reset query.
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->quoteName('#__jomodels_modl'));
            // Only select items that are checked out.
            $query->where($db->quoteName('checked_out') . ' >= 0');
            // Query only to see if we have a rows
            $db->setQuery($query, 0, 1);
            $db->execute();
            if ($db->getNumRows())
            {
                // Get target date in the past.
                $date = Factory::getDate()->modify($time)->toSql();
                // Reset query.
                $query = $db->getQuery(true);

                // Fields to update.
                $fields = [
                    $db->quoteName('checked_out_time') . ' = NULL',
                    $db->quoteName('checked_out') . ' = NULL'
                ];

                // Conditions for which records should be updated.
                $conditions = [
                    $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' > 0',
                    $db->quoteName('checked_out_time') . ' < ' . $db->quote($date)
                ];

                // Check table.
                $query->update($db->quoteName('#__jomodels_modl'))->set($fields)->where($conditions); 

                $db->setQuery($query);

                $db->execute();
            }
        }
    }
}
