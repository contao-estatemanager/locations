<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/locations
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

if(ContaoEstateManager\Locations\AddonManager::valid()) {

    // Extend the default palette
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField(array('locationSingleSRC', 'teamSingleSRC', 'panoramaSingleSRC'), 'singleSRC', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
        ->addField(array('parentProvider'), 'lizenzkennung', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('default', 'tl_provider')
    ;

    $GLOBALS['TL_DCA']['tl_provider']['fields']['parentProvider'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_provider']['parentProvider'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('tl_provider_locations', 'getProvider'),
        'eval'                    => array('includeBlankOption'=>true,'tl_class'=>'w50'),
        'sql'                     => "varchar(255) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_provider']['fields']['locationSingleSRC'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_provider']['locationSingleSRC'],
        'exclude'                 => true,
        'inputType'               => 'fileTree',
        'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'tl_class'=>'w50'),
        'sql'                     => "binary(16) NULL"
    );
    $GLOBALS['TL_DCA']['tl_provider']['fields']['teamSingleSRC'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_provider']['teamSingleSRC'],
        'exclude'                 => true,
        'inputType'               => 'fileTree',
        'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'tl_class'=>'w50'),
        'sql'                     => "binary(16) NULL"
    );
    $GLOBALS['TL_DCA']['tl_provider']['fields']['panoramaSingleSRC'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_provider']['panoramaSingleSRC'],
        'exclude'                 => true,
        'inputType'               => 'fileTree',
        'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'tl_class'=>'w50'),
        'sql'                     => "binary(16) NULL"
    );
}


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

class tl_provider_locations extends Backend
{
    /**
     * Get all locations and return them as array
     *
     * @return array
     */
    public function getProvider()
    {
        $arrLocations = array();
        $objLocations = ContaoEstateManager\ProviderModel::findAll();

        while ($objLocations->next())
        {
            $arrLocations[ $objLocations->id ] = $objLocations->postleitzahl . ' ' . $objLocations->ort . ' (' . $objLocations->firma . ')';
        }

        return $arrLocations;
    }
}