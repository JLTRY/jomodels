<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.3
    @build			26th October, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		RouteHelper.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace JCB\Component\Jomodels\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Categories\Categories;
use JCB\Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels Component Route Helper
 *
 * @since       1.5
 */
abstract class RouteHelper
{
    protected static $lookup;

    /**
     * @param int The route of the Jomodels
     */
    public static function getJomodelsRoute($id = 0, $catid = 0)
    {
        if ($id > 0)
        {
            // Initialize the needel array.
            $needles = array(
                'jomodels'  => array((int) $id)
            );
            // Create the link
            $link = 'index.php?option=com_jomodels&view=jomodels&id='. $id;
        }
        else
        {
            // Initialize the needel array.
            $needles = array(
                'jomodels'  => array()
            );
            // Create the link but don't add the id.
            $link = 'index.php?option=com_jomodels&view=jomodels';
        }
        if ($catid > 1)
        {
            $categories = Categories::getInstance('jomodels.jomodels');
            $category = $categories->get($catid);
            if ($category)
            {
                $needles['category'] = array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&catid='.$catid;
            }
        }

        if ($item = self::_findItem($needles))
        {
            $link .= '&Itemid='.$item;
        }

        return $link;
    }

    protected static function _findItem($needles = null,$type = null)
    {
        $app      = Factory::getApplication();
        $menus    = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language]))
        {
            self::$lookup[$language] = [];

            $component  = ComponentHelper::getComponent('com_jomodels');

            $attributes = array('component_id');
            $values     = array($component->id);

            if ($language != '*')
            {
                $attributes[] = 'language';
                $values[]     = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    $view = $item->query['view'];

                    if (!isset(self::$lookup[$language][$view]))
                    {
                        self::$lookup[$language][$view] = [];
                    }

                    if (isset($item->query['id']))
                    {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
                        {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                    else
                    {
                        self::$lookup[$language][$view][0] = $item->id;
                    }
                }
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$language][$view]))
                {
                    if (ArrayHelper::check($ids))
                    {
                        foreach ($ids as $id)
                        {
                            if (isset(self::$lookup[$language][$view][(int) $id]))
                            {
                                return self::$lookup[$language][$view][(int) $id];
                            }
                        }
                    }
                    elseif (isset(self::$lookup[$language][$view][0]))
                    {
                        return self::$lookup[$language][$view][0];
                    }
                }
            }
        }

        if ($type)
        {
            // Check if the global menu item has been set.
            $params = ComponentHelper::getParams('com_jomodels');
            if ($item = $params->get($type.'_menu', 0))
            {
                return $item;
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();

        if ($active
            && $active->component == 'com_jomodels'
            && ($language == '*' || in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
        {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }
}
