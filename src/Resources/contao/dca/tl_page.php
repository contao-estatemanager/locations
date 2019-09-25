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
        ->addField(array('location'), 'setMarketingType', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
        ->applyToPalette('regular', 'tl_page')
    ;

    $GLOBALS['TL_DCA']['tl_page']['fields']['location'] = array(
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['location'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('tl_page_locations' ,'getLocations'),
        'reference'               => &$GLOBALS['TL_LANG']['tl_page'],
        'eval'                    => array('includeBlankOption'=>true,'tl_class'=>'w50'),
        'sql'                     => "varchar(6) NOT NULL default ''",
    );
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class tl_page_locations extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Get all locations and return them as array
     *
     * @return array
     */
    public function getLocations()
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
