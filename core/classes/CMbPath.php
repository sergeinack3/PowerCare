<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Archive_Tar;
use Directory;
use DirectoryIterator;
use Exception;
use Ox\Core\Logger\LoggerLevels;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

abstract class CMbPath
{
    /**
     * Get first file in Directory (without glob)
     *
     * @param string $dir Directory path
     *
     * @return bool|string false if directory is empty
     */
    static function getFirstFile($dir)
    {
        if (false === $dh = opendir($dir)) {
            trigger_error("Passed argument is not a valid directory or couldn't be opened'", E_USER_WARNING);

            return false;
        }

        readdir($dh); // for ./
        readdir($dh); // for ../
        $child = readdir($dh); // for real first child
        closedir($dh);

        return $child;
    }

    /**
     * Removes all empty sub-directories of a given directory
     *
     * @param string  $dir         Directory from which we want to remove empty directories
     * @param Boolean $delete_root Delete the root directory
     *
     * @return integer Removed directories count
     */
    static function purgeEmptySubdirs($dir, $delete_root = true)
    {
        $removedDirsCount = 0;

        if (false === $dh = opendir($dir)) {
            trigger_error("Passed argument is not a valid directory or couldn't be opened'", E_USER_WARNING);

            return 0;
        }

        while (false !== ($node = readdir($dh))) {
            $path = "$dir/$node";
            if (is_dir($path) && $node !== "." && $node !== "..") {
                $removedDirsCount += self::purgeEmptySubdirs($path);
            }
        }
        closedir($dh);

        if ($delete_root && self::isEmptyDir($dir)) {
            if (rmdir($dir)) {
                $removedDirsCount++;
            }
        }

        return $removedDirsCount;
    }

    /**
     * Checks if a directory is empty
     *
     * @param string $dir Directory path
     *
     * @return bool true if directory is empty
     */
    static function isEmptyDir($dir)
    {
        if (false === $dh = opendir($dir)) {
            trigger_error("Passed argument is not a valid directory or couldn't be opened'", E_USER_WARNING);

            return false;
        }

        readdir($dh); // for ./
        readdir($dh); // for ../
        $file = readdir($dh); // for real first child
        closedir($dh);

        return $file === false;
    }

    /**
     * Get file name
     *
     * @param string $path Path from which we want the extension
     *
     * @return string|null The extension
     */
    static function getBasename($path)
    {
        $info = pathinfo($path);

        if (array_key_exists('basename', $info)) {
            return $info['basename'];
        }

        return null;
    }

    /**
     * Get file name
     *
     * @param string $path Path from which we want the extension
     *
     * @return string|null The extension
     */
    static function getFilename($path)
    {
        $info = pathinfo($path);

        if (array_key_exists('filename', $info)) {
            return $info['filename'];
        }

        return null;
    }

    /**
     * Guess the mime type of a file from its extension
     *
     * @param string $file The file from which we want to guess the mime type
     * @param string $ext  File extension
     *
     * @return string The mime type
     */
    static function guessMimeType(string $file = null, string $ext = null): string
    {
        $ext = $ext ?: strtolower(self::getExtension($file));

        // http://us3.php.net/manual/en/function.mime-content-type.php#84361
        switch ($ext) {
            case "js":
                return "application/x-javascript";

            case "json":
                return "application/json";

            case "jpg":
            case "jpeg":
            case "jpe":
                return "image/jpg";

            case "png":
            case "gif":
            case "bmp":
            case "tiff":
            case "tif":
            case "heic":
            case "heif":
                return "image/$ext";

            case "css":
                return "text/css";

            case "xml":
                return "application/xml";

            case "doc":
            case "docx":
            case "dot":
                return "application/msword";

            case "xls":
            case "xlt":
            case "xlm":
            case "xld":
            case "xla":
            case "xlc":
            case "xlw":
            case "xll":
                return "application/vnd.ms-excel";

            case "odt":
                return "application/vnd.oasis.opendocument.text";

            case "ppt":
            case "pps":
                return "application/vnd.ms-powerpoint";

            case "rtf":
                return "application/rtf";

            case "pdf":
                return "application/pdf";

            case "csv":
                return "text/csv";

            case "html":
            case "htm":
            case "php":
                return "text/html";

            case "txt":
            case "ini":
                return "text/plain";

            case "mpeg":
            case "mpg":
            case "mpe":
                return "video/mpeg";

            case "mp3":
                return "audio/mpeg3";

            case "wav":
                return "audio/wav";

            case "aiff":
            case "aif":
                return "audio/aiff";

            case "avi":
                return "video/msvideo";

            case "wmv":
                return "video/x-ms-wmv";

            case "mov":
                return "video/quicktime";

            case "zip":
                return "application/zip";

            case "tar":
                return "application/x-tar";

            case "swf":
                return "application/x-shockwave-flash";

            case "nfs":
                return "application/vnd.lotus-notes";

            case "spl":
            case "rlb":
                return "application/vnd.sante400";

            case "svg":
                return "image/svg+xml";

            case "mpr":
                return "multipart/related";

            case "dicom":
                return "application/dicom";

            case "hl7":
                return "application/x-hl7";

            // fake mimetype for testing
            case "gfy":
                return "text/goofy";
            case 'hpm':
                return 'application/x-hprim-med';
            case 'hpr':
            case 'hps':
                return 'application/x-hprim-sante';

            case 'pct':
                return 'image/x-pict';

            default:
                return "unknown/$ext";
        }
    }

