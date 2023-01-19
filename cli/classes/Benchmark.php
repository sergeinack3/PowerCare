<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Benchmark
 */
class Benchmark
{
    /**
     * @param int $count
     *
     * @return void
     */
    private static function testProcessorMath(int $count = 140000): void
    {
        $mathFunctions = [
            "abs",
            "acos",
            "asin",
            "atan",
            "floor",
            "exp",
            "sin",
            "tan",
            "is_finite",
            "is_nan",
            "sqrt",
        ];
        foreach ($mathFunctions as $key => $function) {
            if (!function_exists($function)) {
                unset($mathFunctions[$key]);
            }
        }
        for ($i = 0; $i < $count; $i++) {
            foreach ($mathFunctions as $function) {
                $r = call_user_func_array($function, [$i]);
            }
        }
    }

    /**
     * @param int $count
     *
     * @return void
     */
    private static function testProcessorStringManipulation(int $count = 130000): void
    {
        $stringFunctions = [
            "addslashes",
            "chunk_split",
            "metaphone",
            "strip_tags",
            "md5",
            "sha1",
            "strtoupper",
            "strtolower",
            "strrev",
            "strlen",
            "soundex",
            "ord",
        ];
        foreach ($stringFunctions as $key => $function) {
            if (!function_exists($function)) {
                unset($stringFunctions[$key]);
            }
        }
        $string = "the quick brown fox jumps over the lazy dog";
        for ($i = 0; $i < $count; $i++) {
            foreach ($stringFunctions as $function) {
                $r = call_user_func_array($function, [$string]);
            }
        }
    }

    /**
     * @param int $count
     *
     * @return void
     */
    private static function testProcessorLoops(int $count = 19000000): void
    {
        for ($i = 0; $i < $count; ++$i) {
            ;
        }
        $i = 0;
        while ($i < $count) {
            ++$i;
        }
    }

    /**
     * @param int $count
     *
     * @return void Execution time in seconds
     */
    private static function testProcessorIfElse(int $count = 9000000): void
    {
        for ($i = 0; $i < $count; $i++) {
            if ($i == -1) {
            } elseif ($i == -2) {
            } else {
                if ($i == -3) {
                }
            }
        }
    }

    /**
     * @return float Execution time in seconds
     */
    private static function testFilesystem(int $count = 10000): void
    {
        $directory = 'tmp/benchmark';
        $fs = new Filesystem();
        $fs->mkdir($directory);
        for ($i = 0; $i < $count; $i++) {
            $filename = $directory . '/file' . $i . '.txt';
            $fs->touch($filename);
            $fs->appendToFile($filename, 'Delete Me');
            $fs->remove($filename);
        }
        $fs->remove($directory);
    }

    /**
     * @param string $pattern Regex to match function names that will be executed
     *
     * @return float Execution time in seconds
     */
    public static function run(string $pattern = 'test'): float
    {
        $total   = 0;
        $methods = get_class_methods(self::class);
        foreach ($methods as $method) {
            if (preg_match('/^' . $pattern . '/', $method)) {
                $time = microtime(true);
                self::$method();
                $duration = floatval(number_format(microtime(true) - $time, 3));
                $total += $duration;
            }
        }
        return $total;
    }
}
