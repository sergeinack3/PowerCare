<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\Contracts\Client\FTPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSourceStatistic;
use Ox\Mediboard\System\Sources\CSourceFile;

class CSourceFTP extends CSourceFile
{
    // Source type
    public const TYPE = 'ftp';

    /** @var string[] */
    protected const CLIENT_MAPPING = [
        self::CLIENT_FTP => CFTP::class,
    ];

    /** @var string */
    protected const DEFAULT_CLIENT = self::CLIENT_FTP;

    /** @var string */
    public const CLIENT_FTP = 'ftp';

    /** @var FTPClientInterface */
    public $_client;

    // DB Table key
    /** @var int */
    public $source_ftp_id;

    // DB Fields

    /** @var int */
    public $port;

    /** @var int */
    public $default_socket_timeout;

    /** @var int */
    public $timeout;

    /** @var bool */
    public $pasv;

    /** @var mixed */
    public $mode;

    /** @var string */
    public $fileprefix;

    /** @var string */
    public $fileextension;

    /** @var string */
    public $filenbroll;

    /** @var string */
    public $fileextension_write_end;

    /** @var int */
    public $counter;

    /** @var bool */
    public $ssl;

    /** @var bool */
    public $delete_file;

    /** @var string */
    public $ack_prefix;

    /** @var bool */
    public $timestamp_file;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_ftp';
        $spec->key   = 'source_ftp_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["ssl"]                     = "bool default|0";
        $props["port"]                    = "num default|21";
        $props["default_socket_timeout"]  = "num default|1";
        $props["timeout"]                 = "num default|5";
        $props["pasv"]                    = "bool default|0";
        $props["mode"]                    = "enum list|FTP_ASCII|FTP_BINARY default|FTP_BINARY";
        $props["counter"]                 = "str protected loggable|0";
        $props["fileprefix"]              = "str";
        $props["fileextension"]           = "str";
        $props["filenbroll"]              = "enum list|1|2|3|4";
        $props["fileextension_write_end"] = "str";
        $props["delete_file"]             = "bool default|1";
        $props["ack_prefix"]              = "str";
        $props["timestamp_file"]          = "bool default|0";

        return $props;
    }

    /**
     * @return FTPClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if ($client_ftp = $this->_client) {
            return $client_ftp;
        }

        /** @var FTPClientInterface $client_ftp */
        $client_ftp = parent::getClient();

        if ($this->retry_strategy) {
            $client_ftp = new ResilienceFTPClient($client_ftp, $this);
        }

        return $this->_client = $client_ftp;
    }

    /**
     * @inheritDoc
     */
    public function startLog(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;

        $input = $context->getRequest() ?: null;

        $this->_current_echange     = $echange_ftp = new CExchangeFTP();
        $echange_ftp->date_echange  = CMbDT::dateTime();
        $echange_ftp->emetteur      = CAppUI::conf("mb_id");
        $echange_ftp->destinataire  = $this->host;
        $echange_ftp->source_class  = $this->_class;
        $echange_ftp->source_id     = intval($this->_id);
        $echange_ftp->function_name = $function_name ?: '';
        $echange_ftp->input         = $input !== null ?
            serialize(array_map_recursive([CFTP::class, "truncate"], $input)) : null;
    }

    /**
     * @inheritDoc
     */
    public function stopLog(ClientContext $context): void
    {
        /** @var CExchangeFTP $echange_ftp */
        if (!($echange_ftp = $this->_current_echange)) {
            return;
        }

        $echange_ftp->response_time = $this->_current_chronometer->total;

        // response time
        $echange_ftp->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $output = $context->getResponse();

        if ($echange_ftp->ftp_fault != 1) {
            $echange_ftp->output = $output !== null
                ? serialize(array_map_recursive([CFTP::class, "truncate"], $output))
                : null;
        }

        if ($msg = $echange_ftp->store()) {
            CApp::log($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public function exceptionLog(ClientContext $context): void
    {
        /** @var CExchangeFTP $echange_ftp */
        if (!$echange_ftp = $this->_current_echange) {
            return;
        }

        $echange_ftp->response_datetime = CMbDT::dateTime();
        $throwable                      = $context->getThrowable();
        $output                         = $context->getResponse();
        if (!$output && $throwable) {
            $output = $throwable->getMessage();
        }

        $echange_ftp->output    = $output !== null ?
            serialize(array_map_recursive([CFTP::class, "truncate"], $output)) : null;
        $echange_ftp->ftp_fault = 1;
        if ($msg = $echange_ftp->store()) {
            CApp::log($msg);
        }
    }

}
