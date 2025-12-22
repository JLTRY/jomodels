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
namespace JLTRY\Component\Jomodels\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use JLTRY\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use JLTRY\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JLTRY\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Jomodels List Model
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
     * View groups of this component
     *
     * @var   array<string, string>
     * @since 5.1.1
     */
    protected array $viewGroups = [
        'main' => [],
    ];

    /**
     * View access array.
     *
     * @var   array<string, string>
     * @since 5.1.1
     */
    protected array $viewAccess = [
        'modls.access' => 'modl.access',
        'modl.access' => 'modl.access',
        'modls.submenu' => 'modl.submenu',
    ];

    /**
     * The styles array.
     *
     * @var    array
     * @since  4.3
     */
    protected array $styles = [
        'administrator/components/com_jomodels/assets/css/admin.css',
        'administrator/components/com_jomodels/assets/css/dashboard.css'
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
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->user ??= $this->getCurrentUser();
    }

    /**
     * Get dashboard icons, grouped by view sections.
     *
     * @return array<string, array<int, \stdClass|false>>
     * @since  5.1.1
     */
    public function getIcons(): array
    {
        $icons = [];

        foreach ($this->viewGroups as $group => $views)
        {
            if (!UtilitiesArrayHelper::check($views))
            {
                $icons[$group][] = false;
                continue;
            }

            foreach ($views as $view)
            {
                $icon = $this->buildIconObject($view);
                if ($icon !== null)
                {
                    $icons[$group][] = $icon;
                }
            }
        }

        return $icons;
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
     * Build a single dashboard icon if access is granted.
     *
     * @param string $view The view string to parse.
     *
     * @return \stdClass|null  The icon object or null if access denied.
     * @since  5.1.1
     */
    protected function buildIconObject(string $view): ?\stdClass
    {
        $parsed = $this->parseViewDefinition($view);
        if (!$parsed)
        {
            return null;
        }

        [
            'type' => $type,
            'name' => $name,
            'url' => $url,
            'image' => $image,
            'alt' => $alt,
            'viewName' => $viewName,
            'add' => $add,
        ] = $parsed;

        if (!$this->hasAccessToView($viewName, $add))
        {
            return null;
        }

        return $this->createIconObject($url, $name, $image, $alt);
    }

    /**
     * Parse a view string into structured components.
     *
     * @param string $view  The view definition string.
     *
     * @return array<string, mixed>|null  Parsed values or null on failure.
     * @since  5.1.1
     */
    protected function parseViewDefinition(string $view): ?array
    {
        $add = false;

        if (strpos($view, '||') !== false)
        {
            $parts = explode('||', $view);
            if (count($parts) === 3)
            {
                [$type, $name, $url] = $parts;
                return [
                    'type' => $type,
                    'name' => 'COM_JOMODELS_DASHBOARD_' . StringHelper::safe($name, 'U'),
                    'url' => $url,
                    'image' => "{$name}.{$type}",
                    'alt' => $name,
                    'viewName' => $name,
                    'add' => false,
                ];
            }
        }

        if (strpos($view, '.') !== false)
        {
            $parts = explode('.', $view);
            $type = $parts[0] ?? '';
            $name = $parts[1] ?? '';
            $action = $parts[2] ?? null;
            $viewName = $name;

            if ($action)
            {
                if ($action === 'add')
                {
                    $url = "index.php?option=com_jomodels&view={$name}&layout=edit";
                    $image = "{$name}_{$action}.{$type}";
                    $alt = "{$name}&nbsp;{$action}";
                    $name = 'COM_JOMODELS_DASHBOARD_' .
                            StringHelper::safe($name, 'U') . '_ADD';
                    $add = true;
                }
                else
                {
                    if (strpos($action, '_qpo0O0oqp_') !== false)
                    {
                        [$action, $ext] = explode('_qpo0O0oqp_', $action);
                        $extension = str_replace('_po0O0oq_', '.', $ext);
                    }
                    else
                    {
                        $extension = "com_jomodels.{$name}";
                    }
                    $url = "index.php?option=com_categories&view=categories&extension={$extension}";
                    $image = "{$name}_{$action}.{$type}";
                    $alt = "{$name}&nbsp;{$action}";
                    $name = 'COM_JOMODELS_DASHBOARD_' .
                            StringHelper::safe($name, 'U') . '_' .
                            StringHelper::safe($action, 'U');
                }
            }
            else
            {
                $url = "index.php?option=com_jomodels&view={$name}";
                $image = "{$name}.{$type}";
                $alt = $name;
                $name = 'COM_JOMODELS_DASHBOARD_' .
                        StringHelper::safe($name, 'U');
            }

            return compact('type', 'name', 'url', 'image', 'alt', 'viewName', 'add');
        }

        return [
            'type' => 'png',
            'name' => ucwords($view) . '<br /><br />',
            'url' => "index.php?option=com_jomodels&view={$view}",
            'image' => "{$view}.png",
            'alt' => $view,
            'viewName' => $view,
            'add' => false,
        ];
    }

    /**
     * Determine if the user has access to view or create the item.
     *
     * @param string $viewName The base name of the view.
     * @param bool $add If this is an add-action.
     *
     * @return bool
     * @since  5.1.1
     */
    protected function hasAccessToView(string $viewName, bool $add): bool
    {
        $viewAccess = $this->viewAccess;
        $accessAdd = $add && isset($viewAccess["{$viewName}.create"])
            ? $viewAccess["{$viewName}.create"]
            : ($add ? 'core.create' : '');

        $accessTo = $viewAccess["{$viewName}.access"] ?? '';

        $dashboardAdd = isset($viewAccess["{$viewName}.dashboard_add"]) &&
                    $this->user->authorise($viewAccess["{$viewName}.dashboard_add"], 'com_jomodels');

        $dashboardList = isset($viewAccess["{$viewName}.dashboard_list"]) &&
                    $this->user->authorise($viewAccess["{$viewName}.dashboard_list"], 'com_jomodels');

        if ($add && StringHelper::check($accessAdd))
        {
            return $this->user->authorise($accessAdd, 'com_jomodels') && $dashboardAdd;
        }

        if (StringHelper::check($accessTo))
        {
            return $this->user->authorise($accessTo, 'com_jomodels') && $dashboardList;
        }

        return !$accessTo && !$accessAdd;
    }

    /**
     * Create a \stdClass icon object.
     *
     * @param string $url Icon URL.
     * @param string $name Language string or label.
     * @param string $image Image filename.
     * @param string $alt Alt text.
     *
     * @return \stdClass
     * @since  5.1.1
     */
    protected function createIconObject(string $url, string $name, string $image, string $alt): \stdClass
    {
        $icon = new \stdClass;
        $icon->url = $url;
        $icon->name = $name;
        $icon->image = $image;
        $icon->alt = $alt;
        return $icon;
    }
}
