<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		default.php
    @author			Jean-Luc Tryoen <http://www.jltryoen.fr>	
    @copyright		Copyright (C) 2025. All Rights Reserved
    @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use JLTRY\Component\Jomodels\Administrator\Helper\JomodelsHelper;

// No direct access to this file
defined('_JEXEC') or die;

if ($this->saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_jomodels&task=modls.saveOrderAjax&tmpl=component';
    Html::_('sortablelist.sortable', 'modlList', 'adminForm', strtolower($this->listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo Route::_('index.php?option=com_jomodels&view=modls'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container">
<?php
    // Add the trash helper layout
    echo LayoutHelper::render('trashhelper', $this);
    // Add the searchtools
    echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
<?php if (empty($this->items)): ?>
    <div class="alert alert-no-items">
        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
    </div>
<?php else : ?>
    <table class="table table-striped" id="modlList">
        <thead><?php echo $this->loadTemplate('head');?></thead>
        <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
        <tbody><?php echo $this->loadTemplate('body');?></tbody>
    </table>
    <input type="hidden" name="boxchecked" value="0" />
    </div>
<?php endif; ?>
    <input type="hidden" name="task" value="" />
    <?php echo Html::_('form.token'); ?>
</form>
