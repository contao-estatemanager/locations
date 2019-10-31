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

use ContaoEstateManager\ProviderModel;
use ContaoEstateManager\RealEstateModel;

class Locations
{
    /**
     * Replace insert tags
     *
     * - by site reference
     * {{location::current::address}}
     *
     * - by id
     * {{location::10::address}}
     * {{location::10::singleSRC?size=1&template=picture_default}}
     *
     * @param $strTag
     *
     * @return bool|mixed|string|null
     */
    public function replaceInsertTags($strTag)
    {
        $arrTag = explode('::', $strTag);

        if ($arrTag[0] != 'location') {
            return false;
        }

        global $objPage;

        if ($arrTag[1] === 'token')
        {
            if($objPage->location_token)
            {
                return $objPage->location_token;
            }
            elseif(!!$objPage->location)
            {
                $objLocation = ProviderModel::findById($objPage->location);

                if($objLocation !== null)
                {
                    return $objLocation->ort;
                }
            }

            return false;
        }
        elseif($arrTag[1] === 'current' && !!$objPage->location)
        {
            $intLocationId = $objPage->location;
        }
        elseif(is_numeric($arrTag[1]))
        {
            $intLocationId = $arrTag[1];
        }
        else{
            return false;
        }

        $objLocation = ProviderModel::findById($intLocationId);

        if($objLocation === null)
        {
            return false;
        }

        $clearedTag = explode('?', urldecode($arrTag[2]), 2)[0];

        switch($clearedTag)
        {
            case 'panoramaSingleSRC':
            case 'locationSingleSRC':
            case 'teamSingleSRC':
            case 'singleSRC':
                $strImageCache = '';
                $width = null;
                $height = null;
                $alt = '';
                $class = '';
                $rel = '';
                $strFile = $objLocation->{$clearedTag};
                $mode = '';
                $size = null;
                $strTemplate = 'picture_default';

                // Take arguments
                if (strpos($arrTag[2], '?') !== false)
                {
                    $arrChunks = explode('?', urldecode($arrTag[2]), 2);
                    $strSource = \StringUtil::decodeEntities($arrChunks[1]);
                    $strSource = str_replace('[&]', '&', $strSource);
                    $arrParams = explode('&', $strSource);

                    foreach ($arrParams as $strParam)
                    {
                        list($key, $value) = explode('=', $strParam);

                        switch ($key)
                        {
                            case 'alt':
                                $alt = $value;
                                break;

                            case 'class':
                                $class = $value;
                                break;

                            case 'rel':
                                $rel = $value;
                                break;

                            case 'mode':
                                $mode = $value;
                                break;

                            case 'size':
                                $size = (int) $value;
                                break;

                            case 'template':
                                $strTemplate = preg_replace('/[^a-z0-9_]/i', '', $value);
                                break;
                        }
                    }

                    $strFile = $objLocation->{$arrChunks[0]};
                }

                if (\Validator::isUuid($strFile))
                {
                    // Handle UUIDs
                    $objFile = \FilesModel::findByUuid($strFile);

                    if ($objFile === null)
                    {
                        $strImageCache = '';
                        return '';
                    }

                    $strFile = $objFile->path;
                }
                elseif (is_numeric($strFile))
                {
                    // Handle numeric IDs (see #4805)
                    $objFile = \FilesModel::findByPk($strFile);

                    if ($objFile === null)
                    {
                        $strImageCache = '';
                        return '';
                    }

                    $strFile = $objFile->path;
                }
                else
                {
                    // Check the path
                    if (\Validator::isInsecurePath($strFile))
                    {
                        throw new \RuntimeException('Invalid path ' . $strFile);
                    }
                }

                // Generate the thumbnail image
                try
                {
                    $picture = \System::getContainer()->get('contao.image.picture_factory')->create(TL_ROOT . '/' . $strFile, $size);

                    $picture = array
                    (
                        'img' => $picture->getImg(TL_ROOT, TL_FILES_URL),
                        'sources' => $picture->getSources(TL_ROOT, TL_FILES_URL)
                    );

                    $picture['alt'] = $alt;
                    $picture['class'] = $class;
                    $pictureTemplate = new \FrontendTemplate($strTemplate);
                    $pictureTemplate->setData($picture);
                    $strImageCache = $pictureTemplate->parse();

                    // Add a lightbox link
                    if ($rel != '')
                    {
                        if (strncmp($rel, 'lightbox', 8) !== 0)
                        {
                            $attribute = ' rel="' . \StringUtil::specialchars($rel) . '"';
                        }
                        else
                        {
                            $attribute = ' data-lightbox="' . \StringUtil::specialchars(substr($rel, 8)) . '"';
                        }

                        $strImageCache = '<a href="' . TL_FILES_URL . $strFile . '"' . (($alt != '') ? ' title="' . \StringUtil::specialchars($alt) . '"' : '') . $attribute . '>' . $strImageCache . '</a>';
                    }
                }
                catch (\Exception $e)
                {
                    $strImageCache = '';
                }

                return $strImageCache;

            case 'address':
                $strAddress = array();

                $plz = $objLocation->postleitzahl;
                $ort = $objLocation->ort;

                if($objLocation->hausnummer && $objLocation->strasse)
                {
                    $strAddress[] = $objLocation->strasse . ' ' . $objLocation->hausnummer;
                }elseif($objLocation->strasse)
                {
                    $strAddress[] = $objLocation->strasse;
                }

                if($plz && $ort)
                {
                    $strAddress[] = $plz . ' ' . $ort;
                }
                elseif($ort)
                {
                    $strAddress[] = $ort;
                }

                return implode(', ', $strAddress);
        }

        return $objLocation->{$arrTag[2]};
    }

