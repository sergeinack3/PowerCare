<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Mediboard\System\Sources\CSourceFile;
use Ox\Core\Contracts\Client\FileSystemClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;

/**
 * Class CSourceFileSystem
 */
class CSourceFileSystem extends CSourceFile
{
    // Source type
    public const TYPE = 'file_system';

    /** @var string */
    protected const DEFAULT_CLIENT = self::CLIENT_FILE_SYSTEM;

    /** @var array */
    protected const CLIENT_MAPPING = [
        self::CLIENT_FILE_SYSTEM => CFileSystem::class
    ];

    /** @var string */
    public const CLIENT_FILE_SYSTEM = 'file_system';

    /** @var FileSystemClientInterface */
    public $_client;

    // DB Table key

    /** @var int */
    public $source_file_system_id;

    /** @var string */
    public $fileextension;

    /** @var string */
    public $fileextension_write_end;

    /** @var string */
    public $fileprefix;

    /** @var string */
    public $sort_files_by;

    /** @var bool */
    public $delete_file;

    /** @var string */
    public $ack_prefix;

    // Form fields

    /** @var int Legacy field */
    public $_limit;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "source_file_system";
        $spec->key   = "source_file_system_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                            = parent::getProps();
        $props["fileextension"]           = "str";
        $props["fileextension_write_end"] = "str";
        $props["fileprefix"]              = "str";
        $props["sort_files_by"]           = "enum list|date|name|size default|name";
        $props["delete_file"]             = "bool default|1";
        $props["ack_prefix"]              = "str";

        return $props;
    }

    /**
     * @return FileSystemClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if ($client_fs = $this->_client) {
            return $client_fs;
        }

        /** @var FileSystemClientInterface $client_fs */
        $client_fs = parent::getClient();
        if ($this->retry_strategy) {
            $client_fs = new ResilienceFileSystemClient($client_fs, $this);
        }

        return $this->_client = $client_fs;
    }


    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->host;
    }

    /**
     * @param string|null $path
     *
     * @return string|null
     */
    public function getFullPath(?string $path = null): ?string
    {
        $host = rtrim($this->host, "/\\");
        $path = ltrim($path ?? '', "/\\");
        $path = $host . ($path ? "/$path" : "");

        return str_replace("\\", "/", $path);
    }

    /**
     * @inheritDoc
     */
    public function startLog(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;

        $input = $context->getRequest() ?: null;
        $this->_current_echange     = $echange_fs = new CExchangeFileSystem();
        $echange_fs->date_echange  = CMbDT::dateTime();
        $echange_fs->emetteur      = CAppUI::conf("mb_id");
        $echange_fs->destinataire  = $this->host;
        $echange_fs->source_class  = $this->_class;
        $echange_fs->source_id     = intval($this->_id);
        $echange_fs->function_name = $function_name ?: '';
        $echange_fs->input         = $input !== null ?
            serialize($input) : null;
    }

    /**
     * @inheritDoc
     */
    public function stopLog(ClientContext $context): void
    {
        /** @var CExchangeFileSystem $echange_fs */
        if (!($echange_fs = $this->_current_echange)) {
            return;
        }

        $echange_fs->response_time = $this->_current_chronometer->total;

        // response time
        $echange_fs->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $output = $context->getResponse();

        if ($msg = $echange_fs->store()) {
            CApp::log($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public function exceptionLog(ClientContext $context): void
    {
        /** @var CExchangeFileSystem $echange_fs */
        if (!$echange_fs = $this->_current_echange) {
            return;
        }

        $echange_fs->response_datetime = CMbDT::dateTime();
        $throwable                      = $context->getThrowable();
        $output                         = $context->getResponse();
        if (!$output && $throwable) {
            $output = $throwable->getMessage();
        }

        $echange_fs->output    = $output !== null ?
            serialize($output) : null;

        if ($msg = $echange_fs->store()) {
            CApp::log($msg);
        }
    }
}
