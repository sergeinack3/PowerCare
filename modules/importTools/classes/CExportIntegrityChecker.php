<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CExportIntegrityChecker {
  protected $directory;
  protected $stats_file;
  protected $stats;

  protected static $xml_queries = array(
    "CSejour"       => "//object[@class='CSejour']",
    "CConsultation" => "//object[@class='CConsultation']",
    "COperation"    => "//object[@class='COperation']",
    "CFile"         => "//object[@class='CFile']",
    "CCompteRendu"  => "//object[@class='CCompteRendu']",
  );

  public function __construct($directory, $file_path) {
    if (!is_dir($directory)) {
      CAppUI::stepAjax("$directory does not exists", UI_MSG_ERROR);
    }

    if (!is_file($file_path)) {
      if (!touch($file_path)) {
        CAppUI::stepAjax("$file_path does not exists", UI_MSG_ERROR);
      }
    }

    $this->directory  = rtrim($directory, '/\\');
    $this->stats_file = $file_path;
    $file_stats       = file_get_contents($file_path);
    $stats            = json_decode($file_stats, true);
    if ($stats) {
      $this->stats = $stats;
    }
    else {
      $this->stats = array(
        "total"         => 0,
        "start"         => 0,
        "CGroups"       => CGroups::loadCurrent()->_id,
        "CSejour"       => array(),
        "CConsultation" => array(),
        "COperation"    => array(),
        "CFile"         => array(),
        "CCompteRendu"  => array(),
      );
    }
  }

  public function checkExport($start = null, $step = 1) {
    if (!$start && isset($this->stats['start'])) {
      $start = $this->stats['start'];
    }

    $cache = new Cache("CExportIntegrityChecker", $this->directory, Cache::INNER_OUTER);
    if ($cache->exists()) {
      $all_pats = $cache->get();
    }
    else {
      $all_pats = glob("{$this->directory}/*/CPatient-*");
      $cache->put($all_pats);
    }

    if (!isset($this->stats['total']) || $this->stats['total'] == 0) {
      $this->getTotalPatients($all_pats);
    }

    $limit = $start + $step;

    for ($i = $start; $i < $limit; $i++) {
      if (isset($all_pats[$i])) {
        $this->checkFile($all_pats[$i]);
      }
    }

    $this->updateStats($limit);
  }

  protected function checkFile($dir_path) {
    $xml_file = "{$dir_path}/export.xml";
    if (!is_file($xml_file)) {
      CAppUI::stepAjax("{$xml_file} does not exists", UI_MSG_ERROR);
    }

    $xpath = $this->initXPath($xml_file);

    foreach (self::$xml_queries as $_class => $_query) {
      $this->countObjects($xpath, $_query);
    }
  }

  /**
   * @param DOMXPath $xpath
   * @param          $query
   *
   * @return void
   */
  protected function countObjects($xpath, $query) {
    $results = $xpath->query($query);
    /** @var DOMElement $_result */
    foreach ($results as $_result) {
      $guid = $_result->getAttribute('id');
      list($_class, $_id) = explode('-', $guid);

      if ($_id && !isset($this->stats[$_class][$_id])) {
        $this->stats[$_class][$_id] = '';
      }
    }
  }

  protected function initXPath($xml_file) {
    $dom = new DOMDocument();

    $xml = file_get_contents($xml_file);

    // Suppression des caractères invalides pour DOMDocument
    $xml = CMbString::convertHTMLToXMLEntities($xml);

    $dom->loadXML($xml);

    return new DOMXPath($dom);
  }

  protected function updateStats($new_start) {
    $this->stats['start'] = ($new_start >= $this->stats['total']) ? $this->stats['total'] : $new_start;
    $json_stats           = json_encode($this->stats);
    file_put_contents($this->stats_file, $json_stats);
  }

  protected function getTotalPatients($all_pats) {
    $this->stats['total'] = count($all_pats);
  }

  public function getStats() {
    return $this->stats;
  }
}
