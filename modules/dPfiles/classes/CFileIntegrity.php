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
use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

/**
 * Description
 */
class CFileIntegrity implements IShortNameAutoloadable {
  const PREFIX = "check_files";
  const KEY = "integrity";

  const INIT = 'init';
  const DB_CHECK = 'db_check';
  const FS_CHECK = 'fs_check';
  const FINISHED = 'finished';

  /** @var Cache */
  protected $cache;
  /** @var CSQLDataSource */
  protected $ds;
  protected $params;
  protected $files;
  protected $files_entries;

  protected $date_min;
  protected $date_max;

  protected $fs_date_min;
  protected $fs_date_max;

  /**
   * CFileIntegrity constructor.
   *
   * @param int $limit Number of files to check
   */
  public function __construct($limit = null) {
    $this->ds = CSQLDataSource::get("std");
    $this->init($limit);
  }

  /**
   * Vérifie l'intégrité des fichiers. La DSHM est utilisée pour l'avancement de la vérification
   * Si la vérification n'a pas commencée on l'initalise.
   * Si la vérification a commencée on vérifie depuis les fichiers en BDD leur existance sur le FS
   * Si la vérification depuis la BDD est terminée on vérifie les fichiers en BDD depuis les fichiers sur le FS
   *
   * @param int    $step          Count of files to load
   * @param string $date_min      Date min to check
   * @param string $date_max      Date max to check
   * @param string $fs_date_start Date min to check for fs table
   * @param string $fs_date_end   Date max to check for fs table
   *
   * @return void
   * @throws Exception
   */
  public function check($step = 100, $date_min = null, $date_max = null, $fs_date_start = null, $fs_date_end = null) {
    $this->date_min = ($date_min) ?: null;
    $this->date_max = ($date_max) ?: null;
    $this->fs_date_min = ($fs_date_start) ?: null;
    $this->fs_date_max = ($fs_date_end) ?: null;

    switch ($this->params['status']) {
      default:
      case static::INIT:
        CView::enforceSlave();
        $this->initCheck();
        CView::disableSlave();
        break;
      case static::DB_CHECK:
        $this->dbCheck($step);
        break;
      case static::FS_CHECK:
        $this->fsCheck($step);
        break;
      case static::FINISHED:
        return;
    }

    $this->cache->put($this->params);
  }

  /**
   * Initialise le vérificateur d'intégrité des fichiers
   *
   * @param int $limit Number of files to check
   *
   * @return void
   */
  protected function init($limit = null) {
    $this->cache = static::getCache();

    $this->params = ($this->cache->get()) ?: [
      'status'             => static::INIT,
      'offset'             => 0,
      'offset_db_check'    => 0,
      'offset_fs_check'    => 0,
      'limit'              => 100,
      'last_file_date'     => CMbDT::dateTime(),
      'file_entries_count' => 0,
      'db_entries_count'   => 0,
    ];

    if ($limit) {
      $this->params['limit'] = $limit;
    }
  }

  /**
   * Phase d'initialisation, on ajoute le nombre de fichiers présents eb BDD et sur le FS à la DSHM
   *
   * @return void
   * @throws Exception
   */
  protected function initCheck() {
    // Execute db request with cli because of PDO MySQL bug
    // https://bugs.php.net/bug.php?id=62889

    // Mise à jour du nombre d'entrées
    $query = new CRequest();
    $query->addTable('file_entries');
    $query->addWhere(['file_path' => 'NOT ' . $this->ds->prepareLike('%.trash')]);
    $this->params['file_entries_count'] = $this->ds->loadResult($query->makeSelectCount());

    $query = new CRequest();
    $query->addTable('files_mediboard');
    $query->addWhere(['file_date' => $this->ds->prepare('< ?', $this->params["last_file_date"])]);

    $query = $this->addDateConstraint($query);

    $this->params['db_entries_count']   = $this->ds->loadResult($query->makeSelectCount());

    $this->params['status'] = static::DB_CHECK;
  }

