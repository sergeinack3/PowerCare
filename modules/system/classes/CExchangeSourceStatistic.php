<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTransportLayer;
use Ox\Interop\Eai\Resilience\ClientContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

/**
 * Exchange Source
 */
class CExchangeSourceStatistic extends CMbObject
{
    /** @var int */
    public const CONNEXION_STATUS_SUCCESS = 1;

    /** @var int */
    public const CONNEXION_STATUS_FAILED = 2;

    /** @var int */
    public $exchange_source_statistic_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $object_id;

    /** @var int */
    public $failures;

    /** @var string */
    public $retry_strategy;

    /** @var int */
    public $max_retry;

    /** @var int */
    public $failures_average;

    /** @var int */
    public $last_status;

    /** @var string */
    public $first_call_date;

    /** @var int */
    public $nb_call;

    /** @var string */
    public $last_verification_date;
    
    /** @var string */
    public $last_connexion_date;

    /** @var int */
    public $last_response_time;

    /** @var bool */
    public $active_circuit_breaker;
    
    /** @var CExchangeSource[] */
    public $_ref_statistics;

    /** @var CExchangeSource  */
    public CExchangeSource $_source;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'exchange_source_statistic';
        $spec->key   = 'exchange_source_statistic_id';
        $spec->loggable = false;
        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();
        $props["object_class"] = "str notNull class show|0";
        $props["object_id"]    = "ref class|CExchangeSource meta|object_class cascade back|source_statistics";

        $last_status_list = implode('|', [self::CONNEXION_STATUS_SUCCESS, self::CONNEXION_STATUS_FAILED]);

        $props["failures"]               = "num"; //nombre d'échecs
        $props["retry_strategy"]         = "str"; //seuils d'attente avant test de disponibilité en Ms
        $props["max_retry"]              = "num"; //nombre d'essais
        $props["failures_average"]       = "num"; //moyenne des échecs
        $props["last_status"]            = "enum list|$last_status_list"; //dernier status
        $props["first_call_date"]        = "dateTime"; // date de premier call
        $props["nb_call"]                = "num default|0"; // nombre de call depuis la création de la source
        $props["last_verification_date"] = "dateTime"; //date de dernière vérification d'accessibilité
        $props["last_connexion_date"]    = "dateTime"; //date de dernière connexion réussie
        $props["last_response_time"]     = "num";  //temps de réponse en Ms
        $props["active_circuit_breaker"] = "bool default|0"; //active ou non le circuit breaker

        return $props;
    }

    /**
     * @return CExchangeSource
     * @throws Exception
     */
    public function loadRefSource(): CExchangeSource
    {
        return $this->_ref_source = $this->loadFwdRef("object_id");
    }
}
