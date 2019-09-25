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
    protected $strTemplate = 'mod_contactpersonlist';

    /**
     * Contact person aray
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
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyLocationList'];

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

        $arrMetaFields = \StringUtil::deserialize($this->locationMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            if($objLocation->{$metaField})
            {
                // ToDo: Add Images
                switch ($metaField)
                {
                    case 'logo':

                        break;
                    case 'foto':

                        break;
                    default:
                        $objTemplate->{$metaField} = $objLocation->{$metaField};
                }
            }
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;
        $objTemplate->contacts = array();

        if(array_key_exists($objLocation->id, $this->arrContacts))
        {
            $objTemplate->contacts = $this->parseContactPersons($this->arrContacts[ $objLocation->id ]);
        }

        // Add an image
        /*if ($objLocation->addImage && $objLocation->singleSRC != '')
        {
            $objModel = \FilesModel::findByUuid($objLocation->singleSRC);

            if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
            {
                // Do not override the field now that we have a model registry (see #6303)
                $arrLocation = $objLocation->row();

                // Override the default image size
                if ($this->imgSize != '')
                {
                    $size = \StringUtil::deserialize($this->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
                    {
                        $arrLocation['size'] = $this->imgSize;
                    }
                }

                $arrLocation['singleSRC'] = $objModel->path;
                $this->addImageToTemplate($objTemplate, $arrLocation, null, null, $objModel);
            }
        }*/

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

        $arrMetaFields = \StringUtil::deserialize($this->contactPersonMetaFields, true);

        foreach ($arrMetaFields as $metaField)
        {
            if($objContact->{$metaField})
            {
                // ToDo: Add Images
                switch ($metaField)
                {
                    case 'foto':

                        break;
                    default:
                        $objTemplate->{$metaField} = $objContact->{$metaField};
                }

            }
        }

        $objTemplate->class = $strClass;
        $objTemplate->addImage = false;

        // Add an image
        /*if ($objContact->addImage && $objContact->singleSRC != '')
        {
            $objModel = \FilesModel::findByUuid($objContact->singleSRC);

            if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
            {
                // Do not override the field now that we have a model registry (see #6303)
                $arrLocation = $objContact->row();

                // Override the default image size
                if ($this->imgSize != '')
                {
                    $size = \StringUtil::deserialize($this->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
                    {
                        $arrLocation['size'] = $this->imgSize;
                    }
                }

                $arrLocation['singleSRC'] = $objModel->path;
                $this->addImageToTemplate($objTemplate, $arrLocation, null, null, $objModel);
            }
        }*/

        return $objTemplate->parse();
    }

    /**
     * Fetch the matching items
     *
     * @return array
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
                $arrLocationsIds = \StringUtil::deserialize($this->locations);

                if($arrLocationsIds !== null)
                {
                    $arrColumns[] = 'id IN (' . implode(',',$arrLocationsIds) . ')';
                }

                break;
        }

        $objLocations = ProviderModel::findBy($arrColumns, $arrValues, $arrOptions);

        // Contact persons
        $arrColumns = array();
        $arrValues  = array();
        $arrOptions = array('order'=>'department ASC');

        if($arrLocationsIds !== null)
        {
            $arrColumns[] = 'pid IN (' . implode(',',$arrLocationsIds) . ')';
        }
        elseif($intLocationId !== null)
        {
            $arrColumns[] = 'pid=?';
            $arrValues[]  = $intLocationId;
        }
        else{
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

        return array($objLocations, $objContacts);
    }
}
