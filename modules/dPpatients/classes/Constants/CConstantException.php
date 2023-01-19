<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Exception;
use Ox\Core\CAppUI;

/**
 * Description
 */
class CConstantException extends Exception
{
    /** @var int */
    public const INVALID_SPEC                 = 0;
    /** @var int */
    public const INVALID_STORE_RELEVE         = 1;
    /** @var int */
    public const INVALID_ARGUMENT             = 2;
    /** @var int */
    public const IDENTICAL_CONSTANT           = 3;
    /** @var int */
    public const VALUE_NOT_FOUND              = 4;
    /** @var int */
    public const INVALID_STORE_CONSTANT       = 5;
    /** @var int */
    public const FUNCTION_NOT_IMPLEMENTED     = 6;
    /** @var int */
    public const INVALID_DELETE_ALERT         = 7;
    /** @var int */
    public const INVALID_DELETE_SPEC          = 8;
    /** @var int */
    public const INVALID_STORE_SPEC           = 9;
    /** @var int */
    public const INVALID_STORE_ALERT          = 10;
    /** @var int */
    public const INVALID_DELETE_CONSTANT      = 11;
    /** @var int */
    public const RELEVE_NOT_FOUND             = 12;
    /** @var int */
    public const INVALID_VALUE_UNDER_MINIMUM  = 13;
    /** @var int */
    public const INVALID_VALUE_UPPER_MAXIMUM  = 14;
    /** @var int */
    public const INVALID_VALUE_NOT_AUTHORIZED = 15;
    /** @var int */
    public const INVALID_DOCUMENT_XML         = 16;
    /** @var int */
    public const INVALID_RELEVE               = 17;
    /** @var int */
    public const INVALID_UNIT                 = 18;
    /** @var int */
    public const INVALID_OPERATOR             = 19;
    /** @var int */
    public const INVALID_STORE_COMMENT        = 20;
    /** @var int  */
    public const CONSTANT_NOT_FOUND           = 21;

    /**
     * CConstantException constructor.
     *
     * @param int    $id  of exception
     * @param String|null $msg optionnal msg
     */
    public function __construct(int $id, ?string $msg = "")
    {
        $message = CAppUI::tr("CConstantException-" . $id, $msg);
        parent::__construct($message, $id);
    }
}
