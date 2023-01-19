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
class CExamAudioGraphAudiometrieTonale extends CExamAudioGraph {
  public $type = "audiometrie_tonale";

  public $side;

  static $types = array(
    "aerien" => array("color" => "#3876c2", "points" => array("symbol" => "circle", "fill" => false)),
    "osseux" => array("color" => "#dc3535", "points" => array("symbol" => "cross", "fill" => false)),
    "conlat" => array("color" => "grey", "points" => array("symbol" => "CL", "fill" => false), "lines" => array("show" => false)),
    "ipslat" => array("color" => "#907656", "points" => array("symbol" => "IL", "fill" => false), "lines" => array("show" => false)),
    "pasrep" => array("color" => "#0f940f", "points" => array("symbol" => "triangle", "fill" => false), "lines" => array("show" => false)),
    "osseux_pasrep" => array("color" => "#dc3535", "points" => array("symbol" => "triangle", "fill" => false), "lines" => array("show" => false)),
    "aerien_pasrep" => array("color" => "#3876c2", "points" => array("symbol" => "triangle", "fill" => false), "lines" => array("show" => false)),
  );

  /**
   * Build graph data
   *
   * @param string $side Side
   *
   * @return void
   */
  public function make($side, $old_exam = false) {
    $this->side = $side;
    $exam = $this->exam_audio;
    $ticks_frequence = array();
    foreach (CExamAudio::$frequences as $_i => $_frequence) {
      $ticks_frequence[] = array($_i, $_frequence);
    }

    $options = CFlotrGraph::merge(
      "lines", self::$default_options
    );

    $options = CFlotrGraph::merge($options, array(
      'title' => CAppUI::tr("CExamAudio-$this->type-$this->side"),
      'xaxis' => array(
        'min'      => -0.5,
        'max'      => count($ticks_frequence) - 0.5,
        'ticks'    => $ticks_frequence,
        'position' => "top",
      ),
      'yaxis' => array(
        'max'             => 0,
        'min'             => -120,
        'tickSize'        => 10,
      ),
    ));

    $this->options = $options;

    $series = array();

    foreach (self::$types as $_type => $_options) {
      $_serie = array_merge(
        $_options, array(
          "label" => CAppUI::tr("CExamAudio-$this->type-type-$_type"),
          "old_exam" => $old_exam,
          "type"  => $_type,
          "data"  => array(),
        )
      );

        foreach ($exam->{"_{$this->side}_{$_type}"} as $_i => $_v) {
            if ($_v === "") {
                if(($_type == "aerien" || $_type == "osseux") && $exam->{"_{$this->side}_{$_type}_pasrep"}[$_i]) {
                    if (count($_serie["data"])) {
                        $series[] = $_serie;
                        $_serie   = array_merge(
                            $_options, [
                                         "label" => "",
                                         "old_exam" => $old_exam,
                                         "type"  => $_type,
                                         "data"  => [],
                                     ]
                        );
                    }
                } else{
                    continue;
                }
            }else {
                $_serie["data"][] = array($_i, -$_v);
            }

        }

        if($old_exam) {
            if($_serie["type"] == "pasrep" && count($_serie["data"]) ){
                $series[] = $_serie;
            }
            if($_serie["type"] != "pasrep" ){
                $series[] = $_serie;
            }

        }
        if(!$old_exam && $_serie["type"] != "pasrep"){
            $series[] = $_serie;
        }

    }

    $this->series = $series;
  }
}
