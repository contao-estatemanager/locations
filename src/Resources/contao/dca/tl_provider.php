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
use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use ContaoEstateManager\Locations\AddonManager;
use ContaoEstateManager\ProviderModel;

if (AddonManager::valid())
{
    // Extend the default palette
    PaletteManipulator::create()
        ->addField(['locationSingleSRC', 'teamSingleSRC', 'panoramaSingleSRC'], 'singleSRC', PaletteManipulator::POSITION_AFTER)
        ->addField(['parentProvider'], 'lizenzkennung', PaletteManipulator::POSITION_AFTER)
        ->addField(['beschreibung_standort'], 'beschreibung', PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('default', 'tl_provider')
    ;

    $GLOBALS['TL_DCA']['tl_provider']['fields']['parentProvider'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_provider']['parentProvider'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['tl_provider_locations', 'getProvider'],
        'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_provider']['fields']['beschreibung_standort'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_provider']['beschreibung_standort'],
        'exclude' => true,
        'inputType' => 'textarea',
        'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
        'sql' => 'text NULL',
    ];

    $GLOBALS['TL_DCA']['tl_provider']['fields']['locationSingleSRC'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_provider']['locationSingleSRC'],
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'tl_class' => 'w50'],
        'sql' => 'binary(16) NULL',
    ];
    $GLOBALS['TL_DCA']['tl_provider']['fields']['teamSingleSRC'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_provider']['teamSingleSRC'],
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'tl_class' => 'w50'],
        'sql' => 'binary(16) NULL',
    ];
    $GLOBALS['TL_DCA']['tl_provider']['fields']['panoramaSingleSRC'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_provider']['panoramaSingleSRC'],
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'tl_class' => 'w50'],
        'sql' => 'binary(16) NULL',
    ];
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class tl_provider_locations extends Backend
{
    /**
     * Get all provider and return them as array.
     *
     * @return array
     */
    public function getProvider()
    {
        $arrLocations = [];
        $objLocations = ProviderModel::findAll();

        while ($objLocations->next())
        {
            $arrLocations[$objLocations->id] = $objLocations->postleitzahl.' '.$objLocations->ort.' ('.$objLocations->firma.')';
        }

        return $arrLocations;
    }
}
