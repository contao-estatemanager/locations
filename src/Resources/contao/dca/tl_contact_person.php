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
        ->addField(array('department'), 'position', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('default', 'tl_contact_person')
    ;

    $GLOBALS['TL_DCA']['tl_contact_person']['fields']['department'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_contact_person']['department'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('tl_contact_person_department', 'getDepartments'),
        'eval'                    => array('includeBlankOption'=>true,'tl_class'=>'w50'),
        'sql'                     => "int(10) unsigned NOT NULL default '0'",
    );
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
use ContaoEstateManager\Locations\DepartmentModel;

class tl_contact_person_department extends Backend
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
     * Return all departments as array
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getDepartments(DataContainer $dc)
    {
        $arrDepartments = array();
        $objDepartments = ContaoEstateManager\Locations\DepartmentModel::findAll();

        while ($objDepartments->next())
        {
            $arrDepartments[ $objDepartments->id ] = $objDepartments->title;
        }

        return $arrDepartments;
    }
}