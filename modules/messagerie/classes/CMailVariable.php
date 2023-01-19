<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CMbObject;

class CMailVariable
{
    /**
     * @param string $strToReplace
     * @param CMbObject $object
     *
     * @return string
     */
    public function replaceValue(string $strToReplace, CMbObject $object): string
    {
        $fields  = [];
        $params  = [];
        $search  = [];
        $replace = [];

        $object->completeLabelFields($fields, $params);

        foreach ($fields as $_key => $_field) {
            $search[]  = "%\[([0-9]*)$_key\]%";
            $replace[] = "$_field";
        }
        if (count($search) && count($replace)) {
            $strToReplace = preg_replace($search, $replace, $strToReplace);
        }
        return $strToReplace;
    }
}
