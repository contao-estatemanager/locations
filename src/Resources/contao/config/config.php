<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/locations
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

// ESTATEMANAGER
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = array('ContaoEstateManager\\Locations', 'AddonManager');

if(ContaoEstateManager\Locations\AddonManager::valid()) {
    // Backend modules
    $GLOBALS['BE_MOD']['real_estate']['department'] = array
    (
        'tables'                => array('tl_department'),
        'hideInNavigation'      => true
    );

    // Models
    $GLOBALS['TL_MODELS']['tl_department'] = '\\ContaoEstateManager\\Locations\\DepartmentModel';

    // Front end modules
    $GLOBALS['FE_MOD']['estatemanager']['realEstateContactPersonList'] = '\\ContaoEstateManager\\Locations\\ModuleContactPersonList';

    // Back end real estate administration modules
    $GLOBALS['TL_RAM']['provider'][] = 'department';

    // Add permissions
    $GLOBALS['TL_PERMISSIONS'][] = 'department';

    // Style sheet
    if (TL_MODE == 'BE')
    {
        $GLOBALS['TL_CSS'][] = 'bundles/estatemanagerlocations/real_estate_locations.css|static';
    }

    // Hooks
    $GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('\\ContaoEstateManager\\Locations\\Locations', 'replaceInsertTags');
}