    /**
     * Retrieve extension by mine type
     *
     * @param string $mine_type
     *
     * @return string
     */
    public static function getExtensionByMimeType(string $mine_type): ?string
    {
        switch (strtolower($mine_type)) {
            case "image/gif":
                return ".gif";
            case "image/jpeg":
                return ".jpeg";
            case "image/jpg":
                return ".jpg";
            case "image/png":
                return ".png";
            case "image/tiff":
                return ".tiff";
            case "application/rtf":
                return ".rtf";
            case "application/xml":
                return ".xml";
            case "application/pdf":
                return ".pdf";
            default:
                return null;
        }
    }

    /**
     * Get the extension of a file
     *
     * @param string $path Path from which we want the extension
     *
     * @return string|null The extension
     */
    static function getExtension($path)
    {
        $info = pathinfo($path);

        if (array_key_exists('extension', $info)) {
            return $info['extension'];
        }

        return null;
    }

    /**
     * Extracts an archive into a destination directory
     *
     * @param string $archivePath    Path to the archive file
     * @param string $destinationDir Destination forlder
     * @param string $extention      Extension to use
     *
     * @return integer The number of extracted files or false if failed
     */
    static function extract($archivePath, $destinationDir, $extention = null)
    {
        if (!is_file($archivePath)) {
            trigger_error("Archive could not be found", E_USER_WARNING);

            return false;
        }

        if (!self::forceDir($destinationDir)) {
            trigger_error("Destination directory not existing", E_USER_WARNING);

            return false;
        }

        $nbFiles = 0;
        $extract = false;
        $ext     = ($extention) ?: self::getExtension($archivePath);
        switch ($ext) {
            case "gz":
            case "tgz":
                $archive = new Archive_Tar($archivePath);
                $nbFiles = count($archive->listContent());
                $extract = $archive->extract($destinationDir);
                if (!$extract) {
                    trigger_error($archive->error_object->message, E_USER_WARNING);
                }

                break;

            case "zip":
                $archive = new ZipArchive();
                $archive->open($archivePath);
                $nbFiles = $archive->numFiles;
                $extract = $archive->extractTo($destinationDir);
                break;

            default:
                // nothing to do
        }

        if (!$extract) {
            return false;
        }

        return $nbFiles;
    }

    /**
     * Ensures a directory exists by building all tree sub-directories if possible
     *
     * @param string $dir  Directory path
     * @param int    $mode chmod like value
     *
     * @return boolean job done
     */
    static function forceDir($dir, $mode = 0755)
    {
        if (!$dir) {
            trigger_error("Directory is null", E_USER_WARNING);

            return false;
        }

        if (is_dir($dir) || $dir === "/") {
            return true;
        }

        if (self::forceDir(dirname($dir))) {
            return mkdir($dir, $mode);
        }

        return false;
    }

    /**
     * Clears out any file or sub-directory from target path
     *
     * @param string $dir Remove any file / folder from the directory
     * @param bool   $log Log removed dirs
     *
     * @return boolean true on success, false otherwise
     */
    static function emptyDir($dir, $log = true)
    {
        /** @var Directory $dir */
        $dir = dir($dir);

        if (!$dir) {
            return false;
        }

        while (false !== $item = $dir->read()) {
            if ($item !== '.' && $item !== '..' && !self::remove($dir->path . DIRECTORY_SEPARATOR . $item, $log)) {
                $dir->close();

                return false;
            }
        }

        $dir->close();

        return true;
    }

