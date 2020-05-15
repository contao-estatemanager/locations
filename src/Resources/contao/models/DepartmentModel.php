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

use Contao\Model;

/**
 * Reads and writes departments
 *
 * @property integer $id
 * @property string  $title
 *
 * @method static DepartmentModel|null findById($id, array $opt=array())
 * @method static DepartmentModel|null findOneBy($col, $val, $opt=array())
 * @method static DepartmentModel|null findOneByTitle($col, $val, $opt=array())
 *
 * @method static Model\Collection|DepartmentModel[]|DepartmentModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|DepartmentModel[]|DepartmentModel|null findByTitle($val, array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

class DepartmentModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_department';
}
