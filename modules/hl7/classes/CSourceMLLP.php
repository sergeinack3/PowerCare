<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use Ox\Mediboard\System\CExchangeSourceStatistic;
use Ox\Mediboard\System\CSocketSource;

class CSourceMLLP extends CExchangeSourceAdvanced
{
    // Source type
    public const TYPE = 'mllp';

    /** @var string  */
    protected const DEFAULT_CLIENT = self::CLIENT_MLLP;

    /** @var array  */
    protected const CLIENT_MAPPING = [
        self::CLIENT_MLLP => CMLLPLegacy::class
    ];

    /** @var string */
    public const CLIENT_MLLP = 'mllp_client';

    /**
     * Start of an MLLP message
     */
    const TRAILING = "\x0B";     // \v Vertical Tab (VT, decimal 11)

    /**
     * End of an MLLP message
     */
    const LEADING = "\x1C\x0D"; // File separator (FS, decimal 28), \r Carriage return (CR, decimal 13)

    /** @var int */
    public $source_mllp_id;

    public $ssl_enabled;

    /** @var int Délai d'expiration, en secondes, pour l'appel système connect() */
    public $timeout_socket;
    /** @var int Délai d'expiration lors de la lecture/écriture de données via un socket */
    public $timeout_period_stream;
    /** @var int Configure le mode bloquant d'un flux */
    public $set_blocking;

    /** @var CExchangeMLLP $_exchange_mllp MLLP exchange */
    public $_exchange_mllp;

    /** @var Chronometer */
    public $chrono;

    /** @var MLLPClientInterface */
    public $_client;

    public $port;
    public $ssl_certificate;
    public $ssl_passphrase;
    public $iv_passphrase;

    public $_socket_client;

    /**
    /**
     * @see parent::getSpec()
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_mllp';
        $spec->key   = 'source_mllp_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps()
    {
        $props                          = parent::getProps();
        $props["port"]                  = "num default|7001";
        $props["ssl_enabled"]           = "bool notNull default|0";
        $props["ssl_certificate"]       = "str";
        $props["ssl_passphrase"]        = "password show|0 loggable|0";
        $props["iv_passphrase"]         = "str show|0 loggable|0";
        $props["timeout_socket"]        = "num default|5";
        $props["timeout_period_stream"] = "num";
        $props["set_blocking"]          = "bool default|0";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->port;
    }

    /**
     * @see parent::updateEncryptedFields()
     */
    public function updateEncryptedFields()
    {
        if ($this->ssl_passphrase === "") {
            $this->ssl_passphrase = null;
        } else {
            if (!empty($this->ssl_passphrase)) {
                $this->ssl_passphrase = $this->encryptString($this->ssl_passphrase, "iv_passphrase");
            }
        }
    }

    /**
     * @return ClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if ($client_mllp = $this->_client) {
            return $client_mllp;
        }

        /** @var MLLPClientInterface $client_mllp */
        $client_mllp = parent::getClient();

        if ($this->retry_strategy) {
            $client_mllp = new ResilienceMLLPClient($client_mllp, $this);
        }

        return $this->_client = $client_mllp;
    }

    /**
     * @param ClientContext $context
     *
     * @return void
     * @throws Exception
     */
    public function onBeforeRequest(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;
        $server        = $context->getArguments()['server'] ?? null;

        if (!$this->loggable) {
            return;
        }

        $input = $context->getRequest() ?? null;

        $this->_current_echange       = $exchange_mllp = new CExchangeMLLP();
        $exchange_mllp->date_echange  = "now";
        $exchange_mllp->emetteur      = $server ? "$this->host:$this->port" : CAppUI::conf("mb_id");
        $exchange_mllp->function_name = $function_name;
        $exchange_mllp->source_class  = $this->_class;
        $exchange_mllp->source_id     = $this->_id;
        $exchange_mllp->destinataire  = $server ? CAppUI::conf("mb_id") : "$this->host:$this->port";
        $exchange_mllp->input = $input !== null ?
            serialize($input) : null;
        $exchange_mllp->store();
    }

    /**
     * @inheritDoc
     */
    public function startLog(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;

        $input = $context->getRequest() ?: null;

        $this->_current_echange     = $echange_mllp = new CExchangeMLLP();
        $echange_mllp->date_echange  = CMbDT::dateTime();
        $echange_mllp->emetteur      = CAppUI::conf("mb_id");
        $echange_mllp->destinataire  = $this->host;
        $echange_mllp->source_class  = $this->_class;
        $echange_mllp->source_id     = $this->_id;
        $echange_mllp->function_name = $function_name ?: '';
        $echange_mllp->input         = $input !== null ?
            serialize($input) : null;
    }

    /**
     * @inheritDoc
     */
    public function stopLog(ClientContext $context): void
    {
        /** @var CExchangeMLLP $echange_mllp */
        if (!($echange_mllp = $this->_current_echange)) {
            return;
        }

        $echange_mllp->response_time = $this->_current_chronometer->total;

        // response time
        $echange_mllp->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $output = $context->getResponse();

        $echange_mllp->output = $output !== null
            ? serialize($output)
            : null;

        if ($msg = $echange_mllp->store()) {
            CApp::log($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public function exceptionLog(ClientContext $context): void
    {
        /** @var CExchangeMLLP $echange_mllp */
        if (!$echange_mllp = $this->_current_echange) {
            return;
        }

        $echange_mllp->response_datetime = CMbDT::dateTime();
        $throwable                      = $context->getThrowable();
        $output                         = $context->getResponse();
        if (!$output && $throwable) {
            $output = $throwable->getMessage();
        }

        $echange_mllp->output    = $output !== null ?
            serialize($output) : null;

        if ($msg = $echange_mllp->store()) {
            CApp::log($msg);
        }
    }
    /**
     * @see parent::isSecured()
     */
    public function isSecured()
    {
        return ($this->ssl_enabled && $this->ssl_certificate && is_readable($this->ssl_certificate));
    }
}
