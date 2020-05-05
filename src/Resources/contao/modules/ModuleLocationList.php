<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/locations
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

namespace ContaoEstateManager\Locations;

use Contao\BackendTemplate;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\StringUtil;
use Contao\Validator;
use Patchwork\Utf8;
use ContaoEstateManager\ContactPersonModel;
use ContaoEstateManager\ProviderModel;

/**
 * List module for location records.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class ModuleLocationList extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_locationlist';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['locationlist'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyLocationList'];

        $objLocations = $this->fetchItems();

        if($objLocations === null)
        {
            return false;
        }

        // Add the locations
        $this->Template->locations = $this->parseLocations($objLocations);
    }

    /**
     * Parse one or more items and return them as array
     *
     * @param Model\Collection $objLocations
     *
     * @return array
     */
    protected function parseLocations($objLocations)
    {
        $limit = $objLocations->count();

        if ($limit < 1)
        {
            return array();
        }

        $count = 0;
        $arrLocations = array();

        while ($objLocations->next())
        {
            /** @var ProviderModel $objLocation */
            $objLocation = $objLocations->current();

            $arrLocations[] = $this->parseLocation($objLocation, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
        }

        return $arrLocations;
    }

    /**
     * Parse an item and return it as string
     *
     * @param ProviderModel $objLocation
     * @param string        $strClass
     * @param integer       $intCount
     *
     * @return string
     */
    protected function parseLocation($objLocation, $strClass='', $intCount=0)
    {
        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->locationTemplate);

        if ($objLocation->cssClass != '')
        {
            $strClass = ' ' . $objLocation->cssClass . $strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;
        $objTemplate->showLocationInformation = true;
        $objTemplate->contacts = array();

        $arrMetaFields = StringUtil::deserialize($this->locationMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            switch ($metaField)
            {
                case 'singleSRC':
                    $objTemplate->addImage = $this->addSingleImageToTemplate($objTemplate, $objLocation->singleSRC, $this->locationImgSize);
                    break;
                default:
                    if($objLocation->{$metaField})
                    {
                        $objTemplate->{$metaField} = $objLocation->{$metaField};
                    }
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Add image to template
     *
     * @param $objTemplate
     * @param $varSingleSrc
     * @param $imgSize
     *
     * @return boolean
     */
    protected function addSingleImageToTemplate(&$objTemplate, $varSingleSrc, $imgSize)
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

            if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
            {
                $image = array
                (
                    'id'         => $objModel->id,
                    'uuid'       => $objModel->uuid,
                    'name'       => $objModel->basename,
                    'singleSRC'  => $objModel->path,
                    'filesModel' => $objModel->current(),
                    'size'       => $imgSize,
                );

                $this->addImageToTemplate($objTemplate, $image, null, null, $objModel);

                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the matching items
     *
     * @return \Contao\Model\Collection|ProviderModel|null
     */
    protected function fetchItems()
    {
        // Locations
        $intLocationId = null;
        $arrLocationsIds = null;

        $arrColumns = array('published=1');
        $arrValues  = array();
        $arrOptions = array();

        switch($this->locationMode)
        {
            case 'location_page':
                global $objPage;
                $intLocationId = $objPage->location;

                if($intLocationId)
                {
                    $arrColumns[] = 'id=?';
                    $arrValues[]  = $intLocationId;
                }

                break;
            case 'location_custom':
                $arrLocationsIds = StringUtil::deserialize($this->locations);

                if($arrLocationsIds !== null)
                {
                    $arrColumns[] = 'id IN (' . implode(',',$arrLocationsIds) . ')';
                }

                break;
        }

        $objLocations = ProviderModel::findBy($arrColumns, $arrValues, $arrOptions);

        return $objLocations;
    }
}
