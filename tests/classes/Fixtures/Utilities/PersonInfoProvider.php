<?php

/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\Fixtures\Utilities;

use Exception;
use Ox\Mediboard\System\CFirstNameAssociativeSex;

/**
 * Fixtures exceptions.
 */
class PersonInfoProvider
{
    /**
     * @throws Exception
     */
    public static function getFirstName(): string
    {
        $fnas_class = new CFirstNameAssociativeSex();
        $max        = $fnas_class->countList();

        if ($max > 0) {
            $limit      = rand(0, ($max - 1));
            $name = $fnas_class->loadList(null, null, "$limit, 1");

            return reset($name);
        } else {
            return uniqid();
        }
    }

    /**
     * @return string
     */
    public static function getLastName(): string
    {
        return uniqid('fixtures');
    }

    /**
     * @return string
     */
    public static function getSexe(): string
    {
        return rand(0, 1) ? 'm' : 'f';
    }

    /**
     * @return string
     */
    public static function getPhone(): string
    {
        $phone = '0' . rand(6, 7);
        for ($i = 0; $i < 10; $i++) {
            $phone .= rand(0, 9);
        }

        return $phone;
    }

}
