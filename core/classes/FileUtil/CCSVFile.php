<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Ox\Core\CApp;
use Ox\Core\CMbPath;

/**
 * CSV Files general purpose wrapper class
 * Responsibilities:
 *  - read, write and stream CSV files
 *  - delimiters, enclosures configuration
 */
class CCSVFile
{
    const PROFILE_OPENOFFICE = "openoffice";
    const PROFILE_EXCEL      = "excel";
    const PROFILE_TABS       = "tabs";
    const PROFILE_AUTO       = "auto";

    public $handle;
    public $delimiter = ',';
    public $enclosure = '"';

    public $escape_string = '\\';

    /**
     * @var array
     */
    public $column_names;

    static $profiles = [
        self::PROFILE_OPENOFFICE => [
            "delimiter" => ',',
            "enclosure" => '"',
        ],
        self::PROFILE_EXCEL      => [
            "delimiter" => ';',
            "enclosure" => '"',
        ],
        self::PROFILE_TABS       => [
            'delimiter' => "\t",
            'enclosure' => '"',
        ],
    ];

    /**
     * Standard constructor
     *
     * @param mixed  $handle       File handle of file path
     * @param string $profile_name Profile name, one of openoffice and excel
     */
    function __construct($handle = null, $profile_name = self::PROFILE_EXCEL)
    {
        if ($handle) {
            $this->handle = $handle;

            if (is_string($handle)) {
                $this->handle = fopen($handle, "r+");
            }
        } else {
            $this->handle = CMbPath::getTempFile();
        }

        $this->setProfile($profile_name);
    }

    /**
     * Set the profile parameters
     *
     * @param string $profile_name Profile name, one of "openoffice" and "excel"
     *
     * @return void
     */
    function setProfile($profile_name)
    {
        // Auto detect, from the number of "," or ";" occurrences in the first line
        if ($profile_name === self::PROFILE_AUTO) {
            $pos = ftell($this->handle);
            fseek($this->handle, 0);
            $line = fgets($this->handle);
            fseek($this->handle, $pos);

            $_counts = count_chars($line);
            $is_oo   = $_counts[ord(',')] > $_counts[ord(';')];

            $this->setProfile($is_oo ? self::PROFILE_OPENOFFICE : self::PROFILE_EXCEL);

            return;
        }

        if (!isset(self::$profiles[$profile_name])) {
            return;
        }

        $profile = self::$profiles[$profile_name];

        $this->delimiter = $profile["delimiter"];
        $this->enclosure = $profile["enclosure"];
    }

    /**
     * Read a line of the file
     *
     * @param bool $assoc                Make an associative array instead of a numeric-keyed array
     * @param bool $nullify_empty_values Set empty strings to NULL
     *
     * @return array|false|null An indexed array containing the fields read
     */
    public function readLine(bool $assoc = false, bool $nullify_empty_values = false)
    {
        $line = fgetcsv($this->handle, null, $this->delimiter, $this->enclosure, $this->escape_string);

        if (empty($line)) {
            return $line;
        }

        if ($nullify_empty_values) {
            $line = $this->nullifyEmptyValues($line);
        }

        if ($assoc && $this->column_names) {
            $_col_count = count($this->column_names);
            $line       = array_slice($line, 0, $_col_count);

            $_row_count = count($line);
            if ($_row_count !== $_col_count) {
                trigger_error(
                    sprintf(
                        "Rows has not the same number of items as the columns list (%d vs %d, offset = %d)",
                        $_row_count,
                        $_col_count,
                        ftell($this->handle)
                    ),
                    E_USER_WARNING
                );
            }

            return array_combine($this->column_names, $line);
        }

        return $line;
    }

    /**
     * Ignore the N first line
     *
     * @param integer $n line number to skip
     *
     * @return void
     */
    function jumpLine($n)
    {
        while ($n > 0) {
            fgetcsv($this->handle, null, $this->delimiter, $this->enclosure);
            $n--;
        }
    }

    /**
     * Counts the number of lines of the CSV file
     *
     * @return int
     */
    public function countLines(): int
    {
        $h = $this->handle;

        $pos = ftell($h);
        fseek($h, 0);

        $count = 0;
        while ($row = fgetcsv($this->handle, null, $this->delimiter, $this->enclosure, $this->escape_string)) {
            $count++;
        }

        fseek($h, $pos);

        return $count;
    }

    /**
     * Sets file pointer to the nth line (reset the pointer)
     *
     * @param integer $n Line number
     *
     * @return void
     */
    function goToLine($n)
    {
        fseek($this->handle, 0);

        $this->jumpLine($n);
    }

    /**
     * Write a line into the file
     *
     * @param array $values An array of string values
     *
     * @return integer The length of the written string, or false on failure
     */
    function writeLine($values)
    {
        return fputcsv($this->handle, $values, $this->delimiter, $this->enclosure);
    }

    /**
     * Set columns names to be used when reading the CSV file (to return associative arrays)
     *
     * @param array $names The columns names
     *
     * @return void
     */
    function setColumnNames($names)
    {
        $this->column_names = $names;
    }

    /**
     * Changes empty string values to NULL
     *
     * @param array $values An array of strings
     *
     * @return array The same array, with NULL instead of empty strings
     */
    function nullifyEmptyValues($values)
    {
        foreach ($values as &$_value) {
            if ($_value === "") {
                $_value = null;
            }
        }

        return $values;
    }

    /**
     * Get the full content of the file
     *
     * @return string
     */
    function getContent()
    {
        rewind($this->handle);

        $content = "";
        while ($s = fgets($this->handle)) {
            $content .= $s;
        }

        return $content;
    }

    /**
     * Stream the content to the browser
     *
     * @param string $file_name      File name for the browser
     * @param bool   $real_streaming Real streaming, not loading all the data in memory
     *
     * @return void
     */
    function stream($file_name, $real_streaming = false)
    {
        header("Content-Type: text/plain;charset=" . CApp::$encoding);
        header("Content-Disposition: attachment;filename=\"$file_name.csv\"");

        // Real streaming never loads the full file into memory
        if ($real_streaming) {
            // End output buffering
            ob_end_clean();
            rewind($this->handle);

            while ($s = fgets($this->handle)) {
                echo $s;
            }

            return;
        }

        $content = $this->getContent();

        header("Content-Length: " . strlen($content) . ";");

        echo $content;
    }

    /**
     * Close file
     *
     * @return bool
     */
    function close()
    {
        return fclose($this->handle);
    }
}
