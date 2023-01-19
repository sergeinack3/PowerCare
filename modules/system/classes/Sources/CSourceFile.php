<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Sources;

use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Mediboard\System\CExchangeSourceAdvanced;

abstract class CSourceFile extends CExchangeSourceAdvanced
{
    /** @var string Legacy form_field for change directory */
    public $_destination_file;

    /** @var string Legacy form_field */
    public $_path;


    /**
     * @param $current_directory
     *
     * @return array
     */
    public function getRootDirectory($current_directory): array
    {
        $tabRoot = explode("/", $current_directory);
        array_pop($tabRoot);
        $tabRoot[0] = "/";
        $root       = [];
        $i          = 0;
        foreach ($tabRoot as $_tabRoot) {
            if ($i === 0) {
                $path = "/";
            } else {
                $path = $root[count($root) - 1]["path"] . "$_tabRoot/";
            }
            $root[] = [
                "name" => $_tabRoot,
                "path" => $path,
            ];
            $i++;
        }

        return $root;
    }

    /**
     * Generate file name
     *
     * @return string Filename
     */
    static function generateFileName()
    {
        return str_replace(array(" ", ":", "-"), array("_", "", ""), CMbDT::dateTime());
    }

    public static function timestampFileName(string $filename): string
    {
        $extensions_filename = "";
        while (($extension = CMbPath::getExtension($filename))) {
            $extensions_filename = ".$extension{$extensions_filename}";
            $filename = CMbPath::getFilename($filename);
        }

        return $filename . ($filename ? '_' : '') . self::generateFileName() . $extensions_filename;
    }
}
