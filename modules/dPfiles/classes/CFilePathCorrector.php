<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CSQLDataSource;

/**
 * Class CFileCorrector
 */
class CFilePathCorrector implements IShortNameAutoloadable {
  private $old_path;
  private $old_size = [2, 2, 2];
  private $date_min;
  private $date_max;
  private $start;
  private $step;

  private $files_ok = [];
  private $files_ok_old = [];
  private $files_nok = [];

  /**
   * CFileCorrector constructor.
   *
   * @param string $old_path
   * @param array  $old_size
   * @param string $date_min
   * @param string $date_max
   * @param int    $start
   * @param int    $step
   */
  public function __construct($old_path = null, $old_size = [2, 2, 2], $date_min = null, $date_max = null, $start = 0, $step = 100) {
    $this->old_path = $old_path;
    $this->old_size = $old_size;
    $this->date_min = $date_min;
    $this->date_max = $date_max;
    $this->start    = $start;
    $this->step     = $step;
  }

  /**
   * @return int
   * @throws Exception
   */
  public function countFiles() {
    $file = new CFile();
    $ds   = $file->getDS();

    return $file->countList($this->getWhere($ds));
  }

  /**
   * @param bool $count_only
   * @param bool $copy_files
   *
   * @return array
   * @throws Exception
   */
  public function correctFiles($count_only, $copy_files = true) {
    $file = new CFile();
    $ds   = $file->getDS();

    // Load all files in BDD for the period required
    // Put all the files in $this->files_nok
    $this->files_nok = $file->loadList($this->getWhere($ds), 'file_id', "{$this->start},{$this->step}");

    if (!$this->files_nok) {
      return null;
    }

    // Check all the files that are on the FS using the actual config
    // Remove those files from $this->files_nok and put them in $this->files_ok
    $this->checkFilesConfActual();

    // Check all the remaining files in $this->files_nok and try to find them on the FS using the old conf.
    // Put all found files in $this->ok_old
    $this->checkFilesOldConf($count_only, $copy_files);

    return [
      'ok'     => count($this->files_ok),
      'ok_old' => count($this->files_ok_old),
      'nok'    => count($this->files_nok),
    ];
  }

  /**
   * @param CSQLDataSource $ds
   *
   * @return array
   */
  protected function getWhere($ds) {
    $where = [];
    if ($this->date_min && $this->date_max) {
      $where = ['file_date' => $ds->prepare('BETWEEN ?1 AND ?2', $this->date_min, $this->date_max)];
    }
    elseif ($this->date_min) {
      $where = ['file_date' => $ds->prepare('> ?', $this->date_min)];
    }
    elseif ($this->date_max) {
      $where = ['file_date' => $ds->prepare('< ?', $this->date_max)];
    }
    else {
      CAppUI::commonError('Bornes de dates obligatoires');
    }

    return $where;
  }

  /**
   * @return void
   */
  protected function checkFilesConfActual() {
    /** @var CFile $_file */
    foreach ($this->files_nok as $_file) {
      if (file_exists($_file->_file_path)) {
        $this->files_ok[$_file->_id] = $_file;
      }
    }

    $this->files_nok = array_diff_key($this->files_nok, $this->files_ok);
  }

  /**
   * @param bool $count_only
   * @param bool $copy_files
   *
   * @return void
   */
  protected function checkFilesOldConf($count_only, $copy_files = true) {
    // Use the inputed old_path for private directory
    $root_dir = ($this->old_path) ? rtrim($this->old_path, '/') : rtrim(CFile::getPrivateDirectory(), '/');

    $old_ids = [];

    // For each file in $this->files_nok build their path using the old inputed conf
    // If the file existe and the FS put it on $this->files_old_ok
    // If $count_only is false move the file using the actual conf to store it in the right place
    /** @var CFile $_file */
    foreach ($this->files_nok as $_file) {
      $sub_dir = substr($_file->file_real_filename, 0, $this->old_size[0])
        . "/" . substr($_file->file_real_filename, $this->old_size[0], $this->old_size[1])
        . "/" . substr($_file->file_real_filename, $this->old_size[0] + $this->old_size[1], $this->old_size[2]);

      // Old path using inputed path and old directories sizes
      $old_path = $root_dir . '/' . $sub_dir . '/' . $_file->file_real_filename;

      if (file_exists($old_path)) {
        // Keep the ID because we have to remove it temporarly to get the new path to store the file
        $old_ids[] = $old_id = $_file->_id;
        // Keep the realname to rollback changes if we can't move the file
        $old_file_name = $_file->file_real_filename;

        if (!$count_only) {
          // Remove ID and hash to allow new calculation of the real_filename
          $_file->_id                = null;
          $_file->file_real_filename = null;
          $_file->fillFields();
          // Put back the ID
          $_file->_id = $old_id;

          // Move the file to its new location
          // Allow only copying the file instead of moving it
          if ($copy_files) {
            $_file->setCopyFrom($old_path);
          }
          else {
            $_file->setMoveFrom($old_path);
          }

          // Store file with the new file_real_filename
          if ($msg = $_file->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);

            // In case the file cannot be move revert the changes
            $_file->file_real_filename = $old_file_name;
            if ($msg = $_file->store()) {
              CAppUI::setMsg($msg, UI_MSG_WARNING);
            }
          }
          else {
              $this->files_ok[$_file->_id] = $_file;
          }
        }
      }
      else {
        $this->files_ok_old[$_file->_id] = $_file;
      }
    }

    $this->files_nok = array_diff_key($this->files_nok, $this->files_ok_old, $this->files_ok);
  }
}