    /**
     * Recursively removes target path
     *
     * @param string $path Removes a directory
     * @param bool   $log  Log removed dirs
     *
     * @return bool true on success, false otherwise
     * @throws Exception
     */
    static function remove($path, $log = true)
    {
        if (!$path) {
            trigger_error("Path undefined", E_USER_WARNING);
        }

        if (is_dir($path)) {
            if (self::emptyDir($path, $log)) {
                if ($log) {
                    CApp::log(sprintf('CMbPath::remove - rmdir: %s', $path), null, LoggerLevels::LEVEL_DEBUG);
                }

                return rmdir($path);
            }

            return false;
        }

        if ($log) {
            CApp::log(sprintf('CMbPath::remove - unlink: %s', $path), null, LoggerLevels::LEVEL_DEBUG);
        }

        return unlink($path);
    }

    /**
     * Sanitize a base name for various file systems
     *
     * @param string $basename Base name to sanitize
     *
     * @return string
     */
    static function sanitizeBaseName($basename)
    {
        return strtr($basename, ": /\\|", "_____");
    }

    /**
     * Reduces a path, removing "folder/.." occurrences, not necessarily a real file system path
     *
     * @param string $path The path to reduce
     *
     * @return string The reduced path
     */
    static function reduce($path)
    {
        while (preg_match('/([A-z0-9-_])+\/\.\.\//', $path)) {
            $path = preg_replace('/([A-z0-9-_])+\/\.\.\//', '', $path);
        }

        return $path;
    }

    /**
     * Reduces a path, removing "folder/.." occurrences, not necessarily a real file system path
     *
     * @param string $input The path to canonicalize
     *
     * @return string The reduced path
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4 as per RFC 3986
     *
     */
    static function canonicalize($input)
    {
        static $cache = [];

        if (isset($cache[$input])) {
            return $cache[$input];
        }

        // 1.  The input buffer is initialized with the now-appended path
        //     components and the output buffer is initialized to the empty
        //     string.
        $output = '';

        // 2.  While the input buffer is not empty, loop as follows:
        while ($input !== '') {
            // A.  If the input buffer begins with a prefix of "`../`" or "`./`",
            //     then remove that prefix from the input buffer; otherwise,
            if (($prefix = substr($input, 0, 3)) === '../'
                || ($prefix = substr($input, 0, 2)) === './'
            ) {
                $input = substr($input, strlen($prefix));
            }

            // B.  if the input buffer begins with a prefix of "`/./`" or "`/.`",
            //     where "`.`" is a complete path segment, then replace that
            //     prefix with "`/`" in the input buffer; otherwise,
            else {
                if (($prefix = substr($input, 0, 3)) === '/./'
                    || ($prefix = $input) === '/.'
                ) {
                    $input = '/' . substr($input, strlen($prefix));
                }

                // C.  if the input buffer begins with a prefix of "/../" or "/..",
                //     where "`..`" is a complete path segment, then replace that
                //     prefix with "`/`" in the input buffer and remove the last
                //     segment and its preceding "/" (if any) from the output
                //     buffer; otherwise,
                else {
                    if (($prefix = substr($input, 0, 4)) === '/../'
                        || ($prefix = $input) === '/..'
                    ) {
                        $input  = '/' . substr($input, strlen($prefix));
                        $output = substr($output, 0, strrpos($output, '/'));
                    }
                    // D.  if the input buffer consists only of "." or "..", then remove
                    //     that from the input buffer; otherwise,
                    else {
                        if ($input === '.' || $input === '..') {
                            $input = '';
                        }

                        // E.  move the first path segment in the input buffer to the end of
                        //     the output buffer, including the initial "/" character (if
                        //     any) and any subsequent characters up to, but not including,
                        //     the next "/" character or the end of the input buffer.
                        else {
                            $pos = strpos($input, '/');
                            if ($pos === 0) {
                                $pos = strpos($input, '/', $pos + 1);
                            }
                            if ($pos === false) {
                                $pos = strlen($input);
                            }
                            $output .= substr($input, 0, $pos);
                            $input  = (string)substr($input, $pos);
                        }
                    }
                }
            }
        }

        // 3.  Finally, the output buffer is returned as the result of remove_dot_segments.
        return $cache[$input] = $output;
    }

    /**
     * Count the files under $path
     *
     * @param string $path The path to read
     *
     * @return string The number of files
     */
    static function countFiles($path)
    {
        return count(glob("$path/*")) - count(glob("$path/*", GLOB_ONLYDIR));
    }

