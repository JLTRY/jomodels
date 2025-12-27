<?php

/***[JCBGUI.power.licensing_template.7.$$$$]***/
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/***[/JCBGUI$$$$]***/

namespace JLTRY\Joomla\Data;



/***[JCBGUI.power.head.7.$$$$]***/
use Joomla\DI\Container;/***[/JCBGUI$$$$]***/

use JLTRY\Joomla\Service\Table;
use JLTRY\Joomla\Service\Database;
use JLTRY\Joomla\Service\Model;
use JLTRY\Joomla\Service\Data;
use JLTRY\Joomla\Interfaces\FactoryInterface;
use JLTRY\Joomla\Abstraction\Factory as ExtendingFactory;


/**
 * Data Factory
 * 
 * @since 3.2.2
 */
abstract class Factory extends ExtendingFactory implements FactoryInterface
{

/***[JCBGUI.power.main_class_code.7.$$$$]***/
    /**
     * Package Container
     *
     * @var   Container|null
     * @since 5.0.3
     **/
    protected static ?Container $container = null;

    /**
     * Create a container object
     *
     * @return  Container
     * @since 3.2.2
     */
    protected static function createContainer(): Container
    {
        return (new Container())
            ->registerServiceProvider(new Table())
            ->registerServiceProvider(new Database())
            ->registerServiceProvider(new Model())
            ->registerServiceProvider(new Data());
    }/***[/JCBGUI$$$$]***/

}

