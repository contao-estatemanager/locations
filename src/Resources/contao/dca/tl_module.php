<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/locations
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

// Add palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'useSpecificLocations';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'useSpecificDepartments';

// Palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['realEstateLocationList'] = '{title_legend},name,headline,type;{list_legend},useSpecificLocations,useSpecificDepartments;{config_legend},numberOfItems,perPage;{image_legend:hide},imgSize;{template_legend:hide},location_metaFields,location_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Subpalettes
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['useSpecificLocations'] = 'locations';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['useSpecificDepartments'] = 'departments';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['useSpecificLocations'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['useSpecificLocations'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true,'tl_class'=>'w50 clr'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['useSpecificDepartments'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['useSpecificDepartments'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true,'tl_class'=>'w50 clr'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['locations'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locations'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array('tl_module_locations', 'getLocations'),
    'eval'                    => array('includeBlankOption'=>true, 'multiple'=>true,'tl_class'=>'w50 clr'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['departments'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['departments'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array('tl_module_locations', 'getDepartments'),
    'eval'                    => array('includeBlankOption'=>true, 'multiple'=>true,'tl_class'=>'w50 clr'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['location_metaFields'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['location_metaFields'],
    'default'                 => array('location_location', 'location_department', 'location_contact_person'),
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => array('location_location', 'location_department', 'location_contact_person'),
    'reference'               => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['location_template'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['location_template'],
    'default'                 => 'location_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_module_locations', 'getLocationTemplates'),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon>
 */

class tl_module_locations extends Backend
{
    /**
     * Return all location templates as array
     *
     * @return array
     */
    public function getLocationTemplates()
    {
        return $this->getTemplateGroup('location_');
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