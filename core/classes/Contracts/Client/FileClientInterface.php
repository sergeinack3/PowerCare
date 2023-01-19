<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Contracts\Client;

use Ox\Core\CMbException;

interface FileClientInterface extends ClientInterface
{
    /**
     * @param string|null $destination_basename
     */
    public function send(string $destination_basename = null): bool;

    /**
     * @return array
     */
    public function receive(): array;

    /**
     * @param string $file_name
     *
     * @return int
     */
    public function getSize(string $file_name): int;

    /**
     * @param string $oldname
     * @param string $newname
     */
    public function renameFile(
        string $oldname,
        string $newname
    ): void;

    /**
     * @param string $directory_name
     *
     * @return bool
     * @throws CMbException
     */
    public function createDirectory(string $directory_name): bool;

    /**
     * @param string $directory
     *
     * @return bool
     * @throws CMbException
     */
    public function changeDirectory(string $directory): void;

    /**
     * @param string|null $directory
     *
     * @return string
     * @throws CMbException
     */
    public function getCurrentDirectory(string $directory = null): string;

    /**
     * @param string $current_directory
     * @param bool   $information
     *
     * @return string
     * @throws CMbException
     */
    public function getListFiles(string $current_directory, bool $information = false): array;

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CMbException
     */
    public function getListFilesDetails(string $current_directory): array;

    /**
     * @param string $current_directory
     *
     * @return array
     * @throws CMbException
     */
    public function getListDirectory(string $current_directory): array;

    /**
     * @param string $source_file
     * @param string $file_name
     *
     * @return bool
     * @throws CMbException
     */
    public function addFile(string $source_file, string $file_name): bool;

    /**
     * @param string      $path
     * @param string|null $current_directory
     * @param bool        $use_fileprefix
     *
     * @return bool
     * @throws CMbException
     */
    public function delFile(string $path): bool;

    /**
     * @return bool
     */
    public function getError(): ?string;

    /**
     * @param string $path
     * @param string|null $dest
     *
     * @return string
     */
    public function getData(string $path, ?string $dest = null): ?string;
}
