<?php

/***[JCBGUI.power.licensing_template.11.$$$$]***/
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

namespace JLTRY\Joomla\Jomodels;


use JLTRY\Joomla\Interfaces\TableInterface;
use JLTRY\Joomla\Abstraction\BaseTable;


/**
 * Jomodels Tables
 * 
 * @since 3.2.0
 */
final class Table extends BaseTable implements TableInterface
{

/***[JCBGUI.power.main_class_code.11.$$$$]***/
    /**
     * All areas/views/tables with their field details
     *
     * @var     array
     * @since 3.2.0
     **/
    protected array $tables = [
        'modl' => [
            'alias' => [
                'name' => 'alias',
                'guid' => '19c36d30-0085-4b8a-9abf-1813c37607dc',
                'label' => 'COM_JOMODELS_MODL_ALIAS_LABEL',
                'type' => 'text',
                'title' => false,
                'list' => 'modls',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'CHAR(64)',
                    'default' => '',
                    'GUID' => '19c36d30-0085-4b8a-9abf-1813c37607dc',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => true,
                ],
                'link' => NULL,
            ],
            'type' => [
                'name' => 'type',
                'guid' => '0f086582-fa65-4a23-891e-390f36db01d8',
                'label' => 'COM_JOMODELS_MODL_TYPE_LABEL',
                'type' => 'radio',
                'title' => false,
                'list' => 'modls',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'CHAR(1)',
                    'default' => '',
                    'GUID' => '0f086582-fa65-4a23-891e-390f36db01d8',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => false,
                ],
                'link' => NULL,
            ],
            'text' => [
                'name' => 'text',
                'guid' => 'ab45227a-8697-4837-8e2f-8a649627be50',
                'label' => 'COM_JOMODELS_MODL_TEXT_LABEL',
                'type' => 'editor',
                'title' => false,
                'list' => 'modls',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'VARCHAR(2048)',
                    'default' => '',
                    'GUID' => 'ab45227a-8697-4837-8e2f-8a649627be50',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => false,
                ],
                'link' => NULL,
            ],
            'access' => [
                'name' => 'access',
                'label' => 'Access',
                'type' => 'accesslevel',
                'title' => false,
                'store' => NULL,
                'tab_name' => NULL,
                'db' => [
                    'type' => 'INT(10) unsigned',
                    'default' => '0',
                    'key' => true,
                    'null_switch' => 'NULL',
                ],
            ],
        ],
    ];/***[/JCBGUI$$$$]***/

}

