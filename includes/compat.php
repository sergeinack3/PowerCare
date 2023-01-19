<?php
/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;

/**
 * Compat functions emulations
 */
/**
 * Recursively applies a function to values of an array
 *
 * @param string $function      Callback to apply
 * @param array  $array         Array to apply callback on
 * @param bool   $apply_on_keys Apply function also on keys
 * @return array
 */
function array_map_recursive($function, $array, $apply_on_keys = false)
{
    // Recursion closure
    if (!is_array($array)) {
        return call_user_func($function, $array);
    }

    // Rercursive call
    $result = [];
    foreach ($array as $key => $value) {
        $result[$apply_on_keys ? $function($key) : $key] = array_map_recursive($function, $value, $apply_on_keys);
    }

    return $result;
}

/**
 * Checks recursively if a value exists in an array
 *
 * @param mixed $needle   The searched value.
 * @param array $haystack The array.
 * @param bool  $strict   If true also check value types
 *
 * @return bool true if needle is found in the array, false otherwise.
 */
function in_array_recursive($needle, $haystack, $strict = false)
{
    if (in_array($needle, $haystack, $strict)) {
        return true;
    }

    foreach ($haystack as $v) {
        if (is_array($v) && in_array_recursive($needle, $v, $strict)) {
            return true;
        }
    }

    return false;
}

if (!function_exists('getrusage')) {
    /**
     * Gets the current resource usages
     *
     * @param bool|int $who If who is 1, getrusage will be called with RUSAGE_CHILDREN
     *
     * @return array Results
     * @link http://php.net/memory_get_peak_usage
     */
    function getrusage($who = 0)
    {
        return [
            "ru_utime.tv_usec" => -1,
            "ru_utime.tv_sec"  => -1,
            "ru_stime.tv_usec" => -1,
            "ru_stime.tv_sec"  => -1,
        ];
    }
}

if (!function_exists('mb_strtoupper')) {
    /**
     * Make a string uppercase
     * Multi-byte graceful fallback
     *
     * @param string $string Input string
     *
     * @return string the uppercased string
     * @link http://php.net/manual/en/function.strtoupper.php
     */
    function mb_strtoupper($string)
    {
        return strtoupper($string);
    }
}

if (!function_exists('mb_strtolower')) {
    /**
     * Make a string lowecase
     * Multi-byte graceful fallback
     *
     * @param string $string Input string
     *
     * @return string the lowercased string
     * @link http://php.net/manual/en/function.strtolower.php
     */
    function mb_strtolower($string)
    {
        return strtolower($string);
    }
}

if (!function_exists('mb_convert_case')) {
    /**
     * Make a string with uppercased words
     * Multi-byte graceful fallback
     *
     * @param string $string Input string
     *
     * @return string The word uppercased string
     * @link http://php.net/manual/en/function.ucwords.php
     */
    function mb_ucwords($string)
    {
        return ucwords($string);
    }
} else {
    /**
     * Make a string with uppercased words
     * Multi-byte graceful fallback
     *
     * @param string $string Input string
     *
     * @return string the word uppercased string
     * @link http://php.net/manual/en/function.ucwords.php
     */
    function mb_ucwords($string)
    {
        return mb_convert_case($string ?? "", MB_CASE_TITLE, CApp::$encoding);
    }
}

// Todo: Remove?
if (!function_exists('bcmod')) {
    /**
     * (PHP 4, PHP 5)
     * Get modulus of an arbitrary precision number
     *
     * @param string $left_operand Any precision integer value
     * @param int    $modulus      Integer modulus
     *
     * @return int Rest of modulus
     * @link http://php.net/bcmod
     */
    function bcmod($left_operand, $modulus)
    {
        // how many numbers to take at once? carefull not to exceed (int)
        $take = 5;
        $mod  = '';
        do {
            $a            = (int)$mod . substr($left_operand, 0, $take);
            $left_operand = substr($left_operand, $take);
            $mod          = $a % $modulus;
        } while (strlen($left_operand));

        return (int)$mod;
    }
}

if (!function_exists('mime_content_type')) {
    /**
     * (PHP 5 > 5.2)
     * Dectect the mime type of a file
     *
     * @param string $f Name of the file
     *
     * @return string Mime type
     */
    function mime_content_type($f)
    {
        return trim(exec('file -bi ' . escapeshellarg($f)));
    }
}

if (!function_exists('json_last_error_msg')) {
    /**
     * (PHP 5 >= 5.5.0, PHP 7)
     * Returns the error string of the last json_encode() or json_decode() call
     *
     * @return string
     */
    function json_last_error_msg()
    {
        static $ERRORS = [
            JSON_ERROR_NONE           => 'No error',
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX         => 'Syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $error = json_last_error();

        return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
    }
}

// Todo: To remove ASAP
if (PHP_VERSION_ID < 70300) {
    if (!function_exists('is_countable')) {
        function is_countable($var)
        {
            return (is_array(
                    $var
                ) || $var instanceof Countable || $var instanceof SimpleXmlElement || $var instanceof ResourceBundle);
        }
    }
}

// https://www.php.net/manual/fr/function.array-key-first.php
if (!function_exists('array_key_first')) {
    function array_key_first(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }

        return null;
    }
}

// https://www.php.net/manual/fr/function.array-key-last.php
if (!function_exists('array_key_last')) {
    function array_key_last(array $arr)
    {
        $arr = array_keys($arr);

        return end($arr);
    }
}

if (!function_exists('str_starts_with')) {
    // https://php.watch/versions/8.0/str_starts_with-str_ends_with
    function str_starts_with(string $haystack, string $needle): bool
    {
        return \strncmp($haystack, $needle, \strlen($needle)) === 0;
    }
}

// https://php.watch/versions/8.0/str_starts_with-str_ends_with
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || $needle === \substr($haystack, -\strlen($needle));
    }
}

// https://php.watch/versions/8.0/str_contains
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

