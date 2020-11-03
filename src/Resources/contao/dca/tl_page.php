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
    // Extend the regular palette
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField(array('location', 'location_token'), 'setMarketingType', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
        ->applyToPalette('regular', 'tl_page')
    ;

    $GLOBALS['TL_DCA']['tl_page']['fields']['location'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['location'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('tl_page_estate_manager_locations' ,'getLocations'),
        'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
        'sql'                     => "int(10) unsigned NOT NULL default '0'",
    );

    $GLOBALS['TL_DCA']['tl_page']['fields']['location_token'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['location_token'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'eval'                    => array('tl_class'=>'w50'),
        'sql'                     => "varchar(255) NOT NULL default ''",
    );
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Fabian Ekert <https://github.com/eki89>
 */
class tl_page_estate_manager_locations extends Contao\Backend
{

    /**
     * Get all locations and return them as array
     *
     * @return array
     */
    public function getLocations(): array
    {
        $arrLocations = array();
        $objLocations = ContaoEstateManager\ProviderModel::findAll();

        if ($objLocations === null)
        {
            return $arrLocations;
        }

        while ($objLocations->next())
        {
            $arrLocations[ $objLocations->id ] = $objLocations->postleitzahl . ' ' . $objLocations->ort . ' (' . $objLocations->firma . ')';
        }

        return $arrLocations;
    }
}
