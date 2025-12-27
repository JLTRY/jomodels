<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		JomodelsInstallerPowerloader.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die;

// register additional namespace
spl_autoload_register(function ($class) {
    // project-specific base directories and namespace prefix
    $search = [
        'libraries/jcb_powers/JLTRY.Joomla' => 'JLTRY\\Joomla'
    ];
    // Start the search and load if found
    $found = false;
    $found_base_dir = "";
    $found_len = 0;
    foreach ($search as $base_dir => $prefix)
    {
        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0)
        {
            // we have a match so load the values
            $found = true;
            $found_base_dir = $base_dir;
            $found_len = $len;
            // done here
            break;
        }
    }
    // check if we found a match
    if (!$found)
    {
        // not found so move to the next registered autoloader
        return;
    }
    // get the relative class name
    $relative_class = substr($class, $found_len);
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = __DIR__ . '/' . $found_base_dir . '/src' . str_replace('\\', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (file_exists($file))
    {
        require $file;
    }
});
