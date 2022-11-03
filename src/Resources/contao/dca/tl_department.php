<?php

declare(strict_types=1);

/*
 * This file is part of Contao EstateManager.
 *
 * @see        https://www.contao-estatemanager.com/
 * @source     https://github.com/contao-estatemanager/locations
 * @copyright  Copyright (c) 2021 Oveleon GbR (https://www.oveleon.de)
 * @license    https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

$GLOBALS['TL_DCA']['tl_department'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'markAsCopy' => 'title',
        'onload_callback' => [
            ['tl_department', 'checkPermission'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_department']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
                'button_callback' => ['tl_department', 'editDepartment'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_department']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => ['tl_department', 'copyDepartment'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_department']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['tl_department', 'deleteDepartment'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_department']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_department']['title'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];

/*
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\StringUtil;

class tl_department extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_department.
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(): void
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Check permissions to add provider
        if (!$this->User->hasAccess('create', 'departmentp'))
        {
            $GLOBALS['TL_DCA']['tl_department']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_department']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_department']['config']['notCopyable'] = true;
        }

        // Check permissions to delete providers
        if (!$this->User->hasAccess('delete', 'departmentp'))
        {
            $GLOBALS['TL_DCA']['tl_department']['config']['notDeletable'] = true;
        }
    }

    /**
     * Return the edit header button.
     */
    public function editDepartment(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->canEditFieldsOf('tl_department') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the copy department button.
     */
    public function copyDepartment(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('create', 'departmentp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete department button.
     */
    public function deleteDepartment(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('delete', 'departmentp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}
