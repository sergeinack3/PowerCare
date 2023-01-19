<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbServer;
use Ox\Core\CMbString;
use Ox\Core\Contracts\Client\SFTPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSource;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

/**
 * Class SFTP
 *
 * @method testSocket()
 * @method connect()
 * @method getFile($source_file, $destination_file)
 * @method close()
 */
class CSFTP implements SFTPClientInterface
{
    /** @var string */
    public $hostname;

    /** @var string */
    public $port;

    /** @var string */
    public $timeout;

    /** @var string */
    public $username;

    /** @var string */
    public $userpass;

    /** @var string */
    public $loggable;

    /** @var string */
    public $fileextension;

    /** @var string */
    public $fileextension_end;

    /** @var SFTP */
    public $connexion;

    /** @var array */
    private static $aliases = [];

    /** @var CSourceSFTP */
    private $_source;

    private $_data;

    /** @var bool Is data string ? */
    protected $data_string = false;

    /**
     * Magic method (do not call directly)
     *
     * @param string $name method name
     * @param array  $args arguments
     *
     * @return mixed
     *
     * @throws Exception
     * @throws CMbException
     */
    function __call($name, $args)
    {
        $name          = strtolower($name);
        $silent        = strncmp($name, 'try', 3) === 0;
        $function_name = $silent ? substr($name, 3) : $name;
        $function_name = '_' . (isset(self::$aliases[$function_name]) ? self::$aliases[$function_name] : $function_name);

        if (!method_exists($this, $function_name)) {
            throw new CMbException("CSourceSFTP-call-undefined-method", $name);
        }

        if ($function_name === "_init") {
            return call_user_func_array([$this, $function_name], $args);
        }

        if (!$this->loggable) {
            try {
                return call_user_func_array([$this, $function_name], $args);
            } catch (CMbException $fault) {
                throw $fault;
            }
        }

        $echange_ftp               = new CExchangeFTP();
        $echange_ftp->date_echange = CMbDT::dateTime();
        $echange_ftp->emetteur     = CAppUI::conf("mb_id");
        $echange_ftp->destinataire = $this->hostname;

        $echange_ftp->function_name = $name;

        CApp::$chrono->stop();
        $chrono = new Chronometer();
        $chrono->start();
        $output = null;
        try {
            $output = call_user_func_array([$this, $function_name], $args);
        } catch (CMbException $fault) {
            $chrono->stop();
            // response time
            $echange_ftp->response_time = $chrono->total;

            $echange_ftp->response_datetime = CMbDT::dateTime();
            $echange_ftp->output            = $fault->getMessage();
            $echange_ftp->ftp_fault         = 1;
            $echange_ftp->store();

            CApp::$chrono->start();

            throw $fault;
        }
        $chrono->stop();
        CApp::$chrono->start();

        // response time
        $echange_ftp->response_time     = $chrono->total;
        $echange_ftp->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $args = array_map_recursive([self::class, "truncate"], $args);

        $echange_ftp->input = serialize($args);
        if ($echange_ftp->ftp_fault != 1) {
            if ($function_name === "_getlistfiles") {
                // Truncate le tableau des fichiers reçus dans le cas où c'est > 100
                $array_count = count($output);
                if ($array_count > 100) {
                    $output          = array_slice($output, 0, 100);
                    $output["count"] = "$array_count files";
                }
            }
            $echange_ftp->output = serialize(array_map_recursive([self::class, "truncate"], $output));
        }
        $echange_ftp->store();

        return $output;
    }

    /**
     * Truncate the string
     *
     * @param String $string String
     *
     * @return string
     */
    public static function truncate($string)
    {
        if (!is_string($string)) {
            return $string;
        }

        // Truncate
        $max    = 1024;
        $result = CMbString::truncate($string, $max);

        // Indicate true size
        $length = strlen($string);
        if ($length > 1024) {
            $result .= " [$length bytes]";
        }

        return $result;
    }

