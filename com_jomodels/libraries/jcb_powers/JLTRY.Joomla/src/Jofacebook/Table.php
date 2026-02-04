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

namespace JLTRY\Joomla\Jofacebook;


use JLTRY\Joomla\Interfaces\TableInterface;
use JLTRY\Joomla\Abstraction\BaseTable;


/**
 * Jofacebook Tables
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
        'post' => [
            'profile' => [
                'name' => 'profile',
                'guid' => '7a5c359e-00b0-4c5c-afc8-5cff3658d4bd',
                'label' => 'COM_JOFACEBOOK_POST_PROFILE_LABEL',
                'type' => 'text',
                'title' => false,
                'list' => 'posts',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'TEXT',
                    'default' => 'EMPTY',
                    'GUID' => '7a5c359e-00b0-4c5c-afc8-5cff3658d4bd',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => false,
                ],
                'link' => NULL,
            ],
            'post' => [
                'name' => 'post',
                'guid' => '1374c9a0-051e-4a03-9eec-393483c18a2c',
                'label' => 'COM_JOFACEBOOK_POST_POST_LABEL',
                'type' => 'text',
                'title' => false,
                'list' => 'posts',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'TEXT',
                    'default' => 'EMPTY',
                    'GUID' => '1374c9a0-051e-4a03-9eec-393483c18a2c',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => false,
                ],
                'link' => NULL,
            ],
            'category' => [
                'name' => 'category',
                'guid' => 'bd58a073-f97f-462b-a18e-df782299fde2',
                'label' => 'COM_JOFACEBOOK_POST_CATEGORY_LABEL',
                'type' => 'category',
                'title' => false,
                'list' => 'posts',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'INT(64)',
                    'default' => '0',
                    'GUID' => 'bd58a073-f97f-462b-a18e-df782299fde2',
                    'null_switch' => 'NULL',
                    'unique_key' => false,
                    'key' => true,
                ],
                'link' => NULL,
            ],
            'description' => [
                'name' => 'description',
                'guid' => '2c2f4d20-2b76-467a-aa1c-8083bba844ca',
                'label' => 'COM_JOFACEBOOK_POST_DESCRIPTION_LABEL',
                'type' => 'text',
                'title' => false,
                'list' => 'posts',
                'store' => NULL,
                'tab_name' => 'Details',
                'db' => [
                    'type' => 'TEXT',
                    'default' => 'EMPTY',
                    'GUID' => '2c2f4d20-2b76-467a-aa1c-8083bba844ca',
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

