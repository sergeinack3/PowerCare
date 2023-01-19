<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;

/**
 * Formulaire du score POSSUM
 */
class CExamPossum extends CMbObject {
  // DB Table key
  public $exampossum_id;

  // DB References
  public $consultation_id;

  // DB fields
  public $age;
  public $ouverture_yeux;
  public $rep_verbale;
  public $rep_motrice;
  public $signes_respiratoires;
  public $uree;
  public $freq_cardiaque;
  public $signes_cardiaques;
  public $hb;
  public $leucocytes;
  public $ecg;
  public $kaliemie;
  public $natremie;
  public $pression_arterielle;
  public $gravite;
  public $nb_interv;
  public $pertes_sanguines;
  public $contam_peritoneale;
  public $cancer;
  public $circonstances_interv;

  // Form Fields
  public $_glasgow;
  public $_score_physio;
  public $_score_oper;
  public $_morbidite;
  public $_mortalite;
  public $_score_possum_oper;
  public $_score_possum_physio;

  /** @var CConsultation */
  public $_ref_consult;

  /**
   * Standard constructor
   */
  function __construct() {
    parent::__construct();

    static $score_possum_physio = null;
    if (!$score_possum_physio) {
      $score_possum_physio = $this->getScorePhysio();
    }
    $this->_score_possum_physio =& $score_possum_physio;

    static $score_possum_oper = null;
    if (!$score_possum_oper) {
      $score_possum_oper = $this->getScoreOper();
    }
    $this->_score_possum_oper =& $score_possum_oper;
  }

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'exampossum';
    $spec->key   = 'exampossum_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["consultation_id"]      = "ref notNull class|CConsultation back|exampossum";
    $props["age"]                  = "enum list|inf60|61|sup71";
    $props["ouverture_yeux"]       = "enum list|spontane|bruit|douleur|jamais";
    $props["rep_verbale"]          = "enum list|oriente|confuse|inapproprie|incomprehensible|aucune";
    $props["rep_motrice"]          = "enum list|obeit|oriente|evitement|decortication|decerebration|rien";
    $props["signes_respiratoires"] = "enum list|aucun|dyspnee_effort|bpco_leger|dyspnee_inval|bpco_modere|dyspnee_repos|fibrose";
    $props["uree"]                 = "enum list|inf7.5|7.6|10.1|sup15.1";
    $props["freq_cardiaque"]       = "enum list|inf39|40|50|81|101|sup121";
    $props["signes_cardiaques"]    = "enum list|aucun|diuretique|antiangineux|oedemes|cardio_modere|turgescence|cardio";
    $props["hb"]                   = "enum list|inf9.9|10|11.5|13|16.1|17.1|sup18.1";
    $props["leucocytes"]           = "enum list|inf3000|3100|4000|10100|sup20100";
    $props["ecg"]                  = "enum list|normal|fa|autre|sup5|anomalie";
    $props["kaliemie"]             = "enum list|inf2.8|2.9|3.2|3.5|5.1|5.4|sup6.0";
    $props["natremie"]             = "enum list|inf125|126|131|sup136";
    $props["pression_arterielle"]  = "enum list|inf89|90|100|110|131|sup171";
    $props["gravite"]              = "enum list|min|moy|maj|maj+";
    $props["nb_interv"]            = "enum list|1|2|sup2";
    $props["pertes_sanguines"]     = "enum list|inf100|101|501|sup1000";
    $props["contam_peritoneale"]   = "enum list|aucune|mineure|purulente|diffusion";
    $props["cancer"]               = "enum list|absense|tumeur|ganglion|metastases";
    $props["circonstances_interv"] = "enum list|reglee|urg|prgm|sansdelai";

    // Form Fields
    $props["_glasgow"]             = "";
    $props["_score_physio"]        = "";
    $props["_score_oper"]          = "";
    $props["_morbidite"]           = "";
    $props["_mortalite"]           = "";
    $props["_score_possum_oper"]   = "";
    $props["_score_possum_physio"] = "";

