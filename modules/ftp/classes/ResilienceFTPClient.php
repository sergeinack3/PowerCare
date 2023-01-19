<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\Contracts\Client\FTPClientInterface;
use Ox\Interop\Eai\Client\Legacy\FileClientInterface;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Interop\Eai\Resilience\CircuitBreaker;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use Ox\Mediboard\System\Sources\CSourceFile;
use Throwable;

class ResilienceFTPClient implements FTPClientInterface
{
    /** @var FTPClientInterface */
    public FTPClientInterface $client;

    /** @var CircuitBreaker */
    private CircuitBreaker $circuit;

    /** @var ResponseAnalyser */
    private ResponseAnalyser $analyser;

    /** @var CSourceFTP */
    private CSourceFTP $source;

    /**
     * @param FTPClientInterface $client
     * @param CExchangeSource    $source
     */
    public function __construct(FTPClientInterface $client, CSourceFTP $source)
    {
        $this->client = $client;

        $this->analyser = $client instanceof CustomRequestAnalyserInterface
            ? $client->getRequestAnalyser() : new ResponseAnalyser();

        $this->source  = $source;
        $this->circuit = new CircuitBreaker();
    }

    /**
     * @param CExchangeSource $source
     *
     * @return void
     */
    public function init(CExchangeSource $source): void
    {
        $this->client->init($source);
    }

    /**
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function isReachableSource(): bool
    {
        $call = function () {
            return $this->client->isReachableSource();
        };

        try {
            return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
        } catch (Throwable $e) {
            $this->source->_message = $e->getMessage();
            throw $e;
           // return false;
        }
    }

    /**
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function isAuthentificate(): bool
    {
        $call = function () {
            return $this->client->isAuthentificate();
        };
        try {
            return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
        } catch (Throwable $e) {
            $this->source->_message = $e->getMessage();
            throw $e;
        }
    }

    /**
     * @return int
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getResponseTime(): int
    {
        $call = function () {
            return $this->client->getResponseTime();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string|null $destination_basename
     *
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function send(string $destination_basename = null): bool
    {
        $call = function () use ($destination_basename) {
            return $this->client->send($destination_basename);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return array
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function receive(): array
    {
        $call = function () {
            return $this->client->receive();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $file_name
     *
     * @return int
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getSize(string $file_name): int
    {
        $call = function () use ($file_name) {
            return $this->client->getSize($file_name);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $oldname
     * @param string $newname
     *
     * @return void
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function renameFile(string $oldname, string $newname): void
    {
        $call = function () use ($oldname, $newname) {
            $this->client->renameFile($oldname, $newname);
        };
        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $directory_name
     *
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function createDirectory(string $directory_name): bool
    {
        $call = function () use ($directory_name) {
            return $this->client->createDirectory($directory_name);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $directory
     *
     * @return void
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function changeDirectory(string $directory): void
    {
        $call = function () use ($directory) {
            $this->client->changeDirectory($directory);
        };
        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string|null $directory
     *
     * @return string
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getCurrentDirectory(string $directory = null): string
    {
        $call = function () use ($directory) {
            return $this->client->getCurrentDirectory($directory);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $current_directory
     * @param bool   $information
     *
     * @return array
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getListFiles(string $current_directory, bool $information = false): array
    {
        $call = function () use ($current_directory, $information) {
            return $this->client->getListFiles($current_directory, $information);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getListFilesDetails(string $current_directory): array
    {
        $call = function () use ($current_directory) {
            return $this->client->getListFilesDetails($current_directory);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getListDirectory(string $current_directory): array
    {
        $call = function () use ($current_directory) {
            return $this->client->getListDirectory($current_directory);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $source_file
     * @param string $file_name
     *
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function addFile(string $source_file, string $file_name): bool
    {
        $call = function () use ($source_file, $file_name) {
            return $this->client->addFile($source_file, $file_name);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function delFile(string $path): bool
    {
        $call = function () use ($path) {
            return $this->client->delFile($path);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return string|null
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getError(): ?string
    {
        //        $call = function () {
        return $this->client->getError();
        //        };

        //        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string      $path
     * @param string|null $dest
     *
     * @return string|null
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getData(string $path, ?string $dest = null): ?string
    {
        $call = function () use ($path) {
            return $this->client->getData($path);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    //    /**
    //     * @param string $source_file
    //     * @param string $destination_file
    //     *
    //     * @return bool
    //     * @throws CircuitBreakerException
    //     * @throws \Ox\Core\CMbException
    //     */
    //    public function sendFile(string $source_file, string $destination_file): bool
    //    {
    //        $call = function () use ($source_file, $destination_file) {
    //            return $this->client->_sendfile($source_file, $destination_file);
    //        };
    //
    //        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    //    }
}
