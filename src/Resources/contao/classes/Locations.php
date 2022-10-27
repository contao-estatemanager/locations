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

use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use ContaoEstateManager\FilterSession;
use ContaoEstateManager\ProviderModel;
use ContaoEstateManager\RealEstateModel;

class Locations
{
    /**
     * Replace insert tags.
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

        if ('location' !== $arrTag[0])
        {
            return false;
        }

        global $objPage;

        if ('token' === $arrTag[1])
        {
            if ($objPage->location_token)
            {
                return $objPage->location_token;
            }

            if ((bool) $objPage->location)
            {
                $objLocation = ProviderModel::findById($objPage->location);

                if (null !== $objLocation)
                {
                    return $objLocation->ort;
                }
            }

            return false;
        }

        if ('current' === $arrTag[1] && (bool) $objPage->location)
        {
            $intLocationId = $objPage->location;
        }
        elseif (is_numeric($arrTag[1]))
        {
            $intLocationId = $arrTag[1];
        }
        else
        {
            return false;
        }

        $objLocation = ProviderModel::findById($intLocationId);

        if (null === $objLocation)
        {
            return false;
        }

        $clearedTag = explode('?', urldecode($arrTag[2]), 2)[0];

        switch ($clearedTag) {
            case 'panoramaSingleSRC':
            case 'locationSingleSRC':
            case 'teamSingleSRC':
            case 'singleSRC':
                $alt = '';
                $class = '';
                $rel = '';
                $strFile = $objLocation->{$clearedTag};
                $size = null;
                $strTemplate = 'picture_default';

                // Take arguments
                if (false !== strpos($arrTag[2], '?'))
                {
                    $arrChunks = explode('?', urldecode($arrTag[2]), 2);
                    $strSource = StringUtil::decodeEntities($arrChunks[1]);
                    $strSource = str_replace('[&]', '&', $strSource);
                    $arrParams = explode('&', $strSource);

                    foreach ($arrParams as $strParam)
                    {
                        [$key, $value] = explode('=', $strParam);

                        switch ($key) {
                            case 'alt':
                                $alt = $value;
                                break;

                            case 'class':
                                $class = $value;
                                break;

                            case 'rel':
                                $rel = $value;
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

                if (Validator::isUuid($strFile))
                {
                    // Handle UUIDs
                    $objFile = FilesModel::findByUuid($strFile);

                    if (null === $objFile)
                    {
                        return '';
                    }

                    $strFile = $objFile->path;
                }
                elseif (is_numeric($strFile))
                {
                    // Handle numeric IDs (see #4805)
                    $objFile = FilesModel::findByPk($strFile);

                    if (null === $objFile)
                    {
                        return '';
                    }

                    $strFile = $objFile->path;
                }
                else
                {
                    // Check the path
                    if (Validator::isInsecurePath($strFile))
                    {
                        throw new \RuntimeException('Invalid path '.$strFile);
                    }
                }

                // Generate the thumbnail image
                try
                {
                    $picture = System::getContainer()->get('contao.image.picture_factory')->create(TL_ROOT.'/'.$strFile, $size);

                    $picture = [
                        'img' => $picture->getImg(TL_ROOT, TL_FILES_URL),
                        'sources' => $picture->getSources(TL_ROOT, TL_FILES_URL),
                    ];

                    $picture['alt'] = $alt;
                    $picture['class'] = $class;
                    $pictureTemplate = new FrontendTemplate($strTemplate);
                    $pictureTemplate->setData($picture);
                    $strImageCache = $pictureTemplate->parse();

                    // Add a lightbox link
                    if ('' !== $rel)
                    {
                        if (0 !== strncmp($rel, 'lightbox', 8))
                        {
                            $attribute = ' rel="'.StringUtil::specialchars($rel).'"';
                        }
                        else
                        {
                            $attribute = ' data-lightbox="'.StringUtil::specialchars(substr($rel, 8)).'"';
                        }

                        $strImageCache = '<a href="'.TL_FILES_URL.$strFile.'"'.('' !== $alt ? ' title="'.StringUtil::specialchars($alt).'"' : '').$attribute.'>'.$strImageCache.'</a>';
                    }
                }
                catch (\Exception $e)
                {
                    $strImageCache = '';
                }

                return $strImageCache;

            case 'address':
                $strAddress = [];

                $plz = $objLocation->postleitzahl;
                $ort = $objLocation->ort;

                if ($objLocation->hausnummer && $objLocation->strasse)
                {
                    $strAddress[] = $objLocation->strasse.' '.$objLocation->hausnummer;
                }
                elseif ($objLocation->strasse)
                {
                    $strAddress[] = $objLocation->strasse;
                }

                if ($plz && $ort)
                {
                    $strAddress[] = $plz.' '.$ort;
                }
                elseif ($ort)
                {
                    $strAddress[] = $ort;
                }

                return implode(', ', $strAddress);
        }

        return $objLocation->{$arrTag[2]};
    }

    /**
     * Count properties of assigned provider.
     *
     * @param $intCount
     * @param $context
     */
    public function countItems(&$intCount, $context): void
    {
        // ToDo: Performance optimieren
        if ('location_dynamic' !== $context->listMode)
        {
            return;
        }

        $objFilterSession = FilterSession::getInstance();

        [$arrColumns, $arrValues] = $objFilterSession->getTypeParameterByGroups($context->realEstateGroups, $context->filterMode, false, $context);

        /** @var PageModel $objPage */
        global $objPage;

        if (!$objPage->location)
        {
            return;
        }

        $objProvider = ProviderModel::findByPk($objPage->location);

        if (null === $objProvider)
        {
            return;
        }

        if ($objProvider->parentProvider)
        {
            $objProvider = ProviderModel::findByPk($objProvider->parentProvider);
        }

        $arrColumns[] = 'tl_real_estate.anbieternr=?';
        $arrValues[] = $objProvider->anbieternr;

        $intCount = RealEstateModel::countPublishedBy($arrColumns, $arrValues);
    }

    /**
     * Fetch properties of assigned provider.
     *
     * @param $objRealEstate
     * @param $arrOptions
     * @param $context
     */
    public function fetchItems(&$objRealEstate, $arrOptions, $context): void
    {
        // ToDo: Performance optimieren
        if ('location_dynamic' !== $context->listMode)
        {
            return;
        }

        $objFilterSession = FilterSession::getInstance();

        [$arrColumns, $arrValues, $options] = $objFilterSession->getTypeParameterByGroups($context->realEstateGroups, $context->filterMode, false, $context);

        $arrOptions = array_merge($options, $arrOptions);

        /** @var \PageModel $objPage */
        global $objPage;

        if (!$objPage->location)
        {
            return;
        }

        $objProvider = ProviderModel::findByPk($objPage->location);

        if (null === $objProvider)
        {
            return;
        }

        if ($objProvider->parentProvider)
        {
            $objProvider = ProviderModel::findByPk($objProvider->parentProvider);
        }

        $arrColumns[] = 'tl_real_estate.anbieternr=?';
        $arrValues[] = $objProvider->anbieternr;

        $objRealEstate = RealEstateModel::findPublishedBy($arrColumns, $arrValues, $arrOptions);
    }
}
