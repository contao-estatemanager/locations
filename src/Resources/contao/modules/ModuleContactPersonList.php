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

namespace ContaoEstateManager\Locations;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\Module;
use Contao\StringUtil;
use Contao\Validator;
use ContaoEstateManager\ContactPersonModel;
use ContaoEstateManager\ProviderModel;
use Patchwork\Utf8;

/**
 * List module for contact person and location records.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class ModuleContactPersonList extends Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_locationlist';

    /**
     * Contact person array.
     */
    private array $arrContacts = [];

    /**
     * Display a wildcard in the back end.
     */
    public function generate()
    {
        if (TL_MODE === 'BE')
        {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['realEstateContactPersonList'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyContactPersonList'];

        [$objLocations, $objContacts] = $this->fetchItems();

        if (null === $objLocations || null === $objContacts)
        {
            return false;
        }

        while ($objContacts->next())
        {
            if (!\array_key_exists($objContacts->pid, $this->arrContacts))
            {
                $this->arrContacts[$objContacts->pid] = [];
            }

            $this->arrContacts[$objContacts->pid][$objContacts->id] = $objContacts->current();
        }

        // Add the locations
        $this->Template->locations = $this->parseLocations($objLocations);
    }

    /**
     * Parse one or more items and return them as array.
     */
    protected function parseLocations(Collection $objLocations): array
    {
        $limit = $objLocations->count();

        if ($limit < 1)
        {
            return [];
        }

        $count = 0;
        $arrLocations = [];

        while ($objLocations->next())
        {
            /** @var ProviderModel $objLocation */
            $objLocation = $objLocations->current();

            $arrLocations[] = $this->parseLocation($objLocation, (1 === ++$count ? ' first' : '').($count === $limit ? ' last' : '').(0 === $count % 2 ? ' odd' : ' even'), $count);
        }

        return $arrLocations;
    }

    /**
     * Parse an item and return it as string.
     */
    protected function parseLocation(ProviderModel $objLocation, string $strClass = '', int $intCount = 0): string
    {
        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->locationTemplate);

        if ('' !== $objLocation->cssClass)
        {
            $strClass = ' '.$objLocation->cssClass.$strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;
        $objTemplate->contacts = [];
        $objTemplate->showLocationInformation = $this->showLocationInformation;

        $arrMetaFields = StringUtil::deserialize($this->locationMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            switch ($metaField) {
                case 'singleSRC':
                    $objTemplate->addImage = $this->addSingleImageToTemplate($objTemplate, $objLocation->singleSRC, $this->locationImgSize);
                    break;

                default:
                    if ($objLocation->{$metaField})
                    {
                        $objTemplate->{$metaField} = $objLocation->{$metaField};
                    }
            }
        }

        $providerContactsId = $objLocation->parentProvider ?: $objLocation->id;

        if (\array_key_exists($providerContactsId, $this->arrContacts))
        {
            $objTemplate->contacts = $this->parseContactPersons($this->arrContacts[$providerContactsId]);
        }

        return $objTemplate->parse();
    }

    /**
     * Parse one or more items and return them as array.
     */
    protected function parseContactPersons(array $arrContactPersons): array
    {
        $limit = \count($arrContactPersons);

        if ($limit < 1)
        {
            return [];
        }

        $count = 0;
        $arrContacts = [];

        foreach ($arrContactPersons as $objContact)
        {
            $arrContacts[] = $this->parseContactPerson($objContact, (1 === ++$count ? ' first' : '').($count === $limit ? ' last' : '').(0 === $count % 2 ? ' odd' : ' even'), $count);
        }

        return $arrContacts;
    }

    /**
     * Parse an item and return it as string.
     */
    protected function parseContactPerson(ContactPersonModel $objContact, string $strClass = '', int $intCount = 0): string
    {
        $objTemplate = new FrontendTemplate($this->contactPersonTemplate);

        if ('' !== $objContact->cssClass)
        {
            $strClass = ' '.$objContact->cssClass.$strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;

        $arrMetaFields = StringUtil::deserialize($this->contactPersonMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            switch ($metaField) {
                case 'singleSRC':
                    $varSingleSrc = $objContact->{$metaField};

                    if (!$varSingleSrc)
                    {
                        switch (strtolower($objContact->anrede)) {
                            case 'frau':
                                $varSingleSrc = Config::get('defaultContactPersonFemaleImage');
                                break;

                            case 'herr':
                                $varSingleSrc = Config::get('defaultContactPersonMaleImage');
                                break;
                        }

                        if (!$varSingleSrc)
                        {
                            $varSingleSrc = Config::get('defaultContactPersonImage');
                        }
                    }

                    $objTemplate->addImage = $this->addSingleImageToTemplate($objTemplate, $varSingleSrc, $this->contactPersonImgSize);
                    break;

                default:
                    if ($objContact->{$metaField})
                    {
                        $objTemplate->{$metaField} = $objContact->{$metaField};
                    }
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Add image to template.
     *
     * @param $objTemplate
     * @param $varSingleSrc
     * @param $imgSize
     */
    protected function addSingleImageToTemplate(&$objTemplate, $varSingleSrc, $imgSize): bool
    {
        if ($varSingleSrc)
        {
            if (!($varSingleSrc instanceof FilesModel) && Validator::isUuid($varSingleSrc))
            {
                $objModel = FilesModel::findByUuid($varSingleSrc);
            }
            else
            {
                $objModel = $varSingleSrc;
            }

            if (null !== $objModel && is_file(TL_ROOT.'/'.$objModel->path))
            {
                $image = [
                    'id' => $objModel->id,
                    'uuid' => $objModel->uuid,
                    'name' => $objModel->basename,
                    'singleSRC' => $objModel->path,
                    'filesModel' => $objModel->current(),
                    'size' => $imgSize,
                ];

                $this->addImageToTemplate($objTemplate, $image, null, null, $objModel);

                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the matching items.
     */
    protected function fetchItems(): array
    {
        $arrColumns = ['published=1'];
        $arrValues = [];
        $arrOptions = [];

        switch ($this->locationMode) {
            case 'location_page':
                global $objPage;
                $intLocationId = $objPage->location;

                if ($intLocationId)
                {
                    $arrColumns[] = 'id=?';
                    $arrValues[] = $intLocationId;
                }

                break;

            case 'location_custom':
                $arrLocationsIds = StringUtil::deserialize($this->locations);

                if (null !== $arrLocationsIds)
                {
                    $arrColumns[] = 'id IN ('.implode(',', $arrLocationsIds).')';
                }
                break;
        }

        $arrLocationsIds = [];
        $objLocations = ProviderModel::findBy($arrColumns, $arrValues, $arrOptions);

        // Adding the parent provider to be able to deliver their contact persons
        if (null !== $objLocations)
        {
            while ($objLocations->next())
            {
                $arrLocationsIds[] = $objLocations->id;

                if ($objLocations->parentProvider)
                {
                    $arrLocationsIds[] = $objLocations->parentProvider;
                }
            }
        }

        // Contact persons
        $arrColumns = ['published=1'];
        $arrValues = [];
        $arrOptions = ['order' => 'department ASC'];

        if (\count($arrLocationsIds))
        {
            $arrColumns[] = 'pid IN ('.implode(',', $arrLocationsIds).')';
        }
        else
        {
            $arrColumns[] = "pid!=''";
        }

        // Reduce departments
        if ($this->useSpecificDepartments)
        {
            $arrDepartmentIds = StringUtil::deserialize($this->departments);

            if (null !== $arrDepartmentIds)
            {
                $arrColumns[] = 'department IN('.implode(',', $arrDepartmentIds).')';
            }
        }

        $objContacts = ContactPersonModel::findBy($arrColumns, $arrValues, $arrOptions);

        if ($objLocations)
        {
            $objLocations->reset();
        }

        return [$objLocations, $objContacts];
    }
}
