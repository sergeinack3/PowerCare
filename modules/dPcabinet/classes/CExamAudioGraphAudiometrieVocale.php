<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\CFlotrGraph;

/**
 * Description
 */
class CExamAudioGraphAudiometrieVocale extends CExamAudioGraph {
  public $type = "audiometrie_vocale";

  static $optimal = array(
    array(0, 0),
    array(2, 3),
    array(3, 4),
    array(5, 10),
    array(10, 50),
    array(13, 75),
    array(15, 92),
    array(17, 97),
    array(19, 99),
    array(22, 100),
  );

  /**
   * Build graph data
   *
   * @return void
   */
  public function make() {
    $exam = $this->exam_audio;

    $options = CFlotrGraph::merge(
      "lines", self::$default_options
    );

    $options = CFlotrGraph::merge($options, array(
      'xaxis' => array(
        'min'      => 0,
        'max'      => 120,
        'tickSize' => 10,
      ),
      'yaxis' => array(
        'min'      => 0,
        'max'      => 100,
        'tickSize' => 10,
      ),
    ));

    $this->options = $options;

    $series = array();

    // Courbe optimale
    $serie = array(
      "label"     => "Courbe optimale",
      "color"     => "#999",
      "data"      => self::$optimal,
      'clickable' => false,
      "points"    => array(
        "show" => false,
      ),
      "lines"     => array(
        "show"      => true,
        "lineWidth" => 2,
      ),
    );
    $series[] = $serie;

    // Second axe x
    $serie = array(
      "type"      => "axis",
      "color"     => "#00",
      'clickable' => false,
      "data"      => array(
        array(10, 50),
        array(20, 50),
        array(30, 50),
        array(40, 50),
        array(50, 50),
        array(60, 50),
        array(70, 50),
        array(80, 50),
        array(90, 50),
        array(100, 50),
        array(110, 50),
      ),
      "points"    => array(
        "show" => false,
      ),
      "lines"     => array(
        "show" => false,
      ),
    );
    $series[] = $serie;

    foreach (self::$sides as $_side => $_options) {
      $_serie = array_merge(
        $_options, array(
          "label" => CAppUI::tr("CExamAudio-$this->type-$_side"),
          "data"  => array(),
          "side"  => $_side,
        )
      );

      foreach ($exam->{"_{$_side}_vocale"} as $_i => $_v) {
        if ($_v[1] === "") {
          continue;
        }

        $_serie["data"][] = $_v;
      }

      $series[] = $_serie;
    }

    $this->series = $series;
  }
}
