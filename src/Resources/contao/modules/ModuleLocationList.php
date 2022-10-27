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
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\StringUtil;
use Contao\Validator;
use ContaoEstateManager\ProviderModel;
use Model\Collection;

/**
 * List module for location records.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class ModuleLocationList extends Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_locationlist';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE')
        {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.mb_strtoupper($GLOBALS['TL_LANG']['FMD']['realEstateLocationList'][0], 'UTF-8').' ###';
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
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyLocationList'];

        $objLocations = $this->fetchItems();

        if (null === $objLocations)
        {
            return false;
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
        $objTemplate = new FrontendTemplate($this->locationTemplate);

        if ('' !== $objLocation->cssClass)
        {
            $strClass = ' '.$objLocation->cssClass.$strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;
        $objTemplate->showLocationInformation = true;
        $objTemplate->contacts = [];

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
     *
     * @return \Contao\Model\Collection|ProviderModel|null
     */
    protected function fetchItems()
    {
        // Locations
        $intLocationId = null;
        $arrLocationsIds = null;

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

        return ProviderModel::findBy($arrColumns, $arrValues, $arrOptions);
    }
}
