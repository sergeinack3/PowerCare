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
class CExamAudioGraphTympanometrie extends CExamAudioGraph {
  public $type = "tympanometrie";

  public $side;

  /**
   * Build graph data
   *
   * @param string $side Side
   *
   * @return void
   */
  public function make($side) {
    $this->side = $side;
    $exam = $this->exam_audio;

    $ticks_pression = array();
    foreach (CExamAudio::$pressions as $_i => $_pression) {
      $ticks_pression[] = array($_i, "$_pression");
    }

    $options = CFlotrGraph::merge(
      "lines", self::$default_options
    );

    $options = CFlotrGraph::merge($options, array(
      'title' => CAppUI::tr("CExamAudio-$this->type-$this->side"),
      'xaxis' => array(
        'min'          => -0.5,
        'max'          => count($ticks_pression) - 0.5,
        'ticks'        => $ticks_pression,
        'reserveSpace' => true,
        'label'        => "Pression en mm H&#8322;O",
        'labelHeight'  => 24,
      ),
      'yaxis' => array(
        'min'          => 0,
        'max'          => 15,
        'tickSize'     => 5,
        'reserveSpace' => true,
        'label'        => "Admittance x10 en ml",
        'labelWidth'   => 24,
      ),
    ));

    $this->options = $options;

    $series = array();

    $serie = self::$sides[$this->side];

    foreach ($exam->{"_{$this->side}_tympan"} as $_i => $_v) {
      if ($_v === "") {
        continue;
      }

      $serie["data"][] = array($_i, +$_v);
    }

    $series[] = $serie;

    $this->series = $series;
  }
}
