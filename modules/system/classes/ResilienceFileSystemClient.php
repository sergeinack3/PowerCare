<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\FileSystemClientInterface;
use Ox\Interop\Eai\Client\Legacy\FileClientInterface;
use Ox\Interop\Eai\Resilience\CircuitBreaker;
use Ox\Interop\Eai\Resilience\CircuitBreakerException;
use Ox\Interop\Ftp\CustomRequestAnalyserInterface;
use Ox\Interop\Ftp\ResponseAnalyser;


class ResilienceFileSystemClient implements FileSystemClientInterface
{
    /** @var FileSystemClientInterface */
    public FileSystemClientInterface $client;

    /** @var CircuitBreaker */
    private CircuitBreaker $circuit;

    /** @var ResponseAnalyser */
    private ResponseAnalyser $analyser;

    /** @var CSourceFileSystem */
    private CSourceFileSystem $source;

    /**
     * @param FileSystemClientInterface $client
     * @param CSourceFileSystem         $source
     */
    public function __construct(FileSystemClientInterface $client, CSourceFileSystem $source)
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
     * @throws CMbException
     * @throws CircuitBreakerException
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

            return false;
        }
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws CircuitBreakerException
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

            return false;
        }
    }

    /**
     * @return int
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function renameFile(string $oldname, string $newname): void
    {
        $call = function () use ($oldname, $newname) {
            return $this->client->renameFile($oldname, $newname);
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string $directory_name
     *
     * @return bool
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function changeDirectory(string $directory): void
    {
        $call = function () use ($directory) {
            return $this->client->changeDirectory($directory);
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string|null $directory
     *
     * @return string
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function addFile(string $source_file, string $file_name): bool
    {
        $call = function () use ($source_file, $file_name) {
            return $this->client->addFile($source_file, $file_name);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser) ?: false;
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws CMbException
     * @throws CircuitBreakerException
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
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function getError(): ?string
    {
        $call = function () {
            return $this->client->getError();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @param string      $path
     * @param string|null $dest
     *
     * @return string|null
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function getData(string $path, ?string $dest = null): ?string
    {
        $call = function () use ($path, $dest) {
            return $this->client->getData($path, $dest);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }
}
