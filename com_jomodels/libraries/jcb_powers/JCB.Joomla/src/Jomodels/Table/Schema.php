<?php

/***[JCBGUI.power.licensing_template.14.$$$$]***/
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

namespace JCB\Joomla\Jomodels\Table;


use JCB\Joomla\Jomodels\Table;
use JCB\Joomla\Interfaces\SchemaInterface;
use JCB\Joomla\Abstraction\Schema as ExtendingSchema;


/**
 * Jomodels Tables Schema
 * 
 * @since 3.2.1
 */
final class Schema extends ExtendingSchema implements SchemaInterface
{

/***[JCBGUI.power.main_class_code.14.$$$$]***/
    /**
     * Constructor.
     *
     * @param Table   $table   The Table Class.
     *
     * @since 3.2.1
     */
    public function __construct(?Table $table = null)
    {
        $table ??= new Table;

        parent::__construct($table);
    }

    /**
     * Get the targeted component code
     *
     * @return  string
     * @since 3.2.1
     */
    protected function getCode(): string
    {
        return 'jomodels';
    }/***[/JCBGUI$$$$]***/

}

