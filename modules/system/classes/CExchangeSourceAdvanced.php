<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Exchange Source
 */
class CExchangeSourceAdvanced extends CExchangeSource
{
    public const DEFAULT_RETRY_STRATEGY = "1|5 5|60 10|120 20|";

    /** @var int */
    public const SOURCE_AVAILABLE = 0;

    /** @var int */
    public const SOURCE_BLOCKED = 1;

    /** @var string */
    public $retry_strategy;

    /** @var string */
    public $first_call_date;

    /** @var int */
    public static $failure;

    /** @var CExchangeSourceStatistic[] */
    public $_ref_statistics;

    /** @var CExchangeSourceStatistic */
    public $_ref_last_statistics;

    /** @var bool */
    public $_blocked;

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        //Props utilisées par le circuit breaker
        $props                    = parent::getProps();
        $props["retry_strategy"]  = "str"; //seuils d'attente avant test de disponibilité en Ms exemple => "5,10,50,60"
        $props["first_call_date"] = "dateTime"; // date de premier call effectué par la source

        //si la source est bloqué ou non par le circuit breaker
        $blocked_status    = implode('|', [self::SOURCE_AVAILABLE, self::SOURCE_BLOCKED]);
        $props["_blocked"] = "enum list|$blocked_status";

        return $props;
    }

    /**
     * @return ClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if (!$this->client_name) {
            $this->client_name = $this::DEFAULT_CLIENT;
        }

        if (!$client_class = ($this::CLIENT_MAPPING[$this->client_name] ?? null)) {
            throw new CMbException(
                'CExchangeSource-client_name-no mapping class',
                $this->client_name,
                CAppUI::tr($this->_class)
            );
        }

        /** @var ClientInterface $client */
        $client = new $client_class();
        $client->init($this);

        if ($client instanceof EventSubscriberInterface) {
            $this->_dispatcher->addSubscriber($client);
        }

        $this->_dispatcher->addListener(ClientInterface::EVENT_BEFORE_REQUEST, [$this, 'onBeforeRequest']);
        $this->_dispatcher->addListener(ClientInterface::EVENT_AFTER_REQUEST, [$this, 'onAfterRequest']);
        $this->_dispatcher->addListener(ClientInterface::EVENT_EXCEPTION, [$this, 'onException']);

        // trace des calls
        $this->_dispatcher->addListener(ClientInterface::EVENT_BEFORE_REQUEST, [$this, 'startCallTrace'], 0);
        $this->_dispatcher->addListener(ClientInterface::EVENT_AFTER_REQUEST, [$this, 'stopCallTrace'], 999);

        if ($this->loggable) {
            $this->_dispatcher->addListener(ClientInterface::EVENT_BEFORE_REQUEST, [$this, 'startLog'], 1);
            $this->_dispatcher->addListener(ClientInterface::EVENT_AFTER_REQUEST, [$this, 'stopLog'], 2);
            $this->_dispatcher->addListener(ClientInterface::EVENT_EXCEPTION, [$this, 'exceptionLog'], 2);
        }

        return $client;
    }

    /**
     * @inheritDoc
     */
    function initialize(): void
    {
        parent::initialize();

        $this->retry_strategy = self::DEFAULT_RETRY_STRATEGY;
    }

    /**
     * Load all statistics
     *
     * @return CExchangeSourceStatistic[]
     * @throws Exception
     */
    public function loadRefsStatistic(): array
    {
        return $this->_ref_statistics = $this->loadBackRefs("source_statistics");
    }

    /**
     * Load last statistics
     *
     * @return CExchangeSourceStatistic
     * @throws Exception
     */
    public function loadRefLastStatistic(): ?CExchangeSourceStatistic
    {
        $last_stat = $this->loadBackRefs(
            "source_statistics",
            ["last_verification_date DESC"],
            "1",
        );

        return $this->_ref_last_statistics = empty($last_stat) ? null : reset($last_stat);
    }

    /**
     * unlock source when max failures
     *
     * @return void
     * @throws Exception
     */
    public function unlockSource(): void
    {
        //remise à zéro du nombre de failure de la dernière stat pour débloquer la source
        $last_stat           = $this->loadRefLastStatistic();
        $last_stat->failures = 0;

        if ($msg = $last_stat->store()) {
            throw new CMbException($msg);
        }
    }


    /**
     * return true if source blocked
     * return false if source is available
     *
     * @return bool
     */
    public function getBlockedStatus(): bool
    {
        if (empty($this->retry_strategy)) {
            return false;
        } else {
            $max      = $this->getMaxRetryFromStrategy($this->retry_strategy);
            $stat     = $this->loadRefLastStatistic();
            $failures = ($stat === null) ? 0 : $stat->failures;

            return $this->_blocked = $failures >= $max;
        }
    }

    /**
     * give max retry from strategy
     *
     * @param $strategy
     *
     * @return string
     */
    public function getMaxRetryFromStrategy(string $strategy): string
    {
        $max = explode(" ", $strategy);
        $max = explode("|", end($max));

        return reset($max);
    }

    /**
     * * créé et enregistre une statistic en base apres l'éxecution d'un appel à la source
     *
     * @param ClientContext $context
     *
     * @return void
     * @throws CMbException
     */
    public function onAfterRequest(ClientContext $context): void
    {
        $source_statistic = new CExchangeSourceStatistic();

        $source_statistic->object_class = $this->_class;
        $source_statistic->object_id    = $this->_id;

        //si la source n'a jamais effectué d'appel on lui ajoute un date de premier appel
        if ($this->first_call_date === null) {
            $this->first_call_date = CMbDT::dateTime();
            if ($msg = $this->store()) {
                throw new CMbException($msg);
            }
        }

        //dans le cas ou le call est réussis on value les différents attributs de la nouvelle stat
        $last_stat                                = $this->loadRefLastStatistic();
        $source_statistic->nb_call                = $last_stat ? $last_stat->nb_call + 1 : 1;
        $source_statistic->last_status            = CExchangeSourceStatistic::CONNEXION_STATUS_SUCCESS;
        $source_statistic->last_response_time     = $this->_current_chronometer->total;
        $source_statistic->last_verification_date = CMbDT::dateTime();

        $throwable = $context->getThrowable();
        $response  = $context->getResponse();
        //dans le cas ou call renvoie une erreur on value les différents attributs en conséquence
        if (isset($throwable) || $response === false) {
            $this::$failure                        = true;
            $source_statistic->failures            = $last_stat ? $last_stat->failures + 1 : 1;
            $source_statistic->last_status         = CExchangeSourceStatistic::CONNEXION_STATUS_FAILED;
            $source_statistic->last_connexion_date = $last_stat ? $last_stat->last_connexion_date : null;
        } elseif ($this::$failure === true) {
            //si deja un erreur pendant le call precedant on garde le même nombre d'echec pour ce call aussi
            $source_statistic->failures            = $last_stat->failures;
            $source_statistic->last_connexion_date =
                $last_stat !== false ? $last_stat->last_connexion_date : CMbDT::dateTime();
        } else {
            $source_statistic->last_connexion_date = CMbDT::dateTime();
        }

        if ($msg = $source_statistic->store()) {
            throw new CMbException($msg);
        }
    }

}
