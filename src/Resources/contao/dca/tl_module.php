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
    // Add palettes
    $GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'locationMode';
    $GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'useSpecificDepartments';

    // Palettes
    $GLOBALS['TL_DCA']['tl_module']['palettes']['realEstateContactPersonList'] = '{title_legend},name,headline,type;{config_legend},locationMode,useSpecificDepartments,showLocationInformation;{image_legend:hide},locationImgSize,contactPersonImgSize;{template_legend:hide},locationMetaFields,contactPersonMetaFields,locationTemplate,contactPersonTemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
    $GLOBALS['TL_DCA']['tl_module']['palettes']['realEstateLocationList'] = '{title_legend},name,headline,type;{config_legend},locationMode;{image_legend:hide},locationImgSize;{template_legend:hide},locationMetaFields,locationTemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

    // Subpalettes
    $GLOBALS['TL_DCA']['tl_module']['subpalettes']['locationMode_location_custom'] = 'locations';
    $GLOBALS['TL_DCA']['tl_module']['subpalettes']['useSpecificDepartments'] = 'departments';
    $GLOBALS['TL_DCA']['tl_module']['subpalettes']['listMode_location_dynamic'] = 'realEstateGroups,filterMode';

    // Fields
    $GLOBALS['TL_DCA']['tl_module']['fields']['locationMode'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locationMode'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options'                 => array('location_page', 'location_all', 'location_custom'),
        'reference'               => &$GLOBALS['TL_LANG']['MSC'],
        'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true,'tl_class'=>'w50 clr'),
        'sql'                     => "varchar(255) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['useSpecificDepartments'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['useSpecificDepartments'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('submitOnChange'=>true,'tl_class'=>'w50 clr'),
        'sql'                     => "char(1) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['showLocationInformation'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showLocationInformation'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('tl_class'=>'w50 clr'),
        'sql'                     => "char(1) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['locations'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locations'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'options_callback'        => array('tl_module_locations', 'getLocations'),
        'eval'                    => array('includeBlankOption'=>true, 'multiple'=>true,'mandatory'=>true,'tl_class'=>'w50 clr'),
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

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationMetaFields'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locationMetaFields'],
        'default'                 => array('firma', 'ort', 'strasse', 'hausnummer', 'telefon', 'email'),
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'options'                 => array('firma', 'postleitzahl', 'ort', 'strasse', 'hausnummer', 'bundesland', 'land', 'telefon', 'telefon2', 'fax', 'email', 'beschreibung', 'beschreibung_standort', 'singleSRC'),
        'eval'                    => array('multiple'=>true),
        'sql'                     => "blob NULL",
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['contactPersonMetaFields'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['contactPersonMetaFields'],
        'default'                 => array('anrede', 'vorname', 'nachname'),
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'options'                 => array('firma', 'anrede', 'vorname', 'name', 'titel', 'position', 'email_zentrale', 'email_direkt', 'email_privat', 'email_sonstige', 'email_feedback', 'tel_zentrale', 'tel_durchw', 'tel_fax', 'tel_handy', 'tel_privat', 'tel_sonstige', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'singleSRC'),
        'eval'                    => array('multiple'=>true),
        'sql'                     => "blob NULL",
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationTemplate'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locationTemplate'],
        'default'                 => 'location_default',
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => function (){
            return Contao\Controller::getTemplateGroup('location_');
        },
        'eval'                    => array('tl_class'=>'w50'),
        'sql'                     => "varchar(64) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['contactPersonTemplate'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['contactPersonTemplate'],
        'default'                 => 'location_default',
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => function (){
            return Contao\Controller::getTemplateGroup('contact_person_');
        },
        'eval'                    => array('tl_class'=>'w50'),
        'sql'                     => "varchar(64) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationImgSize'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['locationImgSize'],
        'exclude'                 => true,
        'inputType'               => 'imageSize',
        'reference'               => &$GLOBALS['TL_LANG']['MSC'],
        'eval'                    => array('rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
        'options_callback' => function ()
        {
            return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
        },
        'sql'                     => "varchar(64) NOT NULL default ''"
    );

    // Extend listMode options
    $GLOBALS['TL_DCA']['tl_module']['fields']['listMode']['options'][] = 'location_dynamic';
}
/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

class tl_module_locations extends Backend
{
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
