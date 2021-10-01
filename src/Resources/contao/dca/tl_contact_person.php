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

use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use ContaoEstateManager\Locations\AddonManager;
use ContaoEstateManager\Locations\DepartmentModel;

if (AddonManager::valid())
{
    // Extend the default palette
    PaletteManipulator::create()
        ->addField(['department'], 'position', PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('default', 'tl_contact_person')
    ;

    $GLOBALS['TL_DCA']['tl_contact_person']['fields']['department'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_contact_person']['department'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['tl_contact_person_department', 'getDepartments'],
        'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ];
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class tl_contact_person_department extends Backend
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
     * Return all departments as array.
     */
    public function getDepartments(DataContainer $dc): array
    {
        $arrDepartments = [];
        $objDepartments = DepartmentModel::findAll();

        if (null === $objDepartments)
        {
            return $arrDepartments;
        }

        while ($objDepartments->next())
        {
            $arrDepartments[$objDepartments->id] = $objDepartments->title;
        }

        return $arrDepartments;
    }
}
