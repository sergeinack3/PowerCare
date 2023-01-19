<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;

class CMbException extends Exception
{
    public function __construct($text)
    {
        $args = func_get_args();
        $text = CAppUI::tr($text, array_slice($args, 1));

        parent::__construct($text, 0);
    }

    public function stepAjax($type = UI_MSG_WARNING)
    {
        $args = func_get_args();
        $msg  = CAppUI::tr($this->getMessage(), array_slice($args, 1));

        CAppUI::$localize = false;
        CAppUI::stepAjax($msg, $type);
        CAppUI::$localize = true;
    }
}
