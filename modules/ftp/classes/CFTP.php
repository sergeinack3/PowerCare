<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//namespace Ox\Core;
namespace Ox\Interop\Ftp;

use DateTime;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbServer;
use Ox\Core\CMbString;
use Ox\Core\Contracts\Client\FTPClientInterface;
use Ox\Mediboard\System\CExchangeSource;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Ox\Interop\Eai\Resilience\ClientContext;
use Throwable;

/**
 * Class CFTP
 * @method connect()
 * @method close()
 * @method sendFile()
 * @method sendContent()
 * @method getFile()
 */
class CFTP implements FTPClientInterface
{

    /** @var string */
    public $hostname;

    /** @var string */
    public $username;

    /** @var string */
    public $userpass;

    public $connexion;

    /** @var string */
    public $port;

    /** @var string */
    public $timeout;

    /** @var string */
    public $default_socket_timeout;

    /** @var string */
    public $ssl;

    /** @var bool */
    public $passif_mode = false;

    /** @var string */
    public $mode;

    /** @var string */
    public $fileprefix;

    /** @var string */
    public $fileextension;

    public $filenbroll;

    public $loggable;

    public $type_system;

    /** @var CSourceFTP */
    public $_source;

    /** @var string */
    public $_source_file;

    /** @var string */
    public $_destination_file;

    /** @var string */
    public $_path;

    /** @var EventDispatcher */
    protected $dispatcher;

    private static $aliases = [
        'sslconnect' => 'ssl_connect',
        'getoption'  => 'get_option',
        'setoption'  => 'set_option',
        'nbcontinue' => 'nb_continue',
        'nbfget'     => 'nb_fget',
        'nbfput'     => 'nb_fput',
        'nbget'      => 'nb_get',
        'nbput'      => 'nb_put',
    ];

    static $month_to_number = [
        'Jan' => '01',
        'Feb' => '02',
        'Mar' => '03',
        'Apr' => '04',
        'May' => '05',
        'Jun' => '06',
        'Jul' => '07',
        'Aug' => '08',
        'Sep' => '09',
        'Oct' => '10',
        'Nov' => '11',
        'Dec' => '12',
    ];

    /**
     * @param $string
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
     * @param CSourceFTP $exchange_source
     *
     * @return void
     * @throws CMbException
     */
    private function _init(CSourceFTP $exchange_source): void
    {
        if (!$exchange_source->_id) {
            throw new CMbException("CSourceFTP-no-source", $exchange_source->name);
        }

        $this->_source                = $exchange_source;
        $this->hostname               = $exchange_source->host;
        $this->username               = $exchange_source->user;
        $this->userpass               = $exchange_source->getPassword();
        $this->port                   = $exchange_source->port;
        $this->timeout                = $exchange_source->timeout;
        $this->default_socket_timeout = $exchange_source->default_socket_timeout;
        $this->ssl                    = $exchange_source->ssl;
        $this->passif_mode            = $exchange_source->pasv;
        $this->mode                   = $exchange_source->mode;
        $this->fileprefix             = $exchange_source->fileprefix;
        $this->fileextension          = $exchange_source->fileextension;
        $this->filenbroll             = $exchange_source->filenbroll;
        $this->loggable               = $exchange_source->loggable;
        $this->_destination_file      = $exchange_source->_destination_file;
        $this->dispatcher             = $exchange_source->_dispatcher;
    }

    /**
     * @return bool
     * @throws CMbException
     */
    private function _testSocket(): bool
    {
        $errno = $errstr = null;
        $call  = [
            function ($args) {
                return @fsockopen(...$args);
            },

            [$this->hostname, $this->port, $errno, $errstr, $this->default_socket_timeout],
        ];

        if (!$this->dispatch($call, 'testSocket')) {
            throw new CMbException(
                "CSourceFTP-socket-connection-failed", $this->hostname, $this->port, $errno, $errstr
            );
        }

        return true;
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
     * @param callable $call
     * @param string   $function_name
     *
     * @return mixed
     */
    protected function dispatch($call_args, string $function_name)
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

        if ($context->getRequest() instanceof FTP\Connection) {
            $context->setRequest(1);
        } else {
            $context->setRequest(0);
        }
        $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
        $result = call_user_func($callable, $arguments);
        $context->setResponse($result);
        $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);

