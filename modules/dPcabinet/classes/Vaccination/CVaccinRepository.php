<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Vaccination;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;

/**
 * Description
 */
class CVaccinRepository implements IShortNameAutoloadable {
  const VACCINES_FILE_PATH = "modules/dPcabinet/ressources/vaccines.json";

  /** @var Cache */
  private $vaccine_cache;

  /** @var Cache */
  private $vacine_cache_file;

  /** @var Cache */
  private $recall_cache;

  /**
   * CVaccinRepository constructor.
   *
   * @param Cache|null $vaccine_cache
   * @param Cache|null $recall_cache
   * @param Cache|null $vacine_cache_file
   */
  public function __construct(Cache $vaccine_cache = null, Cache $recall_cache = null, $vacine_cache_file = null) {
    $this->vaccine_cache      = ($vaccine_cache) ?: new Cache("vaccination", "vaccines", Cache::INNER_OUTER);
    $this->recall_cache       = ($recall_cache) ?: new Cache("vaccination", "recalls", Cache::INNER_OUTER);
    $this->vacine_cache_file  = ($vacine_cache_file) ?: new Cache("vaccination", "file", Cache::INNER_OUTER);
  }

  /**
   * @param string $type
   *
   * @return mixed|null
   */
  public function findByType($type) {
    $filtered = array_filter(
      $this->getAll(),
      function ($vaccin) use ($type) {
        return ($vaccin->type === $type);
      }
    );

    return (count($filtered) > 0) ? reset($filtered) : null;
  }

  /**
   * @return CVaccin[]
   * @throws Exception
   */
  public function getAll() {
    $json_vaccines = $this->loadVaccineJson();

    $vaccines = [];
    foreach ($json_vaccines["vaccines"] as $vaccine => $_details) {
      $recalls = [];
      foreach ($_details["recall"] as $_recall) {
        $age_max   = (isset($_recall["age_max"])) ? $_recall["age_max"] : null;
        $repeat    = (isset($_recall["repeat"])) ? $_recall["repeat"] : null;
        $colspan   = (isset($_recall["colspan"])) ? $_recall["colspan"] : 1;
        $empty     = (isset($_recall["empty"])) ? $_recall["empty"] : false;
        $mandatory = (isset($_recall["mandatory"])) ? $_recall["mandatory"] : false;
        $recalls[] = new CRecallVaccin($_recall["age"], $age_max, $repeat, $colspan, $empty, $mandatory);
      }


      $v_object = new CVaccin($vaccine, $_details["shortname"], $_details["longname"], $_details["color"], $recalls);

      $vaccines[] = $v_object;
    }

    return $vaccines;
  }

  /**
   * @return mixed
   * @throws Exception
   */
  protected function loadVaccineJson() {
    if ($this->vaccine_cache && $this->vaccine_cache->exists()) {
      return $this->vaccine_cache->get();
    }

    if (!file_exists(CAppUI::conf("root_dir") . "/" . self::VACCINES_FILE_PATH)) {
      throw new Exception("Vaccination ressource unavailable");
    }
    $json_file = file_get_contents(CAppUI::conf("root_dir") . "/" . self::VACCINES_FILE_PATH);

    return $this->vaccine_cache->put(array_map_recursive("utf8_decode", json_decode($json_file, true)));
  }

  /**
   * @return mixed
   * @throws Exception
   */
  public function loadVaccineJsonFile() {
    if ($this->vacine_cache_file && $this->vacine_cache_file->exists()) {
      return $this->vacine_cache_file->get();
    }

    if (!file_exists(CAppUI::conf("root_dir") . "/" . self::VACCINES_FILE_PATH)) {
      throw new Exception("Vaccination ressource unavailable");
    }

    $json_file = file_get_contents(CAppUI::conf("root_dir") . "/" . self::VACCINES_FILE_PATH);

    return $this->vacine_cache_file->put(array_map_recursive("utf8_decode", json_decode($json_file, true)));
  }

  /**
   * Get all available colors of the vaccines (from the json file)
   *
   * @return string[]
   * @throws Exception
   */
  public function getColorsPerType() {
    $colors = [];
    foreach ($this->getAll() as $vaccine) {
      $colors[$vaccine->type] = $vaccine->color;
    }

    return $colors;
  }

  /**
   * @return mixed
   * @throws Exception
   */
  public function getDates() {
    $json_vaccines = $this->loadVaccineJson();

    return $json_vaccines["columns"];
  }

  /**
   * @return CRecallVaccin[]
   * @throws Exception
   */
  public function getRecalls() {
    if ($this->recall_cache->exists()) {
      return $this->recall_cache->get();
    }

    $recalls = [];

    // Get all recalls
    foreach ($this->getAll() as $_vaccine) {
      $recalls = array_merge($recalls, $_vaccine->recall);
    }

    usort(
      $recalls,
      function ($r1, $r2) {
        return $r1 <=> $r2;
      }
    );

    return $this->recall_cache->put($recalls);
  }

  /**
   * @return array
   * @throws Exception
   */
  public function getAvailableTypesRecall() {
    $available = [];
    foreach ($this->getAll() as $_vaccine) {
      foreach ($_vaccine->recall as $_recall) {
        $available[$_recall->getRecallAge()][] = $_vaccine->type;
      }
    }

    return $available;
  }
}
