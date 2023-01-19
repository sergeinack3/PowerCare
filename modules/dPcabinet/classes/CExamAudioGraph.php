<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;

/**
 * Exam audio graph utility class
 */
class CExamAudioGraph implements IShortNameAutoloadable
{
    public static $types           = [
        "audiometrie_vocale" => "CExamAudioGraphAudiometrieVocale",
        "audiometrie_tonale" => "CExamAudioGraphAudiometrieTonale",
        "tympanometrie"      => "CExamAudioGraphTympanometrie",
    ];
    public static $default_options = [
        'title'      => null,
        'shadowSize' => 0,
        'xaxis'      => [],
        'yaxis'      => [],
        'grid'       => [
            'verticalLines' => true,
            'borderWidth'   => 1,
            'clickable'     => true,
            'hoverable'     => true,
            'autoHighlight' => false,
        ],
        'lines'      => [
            'lineWidth' => 1,
        ],
    ];
    public static $sides           = [
        "gauche" => ["color" => "blue", "points" => ["symbol" => "cross"]],
        "droite" => ["color" => "red", "points" => ["symbol" => "circle"]],
    ];
    public $type;
    public $exam_audio;
    public $options;
    public $series;

    /**
     * CExamAudioGraph constructor.
     *
     * @param CExamAudio $exam_audio Exam audio
     */
    public function __construct(CExamAudio $exam_audio)
    {
        $this->exam_audio = $exam_audio;
    }

    /**
     *
     *
     * @param array      $graph
     * @param CExamAudio $old_exam_audio
     *
     * @throws \Exception
     */
    public static function setGraphAsOldGraph(array &$graph, CExamAudio $old_exam_audio): void
    {
        if ($graph["label"] == "") {
            switch ($graph["type"]) {
                case "osseux":
                    $graph["color"] = "#FFB9B9";
                    return;
                case "aerien":
                    $graph["color"] = "#B1D4FF";
                    return;
                default:
                    break;
            }
        }


        if (!$graph["label"]) {
            switch ($graph["points"]["symbol"]) {
                case "cross":
                    $graph["color"] = "#B1D4FF";

                    return;
                case "circle":
                    $graph["color"] = "#FFB9B9";
                    return;
                default:
                    return;
            }
        }

        switch ($graph["label"]) {
            case "Conduction arérienne":
            case "Pas de réponse : Conduction aérienne":
            case "Oreille gauche":
                $graph["color"] = "#B1D4FF";
                break;
            case "Conduction osseuse":
            case "Pas de réponse : Conduction osseuse":
            case "Oreille droite":
                $graph["color"] = "#FFB9B9";
                break;
            case "Stapédien controlarétal":
            case "Courbe optimale":
                $graph["color"] = "#D3D3D3";
                break;
            case "Stapédien ipsilarétal":
                $graph["color"] = "#D0A775";
                break;
            case "Pas de réponse":
                $graph["color"] = "#A2DCA2";
                break;
        }
        $graph["label"] = $graph["label"] . " - " .
            CMbDT::format(CMbDT::date($old_exam_audio->_ref_consult->_date), CAppUI::conf('longdate'));
    }

    public function getStruct(): array
    {
        return [
            "id"     => $this->getId(),
            "series" => $this->series,
        ];
    }

    public function getId(): string
    {
        return "examaudio-$this->type" . (isset($this->side) ? "-$this->side" : "");
    }
}
