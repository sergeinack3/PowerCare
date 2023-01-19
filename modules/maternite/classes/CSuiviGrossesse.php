<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CConsultation;

/**
 * Gestion des dépistages du dossier de périnatalité
 */
class CSuiviGrossesse extends CMbObject {
  // DB Table key
  public $suivi_grossesse_id;

  public $consultation_id;
  public $type_suivi;
  public $evenements_anterieurs;

  // Signes fonctionnels actuels
  public $metrorragies;
  public $leucorrhees;
  public $contractions_anormales;
  public $mouvements_foetaux;
  public $troubles_digestifs;
  public $troubles_urinaires;
  public $autres_anomalies;
  public $hypertension;

  public $mouvements_actifs;

  // Examen général
  public $auscultation_cardio_pulm;
  public $examen_seins;
  public $circulation_veineuse;
  public $oedeme_membres_inf;
  public $rques_examen_general;

  // Examen gyneco-obstétrical
  public $bruit_du_coeur;
  public $col_normal;
  public $longueur_col;
  public $position_col;
  public $dilatation_col;
  public $dilatation_col_num;
  public $consistance_col;
  public $col_commentaire;
  public $presentation_position;
  public $presentation_etat;
  public $segment_inferieur;
  public $membranes;
  public $bassin;
  public $examen_genital;
  public $rques_exam_gyneco_obst;
  public $hauteur_uterine;

  // Examens complémentaires
  public $frottis;
  public $echographie;
  public $prelevement_bacterio;
  public $autre_exam_comp;
  public $glycosurie;
  public $leucocyturie;
  public $albuminurie;
  public $nitrites;

  // Prescriptions
  public $jours_arret_travail;

  // Conclusion
  public $conclusion;

  /** @var CConsultation */
  public $_ref_consultation;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'suivi_grossesse';
    $spec->key   = 'suivi_grossesse_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                          = parent::getProps();
    $props["consultation_id"]       = "ref notNull class|CConsultation back|suivi_grossesse";
    $props["type_suivi"]            = "enum list|surv|urg|htp|autre default|surv";
    $props["evenements_anterieurs"] = "text helped";

    // Signes fonctionnels actuels
    $props["metrorragies"]           = "bool";
    $props["leucorrhees"]            = "bool";
    $props["contractions_anormales"] = "bool";
    $props["mouvements_foetaux"]     = "bool";
    $props["troubles_digestifs"]     = "bool";
    $props["troubles_urinaires"]     = "bool";
    $props["autres_anomalies"]       = "text helped";
    $props["hypertension"]           = "bool";

    $props["mouvements_actifs"] = "enum list|percu|npercu";

    // Examen général
    $props["auscultation_cardio_pulm"] = "enum list|normal|anomalie";
    $props["examen_seins"]             = "enum list|normal|mamomb|autre";
    $props["circulation_veineuse"]     = "enum list|normal|insmod|inssev";
    $props["oedeme_membres_inf"]       = "bool";
    $props["rques_examen_general"]     = "text helped";

    // Examen gyneco-obstétrical
    $props["bruit_du_coeur"]         = "enum list|percu|npercu";
    $props["col_normal"]             = "enum list|o|n";
    $props["longueur_col"]           = "enum list|long|milong|court|eff";
    $props["position_col"]           = "enum list|post|inter|ant";
    $props["dilatation_col"]         = "enum list|ferme|perm";
    $props["dilatation_col_num"]     = "num";
    $props["consistance_col"]        = "enum list|ton|moy|mol";
    $props["col_commentaire"]        = "text helped";
    $props["presentation_position"]  = "enum list|som|sie|tra|inc";
    $props["presentation_etat"]      = "enum list|mob|amo|fix|eng";
    $props["segment_inferieur"]      = "enum list|amp|namp";
    $props["membranes"]              = "enum list|int|romp|susrupt";
    $props["bassin"]                 = "enum list|normal|anomalie";
    $props["examen_genital"]         = "enum list|normal|anomalie";
    $props["rques_exam_gyneco_obst"] = "text helped";
    $props["hauteur_uterine"]        = "num";

    // Examens complémentaires
    $props["frottis"]              = "enum list|fait|nfait";
    $props["echographie"]          = "enum list|fait|nfait";
    $props["prelevement_bacterio"] = "enum list|fait|nfait";
    $props["autre_exam_comp"]      = "text helped";
    $props["glycosurie"]           = "enum list|positif|negatif";
    $props["leucocyturie"]         = "enum list|positif|negatif";
    $props["albuminurie"]          = "enum list|positif|negatif";
    $props["nitrites"]             = "enum list|positif|negatif";

    // Prescriptions
    $props["jours_arret_travail"] = "num";

    // Conclusion
    $props["conclusion"] = "text helped";

    return $props;
  }

  /**
   * Chargement de la consultation
   *
   * @return CConsultation
   */
  function loadRefConsultation() {
    return $this->_ref_consultation = $this->loadFwdRef("consultation_id", true);
  }

  /**
   * Retourne un array des attributs pour un type donné
   * @param string $type
   *
   * @return array
   * @throws Exception
   */
  private function makeArrayFromFieldNames(string $type): array {
    $fields = [
      "exam_general" => ['auscultation_cardio_pulm', 'evenements_anterieurs', 'examen_seins', 'circulation_veineuse', 'rques_examen_general', 'oedeme_membres_inf'],
      "exam_genico" => ["bruit_du_coeur", "presentation_position", "col_normal", "presentation_etat", "longueur_col", "segment_inferieur", "position_col", "membranes", "dilatation_col", "bassin", "consistance_col", "examen_genital", "hauteur_uterine", "col_commentaire", "rques_exam_gyneco_obst"],
      "exam_comp" => ["frottis","glycosurie","echographie","leucocyturie","prelevement_bacterio","albuminurie","nitrites","autre_exam_comp","jours_arret_travail"],
      "functionnal_signs" => ["metrorragies","troubles_digestifs","leucorrhees","troubles_urinaires","contractions_anormales","autres_anomalies","mouvements_foetaux","mouvements_actifs","hypertension"]
    ];

    $fields_to_return = $fields[$type];

    $returned_fields = [];
    foreach ($fields_to_return as $_field_name) {
      if (isset($this->$_field_name)) {
        $returned_fields[$_field_name] = $this->$_field_name;
      }
    }

    return $returned_fields;
  }

  /**
   * Retourne les différents champs de CSuiviGrossesse, triés par catégorie
   *
   * @return array
   * @throws Exception
   */
  public function sortAttributesByCategory(): array {
    return [
      'exam_general' => $this->makeArrayFromFieldNames("exam_general"),
      'exam_genico' => $this->makeArrayFromFieldNames("exam_genico"),
      'exam_comp' => $this->makeArrayFromFieldNames("exam_comp"),
      'functionnal_signs' => $this->makeArrayFromFieldNames("functionnal_signs")
    ];
  }
}