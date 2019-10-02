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

use Patchwork\Utf8;
use ContaoEstateManager\ContactPersonModel;
use ContaoEstateManager\ProviderModel;

/**
 * List module for contact person and location records.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class ModuleContactPersonList extends \Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_locationlist';

    /**
     * Contact person array
     * @var array
     */
    private $arrContacts = array();

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
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['contactpersonlist'][0]) . ' ###';
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
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyContactPersonList'];

        list($objLocations, $objContacts) = $this->fetchItems();

        if($objLocations === null || $objContacts === null)
        {
            return false;
        }

        while($objContacts->next())
        {
            if(!array_key_exists($objContacts->pid, $this->arrContacts))
            {
                $this->arrContacts[ $objContacts->pid ] = array();
            }

            $this->arrContacts[ $objContacts->pid ][ $objContacts->id ] = $objContacts->current();
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
        $objTemplate = new \FrontendTemplate($this->locationTemplate);

        if ($objLocation->cssClass != '')
        {
            $strClass = ' ' . $objLocation->cssClass . $strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;
        $objTemplate->contacts = array();
        $objTemplate->showLocationInformation = $this->showLocationInformation;

        $arrMetaFields = \StringUtil::deserialize($this->locationMetaFields, true);

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

        $providerContactsId = $objLocation->parentProvider ?: $objLocation->id;

        if(array_key_exists($providerContactsId, $this->arrContacts))
        {
            $objTemplate->contacts = $this->parseContactPersons($this->arrContacts[ $providerContactsId ]);
        }

        return $objTemplate->parse();
    }

    /**
     * Parse one or more items and return them as array
     *
     * @param array $arrContactPerons
     *
     * @return array
     */
    protected function parseContactPersons($arrContactPerons)
    {
        $limit = count($arrContactPerons);

        if ($limit < 1)
        {
            return array();
        }

        $count = 0;
        $arrContacts = array();

        foreach ($arrContactPerons as $objContact) {
            $arrContacts[] = $this->parseContactPerson($objContact, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
        }

        return $arrContacts;
    }

    /**
     * Parse an item and return it as string
     *
     * @param ProviderModel $objContact
     * @param string        $strClass
     * @param integer       $intCount
     *
     * @return string
     */
    protected function parseContactPerson($objContact, $strClass='', $intCount=0)
    {
        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new \FrontendTemplate($this->contactPersonTemplate);

        if ($objContact->cssClass != '')
        {
            $strClass = ' ' . $objContact->cssClass . $strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;

        $arrMetaFields = \StringUtil::deserialize($this->contactPersonMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            switch ($metaField)
            {
                case 'foto':
                    $objTemplate->addImage = $this->addSingleImageToTemplate($objTemplate, $objContact->singleSRC, $this->contactPersonImgSize);
                    break;
                default:
                    if($objContact->{$metaField})
                    {
                        $objTemplate->{$metaField} = $objContact->{$metaField};
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
            if (!($varSingleSrc instanceof \FilesModel) && \Validator::isUuid($varSingleSrc))
            {
                $objModel = \FilesModel::findByUuid($varSingleSrc);
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
     * @return array
     */
    protected function fetchItems()
    {
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
                $arrLocationsIds = \StringUtil::deserialize($this->locations);

                if($arrLocationsIds !== null)
                {
                    $arrColumns[] = 'id IN (' . implode(',', $arrLocationsIds) . ')';
                }
                break;
        }

        $arrLocationsIds = array();
        $objLocations = ProviderModel::findBy($arrColumns, $arrValues, $arrOptions);

        // Adding the parent provider to be able to deliver their contact persons
        if($objLocations !== null)
        {
            while($objLocations->next())
            {
                $arrLocationsIds[] = $objLocations->id;

                if($objLocations->parentProvider)
                {
                    $arrLocationsIds[] = $objLocations->parentProvider;
                }
            }
        }

        // Contact persons
        $arrColumns = array();
        $arrValues  = array();
        $arrOptions = array('order'=>'department ASC');

        if($arrLocationsIds !== null)
        {
            $arrColumns[] = 'pid IN (' . implode(',',$arrLocationsIds) . ')';
        }
        else
        {
            // ToDo: Überflüssig => published=1
            $arrColumns[] = "pid!=''";
        }

        // Reduce departments
        if($this->useSpecificDepartments)
        {
            $arrDepartmentIds = \StringUtil::deserialize($this->departments);

            if($arrDepartmentIds !== null)
            {
                $arrColumns[] = 'department IN(' . implode(",", $arrDepartmentIds) . ')';
            }
        }

        $objContacts = ContactPersonModel::findBy($arrColumns, $arrValues, $arrOptions);
        $objLocations->reset();

        return array($objLocations, $objContacts);
    }
}
