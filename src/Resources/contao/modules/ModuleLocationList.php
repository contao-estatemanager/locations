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

/**
 * List module for locations and child records.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class ModuleLocationList extends \Module
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
            $objTemplate = new \BackendTemplate('be_wildcard');

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
        $limit = null;
        $offset = 0;

        // Maximum number of items
        if ($this->numberOfItems > 0)
        {
            $limit = $this->numberOfItems;
        }

        $this->Template->locations = array();
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyList'];

        // Get the total number of items
        $intTotal = $this->countItems();

        if ($intTotal < 1)
        {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage))
        {
            // Adjust the overall limit
            if (isset($limit))
            {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_l' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
            }

            // Set limit and offset
            $limit = $this->perPage;
            $offset += (max($page, 1) - 1) * $this->perPage;
            $skip = 0;

            // Overall limit
            if ($offset + $limit > $total + $skip)
            {
                $limit = $total + $skip - $offset;
            }

            // Add the pagination menu
            $objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $objLocations = $this->fetchItems(($limit ?: 0), $offset);

        // Add the locations
        if ($objLocations !== null)
        {
            $this->Template->locations = $this->parseLocations($objLocations);
        }
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
            /** @var NewsModel $objLocation */
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
        $objTemplate = new \FrontendTemplate($this->location_template);
        $objTemplate->setData($objLocation->row());

        if ($objLocation->cssClass != '')
        {
            $strClass = ' ' . $objLocation->cssClass . $strClass;
        }

        $objTemplate->class = $strClass;
        #$objTemplate->link = \News::generateNewsUrl($objLocation, $blnAddArchive);
        $objTemplate->count = $intCount; // see #5708
        $objTemplate->text = '';
        $objTemplate->addImage = false;

        // Add an image
        if ($objLocation->addImage && $objLocation->singleSRC != '')
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
        }

        return $objTemplate->parse();
    }

    /**
     * Count the total matching items
     *
     * @param array   $locations
     *
     * @return integer
     */
    protected function countItems()
    {
        #return ProviderModel::countBy($locations);
    }

    /**
     * Fetch the matching items
     *
     * @param array   $locations
     * @param integer $limit
     * @param integer $offset
     *
     * @return Model\Collection|ProviderModel|null
     */
    protected function fetchItems($limit, $offset)
    {
        if($this->useSpecificLocations)
        {

        }
        #return \ProviderModel::findMultipleByIds($locations, $limit, $offset);
    }
}
