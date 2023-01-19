<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Post Anaesthetic Discharge Scoring System - Score de Chung modifé
 */
class CChungScore extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $pads_id;

  // DB fields
  public $sejour_id;
  public $administration_id;
  public $datetime;
  public $vital_signs;
  public $activity;
  public $nausea;
  public $pain;
  public $bleeding;
  public $total;

  public static $criteria = array("vital_signs", "activity", "nausea", "pain", "bleeding");

  public static $fields = array("datetime", "vital_signs", "activity", "nausea", "pain", "bleeding");

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "chung_scores";
    $spec->key   = "chung_score_id";
    return $spec;
  }

  /**
   * @inheritDoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["sejour_id"]         = "ref class|CSejour notNull back|chung_scores";
    $props["administration_id"] = "ref class|CAdministration nullify back|chung_scores";
    $props["datetime"]          = "dateTime notNull";
    $props["vital_signs"]       = "enum list|2|1|0";
    $props["activity"]          = "enum list|2|1|0";
    $props["nausea"]            = "enum list|2|1|0";
    $props["pain"]              = "enum list|2|1|0";
    $props["bleeding"]          = "enum list|2|1|0";
    $props["total"]             = "num min|0 max|10";
    return $props;
  }
}
