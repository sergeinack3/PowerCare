<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;

/**
 * Class CPDQm
 * Patient Demographics Query for Mobile
 */
class CPDQm extends CIHEFHIR
{
    /** @var string */
    public const DOMAIN_TYPE = 'PDQm';

    /** @var string */
    public const BASE_PROFILE = 'https://profiles.ihe.net/ITI/PDQm/StructureDefinition';

    /**
     * @var array
     */
    public static $transaction_iti78 = [
        CFHIRInteractionSearch::NAME,
        CFHIRInteractionRead::NAME,
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-78
        CFHIRInteractionSearch::NAME => 'CFHIRInteractionSearch',
        CFHIRInteractionRead::NAME   => 'CFHIRInteractionRead',
    ];

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->_categories = $this->makeExplicitCategories($this::listResourceCanonicals(), self::$transaction_iti78);
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
