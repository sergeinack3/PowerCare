<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Libraries;

/**
 * Library patch. Modification of the original library
 */
class CLibraryPatch {
  public $dirName    = "";
  public $subDirName = "";
  public $sourceName = "";
  public $targetDir  = "";

  function getRootPath() {
    return __DIR__ . "/../../../";
  }

  /**
   * Apply the patch to the library
   *
   * @return bool
   */
  function apply() {
    $mbpath = $this->getRootPath();

    $pkgsDir = $mbpath."libpkg";
    $libsDir = $mbpath."lib";
    $patchDir = "$pkgsDir/patches";
    $sourcePath = "$patchDir/$this->dirName/";
    if ($this->subDirName) {
      $sourcePath .= "$this->subDirName/";
    }
    $sourcePath .= "$this->sourceName";
    $targetPath = "$libsDir/$this->dirName/$this->targetDir/$this->sourceName";
    $oldPath = $targetPath . ".old";
    
    @unlink($oldPath);
    @rename($targetPath, $oldPath);
    return copy($sourcePath, $targetPath);
  }
}