    /**
     * Initialisation
     *
     * @param CSourceSFTP $exchange_source source
     *
     * @return void
     * @throws CMbException
     */
    private function _init(CExchangeSource $exchange_source)
    {
        if (!$exchange_source->_id) {
            throw new CMbException("CSourceSFTP-no-source", $exchange_source->name);
        }
        $this->_source           = $exchange_source;
        $this->hostname          = $exchange_source->host;
        $this->username          = $exchange_source->user;
        $this->userpass          = $exchange_source->getPassword();
        $this->port              = $exchange_source->port;
        $this->timeout           = $exchange_source->timeout;
        $this->loggable          = $exchange_source->loggable;
        $this->fileextension     = $exchange_source->fileextension;
        $this->fileextension_end = $exchange_source->fileextension_write_end;
        $this->_data             = $exchange_source->_data;
        $this->dispatcher        = $exchange_source->_dispatcher;
    }


    /**
     * @param string|null    $function_name
     * @param Throwable|null $throwable
     *
     * @return ClientContext
     */
    private function getContext(?string $function_name = null, ?Throwable $throwable = null): ClientContext
    {
        $arguments = [];
        if ($function_name) {
            $arguments['function_name'] = $function_name;
        }

        return (new ClientContext($this, $this->_source))
            ->setArguments($arguments)
            ->setThrowable($throwable);
    }

    /**
     * @param array  $call_args
     * @param string $function_name
     *
     * @return false|mixed
     * @throws Throwable
     */
    protected function dispatch(array $call_args, string $function_name)
    {
        $context = $this->getContext($function_name);
        if (is_array($call_args)) {
            $arguments = $call_args[1] ?? [];
            $callable  = $call_args[0];
            $context->setRequest($arguments);
        } else {
            $callable  = $call_args;
            $arguments = [];
        }

        try {
            $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
            $result = call_user_func($callable, $arguments);
            $context->setResponse($result);
            $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);
        } catch (Throwable $e) {
            $context->setThrowable($e);
            $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);
            throw $e;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    private function _testSocket()
    {
        $errno  = "";
        $errstr = "";

        $call = [
            function ($args) use ($errno, $errstr) {
                return @fsockopen(...$args);
            },

            [$this->hostname, $this->port, $errno, $errstr, $this->timeout],
        ];

        if (!$this->dispatch($call, 'testSocket')) {
            throw new CMbException(
                "CSourceFTP-socket-connection-failed", $this->hostname, $this->port, $errno, $errstr
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _connect()
    {
        if ($this->connexion) {
            return true;
        }

        if (!defined('NET_SFTP_LOGGING')) {
            define('NET_SFTP_LOGGING', SSH2::LOG_COMPLEX);
        }

        if (!$sftp = new SFTP($this->hostname, $this->port, $this->timeout)) {
            throw new CMbException("CSourceSFTP-connexion-failed");
        }

        $call = [
            function ($args) use ($sftp) {
                return $sftp->login(...$args);
            },
            [$this->username, $this->userpass],
        ];

        if (!$this->dispatch($call, 'login')) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        /*
        $key = $sftp->getServerPublicHostKey();
        $key = substr($key, strpos($key, " ")+1);*/
        //@todo : tester les cles dans la liste blanche

        $this->connexion = $sftp;

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _getCurrentDirectory()
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function () {
                return $this->connexion->pwd();
            },
        ];

        if (!$pwd = $this->dispatch($call, 'getCurrentDirectory')) {
            throw new CMbException("CSourceSFTP-pwd-failed", $this->hostname);
        }

        return $pwd;
    }

    /**
     * @inheritdoc
     */
    private function _changeDirectory($directory)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($directory) {
                return $this->connexion->chdir(...$args);
            },
            [$directory],
        ];

        if (!$chdir = $this->dispatch($call, 'changeDirectory')) {
            throw new CMbException("CSourceSFTP-change-directory-failed", $directory);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _getListDirectory($folder = ".")
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        /**
         * Group by directory :
         * size - uid -gid - permissions - atime - mtime - type
         */
        $call = [
            function ($args) use ($folder) {
                return $this->connexion->rawlist(...$args);
            },
            [$folder],
        ];

        if (!$files = $this->dispatch($call, 'getListDirectory')) {
            throw new CMbException("CSourceSFTP-getlistfiles-failed", $this->hostname, $folder);
        }

        CMbArray::extract($files, ".");
        CMbArray::extract($files, "..");

        $list = [];

        foreach ($files as $key => $_file) {
            if ($_file["type"] !== 2) {
                continue;
            }
            $list[] = CMbArray::get($_file, "filename");
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    private function _getListFiles($folder = ".")
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($folder) {
                return $this->connexion->rawList(...$args);
            },
            [$folder],
        ];

        if (!$files = $this->dispatch($call, 'getListFiles')) {
            throw new CMbException("CSourceSFTP-getlistfiles-failed", $this->hostname);
        }

        CMbArray::extract($files, ".");
        CMbArray::extract($files, "..");

        $array_file = [];

        foreach ($files as $key => $_file) {
            if ($_file["type"] === 2) {
                continue;
            }
            $array_file[] = $key;
        }

        return $array_file;
    }

    /**
     * @inheritdoc
     */
    private function _getListFilesDetails($folder = ".")
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($folder) {
                return $this->connexion->rawList(...$args);
            },
            [$folder],
        ];

