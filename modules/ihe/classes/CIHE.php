<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Core\CMbException;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;

/**
 * Class CIHE
 * IHE classes
 */
class CIHE extends CInteropNorm
{
    /**
     * @var array
     */
    public static $object_handlers = [
        'CSipObjectHandler'     => 'CITI30DelegatedHandler',
        'CSmpObjectHandler'     => 'CITI31DelegatedHandler',
        'CSaEventObjectHandler' => [
            'CRAD3DelegatedHandler',
            'CRAD48DelegatedHandler',
        ],
        'CFilesObjectHandler'   => [
            'CRAD28DelegatedHandler',
            'CPDC01DelegatedHandler',
        ],
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->name = "CIHE";

        parent::__construct();
    }

    /**
     * Retrieve handlers list
     *
     * @return array Handlers list
     */
    public static function getObjectHandlers(): ?array
    {
        return self::$object_handlers;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     * @throws CMbException
     *
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        switch ($exchange->type) {
            case "PAM":
                return CPAM::getEvent($exchange);

            case "PAM_FRA":
                return CPAMFRA::getEvent($exchange);

            case "DEC":
                return CDEC::getEvent($exchange);

            case "SWF":
                return CSWF::getEvent($exchange);

            case "PDQ":
                return CPDQ::getEvent($exchange);

            case "PIX":
                return CPIX::getEvent($exchange);

            case "XDSb":
                return CXDSb::getEvent($exchange);

            default:
                throw new CMbException("CIHE_event-unknown");
        }
    }

    /**
     * Return Patient Administration Management (PAM) transaction
     *
     * @param string $code Event code
     * @param string $i18n Internationalization
     *
     * @return object An instance of PAM transaction
     */
    public static function getPAMTransaction(string $code, ?string $i18n = null): ?string
    {
        switch ($i18n) {
            case "FRA":
                return CPAMFRA::getTransaction($code);

            default:
                return CPAM::getTransaction($code);
        }
    }

    /**
     * Return Device Enterprise Communication (DEC) transaction
     *
     * @param string $code Event code
     *
     * @return object An instance of DEC transaction
     */
    public static function getDECTransaction(string $code): ?string
    {
        return CDEC::getTransaction($code);
    }

    /**
     * Return Scheduled Workflow (SWF) transaction
     *
     * @param string $code Event code
     *
     * @return object An instance of DEC transaction
     */
    public static function getSWFTransaction(string $code): ?string
    {
        return CSWF::getTransaction($code);
    }

    /**
     * Return Patient Demographics Query (PDQ) transaction
     *
     * @param string $code Event code
     *
     * @return object An instance of PDQ transaction
     */
    public static function getPDQTransaction(string $code): ?string
    {
        return CPDQ::getTransaction($code);
    }

    /**
     * Return Patient Demographics Query (PDQ) transaction
     *
     * @param string $code Event code
     *
     * @return object An instance of PDQ transaction
     */
    public static function getPIXTransaction(string $code): ?string
    {
        return CPIX::getTransaction($code);
    }
}
