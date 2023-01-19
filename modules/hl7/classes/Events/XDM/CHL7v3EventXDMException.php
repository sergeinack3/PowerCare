<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDM;

use Exception;
use Ox\Core\CAppUI;

/**
 * XDM Exception
 */
class CHL7v3EventXDMException extends Exception
{
    const EMPTY_ARCHIVE_ZIP        = 1;
    const METADATA_MISSING         = 2;
    const EMPTY_FILE               = 3;
    const METADATA_DIFFERENT_SIZE  = 4;
    const METADATA_DIFFERENT_HASH  = 5;
    const CDA_MISSING              = 6;
    const IS_NO_CDA_FILE           = 7;
    const ERROR_STORE_FILE         = 8;
    const ERROR_STORE_TRACEABILITY = 9;

    public $extraData;

    /**
     * @inheritDoc
     * argument 2 must be named "code" ...
     */
    public function __construct($id, $code = 0)
    {
        $args    = func_get_args();
        $args[0] = "CHL7v3EventXDMException-$id";
        unset($args[1]);

        $this->extraData = $code;
        $message         = call_user_func_array([CAppUI::class, "tr"], $args);

        parent::__construct($message, $id);
    }

    /**
     * @return static
     */
    public static function emptyArchiveZIP(): self
    {
        return new self(self::EMPTY_ARCHIVE_ZIP);
    }

    /**
     * @return static
     */
    public static function emptyMetadataMissing(): self
    {
        return new self(self::METADATA_MISSING);
    }

    /**
     * @return static
     */
    public static function cdaMissing(): self
    {
        return new self(self::CDA_MISSING);
    }

    /**
     * @return static
     */
    public static function noCDAFile(): self
    {
        return new self(self::IS_NO_CDA_FILE);
    }

    /**
     * @return static
     */
    public static function metadataDifferentSize(?int $metadata_size, ?int $cda_size): self
    {
        return new self(self::METADATA_DIFFERENT_SIZE, 0, $metadata_size, $cda_size);
    }

    /**
     * @return static
     */
    public static function metadataDifferentHash(?int $metadata_hash, ?int $cda_hash): self
    {
        return new self(self::METADATA_DIFFERENT_HASH, 0, $metadata_hash, $cda_hash);
    }
}
