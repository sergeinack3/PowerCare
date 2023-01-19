<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;

class CHL7v2Exception extends CMbException
{
    public const EMPTY_MESSAGE                   = 1;
    public const WRONG_MESSAGE_TYPE              = 2;
    public const INVALID_SEPARATOR               = 3;
    public const SEGMENT_INVALID_SYNTAX          = 4;
    public const UNKOWN_SEGMENT_TYPE             = 5;
    public const UNEXPECTED_SEGMENT              = 6;
    public const TOO_MANY_FIELDS                 = 7;
    public const SPECS_FILE_MISSING              = 8;
    public const VERSION_UNKNOWN                 = 10;
    public const INVALID_DATA_FORMAT             = 11;
    public const FIELD_EMPTY                     = 12;
    public const TOO_MANY_FIELD_ITEMS            = 13;
    public const SEGMENT_MISSING                 = 14;
    public const MSG_CODE_MISSING                = 15;
    public const UNKNOWN_AUTHORITY               = 16;
    public const UNEXPECTED_DATA_TYPE            = 17;
    public const DATA_TOO_LONG                   = 18;
    public const UNKNOWN_TABLE_ENTRY             = 19;
    public const EVENT_UNKNOWN                   = 20;
    public const FIELD_FORBIDDEN                 = 21;
    public const UNKNOWN_MSG_CODE                = 22;
    public const UNKNOWN_DOMAINS_RETURNED        = 23;
    public const INVALID_DATA_SOURCE             = 24;
    public const INVALID_ACK_APPLICATION_REJECT  = 25;
    public const INVALID_ACK_APPLICATION_ERROR   = 26;
    public const INVALID_ACK_APPLICATION_WARNING = 27;

    public $extraData;

    /** @var int */
    protected $id;

    // argument 2 must be named "code" ...
    public function __construct($id, $code = 0)
    {
        $args     = func_get_args();
        $this->id = $id;
        $args[0]  = "CHL7v2Exception-$id";

        $this->extraData = $code;
        $message         = call_user_func_array([CAppUI::class, "tr"], $args);

        parent::__construct($message, $id);
    }
}
