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
        ->addField(array('locationSRC'), 'singleSRC', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('default', 'tl_provider')
    ;

    $GLOBALS['TL_DCA']['tl_provider']['fields']['locationSRC'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_provider']['locationSRC'],
        'exclude'                 => true,
        'inputType'               => 'fileTree',
        'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'tl_class'=>'w50'),
        'sql'                     => "binary(16) NULL"
    );
}