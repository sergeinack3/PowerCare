<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use JsonSerializable;

/**
 * Utility methods for arrays
 */
abstract class CMbArray
{
    public const PLUCK_SORT_REMOVE_DIACRITICS = 'removeDiacritics';

    /**
     * Compares the content of two arrays
     *
     * @param array $array1 The first array
     * @param array $array2 The second array
     *
     * @return array An associative array with values
     *   "absent_from_array1"
     *   "absent_from_array2"
     *   "different_values"
     */
    static function compareKeys($array1, $array2)
    {
        $diff = [];

        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                $diff[$key] = "absent_from_array2";
                continue;
            }

            if ($value != $array2[$key]) {
                $diff[$key] = "different_values";
            }
        }

        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                $diff[$key] = "absent_from_array1";
                continue;
            }

            if ($value != $array1[$key]) {
                $diff[$key] = "different_values";
            }
        }

        return $diff;
    }

    /**
     * Get value or array recursively.
     *
     * @param array  $array     array
     * @param string $haystack  recursive key
     * @param null   $default   default value
     * @param string $delimiter delimiter of haystack
     *
     * @return mixed
     */
    static function getRecursive($array, $haystack, $default = null, $delimiter = " ")
    {
        $haystack = explode($delimiter, $haystack);
        foreach ($haystack as $_array) {
            $array = CMbArray::get($array, $_array, $default);
        }

        return $array;
    }

    /**
     * Compute recursively the associative difference between two arrays
     * Function is not commutative, as first array is the reference
     *
     * @param array $array1 The first array
     * @param array $array2 The second array
     *
     * @return array|false The difference
     */
    static function diffRecursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            // Array value
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    if ($new_diff = self::diffRecursive($value, $array2[$key])) {
                        $difference[$key] = $new_diff;
                    }
                }
            } // scalar value
            elseif (isset($value)) {
                if (!isset($array2[$key]) || $array2[$key] != $value) {
                    $difference[$key] = $value;
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key]) {
                    $difference[$key] = $value;
                }
            }
        }

        return isset($difference) ? $difference : false;
    }

    /**
     * Remove all occurrences of given value in array
     *
     * @param mixed $needle   Value to remove
     * @param array $haystack Array to alter
     * @param bool  $strict   Strict search
     *
     * @return int Occurrences count
     */
    static function removeValue($needle, &$haystack, $strict = false)
    {
        if (!is_array($haystack)) {
            return 0;
        }

        $total = count($haystack);

        $haystack = array_filter(
            $haystack,
            function ($v) use ($needle, $strict) {
                return ($strict) ? ($v !== $needle) : ($v != $needle);
            }
        );

        return $total - count($haystack);
    }

    /**
     * Get the previous and next key
     *
     * @param array  $arr The array to seek in
     * @param string $key The target key
     *
     * @return array Previous and next key in an array, null if unavailable
     */
    static function getPrevNextKeys($arr, $key)
    {
        $keys       = array_keys($arr);
        $keyIndexes = array_flip($keys);

        $return = [
            "prev" => null,
            "next" => null,
        ];
        if (isset($keys[$keyIndexes[$key] - 1])) {
            $return["prev"] = $keys[$keyIndexes[$key] - 1];
        }

        if (isset($keys[$keyIndexes[$key] + 1])) {
            $return["next"] = $keys[$keyIndexes[$key] + 1];
        }

        return $return;
    }

    /**
     * Merge recursively two array
     *
     * @param array $paArray1 First array
     * @param array $paArray2 The array to be merged
     *
     * @return array The merge result
     */
    static function mergeRecursive($paArray1, $paArray2)
    {
        if (!is_array($paArray1) || !is_array($paArray2)) {
            return $paArray2;
        }

        foreach ($paArray2 as $sKey2 => $sValue2) {
            $paArray1[$sKey2] = CMbArray::mergeRecursive(@$paArray1[$sKey2], $sValue2);
        }

        return $paArray1;
    }

    /**
     * Merge recursively array with multiple array elements and return one array with sum of each key values
     *
     * @param array $array Array with multiple arrays to merge key values
     *
     * @return array
     */
    public static function sumArraysByKey(array $arrays): array
    {
        $sums = [];

        // Iterate each array:
        foreach ($arrays as $arr_vals) {
            // Iterate their values:
            foreach ($arr_vals as $key => $val) {
                // Initialize the summary key:
                $sums[$key] ??= 0;

                // Add up the value:
                $sums[$key] += $val;
            }
        }

        return $sums;
    }

    /**
     * Alternative to array_merge that always preserves keys
     *
     * @return array The merge result
     */
    static function mergeKeys()
    {
        $args   = func_get_args();
        $result = [];
        foreach ($args as $array) {
            foreach ($array as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    /**
     * Returns the value following the given one in cycle mode
     *
     * @param array $array The array of values to cycle on
     * @param mixed $value The reference value
     *
     * @return mixed Next value, false if $value does not exist
     */
    static function cycleValue($array, $value)
    {
        $array = array_unique($array);
        while ($value !== current($array)) {
            next($array);
            if (false === current($array)) {
                trigger_error("value could not be found in array", E_USER_NOTICE);

                return false;
            }
        }

        if (false === $nextValue = next($array)) {
            $nextValue = reset($array);
        }

        return $nextValue;
    }

    /**
     * Get a value from an array, default value if key is undefined
     *
     * @param array  $array   The array to explore
     * @param string $key     Name of the key to extract
     * @param mixed  $default The default value if $key is not found
     *
     * @return mixed The value corresponding to $key in $array if it exists, else $default
     */
    static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Returns the first value of the array that isset, from keys
     *
     * @param array $array   The array to explore
     * @param array $keys    The keys to read
     * @param mixed $default The default value no value is found
     *
     * @return mixed The first value found
     */
    static function first($array, $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }

        return $default;
    }

    /**
     * Return first element which match with condition in callback
     *
     * @param callable $callback should return a boolean
     * @param array    $array
     * @param int      $mode     0: value; 1: key, 2:key/value
     *
     * @return mixed|null
     */
    static function arrayFirst($callback, $array, $mode = 0)
    {
        $result = null;
        foreach ($array as $_key => $_item) {
            switch ($mode) {
                case 0:
                    if (!$callback($_item)) {
                        continue 2;
                    }
                    break;
                case 1:
                    if (!$callback($_key)) {
                        continue 2;
                    }
                    break;
                case 2:
                    if (!$callback($_key, $_item)) {
                        continue 2;
                    }
                    break;
                default:
            }

            $result = $_item;
            break;
        }

        return $result;
    }

    /**
     * Extract a key from an array, returning the value if exists
     *
     * @param array  $array     The array to explore
     * @param string $key       Name of the key to extract
     * @param mixed  $default   The default value is $key is not found
     * @param bool   $mandatory Will trigger an warning if value is null
     *
     * @return mixed The extracted value
     */
    static function extract(&$array, $key, $default = null, $mandatory = false)
    {
        // Should not use isset
        if (!array_key_exists($key, $array)) {
            if ($mandatory) {
                trigger_error("Could not extract '$key' index in array", E_USER_WARNING);
            }

            return $default;
        }

        $value = $array[$key];
        unset($array[$key]);

        return $value;
    }

    /**
     * Give a default value to key if key is not set
     *
     * @param array $array The array to alter
     * @param mixed $key   The key to check
     * @param mixed $value The default value if key is not set
     *
     * @return void
     */
    static function defaultValue(&$array, $key, $value)
    {
        // Should not use isset
        if (!array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
    }

    /**
     * Increment a key counter in array
     *
     * @param array $array The array to alter
     * @param mixed $key   The counter
     *
     * @return integer Incremented count
     */
    static function inc(&$array, $key)
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = 0;
        }

        return ++$array[$key];
    }

    /**
     * Return a string of XML attributes based on given array key-value pairs
     *
     * @param array $array The source array
     *
     * @return string String attributes like 'key1="value1" ... keyN="valueN"'
     */
    static function makeXmlAttributes($array)
    {
        $return = '';
        foreach ($array as $key => $value) {
            if ($value !== null) {
                $value  = trim(CMbString::htmlSpecialChars($value));
                $return .= "$key=\"$value\" ";
            }
        }

        return $return;
    }

    /**
     * Pluck (collect) given key or attribute name of each value
     * whether the values are arrays or objects. Preserves indexes
     *
     * @param mixed $array The array or object to pluck
     * @param mixed $name  The key or attribute name
     *
     * @return array All plucked values
     */
    static function pluck($array, $name)
    {
        $args = func_get_args();

        if (!is_array($array)) {
            return null;
        }

        // Recursive multi-dimensional call
        if (count($args) > 2) {
            $name  = array_pop($args);
            $array = call_user_func_array([CMbArray::class, "pluck"], $args);
        }

        $values = [];
        foreach ($array as $key => $item) {
            if (is_object($item)) {
                if (!property_exists($item, $name)) {
                    trigger_error("Object at key '$key' doesn't have the '$name' property", E_USER_WARNING);
                    continue;
                }

                $values[$key] = $item->$name;
                continue;
            }

            if (is_array($item)) {
                if (!array_key_exists($name, $item)) {
                    trigger_error("Array at key '$key' doesn't have a value for '$name' key", E_USER_WARNING);
                    continue;
                }

                $values[$key] = $item[$name];
                continue;
            }

            trigger_error("Item at key '$key' is neither an array nor an object", E_USER_WARNING);
        }

        return $values;
    }

    /**
     * Create an array with filtered keys based on having given prefix
     *
     * @param array  $array  The array to filter
     * @param string $prefix The prefix that has to start key strings
     *
     * @return array The filtered array
     */
    static function filterPrefix($array, $prefix)
    {
        $values = [];
        foreach ($array as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * Return number of element requested, but element is unique
     * @param array $array
     * @param int   $nb
     *
     * @return array|null
     */
    public static function arrayRandValues(array $array, int $nb = 1): ?array
    {
        $keys = array_rand($array, $nb);

        if ($keys === null) {
            return null;
        }

        if ($nb === 1) {
            return (array)$array[$keys];
        }

        $results = [];
        foreach ($keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Transpose a 2D matrix
     *
     * @param array $array The matrix to transpose
     *
     * @return array The transposed matrix
     */
    static function transpose($array)
    {
        $out = [];
        foreach ($array as $key => $subarr) {
            foreach ($subarr as $subkey => $subvalue) {
                $out[$subkey][$key] = $subvalue;
            }
        }

        return $out;
    }

    /**
     * Call a method on each object of the array
     *
     * @param object $array  The array of objects
     * @param string $method The method to call on each array
     *
     * @return array The array of objects after the method is called
     */
    static function invoke($array, $method)
    {
        $args = func_get_args();
        $args = array_slice($args, 2);

        foreach ($array as $object) {
            call_user_func_array([$object, $method], $args);
        }

        return $array;
    }

    /**
     * Insert a key-value pair after a specific key
     *
     * @param array  $array   The source array
     * @param string $ref_key The reference key
     * @param string $key     The new key
     * @param mixed  $value   The new value to insert after $_ref_key
     *
     * @return void
     */
    static function insertAfterKey(&$array, $ref_key, $key, $value)
    {
        $keys = array_keys($array);
        $vals = array_values($array);

        $insertAfter = array_search($ref_key, $keys) + 1;

        $keys2 = array_splice($keys, $insertAfter);
        $vals2 = array_splice($vals, $insertAfter);

        $keys[] = $key;
        $vals[] = $value;

        $array = array_merge(array_combine($keys, $vals), empty($keys2) ? [] : array_combine($keys2, $vals2));
    }

    /**
     * Return the standard average of an array
     *
     * @param array $array Scalar values
     *
     * @return float Average value
     */
    static function average($array)
    {
        if (!is_array($array)) {
            return null;
        }

        return array_sum($array) / count($array);
    }

    /**
     * Return the standard variance of an array
     *
     * @param array $array Scalar values
     *
     * @return float: ecart-type
     */
    static function variance($array)
    {
        if (!is_array($array)) {
            return null;
        }

        $moyenne = self::average($array);
        $sigma   = 0;
        foreach ($array as $value) {
            $sigma += pow((floatval($value) - $moyenne), 2);
        }

        return sqrt($sigma / count($array));
    }

    /**
     * Computes the median of an array
     *
     * @param array $array Array of values
     * @param bool  $lower Get lower values instead of average one if even number
     *
     * @return float|null
     */
    static function median(&$array, $lower = false)
    {
        if (!is_array($array) || empty($array) || !sort($array)) {
            return null;
        }

        $count = count($array);

        // Find the middle value, or the lowest middle value
        $middle = (int)floor(($count - 1) / 2);

        // Odd number, middle is the median
        if ($count % 2) {
            $median = $array[$middle];
        } // Even number, calculate avg of 2 medians
        else {
            $low  = $array[$middle];
            $high = $array[$middle + 1];

            $median = ($lower) ? $low : (($low + $high) / 2);
        }

        return $median;
    }

    /**
     * Check whether a value is in array
     *
     * @param mixed $needle   The searched value
     * @param mixed $haystack Array or token space separated string
     * @param bool  $strict   Type based comparaison
     *
     * @return bool
     */
    static function in($needle, $haystack, $strict = false)
    {
        if (is_string($haystack)) {
            $haystack = explode(" ", $haystack);
        }

        return in_array($needle, $haystack, $strict);
    }

    /**
     * Exchanges all keys with their associated values in an array,
     * and keep all the values if there are several occurrences
     *
     * @param array $trans The array to flip
     *
     * @return array[]
     */
    static function flip($trans)
    {
        $result = [];
        foreach ($trans as $_key => $_value) {
            if (!array_key_exists($_value, $result)) {
                $result[$_value] = [$_key];
            } else {
                $result[$_value][] = $_key;
            }
        }

        return $result;
    }

    /**
     * Sort an array by using another array.
     * The values of the $order must be the keys of the $array, in the right order.
     *
     * @param array $array The array to sort
     * @param array $order The
     *
     * @return array The sorted array
     */
    static function ksortByArray($array, $order)
    {
        $ordered = [];
        foreach ($order as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }

        return $ordered;
    }

    /**
     * Sort an array of objects by property name given in parameter
     *
     * @param array  $objects The objects array to sort
     * @param string $prop    The property name
     * @param string $propAlt The alternative property name
     *
     * @return bool Sucess or Failure
     */
    static function ksortByProp(&$objects, $prop, $propAlt = null)
    {
        return usort($objects, CMbArray::objectSorter($prop, $propAlt));
    }

    /**
     * Get a comparaison fonction for two objects
     * using a property (used by ksortByProp)
     *
     * @param string $prop    The property name
     * @param string $propAlt The alternative property name
     *
     * @return callable The fonction
     */
    static function objectSorter($prop, $propAlt = null)
    {
        return function ($object1, $object2) use ($prop, $propAlt) {
            $compare1 = $object1->$prop;
            $compare2 = $object2->$prop;

            if ($propAlt && ($compare1 == $compare2)) {
                return strnatcasecmp(
                    CMbString::removeDiacritics($object1->$propAlt),
                    CMbString::removeDiacritics($object2->$propAlt)
                );
            }

            return strnatcasecmp(CMbString::removeDiacritics($compare1), CMbString::removeDiacritics($compare2));
        };
    }

    /**
     * Collection natural sort utility with diacritic
     *
     * @param array $array The array to sort
     *
     * @return void
     */
    static function naturalSort(&$array)
    {
        uasort(
            $array,
            function ($a, $b) {
                return strnatcasecmp(CMbString::removeDiacritics($a), CMbString::removeDiacritics($b));
            }
        );
    }

    static function naturalKeySort(&$array)
    {
        uksort(
            $array,
            function ($a, $b) {
                return strnatcasecmp(CMbString::removeDiacritics($a), CMbString::removeDiacritics($b));
            }
        );
    }

    /**
     * Objects multi sorting by props
     *
     * @param CMbObject[] $objects       Objects to sort
     * @param string      $prop1         First prop to sort by
     * @param integer     $sort1         Sort(SORT_ASC|SORT_DESC|...)
     * @param bool        $preserve_keys Should we preserve keys?
     *
     * @return void
     */
    static function multiSortByProps(&$objects, $prop1, $sort1 = SORT_ASC, $preserve_keys = true)
    {
        $args = func_get_args();

        if (!$objects) {
            return;
        }

        $props = [$prop1 => $sort1];

        if (isset($args[4])) {
            $args = array_slice($args, 4);

            foreach ($args as $_i => $_arg) {
                if (is_integer($_arg)) {
                    continue;
                }

                if (is_string($_arg) && (isset($args[$_i + 1]) && is_integer($args[$_i + 1]))) {
                    $props[$_arg] = $args[$_i + 1];
                }
            }
        }

        $sort = [];
        $keys = ($preserve_keys) ? array_keys($objects) : [];

        foreach ($objects as $_k => &$_object) {
            foreach ($props as $_prop => $_sort) {
                $sort[$_prop][$_k] = $_object->{$_prop};
            }
        }

        $params = [];

        foreach ($props as $_prop => $_sort) {
            $params[] = $sort[$_prop];
            $params[] = $_sort;
        }

        $params[] = &$objects;

        if ($preserve_keys) {
            $params[] = &$keys;
        }

        call_user_func_array('array_multisort', $params);

        if ($preserve_keys) {
            $objects = array_combine($keys, $objects);
        }
    }

    /**
     * Array/Object collection sorting using a plucked property as sorter, and preserves numeric keys (!!)
     *
     * Can pass self::PLUCK_SORT_REMOVE_DIACRITICS to the function at any position after $order to removeDiacritics
     * before comparison
     *
     * @param array $array The collection to sort
     * @param int   $order Sort order, one of SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING
     *
     * @return void
     */
    public static function pluckSort(&$array, $order /*, ...*/)
    {
        $pluck_args = func_get_args();

        // Check if a property is self::PLUCK_SORT_REMOVE_DIACRITICS to apply the function
        $remove_diacritics = array_search(self::PLUCK_SORT_REMOVE_DIACRITICS, array_slice($pluck_args, 2));
        if ($remove_diacritics !== false) {
            unset($pluck_args[$remove_diacritics + 2]);
        }

        // remove order param
        unset($pluck_args[1]);

        $sorter = call_user_func_array(["self", "pluck"], $pluck_args);

        if ($remove_diacritics !== false) {
            $sorter = array_map([CMbString::class, 'removeDiacritics'], $sorter);
        }

        $keys = array_keys($array);
        array_multisort($sorter, $order, $array, $keys);
        $array = array_combine($keys, $array);
    }

    /**
     * A recursive version of array_search (works for multidimensional array).
     * The result is an array reproducing the structure of the haystack
     *
     * @param mixed $needle   The needle
     * @param array $haystack The haystack
     *
     * @return array
     */
    static function searchRecursive($needle, $haystack)
    {
        $path = [];
        foreach ($haystack as $id => $val) {
            if ($val === $needle) {
                $path[] = $id;

                break;
            } elseif (is_array($val)) {
                $found = CMbArray::searchRecursive($needle, $val);
                if (count($found) > 0) {
                    $path[$id] = $found;

                    break;
                }
            }
        }

        return $path;
    }

    /**
     * Get a value in a $tree, from a $path built with $separator
     *
     * @param array  $tree      The tree containing the value
     * @param string $path      The path to browse
     * @param string $separator The separator used in the path
     *
     * @return mixed
     */
    static function readFromPath($tree, $path, $separator = " ")
    {
        if (!$path) {
            return $tree;
        }

        $items = explode($separator, $path);
        foreach ($items as $part) {
            $tree = CMbArray::get($tree, $part);
        }

        return $tree;
    }

    /**
     * Count the occurrences of the given value
     *
     * @param mixed $needle   The searched value
     * @param array $haystack The array
     * @param bool  $strict   If true, strict comparison (===) will be used
     *
     * @return int
     */
    static function countValues($needle, $haystack, $strict = false)
    {
        return count(array_keys($haystack, $needle, $strict));
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param array $array Array
     *
     * @return array
     */
    static function array_flatten($array)
    {
        $return = [];

        array_walk_recursive(
            $array,
            function ($x) use (&$return) {
                $return[] = $x;
            }
        );

        return $return;
    }

    public static function flattenArrayKeys(array $array, ?string $prefix = null, string $key_separator = ' '): array
    {
        $flattened = [];
        foreach ($array as $_key => $_value) {
            if (is_array($_value)) {
                $flattened = $flattened + self::flattenArrayKeys($_value, $prefix . $key_separator . $_key);
            } else {
                $flattened[$prefix . $key_separator . $_key] = $_value;
            }
        }

        return $flattened;
    }

    /**
     * Unflatten a dictionnary
     *
     * @param array  $array     The dictionnary
     * @param string $separator The separator
     *
     * @return array
     */
    static function unflatten(array $array, $separator = ' ')
    {
        $tree = [];

        foreach ($array as $_key => $_value) {
            $_parts = explode($separator, $_key);

            $node =& $tree;
            foreach ($_parts as $_part) {
                $node =& $node[$_part];
            }

            $node = $_value;
        }

        return $tree;
    }

    /**
     * Map a function recursively on an array, allowing to map on objects too
     *
     * @param callable     $function   The callback function
     * @param array|object $array      The array or object
     * @param bool         $on_objects Act also on objects
     *
     * @return array|mixed
     */
    static function mapRecursive($function, $array, $on_objects = false)
    {
        // Objects
        if ($on_objects && is_object($array)) {
            if ($array instanceof JsonSerializable) {
                $object = $array->jsonSerialize();
            } else {
                $object = get_object_vars($array);
            }

            // Scalar
            if (!is_array($object)) {
                return call_user_func($function, $array);
            }

            foreach ($object as $key => $value) {
                $object[$key] = self::mapRecursive($function, $value, $on_objects);
            }

            return $object;
        }

        // Scalar
        if (!is_array($array)) {
            return call_user_func($function, $array);
        }

        // Arrays
        $result = [];
        foreach ($array as $key => $value) {
            $result[call_user_func($function, $key)] = self::mapRecursive($function, $value, $on_objects);
        }

        return $result;
    }

    /**
     * Encode data to JSON
     *
     * @param mixed $array   The data to encode
     * @param bool  $encode  Make utf-8 encoding too
     * @param int   $options json_encode options
     *
     * @return string|false
     */
    static function toJSON($array, $encode = true, $options = 0)
    {
        if ($encode) {
            $callback = function ($value) {
                if (is_string($value)) {
                    return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                }

                return $value;
            };

            $array = self::mapRecursive($callback, $array, true);
        }

        $str = json_encode($array, $options ?? 0);

        if (json_last_error()) {
            trigger_error(json_last_error_msg(), E_USER_WARNING);
        }

        return $str;
    }

    /**
     * Converts recursively all values of an array to UTF8
     *
     * @param array $array
     * @param bool  $encode_key
     *
     */
    public static function utf8Encoding(array $array, $encode_key = false): array
    {
        if (!$encode_key) {
            array_walk_recursive(
                $array,
                static function (&$item): void {
                    if ($item !== null && !mb_detect_encoding($item, 'UTF-8', true)) {
                        $item = utf8_encode($item);
                    }
                }
            );

            return $array;
        }

        return self::mapRecursive(
            function ($item) {
                if ($item !== null && !mb_detect_encoding($item, 'UTF-8', true)) {
                    $item = utf8_encode($item);
                }

                return $item;
            },
            $array
        );
    }

    /**
     * @param array  $objects
     * @param string $order
     * @param bool   $null_last Return null values as last elements
     *
     * @return void
     * @throws CMbException
     */
    public static function sortObjectsByString(array &$objects, string $order, bool $null_last = false): void
    {
        $order_by = [];

        $order = explode(',', $order);

        foreach ($order as $_order) {
            $matches = [];

            if (preg_match('/(?<field>\w+)(\s(?<order>ASC|DESC))?/i', $_order, $matches)) {
                $_sort = mb_strtoupper(($matches['order']) ?? 'ASC');

                $order_by[] = [
                    'field' => $matches['field'],
                    'sort'  => ($_sort === 'ASC') ? SORT_ASC : SORT_DESC,
                ];
            } else {
                throw new CMbException('CMbArray-error-Invalid sort parameter: %s', $_order);
            }
        }

        if (!$order_by) {
            return;
        }

        $comparator = function ($a, $b) use ($order_by, $null_last) {
            do {
                $current = reset($order_by);
                $_result = CMbArray::compareObjectsByProp($a, $b, $current['field'], $current['sort'], $null_last);

                if ($_result !== 0) {
                    return $_result;
                }
            } while (array_shift($order_by));

            return $_result;
        };

        uasort($objects, $comparator);
    }

    /**
     * @param mixed  $a
     * @param mixed  $b
     * @param string $prop
     * @param int    $sort
     * @param bool   $null_last
     *
     * @return int
     */
    private static function compareObjectsByProp(
        $a,
        $b,
        string $prop,
        int $sort = SORT_ASC,
        bool $null_last = false
    ): int {
        if ($null_last) {
            $val_a = $a->{$prop};
            $val_b = $b->{$prop};

            if ($val_a == null && $val_b == null) {
                return $val_a <=> $val_b;
            }

            if ($val_a == null) {
                return 1;
            }

            if ($val_b == null) {
                return -1;
            }
        }

        return ($sort === SORT_ASC) ? ($a->{$prop} <=> $b->{$prop}) : ($b->{$prop} <=> $a->{$prop});
    }
}
