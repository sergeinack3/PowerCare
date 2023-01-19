<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

/**
 * Delphi Form object (in DFM files)
 */
class CDFMObject {
  public $id;
  public $classname;
  public $csscolor;
  public $picture_data;
  public $valeur;
  public $color;

  public $zeros = 0;
  public $ischild  = false;
  public $children = array();
  public $parent;

  /**
   * Costruct an object from it's ID and classname
   *
   * @param string $classname Class name
   * @param string $id        ID
   */
  function __construct($classname, $id) {
    $this->classname = $classname;
    $this->id        = $id;
  }

  /**
   * Build the image data URI
   *
   * @return null|string
   */
  function getImageDataUri() {
    if (empty($this->picture_data)) {
      return null;
    }

    $type = $this->picture_data["type"];
    $data = $this->picture_data["data"];

    switch ($type) {
      case "TJPEGImage":
        return "data:image/jpeg;base64,".base64_encode($data);

      case "TPNGImage":
        return "data:image/png;base64,".base64_encode($data);
    }

    return null;
  }
}
