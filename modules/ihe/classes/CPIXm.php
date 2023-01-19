<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;

/**
 * Class CPIXm
 * Patient Demographics Query for Mobile
 */
class CPIXm extends CIHEFHIR
{
    /** @var string */
    public const DOMAIN_TYPE = 'PIXm';

    /** @var string */
    public const BASE_PROFILE = 'https://profiles.ihe.net/ITI/PIXm/StructureDefinition';

    /**
     * @var array
     */
    public static $transaction_iti83 = [
        "ihe-pix",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-83
        "ihe-pix" => "CFHIROperationIhePix",
    ];

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->_categories = $this->makeExplicitCategories($this::listResourceCanonicals(), self::$transaction_iti83);
    }

    public static function listResourceCanonicals(): array
    {
        return [
            CFHIRResourcePatient::getCanonical()
        ];
    }

    /**
     * @see parent::getEvenements
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @see parent::getVersions
     */
    public function getVersions(): ?array
    {
        return self::$versions;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
    }
}
