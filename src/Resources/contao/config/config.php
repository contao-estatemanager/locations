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

// ESTATEMANAGER
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\Locations', 'AddonManager'];

use ContaoEstateManager\Locations\AddonManager;
use ContaoEstateManager\Locations\Locations;

if (AddonManager::valid())
{
    // Backend modules
    $GLOBALS['BE_MOD']['real_estate']['department'] = [
        'tables' => ['tl_department'],
        'hideInNavigation' => true,
    ];

    // Models
    $GLOBALS['TL_MODELS']['tl_department'] = 'ContaoEstateManager\Locations\DepartmentModel';

    // Front end modules
    $GLOBALS['FE_MOD']['estatemanager']['realEstateContactPersonList'] = '\ContaoEstateManager\Locations\ModuleContactPersonList';
    $GLOBALS['FE_MOD']['estatemanager']['realEstateLocationList'] = '\ContaoEstateManager\Locations\ModuleLocationList';

    // Back end real estate administration modules
    $GLOBALS['TL_RAM']['provider'][] = 'department';

    // Hooks
    $GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [Locations::class, 'replaceInsertTags'];

    $GLOBALS['TL_HOOKS']['countItemsRealEstateList'][] = [Locations::class, 'countItems'];
    $GLOBALS['TL_HOOKS']['fetchItemsRealEstateList'][] = [Locations::class, 'fetchItems'];

    // Add permissions
    $GLOBALS['TL_PERMISSIONS'][] = 'department';
    $GLOBALS['TL_PERMISSIONS'][] = 'departmentp';
}
