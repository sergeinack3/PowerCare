<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Examen IGS
 */
class CExamIgs extends CMbObject implements IGroupRelated
{
  public $examigs_id;

  // DB References
  public $sejour_id;

  // DB fields
  public $date;
  public $age;
  public $FC;
  public $TA;
  public $temperature;
  public $PAO2_FIO2;
  public $diurese;
  public $uree;
  public $globules_blancs;
  public $kaliemie;
  public $natremie;
  public $HCO3;
  public $billirubine;
  public $glasgow;
  public $maladies_chroniques;
  public $admission;
  public $scoreIGS;
  public $simplified_igs;

  /** @var CSejour */
  public $_ref_sejour;

  public $_no_synchro_eai = false;

  static $fields = array("age", "FC", "TA", "temperature", "PAO2_FIO2", "diurese", "uree", "globules_blancs",
    "kaliemie", "natremie", "HCO3", "billirubine", "glasgow", "maladies_chroniques", "admission");

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'examigs';
    $spec->key   = 'examigs_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                        = parent::getProps();
    $props["date"]                = "dateTime notNull";
    $props["sejour_id"]           = "ref notNull class|CSejour back|exams_igs";
    $props["age"]                 = "enum list|0|7|12|15|16|18";
    $props["FC"]                  = "enum list|11|2|0|4|7";
    $props["TA"]                  = "enum list|13|5|0|2";
    $props["temperature"]         = "enum list|0|3";
    $props["PAO2_FIO2"]           = "enum list|11|9|6";
    $props["diurese"]             = "enum list|11|4|0";
    $props["uree"]                = "enum list|0|6|10";
    $props["globules_blancs"]     = "enum list|12|0|3";
    $props["kaliemie"]            = "enum list|3a|0|3b";
    $props["natremie"]            = "enum list|5|0|1";
    $props["HCO3"]                = "enum list|6|3|0";
    $props["billirubine"]         = "enum list|0|4|9";
    $props["glasgow"]             = "enum list|26|13|7|5|0";
    $props["maladies_chroniques"] = "enum list|9|10|17";
    $props["admission"]           = "enum list|0|6|8";
    $props["scoreIGS"]            = "num";
    $props["simplified_igs"]      = "num";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "Score IGS: $this->scoreIGS";
  }

  /**
   * Charge le séjour
   *
   * @return CSejour|CStoredObject
   * @throws Exception
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Sorts an array by a property of the object
   *
   * @param mixed  $array         the array
   * @param string $property_name name of the property
   *
   * @return mixed the sorted array
   * @throws CMbException
   */
  public static function sortObjectDate($array, $property_name) {
    if (!is_array($array)) {
      throw new CMbException("C'est un array que l'on veut comparer");
    }

    usort($array, function ($a, $b) use ($property_name) {
      return strtotime($a->$property_name) <=> strtotime($b->$property_name);
    });

    return $array;
  }

  /**
   * From an array of IGS objects, return the worse score from the last 24 hours
   *
   * @param CExamIgs[] $exam_igs The scores
   *
   * @return int $igs the real IGS score
   * @throws CMbException
   */
  static function getIGSFromList($exam_igs) {
    if (!$exam_igs && !is_array($exam_igs)) {
      throw new CMbException("La liste des scores IGS n'est pas un tableau");
    }

    $exam_igs = self::sortObjectDate($exam_igs, "date");

    $score_igs = (sizeof($exam_igs) > 0) ? end($exam_igs)->scoreIGS : -1;

    foreach ($exam_igs as $_score_igs) {
      if (CMbDT::hoursRelative($_score_igs->date, CMbDT::dateTime()) <= 24) {
        $score_igs = ($_score_igs->scoreIGS > $score_igs) ? $_score_igs->scoreIGS : $score_igs;
      }
    }

    return ($score_igs > -1) ? $score_igs : null;
  }

    public function loadRelGroup(): CGroups
    {
        return $this->loadRefSejour()->loadRelGroup();
    }
}
