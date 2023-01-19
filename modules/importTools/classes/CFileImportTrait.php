<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Import\ImportHM\CImportHmObject;

/**
 * Description
 */
trait CFileImportTrait {
  abstract public static function getConfName();

  abstract public static function getModuleName();

  /**
   * @return array
   * @throws Exception
   */
  public static function getImportInfos(): array {
    $class_map     = CClassMap::getInstance();
    $child_classes = $class_map->getClassChildren(static::class);

    $infos = [];

    /** @var string $_child */
    foreach ($child_classes as $_child) {
      $infos[$_child] = [
        'short_name'  => $class_map->getShortName($_child),
        'file_exists' => $_child::isFileReadable(),
        'start'       => $_child::getStartFromCache(),
        'file_size'   => number_format($_child::getFileSize(), 0, ',', ' '),
      ];
    }

    return $infos;
  }

  protected static function isFileReadable() {
    if ($conf_name = static::getConfName()) {
      $module = static::getModuleName();
      $file_path = CAppUI::conf($module . ' ' . $conf_name);

      return file_exists($file_path) && is_readable($file_path);
    }

    return false;
  }

  protected static function getStartFromCache() {
    $cache = new Cache(CClassMap::getSN(static::class), 'start_pos', Cache::INNER_OUTER | Cache::DISTR);

    return $cache->exists() ? $cache->get() : 0;
  }

  protected static function getFileSize() {
    $conf_name = static::getConfName();
    if ($conf_name) {
      $module = static::getModuleName();
      $file_path = CAppUI::conf($module . ' ' . $conf_name);

      return filesize($file_path);
    }

    return 0;
  }

  /**
   * @param int $start
   *
   * @return void
   */
  protected function setStartInCache(int $start): void {
    $cache = new Cache(CClassMap::getSN(static::class), 'start_pos', Cache::INNER_OUTER | Cache::DISTR);
    $cache->put($start);
  }
}