    return $props;
  }

  /**
   * Donne les scores pour chaque valeur physio
   *
   * @return array
   */
  function getScorePhysio() {
    return array(
      "age" => array(
        "inf60" => 1,
        "61"    => 2,
        "sup71" => 4,
      ),
      "ouverture_yeux" => array(
        "spontane" => 4,
        "bruit"    => 3,
        "douleur"  => 2,
        "jamais"   => 1,
      ),
      "rep_verbale" => array(
        "oriente"          => 5,
        "confuse"          => 4,
        "inapproprie"      => 3,
        "incomprehensible" => 2,
        "aucune"           => 1,
      ),
      "rep_motrice"  => array(
        "obeit"         => 6,
        "oriente"       => 5,
        "evitement"     => 4,
        "decortication" => 3,
        "decerebration" => 2,
        "rien"          => 1,
      ),
      "signes_respiratoires" => array(
        "aucun"          => 1,
        "dyspnee_effort" => 2,
        "bpco_leger"     => 2,
        "dyspnee_inval"  => 4,
        "bpco_modere"    => 4,
        "dyspnee_repos"  => 8,
        "fibrose"        => 8,
      ),
      "uree" => array(
        "inf7.5"  => 1,
        "7.6"     => 2,
        "10.1"    => 4,
        "sup15.1" => 8,
      ),
      "freq_cardiaque" => array(
        "inf39"  => 8,
        "40"     => 2,
        "50"     => 1,
        "81"     => 2,
        "101"    => 4,
        "sup121" => 8,
      ),
      "signes_cardiaques" => array(
        "aucun"         => 1,
        "diuretique"    => 2,
        "antiangineux"  => 2,
        "oedemes"       => 4,
        "cardio_modere" => 4,
        "turgescence"   => 8,
        "cardio"        => 8,
      ),
      "hb" => array(
        "inf9.9"  => 8,
        "10"      => 4,
        "11.5"    => 2,
        "13"      => 1,
        "16.1"    => 2,
        "17.1"    => 4,
        "sup18.1" => 8,
      ),
      "leucocytes"  => array(
        "inf3000"  => 4,
        "3100"     => 2,
        "4000"     => 1,
        "10100"    => 2,
        "sup20100" => 4,
      ),
      "ecg" => array(
        "normal"   => 1,
        "fa"       => 4,
        "autre"    => 8,
        "sup5"     => 8,
        "anomalie" => 8,
      ),
      "kaliemie" => array(
        "inf2.8" => 8,
        "2.9"    => 4,
        "3.2"    => 2,
        "3.5"    => 1,
        "5.1"    => 2,
        "5.4"    => 4,
        "sup6.0" => 8,
      ),
      "natremie" => array(
        "inf125" => 8,
        "126"    => 4,
        "131"    => 2,
        "sup136" => 1,
      ),
      "pression_arterielle" => array(
        "inf89"  => 8,
        "90"     => 4,
        "100"    => 2,
        "110"    => 1,
        "131"    => 2,
        "sup171" => 4,
      )
    );
  }

  /**
   * Donne les scores pour chaque valeur opératoire
   *
   * @return array
   */
  function getScoreOper() {
    return array(
      "gravite" => array(
        "min"  => 1,
        "moy"  => 2,
        "maj"  => 4,
        "maj+" => 8,
      ),
      "nb_interv" => array(
        "1"    => 1,
        "2"    => 4,
        "sup2" => 8,
      ),
      "pertes_sanguines" => array(
        "inf100"  => 1,
        "101"     => 2,
        "501"     => 4,
        "sup1000" => 8,
      ),
      "contam_peritoneale" => array(
        "aucune"    => 1,
        "mineure"   => 2,
        "purulente" => 4,
        "diffusion" => 8,
      ),
      "cancer" => array(
        "absense"    => 1,
        "tumeur"     => 2,
        "ganglion"   => 4,
        "metastases" => 8,
      ),
      "circonstances_interv" => array(
        "reglee"    => 1,
        "urg"       => 4,
        "prgm"      => 4,
        "sansdelai" => 8,
      )
    );
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields(){
    parent::updateFormFields();

    // Calcul Glasgow
    $this->_glasgow = 0;
    if ($this->ouverture_yeux) {
      $this->_glasgow += $this->_score_possum_physio["ouverture_yeux"][$this->ouverture_yeux];
    }
    if ($this->rep_verbale) {
      $this->_glasgow += $this->_score_possum_physio["rep_verbale"][$this->rep_verbale];
    }
    if ($this->rep_motrice) {
      $this->_glasgow += $this->_score_possum_physio["rep_motrice"][$this->rep_motrice];
    }

    $this->_score_physio = 0;
    foreach ($this->_score_possum_physio as $field => $value) {
      if ($field == "ouverture_yeux" || $field == "rep_verbale") {
        continue;
      }
      if ($field == "rep_motrice") {
        if ($this->_glasgow >= 1 && $this->_glasgow <= 8) {
          $this->_score_physio += 8;
        }
        elseif ($this->_glasgow >= 9 && $this->_glasgow <= 11) {
          $this->_score_physio += 4;
        }
        elseif ($this->_glasgow >= 12 && $this->_glasgow <= 14) {
          $this->_score_physio += 2;
        }
        elseif ($this->_glasgow == 15) {
          $this->_score_physio += 1;
        }
        continue;
      }

      if ($this->$field) {
        $this->_score_physio += $this->_score_possum_physio[$field][$this->$field];
      }
    }

    $this->_score_oper = 0;
    foreach ($this->_score_possum_oper as $field => $value) {
      if ($this->$field) {
        $this->_score_oper += $this->_score_possum_oper[$field][$this->$field];
      }
    }

    // Calcul de la morbidité
    $temp = (0.16 * $this->_score_physio) + (0.19 * $this->_score_oper) - 5.91;
    $this->_morbidite = round(100 / (1 + exp(-$temp)), 1);

    // Calcul de la Mortalité
    $temp = (0.13 * $this->_score_physio) + (0.16 * $this->_score_oper) - 7.04;
    $this->_mortalite = round(100 / (1 + exp(-$temp)), 1);

    $this->_view = "Scores POSSUM (morb./mort.) : $this->_morbidite / $this->_mortalite";
  }

  /**
   * Charge la consultation associée
   *
   * @return CConsultation
   */
  function loadRefConsult() {
    return $this->_ref_consult = $this->loadFwdRef("consultation_id", true);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    return $this->loadRefConsult()->getPerm($permType);
  }
}