  /**
   * @param CRequest $query    Query to add date constraints to
   * @param string   $field    Field to add constraint to
   * @param string   $date_min name of date_min field to check
   * @param string   $date_max name of date_max field to check
   *
   * @return CRequest
   */
  protected function addDateConstraint($query, $field = 'file_date', $date_min = 'date_min', $date_max = 'date_max') {
    if ($this->{$date_max} && $this->{$date_min}) {
      $query->addWhere([$field => $this->ds->prepare('BETWEEN ?1 AND ?2', $this->{$date_min}, $this->{$date_max})]);
    }
    elseif ($this->{$date_max}) {
      $query->addWhere([$field => $this->ds->prepare('< ?', $this->{$date_max})]);
    }
    elseif ($this->{$date_min}) {
      $query->addWhere([$field => $this->ds->prepare('> ?', $this->{$date_min})]);
    }

    return $query;
  }

  /**
   * Vérifie l'intégrité des fichiers dans le sens DB -> FS
   *
   * @param int $step Nombre de fichiers à charger
   *
   * @return void
   * @throws Exception
   */
  protected function dbCheck($step) {
    $start      = 0;
    $file_count = 0;

    while ($start < $this->params['limit']) {
      CView::enforceSlave();
      $this->files = $this->getDbFilesToCheck();

      $this->setFilesPaths();

      $this->files_entries = $this->getEntriesFromFiles();
      CView::disableSlave();

      $file_count = $this->checkFiles($file_count);
      $start      += $step;
    }

    if ($file_count == 0) {
      $this->params['status'] = static::FS_CHECK;
    }
  }

  /**
   * Vérifie l'intégrité des fichiers dans le sens FS -> DB
   *
   * @param int $step Nombre de fichiers à charger
   *
   * @return void
   * @throws Exception
   */
  protected function fsCheck($step) {
    $start      = 0;
    $file_count = 0;

    while ($start < $this->params['limit']) {
      CView::enforceSlave();
      $this->files_entries = $this->getEntriesToCheck($step);

      $this->files = $this->getFilesFromEntries();
      CView::disableSlave();

      $file_count = $this->checkFsFiles($file_count);
      $start += $step;
    }
    if ($file_count == 0) {
      $this->params['status'] = static::FINISHED;
    }
  }

  /**
   * @param int $file_count File count to increment
   *
   * @return int
   * @throws Exception
   */
  protected function checkFsFiles($file_count) {
    foreach ($this->files_entries as $_file_entry) {
      $has_error  = false;
      $file_count++;

      $file_report            = new CFileReport();
      $file_report->file_hash = basename($_file_entry['file_path']);
      $file_report->file_path = $_file_entry['file_path'];
      $file_report->file_size = $_file_entry['file_size'];

      if (!array_key_exists($_file_entry['file_real_filename'], $this->files)) {
        $file_report->db_unfound = 1;
        $has_error               = true;
      }

      if ($has_error) {
        $file_report->rawStore();
      }

      $this->params['offset_fs_check'] = $_file_entry['file_entries_id'];
      $this->params['offset']++;
    }

    return $file_count;
  }

  /**
   * Récupère les fichiers à vérifier en BDD
   *
   * @param int $step Nombre de fichiers à récupérer
   *
   * @return array
   * @throws Exception
   */
  protected function getDbFilesToCheck($step = 100) {
    $query = new CRequest();
    $query->addSelect(['file_id', 'file_real_filename', 'object_class', 'object_id', 'doc_size', 'file_date']);
    $query->addTable('files_mediboard');
    $query->addWhere(
      [
        'file_id'   => $this->ds->prepare('> ?', $this->params['offset_db_check']),
        'file_date' => $this->ds->prepare('<= ?', $this->params['last_file_date']),
      ]
    );

    $query = $this->addDateConstraint($query);

    $query->addOrder('file_id');
    $query->setLimit($step);

    return $this->ds->loadList($query->makeSelect());
  }

  /**
   * Associe son chemin à chaque fichiers chargé depuis la BDD
   *
   * @return void
   */
  protected function setFilesPaths() {
    foreach ($this->files as $key => $_file) {
      $file_path = CFile::getPrivateDirectory() . CFile::getSubDir($_file['file_real_filename']) . '/' . $_file['file_real_filename'];

      $this->files[$key]['file_path'] = $file_path;
    }
  }

