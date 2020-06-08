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
        ->addField('location_token', 'location', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('regular', 'tl_page')
    ;

    $GLOBALS['TL_DCA']['tl_page']['fields']['location_token'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['location_token'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'eval'                    => array('tl_class'=>'w50'),
        'sql'                     => "varchar(255) NOT NULL default ''",
    );
}