    /**
     * Coompare file names, with directory first
     *
     * @param string $a File A
     * @param string $b File B
     *
     * @return int Comparison result as an integer, 0 being tie
     */
    static function cmpFiles($a, $b)
    {
        $dira = is_dir($a);
        $dirb = is_dir($b);

        return $dira == $dirb ? strcmp($a, $b) : $dirb - $dira;
    }

    /**
     * Get the path tree under a given directory
     *
     * @param string $dir        Directory to get the tree of
     * @param array  $ignored    Ignored patterns
     * @param array  $extensions Restricted extensions, if not null
     *
     * @return array|null Recursive array of basenames
     */
    static function getPathTreeUnder($dir, $ignored = [], $extensions = null)
    {
        // Restricted extensions
        if (!is_dir($dir) && is_array($extensions) && !in_array(self::getExtension($dir), $extensions)) {
            return null;
        }

        // Ignored patterns
        foreach ($ignored as $_ignored) {
            $replacements = [
                "*" => ".*",
                "." => "[.]",
            ];

            $_ignored = strtr($_ignored, $replacements);

            if (preg_match("|{$_ignored}|i", $dir) === 1) {
                return null;
            }
        }

        // File case
        if (!is_dir($dir)) {
            return true;
        }

        // Directory case
        $tree  = [];
        $files = glob("$dir/*");
        usort($files, [CMbPath::class, "cmpFiles"]);

        foreach ($files as $file) {
            $branch = self::getPathTreeUnder($file, $ignored, $extensions);
            if ($branch == null || (is_array($branch) && !count($branch))) {
                continue;
            }
            $tree[basename($file)] = $branch;
        }

        return $tree;
    }

    /**
     * Add a directory to include path
     *
     * @param string $dir Directory to add
     *
     * @return string The former include path
     */
    static function addInclude($dir)
    {
        if (!is_dir($dir)) {
            trigger_error("'$dir' is not an actual directory", E_USER_WARNING);
        }

        $paths   = explode(PATH_SEPARATOR, get_include_path());
        $paths[] = $dir;

        return set_include_path(implode(PATH_SEPARATOR, array_unique($paths)));
    }

    public static function includeFileIfExists(string $file_path): void
    {
        if (file_exists($file_path)) {
            include $file_path;
        }
    }

    /**
     * Get a list of the files inside a direcory (not the subdirectories)
     *
     * @param string $path The directory
     *
     * @return string[] The list of files
     */
    static function getFiles($path)
    {
        return array_diff(glob("$path/*"), glob("$path/*", GLOB_ONLYDIR));
    }

    /**
     * Get a file pointer to a temporary file in binary write mode (rb+)
     *
     * @return resource The resource to the temporary file
     */
    static function getTempFile()
    {
        return fopen("php://temp", "rb+");
    }

