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

use Contao\Backend;
use Contao\BackendUser;
use Contao\System;
use ContaoEstateManager\Locations\AddonManager;
use ContaoEstateManager\Locations\DepartmentModel;
use ContaoEstateManager\ProviderModel;

if (AddonManager::valid())
{
    // Load language file
    System::loadLanguageFile('tl_locations_meta');

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
    $GLOBALS['TL_DCA']['tl_module']['fields']['locationMode'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['locationMode'],
        'exclude' => true,
        'inputType' => 'select',
        'options' => ['location_page', 'location_all', 'location_custom'],
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql' => "varchar(255) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['useSpecificDepartments'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['useSpecificDepartments'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql' => "char(1) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['showLocationInformation'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['showLocationInformation'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50 clr'],
        'sql' => "char(1) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['locations'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['locations'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'options_callback' => ['tl_module_locations', 'getLocations'],
        'eval' => ['includeBlankOption' => true, 'multiple' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
        'sql' => "varchar(255) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['departments'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['departments'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'options_callback' => ['tl_module_locations', 'getDepartments'],
        'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'multiple' => true, 'tl_class' => 'w50 clr'],
        'sql' => "varchar(255) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationMetaFields'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['locationMetaFields'],
        'default' => ['firma', 'ort', 'strasse', 'hausnummer', 'telefon', 'email'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'options' => ['firma', 'postleitzahl', 'ort', 'strasse', 'hausnummer', 'bundesland', 'land', 'telefon', 'telefon2', 'fax', 'email', 'beschreibung', 'beschreibung_standort', 'singleSRC'],
        'eval' => ['multiple' => true],
        'reference' => &$GLOBALS['TL_LANG']['tl_locations_meta'],
        'sql' => 'text NULL',
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['contactPersonMetaFields'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['contactPersonMetaFields'],
        'default' => ['anrede', 'vorname', 'nachname'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'options' => ['firma', 'anrede', 'vorname', 'name', 'titel', 'position', 'email_zentrale', 'email_direkt', 'email_privat', 'email_sonstige', 'email_feedback', 'tel_zentrale', 'tel_durchw', 'tel_fax', 'tel_handy', 'tel_privat', 'tel_sonstige', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'singleSRC'],
        'eval' => ['multiple' => true],
        'reference' => &$GLOBALS['TL_LANG']['tl_locations_meta'],
        'sql' => 'text NULL',
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationTemplate'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['locationTemplate'],
        'default' => 'location_default',
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['tl_module_locations', 'getLocationTemplates'],
        'eval' => ['tl_class' => 'w50'],
        'sql' => "varchar(64) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['contactPersonTemplate'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['contactPersonTemplate'],
        'default' => 'contact_person_default',
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['tl_module_locations', 'geContactPersonTemplates'],
        'eval' => ['tl_class' => 'w50'],
        'sql' => "varchar(64) NOT NULL default ''",
    ];

    $GLOBALS['TL_DCA']['tl_module']['fields']['locationImgSize'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['locationImgSize'],
        'exclude' => true,
        'inputType' => 'imageSize',
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
        'options_callback' => static fn () => System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance()),
        'sql' => "varchar(64) NOT NULL default ''",
    ];

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
     * Return all location templates as array.
     *
     * @return array
     */
    public function getLocationTemplates()
    {
        return $this->getTemplateGroup('location_');
    }

    /**
     * Return all location templates as array.
     *
     * @return array
     */
    public function geContactPersonTemplates()
    {
        return $this->getTemplateGroup('contact_person_');
    }

    /**
     * Get all locations and return them as array.
     */
    public function getLocations(): array
    {
        $arrLocations = [];
        $objLocations = ProviderModel::findAll();

        while ($objLocations->next())
        {
            $arrLocations[$objLocations->id] = $objLocations->postleitzahl.' '.$objLocations->ort.' ('.$objLocations->firma.')';
        }

        return $arrLocations;
    }

    /**
     * Return all departments as array.
     */
    public function getDepartments(DataContainer $dc): array
    {
        $arrDepartments = [];
        $objDepartments = DepartmentModel::findAll();

        while ($objDepartments->next())
        {
            $arrDepartments[$objDepartments->id] = $objDepartments->title;
        }

        return $arrDepartments;
    }
}
