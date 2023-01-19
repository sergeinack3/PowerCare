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
use Ox\Core\Contracts\Client\SFTPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSourceStatistic;
use Ox\Mediboard\System\Sources\CSourceFile;

/**
 * Source SFTP
 */
class CSourceSFTP extends CSourceFile
{
    // Source type
    public const TYPE = 'sftp';

    /** @var string */
    protected const DEFAULT_CLIENT = self::CLIENT_SFTP;

    /** @var array */
    protected const CLIENT_MAPPING = [
        self::CLIENT_SFTP => CSFTP::class
    ];

    /** @var string */
    public const CLIENT_SFTP = 'sftp';

    /** @var SFTPClientInterface */
    public $_client;

    /** @var integer Primary key */
    public $source_sftp_id;
    public $port;
    public $timeout;
    public $fileprefix;
    public $fileextension_write_end;
    public $fileextension;
    public $delete_file;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "source_sftp";
        $spec->key   = "source_sftp_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["port"]                    = "num default|22";
        $props["timeout"]                 = "num default|10";
        $props["fileprefix"]              = "str";
        $props["fileextension_write_end"] = "str";
        $props["fileextension"]           = "str";
        $props["delete_file"]             = "bool default|1";

        return $props;
    }

    /**
     * @return SFTPClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if ($client_sftp = $this->_client) {
            return $client_sftp;
        }

        /** @var SFTPClientInterface $client_ftp */
        $client_sftp = parent::getClient();

        if ($this->retry_strategy) {
            $client_sftp = new ResilienceSFTPClient($client_sftp, $this);
        }

        return $this->_client = $client_sftp;
    }

    /**
     * @param ClientContext $context
     *
     * @return void
     */
    public function onBeforeRequest(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;
        $input = $context->getRequest();

        $this->_current_echange     = $echange_ftp = new CExchangeFTP();
        $echange_ftp->date_echange  = CMbDT::dateTime();
        $echange_ftp->emetteur      = CAppUI::conf("mb_id");
        $echange_ftp->destinataire  = $this->host;
        $echange_ftp->source_class  = $this->_class;
        $echange_ftp->source_id     = $this->_id;
        $echange_ftp->function_name = $function_name ?: '';
        $echange_ftp->input         = $input !== null ?
            serialize(array_map_recursive([CFTP::class, "truncate"], $input)) : null;
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
        $echange_ftp->source_id     = $this->_id;
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
