<?php
/**
 * @package Mediboard\Core\ResourceLoaders
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\ResourceLoaders;

use Ox\Core\CAppUI;

/**
 * Favicon loader utility class
 */
abstract class CFaviconLoader extends CHTMLResourceLoader {

  /**
   * Links a shortcut icon (aka "favicon")
   * Only to be called while in the HTML header
   *
   * @param string $file The path to the favicon file
   * @param string $type The favicon mime type
   *
   * @return string An HTML tag to load the favicon
   */
  static function loadFile($file, $type = "image/ico") {
    return self::getTag(
      "link", array(
        "type" => $type,
        "rel"  => "shortcut icon",
        "href" => "$file?".self::getBuild(),
      )
    );
  }
}