        return $result;
    }

    /**
     * @return bool
     * @throws CMbException
     */
    protected function _connect(): bool
    {
        // If server provides SSL mode
        if ($this->ssl) {
            if (!function_exists("ftp_ssl_connect")) {
                throw new CMbException("CSourceFTP-function-not-available", "ftp_ssl_connect");
            }

            $connexion = null;
            // Set up over-SSL connection
            $call = [
                function ($args) use (&$connexion) {
                    $result = $connexion = ftp_ssl_connect(...$args);

                    return ['connection_ftp' => ($result instanceof \FTP\Connection ? 1 : 0)];
                },
                [$this->hostname, $this->port, $this->default_socket_timeout],
            ];

            $this->dispatch($call, 'ftp_ssl_connect');
            if (!$this->connexion = $connexion) {
                throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
            }
        } else {
            if (!function_exists("ftp_connect")) {
                throw new CMbException("CSourceFTP-function-not-available", "ftp_connect");
            }

            $connexion = null;
            // Set up basic connection
            $call = [
                function ($args) use (&$connexion) {
                    $result = $connexion = @ftp_connect(...$args);

                    return ['connection_ftp' => ($result instanceof \FTP\Connection ? 1 : 0)];
                },
                [$this->hostname, $this->port, $this->timeout],
            ];

            $this->dispatch($call, 'ftp_connect');
            if (!$this->connexion = $connexion) {
                throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
            }
        }

        // Login with username and password
        $call = [
            function ($args) {
                return @ftp_login(...$args);
            },
            [$this->connexion, $this->username, $this->userpass],
        ];

        if (!$this->dispatch($call, 'ftp_login')) {
            throw new CMbException("CSourceFTP-identification-failed", $this->username);
        }

        // Turn passive mode on
        if ($this->passif_mode && !@ftp_pasv($this->connexion, true)) {
            throw new CMbException("CSourceFTP-passive-mode-on-failed");
        }

        $this->type_system = ftp_systype($this->connexion);

        return true;
    }

    /**
     * @param $folder
     * @param $information
     *
     * @return array|bool
     * @throws CMbException
     */
    private function _getListFiles($folder = ".", $information = false)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }
        $call = [
            function ($args) use ($folder) {
                return ftp_nlist(...$args);
            },
            [$this->connexion, $folder],
        ];

        if (($files = $this->dispatch($call, 'getListFiles')) === false) {
            throw new CMbException("CSourceFTP-getlistfiles-failed", $this->hostname);
        }

        // Alphabetical sorting
        sort($files);

        foreach ($files as &$_file) {
            $_file = str_replace("\\", "/", $_file);
        }

        if (!$information) {
            return $files;
        }

        if ($folder && (substr($folder, -1) != "/")) {
            $folder = "$folder/";
        }

        $tabFileDir = [];
        foreach ($files as &$_file) {
            $tabFileDir[] = ["path" => $_file, "size" => ftp_size($this->connexion, $_file)];
            // Some FTP servers do not retrieve whole paths
            if ($folder && $folder != "." && strpos($_file, $folder) !== 0) {
                $_file                = "$folder/$_file";
                $tabFileDir[]["path"] = $_file;
            }
        }

        return $tabFileDir;
    }

    /**
     * @param $folder
     *
     * @return array
     * @throws CMbException
     */
    private function _getListFilesDetails($folder = "."): array
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($folder) {
                return ftp_rawlist(...$args);
            },
            [$this->connexion, $folder],
        ];

        if (($files = $this->dispatch($call, 'getListFilesDetails')) === false) {
            throw new CMbException("CSourceFTP-getlistfiles-failed", $this->hostname);
        }

        $system = $this->type_system;
        $limit  = 9;
        if ($system && strpos($system, "Windows") !== false) {
            $limit = 4;
        }

        $fileInfo = [];
        foreach ($files as $_file) {
            $pregInfo = preg_split("/[\s]+/", $_file, $limit);

            if ($system && strpos($system, "Windows") !== false) {
                $format   = "m-d-y h:iA";
                $datetime = "$pregInfo[0] $pregInfo[1]";
                $type     = strpos($pregInfo[2], "DIR") ? "d" : "f";
                $user     = "";
                $size     = $pregInfo[2];
                $name     = $pregInfo[3];
            } else {
                $year = $pregInfo[7];
                if (strpos($year, ":")) {
                    $year = explode("-", CMbDT::date());
                    $year = $year[0] . " $pregInfo[7]";
                }
                $format   = "M-d-Y H:i";
                $datetime = "$pregInfo[5]-$pregInfo[6]-$year";
                $type     = $pregInfo[0];
                $user     = "$pregInfo[2] $pregInfo[3]";
                $size     = $pregInfo[4];
                $name     = $pregInfo[8];
            }

            if (strpos($type, "d") !== false || $pregInfo[0] == "total") {
                continue;
            }

            $datetime = DateTime::createFromFormat($format, $datetime);
            $date     = "";
            if ($datetime) {
                $date = $datetime->format("Y-m-d H:m");
            }

            $fileInfo[] = [
                "type"         => $type,
                "user"         => $user,
                "size"         => CMbString::toDecaBinary($size),
                "date"         => $date,
                "name"         => $name,
                "relativeDate" => CMbDT::daysRelative($date, CMbDT::date()),
            ];
        }

        return $fileInfo;
    }

    /**
     * @param $folder
     *
     * @return array
     * @throws CMbException
     */
    private function _getListDirectory($folder = "."): array
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($folder) {
                return ftp_rawlist(...$args);
            },
            [$this->connexion, $folder],
        ];

        if (($files = $this->dispatch($call, 'getListDirectory')) === false) {
            throw new CMbException("CSourceFTP-getlistfiles-failed", $this->hostname);
        }

        $system = $this->type_system;
        $limit  = 9;
        if ($system && strpos($system, "Windows") !== false) {
            $limit = 4;
        }

        $fileInfo = [];
        foreach ($files as $_file) {
            $pregInfo = preg_split("/[\s]+/", $_file, $limit);
            if ($system && strpos($system, "Windows") !== false) {
                $type = strpos($pregInfo[2], "DIR") ? "d" : "f";
                $name = $pregInfo[3];
            } else {
                $type = $pregInfo[0];
                $name = $pregInfo[8];
            }
            if (strpos($type, "d") === false || $name === "." || $name === "..") {
                continue;
            }
            $fileInfo[] = $name;
        }

        return $fileInfo;
    }

    /**
     * @return string
     * @throws CMbException
     */
    private function _getCurrentDirectory(): string
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) {
                return ftp_pwd(...$args);
            },
            [$this->connexion],
        ];

        if (($pwd = $this->dispatch($call, 'getCurrentDirectory')) === false) {
            throw new CMbException("CSourceFTP-getlistfiles-failed", $this->hostname);
        }

        if ($pwd === "/") {
            return "$pwd";
        }

        return "$pwd/";
    }

    /**
     * @param $file
     *
     * @return bool
     * @throws CMbException
     */
    private function _delFile($file): bool
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Download the file
        $call = [
            function ($args) {
                return @ftp_delete(...$args);
            },
            [$this->connexion, $file],
        ];

        if (!$this->dispatch($call, 'delFile')) {
            throw new CMbException("CSourceFTP-delete-file-failed", $file);
        }

        return true;
    }

    /**
     * @param $source_file
     * @param $destination_file
     *
     * @return mixed|string|null
     * @throws CMbException
     */
    private function _getFile($source_file, $destination_file = null)
    {
        $source_base = basename($source_file);

        if (!$destination_file) {
            $destination_file = "tmp/$source_base";
        }
        $destination_info = pathinfo($destination_file);
        CMbPath::forceDir($destination_info["dirname"]);

        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Download the file
        $call = [
            function ($args) use ($destination_file, $source_file) {
                return @ftp_get(...$args);
            },
            [$this->connexion, $destination_file, $source_file, constant($this->mode)],
        ];

        if (!$this->dispatch($call, 'getFile')) {
            throw new CMbException("CSourceFTP-download-file-failed", $source_file, $destination_file);
        }

        return $destination_file;
    }

    /**
     * @param $source_content
     * @param $destination_file
     *
     * @return bool
     * @throws CMbException
     */
    private function _sendContent($source_content, $destination_file)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        $tmpfile = tempnam("", "ftp_");
        file_put_contents($tmpfile, $source_content);

        try {
            $result = $this->_sendFile($tmpfile, $destination_file);
            unlink($tmpfile);
        } catch (Exception $e) {
            unlink($tmpfile);
            trigger_error($e->getMessage(), E_USER_WARNING);

            return false;
        }

        return $result;
    }

    /**
     * @param $source_file
     * @param $destination_file
     *
     * @return bool
     * @throws CMbException
     */
    protected function _sendFile($source_file, $destination_file)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Check for path, try to build it if needed
        $dir = dirname($destination_file);

        if ($dir != ".") {
            $pwd = ftp_pwd($this->connexion);

            $parts = explode("/", $dir);
            foreach ($parts as $_part) {
                if (!@ftp_chdir($this->connexion, $_part)) {
                    @ftp_mkdir($this->connexion, $_part);
                    @ftp_chdir($this->connexion, $_part);
                }
            }

            ftp_chdir($this->connexion, $pwd);
        }

        // Upload the file

        $call = [
            function ($args) use ($source_file) {
                return @ftp_put(...$args);
            },
            [$this->connexion, $destination_file, $source_file, constant($this->mode)],
        ];

        if (!$result = $this->dispatch($call, 'SendFile')) {
            throw new CMbException("CSourceFTP-upload-file-failed", $source_file);
        }

        if (!$result) {
            throw new CMbException("CSourceFTP-upload-file-failed", $source_file);
        }

        return true;
    }

    /**
     * @param $source_file
     * @param $file_name
     *
     * @return bool
     * @throws CMbException
     */
    private function _addFile($source_file, $file_name)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Upload the file
        $call = [
            function ($args) use ($file_name, $source_file) {
                return @ftp_put(...$args);
            },
            [$this->connexion, $file_name, $source_file, constant($this->mode)],
        ];

        if (!$this->dispatch($call, 'addFile')) {
            throw new CMbException("CSourceFTP-upload-file-failed", $source_file);
        }

        return true;
    }

    /**
     * @param $directory
     *
     * @return bool
     * @throws CMbException
     */
    private function _changeDirectory($directory)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Change the directory
        $call = [
            function ($args) use ($directory) {
                return @ftp_chdir(...$args);
            },
            [$this->connexion, $directory],
        ];

        if (!$this->dispatch($call, 'changeDirectory')) {
            throw new CMbException("CSourceFTP-change-directory-failed", $directory);
        }

        return true;
    }

    /**
     * @param $oldname
     * @param $newname
     *
     * @return bool
     * @throws CMbException
     */
    private function _renameFile($oldname, $newname)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Rename the file
        $call = [
            function ($args) use ($oldname, $newname) {
                return @ftp_rename(...$args);
            },
            [$this->connexion, $oldname, $newname],
        ];

        if (!$this->dispatch($call, 'renameFile')) {
            throw new CMbException("CSourceFTP-rename-file-failed", $oldname, $newname);
        }

        return true;
    }


    /**
     * @return bool
     * @throws CMbException
     */
    private function _close()
    {
        // close the FTP stream
        $call = [
            function ($args) {
                return @ftp_close(...$args);
            },
            [$this->connexion],
        ];

        if (!$this->dispatch($call, 'close')) {
            throw new CMbException("CSourceFTP-close-connexion-failed", $this->hostname);
        }

        $this->connexion = null;

        return true;
    }

    /**
     * @param $file
     *
     * @return mixed
     * @throws CMbException
     */
    private function _getSize(string $file): ?int
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        // Rename the file
        $call = [
            function ($args) use ($file) {
                return ftp_size(...$args);
            },
            [$this->connexion, $file],
        ];

        $size = $this->dispatch($call, 'getSize');
        if ($size == -1) {
            throw new CMbException("CSourceFTP-size-file-failed", $file);
        }

        return $size;
    }

    /**
     * @param $directory
     *
     * @return mixed
     * @throws CMbException
     */
    private function _createDirectory($directory)
    {
        if (!$this->connexion) {
            throw new CMbException("CSourceFTP-connexion-failed", $this->hostname);
        }

        $call = [
            function ($args) use ($directory) {
                return @ftp_mkdir(...$args);
            },
            [$this->connexion, $directory],
        ];

        if (!$directory = $this->dispatch($call, 'createDirectory')) {
            throw new CMbException("CSourceFTP-close-connexion-failed", $this->hostname);
        }

        return $directory;
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
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            throw $e;
        } catch (Throwable $e) {
            $this->_source->_reachable = 0;
            $this->_source->_message   = $e->getMessage();
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
            $this->_close();
        } catch (CMbException $e) {
            $this->_source->_reachable = 0;
            $this->_source->_message   = $e->getMessage();
            $this->dispatcher->dispatch($this->getContext('isAuthentificate', $e), self::EVENT_EXCEPTION);
            throw $e;
        } catch (Throwable $e) {
            $this->_source->_reachable = 0;
            $this->_source->_message   = $e->getMessage();
            $this->dispatcher->dispatch($this->getContext('isAuthentificate', $e), self::EVENT_EXCEPTION);

            throw $e;
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
        $this->_source->counter++;

        if (!$destination_basename) {
            $destination_basename =
                sprintf(
                    "%s%0" . $this->filenbroll . "d",
                    $this->fileprefix,
                    $this->_source->counter % pow(10, $this->filenbroll)
                );
        }

        if ($this->_source->timestamp_file) {
            $destination_basename = $this->_source::timestampFileName($destination_basename);
        }

        $file_path = $destination_basename;

        try {
            if ($this->_connect()) {
                if ($this->fileextension && (CMbArray::get(
                            pathinfo($destination_basename),
                            "extension"
                        ) != $this->fileextension)) {
                    $destination_basename = "$file_path" . $this->fileextension;
                }

                $this->_sendContent($this->_source->_data, $destination_basename);

                if ($this->_source->fileextension_write_end) {
                    $this->_sendContent($this->_source->_data, "$file_path" . $this->_source->fileextension_write_end);
                }

                $this->_close();
                $this->_source->store();

                return true;
            }

            return false;
        } catch (Throwable $e) {
            // dispatch exception
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
        $path = $this->fileprefix ? "$this->fileprefix/{$this->_source->_path}" : $this->_source->_path;

        $files = [];

        try {
            $this->_connect();
            $files = $this->_getListFiles($path);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('receive', $e), self::EVENT_EXCEPTION);
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
     * @param string $oldname
     * @param string $newname
     *
     * @return void
     * @throws CMbException
     * @throws Throwable
     */
    public function renameFile(
        string $oldname,
        string $newname
    ): void {
        try {
            $this->_connect();

            if ($this->fileprefix) {
                $oldname = "$this->fileprefix/$oldname";
                $newname = "$this->fileprefix/$newname";
            }

            $this->_renameFile($oldname, $newname);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('renameFile', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
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
            $this->dispatcher->dispatch($this->getContext('changeDirectory', $e), self::EVENT_EXCEPTION);

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
            $directory = $this->fileprefix;
        }
        $curent_directory = null;

        try {
            $this->_connect();
            if ($directory) {
                $this->_changeDirectory($directory);
            }
            $curent_directory = $this->_getCurrentDirectory();
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getCurrentDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $curent_directory;
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
        $files = [];

        try {
            $this->_connect();
            $files = $this->_getListFilesDetails($current_directory);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getListFilesDetails', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
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
    public function getListDirectory(string $current_directory): array
    {
        $directories = [];

        if (!$current_directory) {
            $current_directory = $this->fileprefix;
        }

        try {
            $this->_connect();
            $directories = $this->_getListDirectory($current_directory);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getListDirectory', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return $directories;
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
            if ($this->_destination_file) {
                $this->_changeDirectory($this->_destination_file);
            }

            $this->_addFile($source_file, $file_name);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('addFile', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }

        return true;
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
        $files = [];

        try {
            $this->_connect();


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
     * @param string $path
     *
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function delFile(string $path): bool
    {
        try {
            $current_directory = $this->_destination_file;
            $this->_connect();

            if ($current_directory) {
                $this->_changeDirectory($current_directory);
            }

            if (!$current_directory && $this->fileprefix) {
                $path = "$this->fileprefix/$path";
            }

            $this->_delFile($path);

            return true;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('delFile', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }

    /**
     * @param CSourceFTP $source
     *
     * @return void
     * @throws CMbException
     */
    public function init(CExchangeSource $source): void
    {
        $this->_init($source);
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
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->_source->_message;
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
            if ($this->fileprefix && $dest === null) {
                $path = "$this->fileprefix/$path";
            }

            if ($dest === null) {
                $tmp = tempnam(sys_get_temp_dir(), "mb_");
            }

            $file             = $this->_getFile($path, $tmp ?? $dest);
            $file_get_content = file_get_contents($file);

            if (isset($tmp)) {
                unlink($tmp);
            }

            return $file_get_content === false ? null : $file_get_content;
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('getData', $e), self::EVENT_EXCEPTION);
            throw $e;
        } finally {
            $this->_close();
        }
    }
}
