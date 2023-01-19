<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Description
 */
class CFileChecker implements IShortNameAutoloadable {
  protected $files_to_check;
  protected $files;

  public static $regenerables = [
    'Impression de la facture',
  ];

  /**
   * CFileChecker constructor.
   *
   * @param array $files_to_check List of file to check
   */
  public function __construct($files_to_check) {
    $this->files_to_check = $files_to_check;
  }

  public function check() {
    $this->loadFiles();

    return $this->checkFiles();
  }

  protected function loadFiles() {
    $file = new CFile();
    $ds   = $file->getDS();

    $where = array(
      "file_real_filename" => $ds->prepareIn(array_keys($this->files_to_check))
    );

    $this->files = $file->loadList($where);
  }

  protected function checkFiles() {
    $infos         = array();
    $files_details = array();

    /** @var CFile $_file */
    foreach ($this->files as $_file) {
      if (!file_exists($_file->_file_path)) {
        continue;
      }

      if (!array_key_exists($_file->object_class, $infos)) {
        $infos[$_file->object_class] = array(
          'ok'        => [], // FS = DB
          'nok'       => [], // FS != DB
          'empty_ok'  => [], // FS = DB = 0
          'empty_nok' => [], //DB != (FS = 0)
        );
      }

      $fs_size = filesize($_file->_file_path);

      if ($fs_size == 0 && $_file->doc_size != 0) {
        $infos[$_file->object_class]['empty_nok'][] = $_file->file_name;
      }
      elseif ($fs_size == 0 && $_file->doc_size == 0) {
        $infos[$_file->object_class]['empty_ok'][] = $_file->file_name;
      }
      elseif ($fs_size == $_file->doc_size) {
        $infos[$_file->object_class]['ok'][] = $_file->file_name;
      }
      else {
        $infos[$_file->object_class]['nok'][] = $_file->file_name;
      }

      $_log = $_file->loadLastLog();

      if (!array_key_exists($_file->file_real_filename, $files_details)) {
        $files_details[$_file->file_real_filename] = [
          'path'          => utf8_encode($_file->_file_path),
          'hash'          => $_file->file_real_filename,
          'nom'           => utf8_encode($_file->file_name),
          'context'       => utf8_encode($_file->object_class),
          'last_modif'    => $_log->date,
          'last_modif_fs' => date("Y-m-d H:i:s.", filemtime($_file->_file_path)),
          'regenerable'   => ($this->isRegenerable($_file)) ? 'oui' : 'non',
        ];
      }
    }

    if ($files_details) {
      file_put_contents(rtrim(CAppUI::conf('root_dir'), '/') . '/files/upload/files_types.json', json_encode($files_details));
    }

    return $infos;
  }

  protected function isRegenerable($file) {
    if ($file->object_class == 'CCompteRendu') {
      return true;
    }

    foreach (static::$regenerables as $_file_name) {
      if (stripos($file->file_name, $_file_name) === 0) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $filename       Name of the file to parse
   * @param array  $files_to_check Files to check
   *
   * @return void
   * @deprecated
   */
  static function parseFile($filename, &$files_to_check) {
    // Obsolete feature, to henceforward
    return;

    $fp  = fopen($filename, 'r');
    $csv = new CCSVFile($fp);
    $csv->setColumnNames(array('file_path', 'file_name', 'time'));
    $csv->readLine(); // Skip the titles

    while ($line = $csv->readLine(true, true)) {
      if (!file_exists($line['file_path'])) {
        continue;
      }

      $files_to_check[$line['file_name']] = $line['file_path'];
    }

    $csv->close();

    //unlink($filename);
  }


}