  /**
   * Récupère les fichiers du FS correspondants aux chemins des fichiers en BDD
   *
   * @return array
   * @throws Exception
   */
  protected function getEntriesFromFiles() {
    $query = new CRequest();
    $query->addSelect(['file_path', 'file_entries_id', 'create_date', 'file_size']);
    $query->addTable('file_entries');
    $query->addWhere(['file_path' => $this->ds->prepareIn(CMbArray::pluck($this->files, 'file_path'))]);

    return $this->ds->loadHashAssoc($query->makeSelect());
  }

  /**
   * Vérifie les fichiers et pour chacun ajoute une entrée dans la table file_report
   *
   * @param int $file_count Nombre de fichiers à vérifier
   *
   * @return int
   * @throws Exception
   */
  protected function checkFiles($file_count = 0) {
    foreach ($this->files as $_file) {
      $file_count++;
      $has_error = false;

      $file_report               = new CFileReport();
      $file_report->file_path    = $_file['file_path'];
      $file_report->file_hash    = $_file['file_real_filename'];
      $file_report->object_class = $_file['object_class'];
      $file_report->object_id    = $_file['object_id'];

      if (!array_key_exists($_file['file_path'], $this->files_entries)) {
        $has_error                 = true;
        $file_report->file_unfound = 1;
      }
      else {
        $_file_entry = $this->files_entries[$_file['file_path']];

        if (CMbDT::date($_file_entry["create_date"]) != CMbDT::date($_file['file_date'])) {
          $has_error                  = true;
          $file_report->date_mismatch = 1;
        }

        if ($_file['doc_size'] == 0) {
          $has_error               = true;
          $file_report->empty_file = 1;
        }

        if ($_file_entry["file_size"] != $_file['doc_size']) {
          $has_error                  = true;
          $file_report->size_mismatch = 1;
          $file_report->file_size     = $_file_entry['file_size'];
        }
      }

      if ($has_error) {
        $file_report->rawStore();
      }

      $this->params['offset_db_check'] = $_file['file_id'];
      $this->params['offset']++;
    }

    return $file_count;
  }

  /**
   * Récupère les file_entries à vérifier
   *
   * @param int $step Nombre de fichiers à récupérer
   *
   * @return array
   * @throws Exception
   */
  protected function getEntriesToCheck($step = 100) {
    $query = new CRequest();
    $query->addSelect(
      ['file_entries_id', 'create_date', 'file_path', 'file_size', "SUBSTRING_INDEX(file_path, '/', -1) AS file_real_filename"]
    );
    $query->addTable('file_entries');
    $query->addWhere(
      [
        'file_entries_id' => $this->ds->prepare('> ?', $this->params['offset_fs_check']),
        'file_path' => 'NOT ' . $this->ds->prepareLike('%.trash'),
      ]
    );

    $query = $this->addDateConstraint($query, 'create_date', 'fs_date_min', 'fs_date_max');

    $query->setLimit($step);

    return $this->ds->loadList($query->makeSelect());
  }

  /**
   * Récupère les fichiers en BDD qui correspondent à deux chargés dans les file_entries
   *
   * @return array
   * @throws Exception
   */
  protected function getFilesFromEntries() {
    $query = new CRequest();
    $query->addSelect(['file_real_filename', 'doc_size']);
    $query->addTable('files_mediboard');
    $query->addWhere(['file_real_filename' => $this->ds->prepareIn(CMbArray::pluck($this->files_entries, 'file_real_filename'))]);

    return $this->ds->loadHashAssoc($query->makeSelect());
  }

  /**
   * Get CFileIntegrity Cache
   *
   * @return Cache
   */
  public static function getCache() {
    return new Cache(static::PREFIX, static::KEY, Cache::DISTR);
  }

  /**
   * @return string
   */
  public function getStatus() {
    return $this->params['status'];
  }

  /**
   * @return array
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * @return void
   */
  public function resetCheck() {
    $this->cache->rem();
  }

  /**
   * @param array $params Params to save
   *
   * @return void
   */
  public function setCache($params) {
    $this->cache->put($params);
  }
}