    /**
     * Count properties of assigned provider
     *
     * @param $intCount
     * @param $context
     */
    public function countItems(&$intCount, $context)
    {
        if($context->listMode !== 'location_dynamic')
        {
            return;
        }

        /** @var \PageModel $objPage */
        global $objPage;

        $arrColumns = array("tl_real_estate.published=1");
        $arrValues = array();

        if (!$objPage->location)
        {
            return;
        }

        $objProvider = ProviderModel::findByPk($objPage->location);

        if ($objProvider === null)
        {
            return;
        }

        $objPageDetails = $objPage->loadDetails();
        $objRootPage = \PageModel::findByPk($objPageDetails->rootId);

        if ($objRootPage->realEstateQueryLanguage)
        {
            $arrColumns[] = "tl_real_estate.sprache=?";
            $arrValues[]  = $objRootPage->realEstateQueryLanguage;
        }

        if ($objProvider->parentProvider)
        {
            $objProvider = ProviderModel::findByPk($objProvider->parentProvider);
        }

        $arrColumns[] = "tl_real_estate.anbieternr=?";
        $arrValues[]  = $objProvider->anbieternr;

        $intCount = RealEstateModel::countBy($arrColumns, $arrValues);
    }

    /**
     * Fetch properties of assigned provider
     *
     * @param $objRealEstate
     * @param $limit
     * @param $offset
     * @param $context
     */
    public function fetchItems(&$objRealEstate, $limit, $offset, $context)
    {
        if($context->listMode !== 'location_dynamic')
        {
            return;
        }

        /** @var \PageModel $objPage */
        global $objPage;

        $arrColumns = array("tl_real_estate.published=1");
        $arrValues = array();

        if (!$objPage->location)
        {
            return;
        }

        $objProvider = ProviderModel::findByPk($objPage->location);

        if ($objProvider === null)
        {
            return;
        }

        $objPageDetails = $objPage->loadDetails();
        $objRootPage = \PageModel::findByPk($objPageDetails->rootId);

        if ($objRootPage->realEstateQueryLanguage)
        {
            $arrColumns[] = "tl_real_estate.sprache=?";
            $arrValues[]  = $objRootPage->realEstateQueryLanguage;
        }

        if ($objProvider->parentProvider)
        {
            $objProvider = ProviderModel::findByPk($objProvider->parentProvider);
        }

        $arrColumns[] = "tl_real_estate.anbieternr=?";
        $arrValues[]  = $objProvider->anbieternr;

        $arrOptions = array(
            'limit' => $limit,
            'offset' => $offset
        );

        $objRealEstate = RealEstateModel::findBy($arrColumns, $arrValues, $arrOptions);
    }
}