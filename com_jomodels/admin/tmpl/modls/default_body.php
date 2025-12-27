<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
                JL Tryoen 
/-------------------------------------------------------------------------------------------------------/

    @version		1.0.6
    @build			27th December, 2025
    @created		27th October, 2025
    @package		JO Models
    @subpackage		default_body.php
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
use JLTRY\Component\Jomodels\Administrator\Helper\JomodelsHelper;
use JLTRY\Joomla\Jomodels\Utilities\Permitted\Actions;
use Joomla\CMS\User\UserFactoryInterface;

// No direct access to this file
defined('_JEXEC') or die;

$edit = "index.php?option=com_jomodels&view=modls&task=modl.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
    <?php
        $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
        $userChkOut = Factory::getContainer()->
            get(UserFactoryInterface::class)->
                loadUserById((int) ($item->checked_out ?? 0));
        $canDo = Actions::get('modl', $item, 'modls');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="order nowrap center hidden-phone">
        <?php if (!$this->isModal && $canDo->get('core.edit.state')): ?>
            <?php
                $iconClass = '';
                if (!$this->saveOrder)
                {
                    $iconClass = ' inactive tip-top" hasTooltip" title="' . Html::tooltipText('JORDERINGDISABLED');
                }
            ?>
            <span class="sortable-handler<?php echo $iconClass; ?>">
                <i class="icon-menu"></i>
            </span>
            <?php if ($this->saveOrder) : ?>
                <input type="text" style="display:none" name="order[]" size="5"
                value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
            <?php endif; ?>
        <?php else: ?>
            &#8942;
        <?php endif; ?>
        </td>
        <td class="nowrap center">
        <?php if (!$this->isModal && $canDo->get('core.edit')): ?>
                <?php if ($item->checked_out) : ?>
                    <?php if ($canCheckin) : ?>
                        <?php echo Html::_('grid.id', $i, $item->id); ?>
                    <?php else: ?>
                        &#9633;
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo Html::_('grid.id', $i, $item->id); ?>
                <?php endif; ?>
        <?php else: ?>
            &#9633;
        <?php endif; ?>
        </td>
        <td class="hidden-phone">
            <?php echo $this->escape($item->alias); ?>
        </td>
        <td class="hidden-phone">
            <?php echo Text::_($item->type); ?>
        </td>
        <td class="center">
        <?php if (!$this->isModal && $canDo->get('core.edit.state')) : ?>
                <?php if ($item->checked_out) : ?>
                    <?php if ($canCheckin) : ?>
                        <?php echo Html::_('jgrid.published', $item->published, $i, 'modls.', true, 'cb'); ?>
                    <?php else: ?>
                        <?php echo Html::_('jgrid.published', $item->published, $i, 'modls.', false, 'cb'); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo Html::_('jgrid.published', $item->published, $i, 'modls.', true, 'cb'); ?>
                <?php endif; ?>
        <?php else: ?>
            <?php echo Html::_('jgrid.published', $item->published, $i, 'modls.', false, 'cb'); ?>
        <?php endif; ?>
        </td>
        <td class="nowrap center hidden-phone">
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>