<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Files\CFile;

/**
 * Export Gesture's perop in XML
 */
class CGestePeropXMLExport implements IShortNameAutoloadable {
  public static $export_dir;

  public static $minimized_backrefs_tree = array(
    "CGestePerop"           => array(
      "files",
      "geste_perop_precisions"
    ),
    "CGestePeropPrecision"  => array(
      "precision_valeurs"
    ),
    "CAnesthPeropCategorie" => array(
      "files"
    )
  );

  public static $minimized_fwrefs_tree = array(
    "CGestePerop"           => array(
      "categorie_id"
    ),
    "CAnesthPeropCategorie" => array(
      "chapitre_id"
    ),
    "CAnesthPeropChapitre"  => array(),
    "CPrecisionValeur"      => array()
  );

  /**
   * Export in XML
   *
   * @param array $where Where clause
   *
   * @return void
   * @throws Exception
   */
  static function exportCallBack($object, $dir) {
    if ($object instanceof CFile) {
      $file_name = $object->file_real_filename;

      file_put_contents($dir . "/" . $file_name, @$object->getBinaryContent());
    }
  }

  /**
   * Export in XML
   *
   * @param array $where Where clause
   *
   * @return int
   * @throws Exception
   */
  static function export($where = array()) {
    self::$export_dir = rtrim(CAppUI::conf("root_dir")) . "/tmp/gestes_export";
    $counter = 0;

    $geste_perop  = new CGestePerop();
    $order        = "libelle ASC";
    $gestes_perop = $geste_perop->loadList($where, $order);

    foreach ($gestes_perop as $_geste) {
      try {
        $dir = self::$export_dir . "/{$_geste->_guid}";
        CMbPath::forceDir($dir);

        $export = new CMbObjectExport($_geste, self::$minimized_backrefs_tree);
        $export->setForwardRefsTree(self::$minimized_fwrefs_tree);

        // Define callback for each CPatient because the $dir change for each
        $callback = function (CStoredObject $object) use ($dir) {
          self::exportCallBack($object, $dir);
        };

        $export->setObjectCallback($callback);

        $xml = $export->toDOM()->saveXML();
        file_put_contents("$dir/export.xml", $xml);
        $counter++;
      }
      catch (Exception $e) {
        CApp::log($e->getMessage());
      }
    }

    $zip_path = self::$export_dir . ".zip";
    CMbPath::zip(self::$export_dir, $zip_path);

    header("Content-Type: application/zip;charset=".CApp::$encoding);
    header("Content-Disposition: attachment;filename=\"gestes_perop.zip\"");
    ob_end_clean();
    header("Content-Length: ".filesize($zip_path).";");
    echo file_get_contents($zip_path);

    if (is_dir(self::$export_dir) && file_exists($zip_path)) {
      CMbPath::remove(self::$export_dir);
      CMbPath::remove($zip_path);
    }

    return $counter;
  }
}
