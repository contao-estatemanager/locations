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

use Contao\Config;
use Contao\Environment;
use ContaoEstateManager\EstateManager;

class AddonManager
{
    /**
     * Bundle name.
     *
     * @var string
     */
    public static $bundle = 'EstateManagerLocations';

    /**
     * Package.
     *
     * @var string
     */
    public static $package = 'contao-estatemanager/locations';

    /**
     * Addon config key.
     *
     * @var string
     */
    public static $key = 'addon_locations_license';

    /**
     * Is initialized.
     *
     * @var bool
     */
    public static $initialized = false;

    /**
     * Is valid.
     *
     * @var bool
     */
    public static $valid = false;

    /**
     * Licenses.
     *
     * @var array
     */
    private static $licenses = [
        '67e9576f42a7b5b15a7f35dc16e22fe2',
        '1941eee16f9937927269f44e7a204bdf',
        'e32d469904cf3fcc463af566d551a527',
        'a94f0f95f7e7f01c837d57a6963b2747',
        '6d158da2d5dede0791a6e6977d6aac4b',
        '8995ecac21dc8214e0315e58761ec904',
        'b6f4099a80c179abea50f69467116372',
        'c0e1b9435d04122b52050b0aac8e08ec',
        'b98aacae2badb64d84d0fd9bf892bfc2',
        '10a4aea46edf35d3c89658dd8e35fc7f',
        '8915ffe2559ed219faf10208faa7668f',
        '2fdc458331267a302111c1812b05580d',
        '9d7e1b5c6e800bf644236678e30db411',
        '82638d502e403acc577ea8306bb88f47',
        '1753b9821cd2813fb43be4a6d232646f',
        'b80c6ddf5ee1a23e36cefe6377fba78f',
        '57bee9e383bf2d65c259e8162e417fe1',
        'd4a3da317ec03b33bf62bb58c80e86e7',
        'df29c36bb3d09b6a536a89582adac3f9',
        'b726b37dd582f610f844939149ac6db6',
        '546d8380b380fd589aacb2146c0bfdda',
        '6e4a43e59a63a44ef57ace4554983582',
        '41d3b113d2770f1dd59d4e93075a9d26',
        'b0ec8d740c501d316bc2d1527e3f76c0',
        'fede6445565b4d41dc2ab6189f166634',
        'a9444c23e95a15d9bd8fcbf9d1690ee7',
        'b279612c7d81f9eaa02924ee60a2713e',
        'ae34433009f2cec200d8ca4efc7a0684',
        '1a834f5381d87f95ad261f9d30c07df8',
        '5b8229adfbacbd91ee311c0baf22f187',
        '1ff874f97785051329f6e617790926b5',
        '82fde7524c861cd5da82aee40fb9d430',
        '3e363507aaf9d372b4a797eac6d45da3',
        'ebecff9f49214ff5e28aa11acf629884',
        'bb848082863cd29c67668f692c5b2102',
        'd37a88115d843c7e7acabe2fbc50f71a',
        'c315c8eb8cfdbf9cd1c643f02b2f1f85',
        '6a1cd67dc53028398eba90b6e3400af0',
        'e8b9322f7cd08bc1fc2595ac5b2d24e3',
        '480092772702c8940a355dd5fd35beb0',
        '924e1a2b6129fe39ebe056f9b0abbd03',
        'afe6c2d813688ad1a0ed9508b80b1064',
        '50de3508ca33cdfd9934806b65f30c0b',
        'be2847f6a5f75ed4edec50ad59918dea',
        '03c0f81d82649e6a2cd2ee75ecff37ae',
        '3193948dd23f35a1a0a41764911b3ed7',
        '71da3820b1d24a94889658358ced2136',
        '8b38ef7c1a519e982d77d7a9150656dc',
        'e463033823e88d245b609740352140d0',
        '8a745b3103a0e62f86e81c5f66be1cd6',
    ];

    public static function getLicenses()
    {
        return static::$licenses;
    }

    public static function valid()
    {
        if ('/contao/install' === Environment::get('requestUri'))
        {
            return true;
        }

        if (false === static::$initialized)
        {
            static::$valid = EstateManager::checkLicenses(Config::get(static::$key), static::$licenses, static::$key);
            static::$initialized = true;
        }

        return static::$valid;
    }
}
