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

use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes departments.
 *
 * @property int    $id
 * @property string $title
 *
 * @method static DepartmentModel|null findById($id, array $opt=array())
 * @method static DepartmentModel|null findOneBy($col, $val, $opt=array())
 * @method static DepartmentModel|null findOneByTitle($col, $val, $opt=array())
 * @method static Collection|DepartmentModel[]|DepartmentModel|null findMultipleByIds($val, array $opt = [])
 * @method static Collection|DepartmentModel[]|DepartmentModel|null findByTitle($val, array $opt = [])
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class DepartmentModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_department';
}
