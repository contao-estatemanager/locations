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
use ContaoEstateManager\Locations\AddonManager;
use ContaoEstateManager\ProviderModel;

if (AddonManager::valid())
{
    // Extend the regular palette
    PaletteManipulator::create()
        ->addField(['location', 'location_token'], 'setMarketingType', PaletteManipulator::POSITION_BEFORE)
        ->applyToPalette('regular', 'tl_page')
    ;

    $GLOBALS['TL_DCA']['tl_page']['fields']['location'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['location'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['tl_page_estate_manager_locations', 'getLocations'],
        'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ];

    $GLOBALS['TL_DCA']['tl_page']['fields']['location_token'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['location_token'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ];
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Fabian Ekert <https://github.com/eki89>
 */
class tl_page_estate_manager_locations extends Backend
{
    /**
     * Get all locations and return them as array.
     */
    public function getLocations(): array
    {
        $arrLocations = [];
        $objLocations = ProviderModel::findAll();

        if (null === $objLocations)
        {
            return $arrLocations;
        }

        while ($objLocations->next())
        {
            $arrLocations[$objLocations->id] = $objLocations->postleitzahl.' '.$objLocations->ort.' ('.$objLocations->firma.')';
        }

        return $arrLocations;
    }
}