    /**
     * Recursively zip a folder
     *
     * @param string $source      Source folder
     * @param string $destination Desctination archive
     *
     * @return bool
     * @link http://stackoverflow.com/a/1334949/92315
     *
     */
    static function zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                $file = realpath($file);
                $file = str_replace('\\', '/', $file); // For Windows

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                }
            }
        } else {
            if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }

        return $zip->close();
    }

    /**
     * Build recursive tree
     *
     * @param string $dir Directory to get the tree of
     *
     * @return array
     */
    static function getTree($dir)
    {
        $tree = [];

        self::_getTree($dir, $tree);

        return $tree;
    }

    /**
     * Private tree builder
     *
     * @param string $dir  Directory to parse
     * @param array  $tree Tree to fill
     *
     * @return void
     */
    private static function _getTree($dir, &$tree)
    {
        /** @var SplFileInfo[] $path */
        $path = new RecursiveDirectoryIterator($dir);

        foreach ($path as $_subpath) {
            $_realpath = str_replace("\\", "/", $_subpath->getPathname());

            // We don't list "dot" files/folders
            $_dirname = basename($_realpath);
            if (strpos($_dirname, ".") === 0) {
                continue;
            }

            if ($_subpath->isDir()) {
                $subtree = [];
                self::_getTree($_realpath, $subtree);
                $tree[$_realpath] = $subtree;
            } else {
                $tree[$_realpath] = basename($_realpath);
            }
        }
    }

    /**
     * Recursivly remove empty subdirectories
     *
     * @param string $dir Path to the root directory
     *
     * @return bool
     */
    static function recursiveRmEmptyDir($dir)
    {
        $it = new DirectoryIterator($dir);

        while ($sub_dir = $it->getFilename()) {
            if (!$it->isDot() && $it->isDir()) {
                static::recursiveRmEmptyDir($it->getPathname());
            }

            $it->next();
        }

        return static::rmEmptyDir($dir);
    }

    /**
     * Remove an empty directory
     *
     * @param string $dir Directory
     *
     * @return bool
     */
    static function rmEmptyDir($dir)
    {
        if (self::isEmptyDir($dir)) {
            return rmdir($dir);
        }

        return false;
    }

    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author  Torleif Berger, Lorenzo Stanco
     * @link    http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    static function tailCustom($filepath, $lines = 1, $adaptive = true)
    {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) {
            return false;
        }
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        }
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }

        // Start reading
        $output = '';
        $chunk  = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);

        return trim($output);
    }

    /**
     * Modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/ and of
     * https://gist.github.com/lorenzos/1711e81a9162320fde20
     * @author  Kinga the Witch (Trans-dating.com), Torleif Berger, Lorenzo Stanco
     * @link    http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    static function tailWithSkip($filepath, $lines = 1, $skip = 0, $adaptive = true)
    {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false || @flock($f, LOCK_SH) === false) {
            return false;
        }

        if (!$adaptive) {
            $buffer = 4096;
        } else {
            // Sets buffer size, according to the number of lines to retrieve.
            // This gives a performance boost when reading a few lines from the file.
            $max    = max($lines, $skip);
            $buffer = ($max < 2 ? 64 : ($max < 10 ? 512 : 4096));
        }

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) == "\n") {
            if ($skip > 0) {
                $skip++;
                $lines--;
            }
        } else {
            $lines--;
        }

        // Start reading
        $output = '';
        $chunk  = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk
            $chunk = fread($f, $seek);

            // Calculate chunk parameters
            $count  = substr_count($chunk, "\n");
            $strlen = mb_strlen($chunk, '8bit');

            // Move the file pointer
            fseek($f, -$strlen, SEEK_CUR);

            if ($skip > 0) { // There are some lines to skip
                if ($skip > $count) {
                    $skip  -= $count;
                    $chunk = '';
                } // Chunk contains less new line symbols than
                else {
                    $pos = 0;

                    while ($skip > 0) {
                        if ($pos > 0) {
                            $offset = $pos - $strlen - 1;
                        } // Calculate the offset - NEGATIVE position of last new line symbol
                        else {
                            $offset = 0;
                        } // First search (without offset)

                        $pos = strrpos($chunk, "\n", $offset); // Search for last (including offset) new line symbol

                        if ($pos !== false) {
                            $skip--;
                        } // Found new line symbol - skip the line
                        else {
                            break;
                        } // "else break;" - Protection against infinite loop (just in case)
                    }
                    $chunk = substr($chunk, 0, $pos); // Truncated chunk
                    $count = substr_count($chunk, "\n"); // Count new line symbols in truncated chunk
                }
            }

            if (strlen($chunk) > 0) {
                // Add chunk to the output
                $output = $chunk . $output;
                // Decrease our line counter
                $lines -= $count;
            }
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }

        // Close file and return
        @flock($f, LOCK_UN);
        fclose($f);

        return trim($output);
    }

    /**
     * Get the path relative to Mediboard root
     *
     * @param string $absPath Absolute path
     *
     * @return string Relative path
     */
    public static function getRelativePath($absPath)
    {
        //global $dPconfig;
        //$mbPath = $dPconfig["root_dir"];
        $mbPath = dirname(__FILE__, 3);

        $absPath = strtr($absPath, "\\", "/");
        $mbPath  = strtr($mbPath, "\\", "/");

        // Hack for MS Windows server
        $relPath = strpos($absPath, $mbPath) === 0 ?
            substr($absPath, strlen($mbPath) + 1) :
            $absPath;

        return $relPath;
    }

    /**
     * @param $file
     *
     * @return int
     * @throws CMbException
     */
    static function countLines($file_path)
    {
        if (!file_exists($file_path)) {
            throw new CMbException("File {$file_path} dos not exist.");
        }

        $f = fopen($file_path, 'rb');

        $lines = 0;

        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }

        fclose($f);

        return $lines;
    }

    /**
     * Generate an MD5 hash string from the contents of a directory.
     *
     * @param string $directory
     * @return string|null
     */
    public static function hashDirectory(string $directory): ?string
    {
        if (!is_dir($directory)) {
            return '';
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..') {
                if (is_dir($directory . '/' . $file)) {
                    $files[] = self::hashDirectory($directory . '/' . $file);
                } else {
                    $files[] = md5_file($directory . '/' . $file);
                }
            }
        }

        $dir->close();

        sort($files);

        return md5(implode('', $files));
    }
}
