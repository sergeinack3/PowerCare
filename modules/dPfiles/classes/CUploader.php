<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CValue;

/**
 * Resumable upload server
 */
class CUploader implements IShortNameAutoloadable
{
    /** @var array  */
    private $messages = [];

    /**
     * Logging operation to stdout
     *
     * @param string $str - the logging string
     *
     * @return void
     */
    private function log(string $str, string $msg_type = UI_MSG_OK): void
    {
        $this->messages[] =
            [
                CMbDT::dateTime() . ": {$str}",
                $msg_type
            ];
    }

    /**
     * Get upload directory
     *
     * @return string
     */
    public static function getUploadDir(): string
    {
        $dir = CFile::getDirectory() . "/upload";
        CMbPath::forceDir($dir);

        return $dir;
    }

    /**
     * Check if all the parts exist, and
     * gather all the parts of the file together
     *
     * @param string $temp_dir    the temporary directory holding all the parts of the file
     * @param string $fileName    the original file name
     * @param string $chunkSize   each chunk size (in bytes)
     * @param string $totalSize   original file size (in bytes)
     * @param int    $total_files Total number of files
     *
     * @return bool
     */
    private function createFileFromChunks(
        string $temp_dir,
        string $fileName,
        int $chunkSize,
        int $totalSize,
        int $total_files
    ): bool {
        // count all the parts of this file
        $total_files_on_server_size = 0;
        $temp_total                 = 0;
        foreach (scandir($temp_dir) as $file) {
            $temp_total                 = $total_files_on_server_size;
            $tempfilesize               = filesize($temp_dir . '/' . $file);
            $total_files_on_server_size = $temp_total + $tempfilesize;
        }

        // check that all the parts are present
        // If the Size of all the chunks on the server is equal to the size of the file uploaded.
        if ($total_files_on_server_size >= $totalSize) {
            // create the final destination file
            $target = self::getUploadDir() . '/' . $fileName;

            if (($fp = fopen($target, 'w')) !== false) {
                for ($i = 1; $i <= $total_files; $i++) {
                    fwrite($fp, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
                    $this->log('Writing chunk #' . $i);
                }

                fclose($fp);

                if (!CFile::isAllowedFileType($target)) {
                    unlink($target);
                    $this->log('Error while writting file : File type is not allowed', UI_MSG_WARNING);

                    // Remove potentially dangerous file
                    CMbPath::remove($temp_dir);

                    return false;
                }

                $this->log('File written in ' . $target);
            } else {
                $this->log('Cannot create the destination file', UI_MSG_WARNING);

                return false;
            }

            // rename the temporary directory (to avoid access from other
            // concurrent chunks uploads) and than delete it
            if (rename($temp_dir, $temp_dir . '_UNUSED')) {
                CMbPath::remove($temp_dir . '_UNUSED');
            } else {
                CMbPath::remove($temp_dir);
            }
        }

        return true;
    }

    /**
     * Sanitize input string
     *
     * @param string $path Path to sanitize
     *
     * @return string
     */
    public static function sanitize(string $path): string
    {
        return preg_replace('/[^\w_\. -]/', '', trim(str_replace('..', '', $path)));
    }

    /**
     * Remove an uploaded file
     *
     * @param string $filename File to remove
     *
     * @return bool
     */
    public static function removeUploadedFile(string $filename): bool
    {
        $path = self::getUploadDir() . "/" . self::sanitize($filename);

        return unlink($path);
    }

    /**
     * Remove an uploaded temp directory
     *
     * @param string $dirname Directory to remove
     *
     * @return bool
     */
    public static function removeUploadedTemp(string $dirname): bool
    {
        $path = self::getUploadDir() . "/temp/" . self::sanitize($dirname);

        return CMbPath::emptyDir($path) && rmdir($path);
    }

    /**
     * Get the maximum upload size, based on disk free space and chunk size
     *
     * @return int
     */
    public function getMaxUploadSize(): int
    {
        return max(0, disk_free_space(self::getUploadDir()) - 2 * 1024 * 1024);
    }

    /**
     * Check if the requested chunk exists or not. this makes testChunks work
     */
    public function checkChunks(): bool
    {
        $resumableIdentifier  = self::sanitize(CValue::get("resumableIdentifier"));
        $resumableFilename    = self::sanitize(CValue::get("resumableFilename"));
        $resumableChunkNumber = self::sanitize(CValue::get("resumableChunkNumber"));

        $temp_dir = self::getUploadDir() . '/temp/' . $resumableIdentifier;

        $chunk_file = $temp_dir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

        // Return 200 if chunk has already been uploaded
        return file_exists($chunk_file);
    }

    /**
     * Handle chunk
     *
     * @return void
     */
    public function handleRequest(): void
    {
        // loop through files and move the chunks to a temporarily created directory
        if (!empty($_FILES)) {
            $resumableIdentifier  = self::sanitize(CValue::post("resumableIdentifier"));
            $resumableFilename    = self::sanitize(CValue::post("resumableFilename"));
            $resumableChunkNumber = self::sanitize(CValue::post("resumableChunkNumber"));
            $resumableChunkSize   = CValue::post("resumableChunkSize");
            $resumableTotalSize   = CValue::post("resumableTotalSize");

            $free     = disk_free_space(self::getUploadDir());
            $required = $resumableTotalSize + $resumableChunkSize * 2;
            if ($required > $free) {
                header("HTTP/1.0 507 Insufficient Storage");

                $this->log('Not enough space', UI_MSG_WARNING);

                return;
            }

            foreach ($_FILES as $file) {
                // check the error status
                if ($file['error'] != 0) {
                    $this->log('Error ' . $file['error'] . ' in file ' . $resumableFilename, UI_MSG_WARNING);
                    continue;
                }

                $temp_dir = self::getUploadDir() . '/temp';

                // init the destination file (format <filename.ext>.part<#chunk>
                // the file is stored in a temporary directory
                if ($resumableIdentifier) {
                    $temp_dir .= '/' . $resumableIdentifier;
                }

                $dest_file = $temp_dir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

                // create the temporary directory
                CMbPath::forceDir($temp_dir);

                // move the temporary file
                if (!move_uploaded_file($file['tmp_name'], $dest_file)) {
                    $this->log(
                        'Error saving (move_uploaded_file) chunk #' . $resumableChunkNumber . ' for file '
                        . $resumableFilename,
                        UI_MSG_WARNING
                    );
                } else {
                    $resumableTotalChunks = CValue::post("resumableTotalChunks");

                    // check if all the parts present, and create the final destination file
                    $this->createFileFromChunks(
                        $temp_dir,
                        $resumableFilename,
                        (int) $resumableChunkSize,
                        (int) $resumableTotalSize,
                        (int) $resumableTotalChunks
                    );
                }
            }
        }
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
