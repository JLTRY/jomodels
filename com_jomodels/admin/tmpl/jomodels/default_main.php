<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		default_main.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die;

?>
<?php if(isset($this->icons['main']) && is_array($this->icons['main'])) :?>
    <?php foreach($this->icons['main'] as $icon): ?>
        <div class="dashboard-wraper">
            <div class="dashboard-content">
                <a class="icon" href="<?php echo $icon->url; ?>">
                    <img alt="<?php echo $icon->alt; ?>" src="components/com_jomodels/assets/images/icons/<?php  echo $icon->image; ?>">
                    <span class="dashboard-title"><?php echo Text::_($icon->name); ?></span>
                </a>
             </div>
        </div>
    <?php endforeach; ?>
    <div class="clearfix"></div>
<?php else: ?>
    <div class="alert alert-error"><h4 class="alert-heading"><?php echo Text::_("Permission denied, or not correctly set"); ?></h4><div class="alert-message"><?php echo Text::_("Please notify your System Administrator if result is unexpected."); ?></div></div>
<?php endif; ?>