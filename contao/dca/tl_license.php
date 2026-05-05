<?php

declare(strict_types=1);

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_license'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onsubmit_callback' => [
            ['erdmannfreunde.license.dca.tl_license_submit', 'onSubmit'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'product' => 'unique',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['product'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['product', 'license_key', 'status', 'valid_until', 'last_checked'],
            'showColumns' => true,
            'label_callback' => ['erdmannfreunde.license.dca.tl_license_options', 'getLabel'],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? 'Wirklich löschen?').'\'))return false;Backend.getScrollOffset()"',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{license_legend},product,license_key;{status_legend},status,valid_until,last_checked',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'product' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'submitOnChange' => false],
            'reference' => &$GLOBALS['TL_LANG']['tl_license']['products'],
            'options_callback' => ['erdmannfreunde.license.dca.tl_license_options', 'getProducts'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'license_key' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50 clr', 'rgxp' => 'extnd'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'status' => [
            'inputType' => 'select',
            'options' => ['unknown', 'valid', 'trial', 'invalid', 'major_mismatch', 'revoked', 'no_key', 'offline_grace'],
            'reference' => &$GLOBALS['TL_LANG']['tl_license']['states'],
            'eval' => ['readonly' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default 'unknown'",
        ],
        'valid_until' => [
            'inputType' => 'text',
            'eval' => ['readonly' => true, 'rgxp' => 'date', 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'last_checked' => [
            'inputType' => 'text',
            'eval' => ['readonly' => true, 'rgxp' => 'datim', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'activation_data' => [
            'sql' => 'text NULL',
        ],
    ],
];
