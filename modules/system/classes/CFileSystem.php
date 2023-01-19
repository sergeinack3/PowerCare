<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\Contracts\Client\FileSystemClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class CFileSystem implements FileSystemClientInterface
{
    /** @var CSourceFileSystem */
    private $source;

    /** @var int */
    private $source_id;

    /** @var string */
    private $source_class;

    /** @var string */
    private $input;

    /** @var string */
    private $destinataire;

    /** @var mixed|null */
    private $emetteur;

    /** @var float|int|string */
    private $date_echange;

    /** @var string */
    public $_path;

    /** @var string */
    public $_file_path;

    /** @var array */
    public $_files = [];

    /** @var array */
    public $_dir_handles = [];

    /** @var int|null */
    public $_limit;

    /** @var bool */
    public $_acknowledgement = false;

    /** @var EventDispatcher */
    protected $dispatcher;

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

        return (new ClientContext($this, $this->source))
            ->setArguments($arguments)
            ->setThrowable($throwable);
    }

    /**
     * @param callable $call
     * @param string   $function_name
     *
     * @return mixed
     */
    protected function dispatch(array $call_args, string $function_name)
    {
        $context = $this->getContext($function_name);
        if (is_array($call_args)) {
            $arguments = $call_args[1] ?? [];
            $callable = $call_args[0];
            $context->setRequest($arguments);
        } else {
            $callable = $call_args;
            $arguments = [];
        }

        $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
        $result = call_user_func($callable, $arguments);
        $context->setResponse($result);
        $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);

        return $result;
    }

    /**
     * @param CSourceFileSystem $source
     *
     * @return void
     * @throws Exception
     */
    public function init(CExchangeSource $source): void
    {
        $this->date_echange = CMbDT::dateTime();
        $this->emetteur     = CAppUI::conf("mb_id");
        $this->destinataire = $source->host;
        $this->input        = serialize($source->_data);
        $this->source_class = $source->_class;
        $this->source_id    = $source->_id;
        $this->source       = $source;
        $this->_limit       = $source->_limit;
        $this->dispatcher   = $source->_dispatcher;
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function isReachableSource(): bool
    {
        try {
            if (is_dir($this->source->host)) {
                return true;
            } else {
                $this->source->_reachable = 0;
                $this->source->_message   = CAppUI::tr("CSourceFileSystem-path-not-found", $this->source->host);

                return false;
            }
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);

            throw $e;
        }
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function isAuthentificate(): bool
    {
        try {
            if (is_writable($this->source->host)) {
                return true;
            } else {
                $this->source->_reachable = 1;
                $this->source->_message   = CAppUI::tr("CSourceFileSystem-path-not-writable", $this->source->host);

                return false;
            }
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('isAuthentificate', $e), self::EVENT_EXCEPTION);

            throw $e;
        }
    }

    /**
     * @return int
     * @throws Throwable
     */
    public function getResponseTime(): int
    {
        $start = microtime(true);
        $this->isReachableSource();
        $end           = microtime(true);
        $response_time = $end - $start;

        return $this->source->response_time = intval($response_time);
    }

    /**
     * @param string|null $destination_basename
     *
     * @return bool
     * @throws CMbException
     */
    public function send(string $destination_basename = null): bool
    {
        $file_path = !$destination_basename ? $this->source->generateFileName() : $destination_basename;

        // Ajout du prefix si existant
        $file_path = $this->source->fileprefix . $file_path;

        if ($this->source->_exchange_data_format && $this->source->_exchange_data_format->_id) {
            $file_path = "$file_path-{$this->source->_exchange_data_format->_id}";
        }
        $this->_file_path = $file_path;

        try {
            if ($this->source->fileextension && (CMbArray::get(
                        pathinfo($file_path),
                        "extension"
                    ) != $this->source->fileextension)) {
                $this->_file_path .= ".{$this->source->fileextension}";
            }

            $path = rtrim($this->source->getFullPath($this->_path), "\\/");

            $file_path = $path . "/" . $this->_file_path;

            $call = [
                function ($args) {
                    return is_writable(...$args);
                },
                [$path],
            ];

            if (!$this->dispatch($call, "send")) {
                $e = new CMbException("CSourceFileSystem-path-not-writable", $path);
                $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
                throw $e;
                //throw new CMbException("CSourceFileSystem-path-not-writable", $path);
            }


            $context = $this->getContext('file_put_contents');

            $call = [
                function ($args) {
                    return file_put_contents(...$args);
                },
                [$file_path, $this->source->_data],
            ];

            $return = $this->dispatch($call, "send");

            if ($this->source->fileextension_write_end) {
                $pos       = strrpos($file_path, ".");
                $file_path = substr($file_path, 0, $pos);

                $call = [
                    function ($args) {
                        return file_put_contents(...$args);
                    },
                    ["$file_path.{$this->source->fileextension_write_end}", ""],
                ];

                $return = $this->dispatch($call, "send");
            }
        } catch (CMbException $e) {
            $this->source->_message = $e->getMessage();
            $this->dispatcher->dispatch($this->getContext('send', $e), self::EVENT_EXCEPTION);

            throw $e;
        }

        return $return;
    }

    /**
     * @return array
     * @throws CMbException
     * @throws Throwable
     */
    public function receive(): array
    {
        $path = $this->source->getFullPath($this->_path);

        try {
            if (!is_dir($path)) {
                throw new CMbException("CSourceFileSystem-path-not-found", $path);
            }

            if (!is_readable($path)) {
                throw new CMbException("CSourceFileSystem-path-not-readable", $path);
            }

            if (!$handle = opendir($path)) {
                throw new CMbException("CSourceFileSystem-path-not-readable", $path);
            }

            /* Loop over the directory
             * $this->_files = CMbPath::getFiles($path); => pas optimisé pour un listing volumineux
             * */
            $i     = 1;
            $files = [];

            while (false !== ($entry = readdir($handle))) {
                $limit = 5000;
                $entry = "$path/$entry";
                if ($i == $limit) {
                    break;
                }

                /* We ignore folders */
                if (is_dir($entry)) {
                    continue;
                }

                $files[] = $entry;

                $i++;
            }

            closedir($handle);

            switch ($this->source->sort_files_by) {
                default:
                case "name":
                    sort($files);
                    break;
                case "date":
                    usort($files, [$this, "sortByDate"]);
                    break;
                case "size":
                    usort($files, [$this, "sortBySize"]);
                    break;
            }

            if ($this->_limit) {
                $files = array_slice($files, 0, $this->_limit);
            }
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('receive', $e), self::EVENT_EXCEPTION);

            throw $e;
        }
        return $files;
    }

    /**
     * @param string $file_name
     *
     * @return int
     */
    public function getSize(string $file_name): int
    {
        if ($this->_path) {
            $path      = rtrim($this->source->getFullPath($this->_path), "\\/");
            $file_name = "$path/$file_name";
        }

        $call = [
            function ($args) use ($file_name) {
                return filesize(...$args);
            },
            [$file_name],
        ];

        return $this->dispatch($call, 'getSize');
    }

    /**
     * @param string      $oldname
     * @param string      $newname
     * @param string|null $current_directory
     * @param bool        $utf8_encode
     *
     * @return void
     * @throws CMbException
     */
    public function renameFile(
        string $oldname,
        string $newname,
        string $current_directory = null,
        bool $utf8_encode = false
    ): void {
        $current_directory = $this->source->host . '/';
        $path              = $current_directory . $oldname;

        $call = [
            function ($args) use ($path, $current_directory, $newname) {
                return rename(...$args);
            },
            [$path, $current_directory . $newname],
        ];

        if (!$this->dispatch($call, 'renameFile')) {
            $exception = new CMbException("CSourceFileSystem-error-renaming", $oldname);
            $this->dispatcher->dispatch($this->getContext('renameFile', $exception), self::EVENT_EXCEPTION);
            throw $exception;
        }
    }

    /**
     * @param string $directory_name
     *
     * @return bool
     * @throws CMbException
     */
    public function createDirectory(string $directory_name): bool
    {
        $path = $this->source->getFullPath($this->_path) . "/" . $directory_name;

        $call = [
            function ($args) use ($path) {
                return mkdir(...$args);
            },
            [$path],
        ];

        if (!is_dir($path) && ($this->dispatch($call, 'createDirectory') === false)) {
            $exception = new CMbException("CSourceFileSystem-error-createDirectory", $directory_name);
            $this->dispatcher->dispatch($this->getContext('createDirectory', $exception), self::EVENT_EXCEPTION);

            throw $exception;
        }

        return true;
    }

    public function changeDirectory(string $directory): void
    {
    }

    /**
     * @param string|null $directory
     *
     * @return string
     */
    public function getCurrentDirectory(string $directory = null): string
    {
        if (!$directory) {
            $directory = $this->source->host;
            if (substr($directory, -1, 1) !== "/" && substr($directory, -1, 1) !== "\\") {
                $directory = "$directory/";
            }
        }

        return str_replace("\\", "/", $directory);
    }

    /**
     * @param string $current_directory
     * @param bool   $information
     *
     * @return array
     * @throws CMbException
     */
    public function getListFiles(string $current_directory, bool $information = false): array
    {
        $directory = $this->source->getFullPath($this->_path) . "/" . $current_directory;

        $call = [
            function ($args) use ($directory) {
                return file_exists(...$args);
            },
            [$directory],
        ];

        if (!$this->dispatch($call, 'getListFiles')) {
            $e = new CMbException("CSourceFileSystem-msg-Folder does not exist", UI_MSG_ERROR);
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            throw $e;
        }

        $call = [
            function ($args) use ($directory) {
                return scandir(...$args);
            },
            [$directory],
        ];

        $files      = [];

        if (($list_files = $this->dispatch($call, 'getListFiles')) === false) {
            $list_files = [];
        }

        foreach ($list_files as $_file) {
            if ($_file == "." || $_file == "..") {
                continue;
            }

            $files[] = $_file;
        }

        return $files;
    }

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CMbException
     */
    public function getListFilesDetails(string $current_directory): array
    {
        $call = [
            function ($args) use ($current_directory) {
                return file_exists(...$args);
            },
            [$current_directory],
        ];

        if (!$this->dispatch($call, 'getListFiles')) {
            $e = new CMbException("CSourceFileSystem-msg-Folder does not exist", UI_MSG_ERROR);
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            throw $e;
        }

        $call = [
            function ($args) use ($current_directory) {
                return scandir(...$args);
            },
            [$current_directory],
        ];

        $fileInfo = [];
        $contain  = ($this->dispatch($call, 'getListFilesDetails')) ?: [];
        foreach ($contain as $_contain) {
            $full_path = $current_directory . $_contain;
            if (!is_dir($full_path) && @filetype($full_path) && !is_link($full_path)) {
                $fileInfo[] = [
                    "type"         => "f",
                    "user"         => fileowner($full_path),
                    "size"         => CMbString::toDecaBinary($this->getSize($full_path, true)),
                    "date"         => CMbDT::strftime(CMbDT::ISO_DATETIME, filemtime($full_path)),
                    "name"         => $_contain,
                    "relativeDate" => CMbDT::daysRelative(fileatime($full_path), CMbDT::date()),
                ];
            }
        }

        return $fileInfo;
    }

    /**
     * @param string $current_directory
     *
     * @return array
     */
    public function getListDirectory(string $current_directory): array
    {
        $call = [
            function ($args) use ($current_directory) {
                return scandir(...$args);
            },
            [$current_directory],
        ];

        $dir     = [];
        $contain = $this->dispatch($call, 'getListDirectory') ?: [];
        foreach ($contain as $_contain) {
            $full_path = $current_directory . $_contain;
            if (is_dir($full_path) && "$_contain/" !== "./" && "$_contain/" !== "../") {
                $dir[] = "$_contain/";
            }
        }

        return $dir;
    }

    /**
     * @param string $source_file
     * @param string $file_name
     *
     * @return bool
     * @throws CMbException
     */
    public function addFile(string $source_file, string $file_name): bool
    {
        $call = [
            function ($args) use ($source_file, $file_name) {
                return copy(...$args);
            },
            [$source_file, $this->source->host . "/" . $file_name],
        ];

        if (($result = $this->dispatch($call, 'addFile')) === false) {
            $exception = new CMbException("CSourceFileSystem-error-add", $file_name);
            $this->dispatcher->dispatch($this->getContext('addFile', $exception), self::EVENT_EXCEPTION);

            throw $exception;
        }

        return $result;
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws CMbException
     */
    public function delFile(string $path): bool
    {
        $current_directory = $this->source->host;
        if ($current_directory) {
            $path = rtrim($current_directory, '/') . '/' . $path;
        }

        $call = [
            function ($args) use ($path) {
                return unlink(...$args);
            },
            [$path],
        ];
        $result = false;
        if (file_exists($path) && ($result = $this->dispatch($call, 'delFile')) === false) {
            $exception = new CMbException("CSourceFileSystem-file-not-deleted", $path);
            $this->dispatcher->dispatch($this->getContext('delFile', $exception), self::EVENT_EXCEPTION);
            throw $exception;
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->source->_message;
    }

    /**
     * @param string      $path
     * @param string|null $dest
     *
     * @return string|null
     * @throws CMbException
     */
    public function getData(string $path, ?string $dest = null): ?string
    {
        if (!is_readable($path)) {
            $path = $this->source->host . "/" . $path;
        }
        $call = [
            function ($args) use ($path) {
                return file_get_contents(...$args);
            },
            [$path],
        ];

        $data = $this->dispatch($call, 'getData');

        return $data;
    }

    /**
     * Sort by date
     *
     * @param int $a Variable a
     * @param int $b Variable b
     *
     * @return int
     */
    private function sortByDate(int $a, int $b): int
    {
        return filemtime($a) - filemtime($b);
    }

    /**
     * Sort by size
     *
     * @param int $a Variable a
     * @param int $b Variable b
     *
     * @return int
     */
    private function sortBySize(int $a, int $b): int
    {
        return filesize($a) - filesize($b);
    }
}
