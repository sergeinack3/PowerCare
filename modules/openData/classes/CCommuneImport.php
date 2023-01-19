<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Files\CFile;
use ZipArchive;

/**
 * Description
 */
class CCommuneImport implements IShortNameAutoloadable {
  protected $file_url;
  protected $file_path;
  protected $zip_name;

  protected $_class;

  protected $map = array();
  protected $communes_types = array();

  public static $versions_france = array(
    '2013' => 'CCommuneFranceImportV2013',
    '2014' => 'CCommuneFranceImportV2014',
  );

  /**
   * CCommuneImport constructor.
   *
   * @param string $file_url URL of the file to download
   */
  function __construct($file_url) {
    $this->file_url = $file_url;
  }

  /**
   * Download a file
   *
   * @param bool $zip Use a zip and don't download the file
   *
   * @return void
   */
  function getFile($zip = false) {
    if (!$this->file_url) {
      CAppUI::stepAjax('CCommuneImport-file.none', UI_MSG_ERROR);
    }

    if (file_exists($this->file_path)) {
      return;
    }

    if ($zip) {
      $archive = new ZipArchive();
      $root_dir = rtrim(CAppUI::conf('root_dir'), '/');
      if ($archive->open($root_dir . '/modules/openData/resources/' . $this->zip_name) === true) {
        if ($archive->extractTo(rtrim(CFile::getDirectory(), '/\\') . '/upload/communes') === true) {
          return;
        }
      }

      CAppUI::stepAjax('CCommunesImport-file-zip-error', UI_MSG_ERROR);
    }

    $this->file_path = ($this->file_path) ?: tempnam(rtrim(CFile::getDirectory(), '/\\') . '/upload/communes', 'com_');

    $content = file_get_contents($this->file_url);
    if ($content) {
      file_put_contents($this->file_path, $content);
      CAppUI::stepAjax('CCommuneImport-file-downloaded', UI_MSG_OK);
      return;
    }

    CAppUI::stepAjax('CCommuneImport-file-download-failed', UI_MSG_ERROR);
  }

  /**
   * Delete a file after import
   *
   * @return void
   */
  function removeFile() {
    if (!$this->file_url) {
      CAppUI::stepAjax('CCommuneImport-file.none', UI_MSG_WARNING);
    }

    if (file_exists($this->file_path)) {
      if (unlink($this->file_path)) {
        CAppUI::stepAjax('CCommuneImport-file-deleted', UI_MSG_OK);
        return;
      }

      CAppUI::stepAjax('CCommuneImport-file-deleted-not', UI_MSG_WARNING);
    }

    CAppUI::stepAjax('CCommuneImport-file-exists-not', UI_MSG_WARNING);

  }

  /**
   * @param int  $start  Number of lines to skip
   * @param int  $step   Number of lines to parse, if 0 the whole file is imported
   * @param bool $update Update values of existing objects
   *
   * @return int
   */
  function importFile($start = 0, $step = 0, $update = false) {
    $fp = fopen($this->file_path, 'r');
    $csv = new CCSVFile($fp, CCSVFile::PROFILE_EXCEL);

    // Setting columns names
    $csv->column_names = array_map('utf8_decode', $csv->readLine());

    $num_line = $start;
    if ($start > 1) {
      $csv->jumpLine($start-1);
    }

    while ($_commune = $csv->readLine(true, true)) {
      if ($step > 0 && ($num_line-$start) >= $step) {
        break;
      }
      $num_line++;

      $_commune = $this->doMapping($_commune);
      $commune = $this->getCommuneFromFields($_commune);

      if ($commune && $commune->_id && !$update) {
        CAppUI::setMsg('CCommuneImport-commune-retrieved', UI_MSG_OK);
        continue;
      }

      $commune->bind($_commune, false);
      $commune = $this->sanitizeCommune($commune);
      $commune->loadMatchingObjectEsc();

      if ($commune->_id) {
        CAppUI::setMsg('CCommune-commune-retrieved', UI_MSG_OK);
        if (!$update) {
          continue;
        }
      }
      else {
        if ($msg = $commune->store()) {
          CAppUI::setMsg('CCommuneImport-error-store', UI_MSG_WARNING, $num_line, $msg);
          continue;
        }
        else {
          CAppUI::setMsg('CCommune-msg-create', UI_MSG_OK);
        }
      }

      if (!$commune || !$commune->_id) {
        CAppUI::setMsg('CCommuneImport-error', UI_MSG_WARNING, $num_line);
        continue;
      }

      $this->handleImportCp($_commune, $commune, $num_line, $update);
    }

    if (!$_commune) {
      CAppUI::setMsg('Importation terminée', UI_MSG_OK);
      return null;
    }

    CAppUI::setMsg('CCommuneImport-lines-imported', UI_MSG_OK, ($num_line-$start));

    $csv->close();
    return $num_line;
  }

  /**
   * @param array $_commune A Line of the communes file
   *
   * @return array
   */
  function doMapping($_commune) {
    if (!$_commune) {
      CAppUI::stepAjax('Importation terminée', UI_MSG_OK);
      CApp::rip();
    }

    foreach ($_commune as $_key => $_value) {
      if (isset($this->map[$_key]) && $_key !== $this->map[$_key]) {
        $_commune[$this->map[$_key]] = utf8_decode(trim($_value));
        unset($_commune[$_key]);
      }
      else {
        $_commune[$_key] = utf8_decode(trim($_value));
      }
    }

    return $_commune;
  }

  /**
   * @param CMbObject $commune Commune to sanitize
   *
   * @return mixed
   */
  function sanitizeCommune($commune) {
    return $commune;
  }

  /**
   * @param array     $line     Line to import
   * @param CMbObject $commune  Commune for the CP
   * @param int       $num_line Line number
   * @param bool      $update   Update existing CP
   *
   * @return void
   */
  function handleImportCp($line, $commune, $num_line, $update = false) {

  }

  /**
   * @param array $_commune A CSV line
   *
   * @return CMbObject
   */
  function getCommuneFromFields($_commune) {
    return null;
  }
}