        if (!$files = $this->dispatch($call, 'getListFilesDetails')) {
            throw new CMbException("CSourceSFTP-getlistfiles-failed", $this->hostname, $folder);
        }

        CMbArray::extract($files, ".");
        CMbArray::extract($files, "..");

        $fileInfo = [];
        foreach ($files as $key => $_file) {
            if ($_file["type"] === 2) {
                continue;
            }

            $date = date("d-m-Y H:m", CMbArray::get($_file, "mtime"));

            $fileInfo[] = [
                "type"         => CMbArray::get($_file, "type"),
                "user"         => CMbArray::get($_file, "uid"),
                "size"         => CMbString::toDecaBinary(CMbArray::get($_file, "size")),
                "date"         => $date,
                "name"         => CMbArray::get($_file, "filename"),
                "path"         => $folder . CMbArray::get($_file, "filename"),
                "relativeDate" => CMbDT::daysRelative($date, CMbDT::date()),
            ];
        }

        return $fileInfo;
    }

    /**
     * @inheritdoc
     */
    private function _delFile($file)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        // Download the file
        $call = [
            function ($args) use ($file) {
                return $this->connexion->delete(...$args);
            },
            [$file],
        ];

        if (!$this->dispatch($call, 'delFile')) {
            throw new CMbException("CSourceSFTP-delete-file-failed", $file);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _renameFile($oldname, $newname)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        // Rename the file
        $call = [
            function ($args) use ($oldname, $newname) {
                return $this->connexion->rename(...$args);
            },
            [$oldname, $newname],
        ];

        if (!$this->dispatch($call, 'renameFile')) {
            throw new CMbException("CSourceSFTP-rename-file-failed", $oldname, $newname);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _addFile($file_name, $source_file, $data_string = true)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        // Upload the file
        $call = [
            function ($args) use ($file_name, $source_file, $data_string) {
                return $this->connexion->put(...$args);
            },
            [
                $file_name,
                $source_file,
                $data_string ? SFTP::SOURCE_STRING : SFTP::SOURCE_LOCAL_FILE,
            ],
        ];

        if (!$this->dispatch($call, 'addFile')) {
            throw new CMbException("CSourceSFTP-upload-file-failed", $source_file);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function _getFile($source_file, $destination_file = false)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        // Download the file
        $call = [
            function ($args) use ($source_file, $destination_file) {
                return $this->connexion->get(...$args);
            },
            [$source_file, $destination_file],
        ];

        if (!$data = $this->dispatch($call, 'getFile')) {
            throw new CMbException("CSourceSFTP-download-file-failed", $source_file, $destination_file);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    private function _createDirectory($directory)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($directory) {
                return $this->connexion->mkdir(...$args);
            },
            [$directory],
        ];

        if (!$response = $this->dispatch($call, 'createDirectory')) {
            throw new CMbException("CSourceSFTP-create-directory-failed", $directory);
        }


        return $response;
    }

    /**
     * @inheritdoc
     */
    private function _getSize($file)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($file) {
                return $this->connexion->filesize(...$args);
            },
            [$file],
        ];

        if (!$response = $this->dispatch($call, 'getSize')) {
            throw new CMbException("CSourceSFTP-error-size-file", $file);
        }


        return $response;
    }

    /**
     * @inheritdoc
     */
    private function _close()
    {
        try {
            if (!$this->connexion) {
                throw new CMbException("CSourceSFTP-connexion-failed", $this->hostname);
            }

            // close the FTP stream
            $call = function () {
                return $this->connexion->disconnect();
            };

            if (!$this->dispatch($call, 'close')) {
                throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
            }

            $this->connexion = null;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('close', $e), self::EVENT_EXCEPTION);
        }

        return true;
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function isReachableSource(): bool
    {
        try {
            $this->_testSocket();
        } catch (CMbException $e) {
            $this->_source->_reachable = 0;
            $this->_source->_message   = $e->getMessage();

            return false;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function isAuthentificate(): bool
    {
        try {
            $this->_connect();
        } catch (CMbException $e) {
            $this->_source->_reachable = 0;
            $this->_source->_message   = $e->getMessage();

            return false;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('isAuthentificate', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return true;
    }

    /**
     * @return int
     */
    public function getResponseTime(): int
    {
        $response_time = CMbServer::getUrlResponseTime($this->_source->host, $this->_source->port);

        return $this->_source->_response_time = intval($response_time);
    }

    /**
     * @param string|null $destination_basename
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function send(string $destination_basename = null): bool
    {
        $file_path = !$destination_basename ? $this->_source->generateFileName() : $destination_basename;

        // Ajout du prefix si existant
        $file_path = ($this->_source->fileprefix ?: '') . $file_path;

        if ($this->_source->_exchange_data_format && $this->_source->_exchange_data_format->_id) {
            $file_path = "$file_path-{$this->_source->_exchange_data_format->_id}";
        }

        try {
            if (
                $this->fileextension
                && (CMbArray::get(pathinfo($destination_basename), "extension") != $this->fileextension)
            ) {
                $file_path = "$file_path.$this->fileextension";
            }

            $this->sendContent($file_path, $this->_data);
            if ($this->fileextension_end) {
                $this->sendContent("$file_path.$this->fileextension_end", "");
            }

            return true;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('send', $e), self::EVENT_EXCEPTION);
            throw $e;
        }
    }

    /**
     * @return array
     * @throws CMbException
     * @throws Throwable
     */
    public function receive(): array
    {
        try {
            $this->_connect();
            $path  = $this->_getCurrentDirectory();
            $path  = $this->_source->fileprefix ? "$path/" . $this->_source->fileprefix : $path;
            $files = $this->_getListFiles($path);

            if (empty($files)) {
                throw new CMbException("No-file");
            }
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('receive', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $files;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        if ((!$this && !$this->connexion) || $this->connexion === null) {
            return null;
        }

        return $this->connexion->getLastSFTPError();
    }

    /**
     * @param string $file_name
     *
     * @return int
     * @throws CMbException
     * @throws Throwable
     */
    public function getSize(string $file_name): int
    {
        $size = -1;
        try {
            $this->_connect();
            $size = $this->_getSize($file_name);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getSize', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $size;
    }

    /**
     * @param string $oldname
     * @param string $newname
     *
     * @return void
     * @throws CMbException
     * @throws Throwable
     */
    public function renameFile(string $oldname, string $newname): void
    {
        try {
            $this->_connect();

            if ($prefix = $this->_source->fileprefix) {
                $oldname = "$prefix/$oldname";
                $newname = "$prefix/$newname";
            }

            $this->_renameFile($oldname, $newname);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getSize', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param string|null $directory
     *
     * @return string
     * @throws CMbException
     * @throws Throwable
     */
    public function getCurrentDirectory(string $directory = null): string
    {
        if (!$directory) {
            $directory = $this->_source->fileprefix;
        }

        try {
            $this->_connect();
            if ($directory) {
                $this->_changeDirectory($directory);
            }
            $curent_directory = $this->_getCurrentDirectory();

            return "$curent_directory/";
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getCurrentDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param string $current_directory
     * @param bool   $information
     *
     * @return array
     * @throws CMbException
     * @throws Throwable
     */
    public function getListFiles(string $current_directory, bool $information = false): array
    {
        try {
            $this->_connect();

            $files = [];
            foreach ($this->_getListFiles($current_directory) as $_file) {
                if ($_file == "." || $_file == "..") {
                    continue;
                }

                $files[] = $_file;
            }
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getListFiles', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }


        if (empty($files)) {
            throw new CMbException("No-file");
        }

        return $files;
    }

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CMbException
     * @throws Throwable
     */
    public function getListFilesDetails(string $current_directory): array
    {
        try {
            $this->_connect();
            $list = $this->_getListFilesDetails($current_directory);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getCurrentDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $list;
    }

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CMbException
     * @throws Throwable
     */
    public function getListDirectory(string $current_directory): array
    {
        try {
            $this->_connect();
            $list = $this->_getListDirectory($current_directory);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getCurrentDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $list;
    }

    /**
     * @param string $directory
     *
     * @return void
     * @throws CMbException
     * @throws Throwable
     */
    public function changeDirectory(string $directory): void
    {
        try {
            $this->_connect();
            $this->_changeDirectory($directory);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getCurrentDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param string $source_file
     * @param string $file_name
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function addFile(string $source_file, string $file_name): bool
    {
        try {
            $this->_connect();
            if ($current_directory = $this->_source->_destination_file) {
                $this->_changeDirectory($current_directory);
            }

            $this->_addFile($source_file, $file_name, $this->data_string);

            return true;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('addFile', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function delFile(string $path): bool
    {
        try {
            $this->_connect();

            if ($this->_source->fileprefix) {
                $path = $this->_source->fileprefix . "/$path";
            }
            $delete = $this->_delFile($path);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('delFile', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $delete;
    }

    /**
     * @param string $directory_name
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function createDirectory(string $directory_name): bool
    {
        try {
            $this->_connect();
            $this->_createDirectory($directory_name);

            return true;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('createDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param CExchangeSource $source
     *
     * @return void
     * @throws CMbException
     */
    public function init(CExchangeSource $source): void
    {
        $this->_init($source);
    }

    /**
     * @param string      $path
     * @param string|null $dest
     *
     * @return string|null
     * @throws CMbException
     * @throws Throwable
     */
    public function getData(string $path, ?string $dest = null): ?string
    {
        try {
            $this->_connect();
            if ($this->_source->fileprefix) {
                $path = rtrim($this->_source->fileprefix, "\\/") . "/$path";
            }

            if ($dest === null) {
                $tmp = tempnam(sys_get_temp_dir(), "mb_");
            }

            $file = $tmp ?? $dest;
            $this->_getFile($path, $file);
            $file_get_content = file_get_contents($file);

            if (isset($tmp)) {
                unlink($tmp);
            }

            return $file_get_content === false ? null : $file_get_content;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('createDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param $remote_file
     * @param $content
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    protected function sendContent($remote_file, $content)
    {
        try {
            $this->data_string = true;
            $this->addFile($remote_file, $content);

            return true;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('createDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        }
    }
}
