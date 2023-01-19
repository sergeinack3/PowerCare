<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Fhir\Resources\R4\DocumentManifest\CFHIRResourceDocumentManifest;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\CFHIRResourceDocumentReference;
use Ox\Interop\Fhir\Resources\R4\Binary\CFHIRResourceBinary;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7;

/**
 * Class CILWFRA
 * Inter-Laboratory Workflow Extension Française
 */
class CILWFRA extends CIHE
{
    /** @var string[] */
    public static $versions = [
        "2.1",
        "2.2",
        "2.3",
        "2.4",
        "2.5",
        "2.5.1",
    ];

    /**
     * @var array
     */
    public static $transaction_lab36 = [
        'R01'
    ];

    public static $evenements = [
        "R01" => "CHL7EventORUR01",
    ];

    /**
     * Construct
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_PaLM;
        $this->type   = "ILW_FRA";

        $this->_categories = [
            'LAB-36' => self::$transaction_lab36
        ];

        parent::__construct();
    }

    /**
     * Retrieve transaction name
     *
     * @param string $code Event code
     *
     * @return string|null Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        if (in_array($code, self::$transaction_lab36)) {
            return "R01";
        }

        return null;
    }


    /**
     * @see parent::getEvenements
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }
}
