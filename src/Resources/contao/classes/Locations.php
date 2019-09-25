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

class Locations extends \System
{
    /**
     * Replace insert tags
     *
     * @param $strTag
     *
     * @return bool|mixed|string|null
     */
    public function replaceInsertTags($strTag)
    {
        $arrTag = explode('::', $strTag);

        if ($arrTag[0] != 'location')
        {
            return false;
        }

        global $objPage;

        if($arrTag[1] === 'current' && !!$objPage->location)
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

        switch($arrTag[2])
        {
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
}