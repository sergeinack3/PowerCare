<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Ccam\CActiviteClassifCCAM;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CCodeCCAM;
use Ox\Mediboard\Ccam\CContexteTarifaireCCAM;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureItem;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSE;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSEAct;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Classe servant à gérer les enregistrements des actes CCAM pendant les
 * interventions
 */
class CActeCCAM extends CActe implements ImportableInterface, IGroupRelated
{
  const RESOURCE_TYPE = 'actesCcam';

  static $coef_associations = array (
    "1" => 100,
    "2" => 50,
    "3" => 75,
    "4" => 100,
    "5" => 100,
  );

  // DB Table key
  public $acte_id;

  // DB Fields
  public $code_acte;
  public $code_extension;
  public $code_activite;
  public $code_phase;
  public $modificateurs;
  public $motif_depassement;
  public $commentaire;
  public $code_association;
  public $extension_documentaire;
  public $rembourse;
  public $charges_sup;
  public $regle;
  public $regle_dh;
  public $signe;
  public $sent;
  public $exoneration;
  public $lieu;
  public $ald;
  public $position_dentaire;
  public $numero_forfait_technique;
  public $numero_agrement;
  public $rapport_exoneration;
  public $accord_prealable;
  public $date_demande_accord;
  public $reponse_accord;
  public $coding_datetime;
  public $prescription_id;
  public $other_executant_id;
  public $motif;
  public $motif_unique_cim;
  /** @var bool Permet de savoir si le champ facturable a été renseigné automatiquement ou par l'utilisateur */
  public $facturable_auto;

  // Derived fields
  public $_modificateurs = array();
  public $_dents         = array();
  public $_rembex;
  public $_anesth;
  public $_anesth_associe;
  public $_tarif_base;
  public $_tarif_sans_asso;
  public $_tarif;
  public $_activite;
  public $_phase;
  public $_position;
  public $_guess_facturable;
  public $_guess_association;
  public $_guess_regle_asso;
  public $_exclusive_modifiers;
  public $_total;
  public $_total_saisi;

  // Behaviour fields
  public $_adapt_object = false;
  public $_calcul_montant_base = false;
  public $_edit_modificateurs = false;
  public $_spread_modifiers = true;
  public $_display = true;
  public $_update_codage_rule = true;

  // References
  /** @var  CDatedCodeCCAM */
  public $_ref_code_ccam;
  /** @var  CCodable */
  public $_ref_object;
  /** @var CCodageCCAM */
  public $_ref_codage_ccam;
  /** @var CPatient */
  public $_ref_patient;
  /** @var CPrescription */
  public $_ref_prescription;

  // Collections
  /** @var  CActeCCAM[] */
  public $_ref_siblings;
  /** @var  CActeCCAM[] */
  public $_linked_actes;

  /**
   * @see parent::getSpec()
   */
  public function getSpec(): CMbObjectSpec {
    $spec = parent::getSpec();
    $spec->table = 'acte_ccam';
    $spec->key   = 'acte_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  public function getProps(): array {
    $props = parent::getProps();

    // DB fields
    $props["object_id"]               .= " back|actes_ccam";
    $props["code_acte"]                = "code notNull ccam seekable fieldset|default";
    $props['code_extension']           = 'str minLength|2 maxLength|2 fieldset|default';
    $props["code_activite"]            = "num notNull min|0 max|99 fieldset|default";
    $props["code_phase"]               = "num notNull min|0 max|99 fieldset|default";
    $props["modificateurs"]            = "str maxLength|4 fieldset|extra";
    $props["motif_depassement"]        = "enum list|d|e|f|g|n|a|b|l fieldset|extra";
    $props["commentaire"]              = "text helped fieldset|extra";
    $props["code_association"]         = "enum list|1|2|3|4|5 fieldset|default";
    $props["extension_documentaire"]   = "enum list|1|2|3|4|5|6 fieldset|extra";
    $props["rembourse"]                = "bool fieldset|extra";
    $props["charges_sup"]              = "bool fieldset|extra";
    $props["regle"]                    = "bool default|0 fieldset|extra";
    $props["regle_dh"]                 = "bool default|0 fieldset|extra";
    $props["signe"]                    = "bool default|0 fieldset|extra";
    $props["sent"]                     = "bool default|0 fieldset|extra";
    $props["lieu"]                     = "enum list|C|D default|C fieldset|extra";
    $props["exoneration"]              = "enum list|N|3|7 default|N fieldset|extra";
    $props["ald"]                      = "bool fieldset|extra";
    $props["position_dentaire"]        = "str fieldset|extra";
    $props["numero_forfait_technique"] = "num min|1 max|99999 fieldset|extra";
    $props["numero_agrement"]          = "num min|1 max|99999999999999 fieldset|extra";
    $props["rapport_exoneration"]      = "enum list|4|7|C|R fieldset|extra";
    $props['accord_prealable']         = 'bool default|0 fieldset|extra';
    $props['date_demande_accord']      = 'date fieldset|extra';
    $props['reponse_accord']           = 'enum list|no_answer|accepted|emergency|refused fieldset|extra';
    $props['coding_datetime']          = 'dateTime fieldset|default';
    $props["prescription_id"]          = "ref class|CPrescription back|actes_ccam";
    $props["executant_id"]            .= " back|actes_ccam_executes";
    $props["other_executant_id"]       = "ref class|CMedecin back|actes_ccam";
    $props["motif"]                    = "text helped fieldset|default";
    $props["motif_unique_cim"]         = "code cim10 show|0";
    $props['facturable_auto']          = 'bool default|1 fieldset|extra';

    // Derived fields
    $props["_rembex"]           = "bool";
    $props["_tarif_base"]       = "currency fieldset|default";
    $props["_tarif_sans_asso"]  = "currency fieldset|default";
    $props["_tarif"]            = "currency fieldset|default";
    $props['_total']            = 'currency fieldset|default';
    $props['_total_saisi']      = 'currency fieldset|default';

    return $props;
  }

  /**
   * Check the number of codes compared to the number of actes
   *
   * @return string check-like message
   */
  function checkEnoughCodes() {
    $this->loadTargetObject();
    if (!$this->_ref_object || !$this->_ref_object->_id) {
      return null;
    }

    $acte = new CActeCCAM();
    $where = array();
    if ($this->_id) {

      // dans le cas de la modification
      $where["acte_id"]     = "<> '$this->_id'";
    }

    $this->completeField("code_acte", "object_class", "object_id", "code_activite", "code_phase");
    $where["code_acte"]     = "= '$this->code_acte'";
    $where["object_class"]  = "= '$this->object_class'";
    $where["object_id"]     = "= '$this->object_id'";
    $where["code_activite"] = "= '$this->code_activite'";
    $where["code_phase"]    = "= '$this->code_phase'";

    $this->_ref_siblings = $acte->loadList($where);

    // retourne le nombre de code semblables
    $siblings = count($this->_ref_siblings);

    // compteur d'acte prevue ayant le meme code_acte dans l'intervention
    $nbCode = 0;
    $this->_ref_object->updateCCAMFormField();
    foreach ($this->_ref_object->_codes_ccam as $code) {
      // si le code est sous sa forme complete, garder seulement le code
      $code = substr($code, 0, 7);
      if ($code == $this->code_acte) {
        $nbCode++;
      }
    }
    if ($siblings >= $nbCode) {
      return "$this->_class-check-already-coded";
    }
    return null;
  }

  /**
   * @see parent::checkCoded()
   */
  public function checkCoded(): ?string {
    $this->loadRefCodageCCAM();
    if ($this->_ref_codage_ccam && $this->_ref_codage_ccam->_id && $this->_check_coded
        && $this->_ref_codage_ccam->locked && !CModule::getCanDo('dPpmsi')->edit
    ) {
      return "Codage CCAM verrouillé, impossible de modifier l'acte";
    }
    return parent::checkCoded();
  }

  /**
   * @see parent::canDeleteEx()
   */
  function canDeleteEx(){
    // Test si la consultation est validée
    if ($msg = $this->checkCoded()) {
      return $msg;
    }

    $msg = parent::canDeleteEx();

    if ($msg) {
      return $msg;
    }

    if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation') {
      /** @var CPyxvitalFSEAct[] $fse_links */
      $fse_links = $this->loadBackRefs('fse_links');
      if ($fse_links) {
        foreach ($fse_links as $_link) {
          $_link->loadRefFSE();
          if ($_link->_ref_fse->state != 'creating' && $_link->_ref_fse->state != 'cancelled') {
            $msg = CAppUI::tr('CMbObject-msg-nodelete-backrefs') . ': ' . count($fse_links) . ' ' . CAppUI::tr("CActe-back-fse_links");
          }
        }
      }
    }

    return $msg;
  }


  /**
   * @see parent::delete()
   */
  public function delete(): ?string {
    /* We delete the links between the act and the fse that are in creation or cancelled */
    if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation') {
      /** @var CPyxvitalFSEAct[] $fse_links */
      $fse_links = $this->loadBackRefs('fse_links');
      if ($fse_links) {
        foreach ($fse_links as $_link) {
          $_link->loadRefFSE();
          if ($_link->_ref_fse->state == 'creating' || $_link->_ref_fse->state == 'cancelled') {
            if ($msg = $_link->delete()) {
              return $msg;
            }
          }
        }
      }
    }

    $this->loadRefCodageCCAM();
    if ($msg = parent::delete()) {
      return $msg;
    }

    if (isset($this->_ref_codage_ccam)) {
      if ($this->_ref_codage_ccam->_id) {
        $this->_ref_codage_ccam->updateRule(true);
        $this->_ref_codage_ccam->store();
      }
    }

    return null;
  }

  /**
   * @see parent::check()
   */
  function check() {
    // Test si la consultation est validée
    if ($msg = $this->checkCoded()) {
      return $msg;
    }

    // Test si on n'a pas d'incompatibilité avec les autres codes
    if ($msg = $this->checkCompat()) {
      return $msg;
    }

    if ($msg = $this->checkEnoughCodes()) {
      // Ajoute le code si besoins à l'objet
      if ($this->_adapt_object || $this->_forwardRefMerging) {
        $this->_ref_object->_codes_ccam[] = $this->code_acte;
        $this->_ref_object->updateCCAMPlainField();

        /*if ($this->_forwardRefMerging) {
          $this->_ref_object->_merging = true;
        }*/

        return $this->_ref_object->store();
      }
      return $msg;
    }

    if ($msg = $this->checkExclusiveModifiers()) {
      return $msg;
    }

    $this->loadRefCodeCCAM();
    $this->loadRefExecutant();
    /*if (!$this->_ref_code_ccam->isCodeAllowedForUSer($this->_ref_executant)) {
      $speciality = $this->_ref_executant->loadRefSpecCPAM();
      return CAppUI::tr('CActeCCAM-error-code_not_allowed_for_user', $this->code_acte, strtolower($speciality->text));
    }*/

    $codage_ccam = CCodageCCAM::get(
      $this->_ref_object, $this->executant_id, $this->code_activite, CMbDT::date(null, $this->execution)
    );
    if (!$codage_ccam->_id) {
      if ($msg = $codage_ccam->store()) {
        return $msg;
      }
    }

    /* Verification de l'extension PMSI dans le cas ou elle est obligatoire */
    if (CAppUI::gconf('dPccam codage pmsi_extension_mandatory')
        && !in_array($this->object_class, array('CConsultation', 'CDevisCodage'))
    ) {
      $this->loadRefCodeCCAM();
      $this->completeField('code_extension');
      if (count($this->_ref_code_ccam->extensions) && !$this->code_extension) {
        return CAppUI::tr('CActeCCAM-error-code_extension_not_set');
      }
    }

    $this->completeField('extension_documentaire');
    /* Verification de l'extension PMSI dans le cas ou elle est obligatoire */
    if (CAppUI::gconf('dPccam codage doc_extension_mandatory')
        && !in_array($this->object_class, array('CConsultation')) && !$this->extension_documentaire && $this->code_activite == 4
    ) {
      return CAppUI::tr('CActeCCAM-error-extension_documentaire_not_set');
    }

    return parent::check();
    // datetime_execution: attention à rester dans la plage de l'opération
  }

  /**
   * @see parent::makeFullCode();
   */
  public function makeFullCode(): string {
    return $this->_full_code =
      $this->code_acte.
      "-". $this->code_activite.
      "-". $this->code_phase.
      "-". $this->modificateurs.
      "-". str_replace("-", "*", $this->montant_depassement ?? '').
      "-". $this->code_association.
      "-". $this->rembourse.
      "-". $this->charges_sup.
      "-". $this->gratuit.
      '-'. $this->code_extension.
      '-'. $this->extension_documentaire.
      '-'. str_replace('|', '+', $this->position_dentaire ?? '').
      '-'.$this->motif_depassement .
      '-' . $this->exoneration .
      '-' . $this->facturable;
  }

  /**
   * CActe redefinition
   *
   * @param string $code Serialised full code
   *
   * @return void
   */
  public function setFullCode(string $code): void {
    $details = explode("-", $code);
    if (count($details) > 2) {
      $this->code_acte     = $details[0];
      $this->code_activite = $details[1];
      $this->code_phase    = $details[2];

      // Modificateurs
      if (count($details) > 3) {
        $modificateurs       = str_split($details[3]);
        $list_modifs_actifs  = str_split(CCodeCCAM::getModificateursActifs());
        $this->modificateurs = implode('', array_intersect($modificateurs, $list_modifs_actifs));
      }

      // Dépassement
      if (count($details) > 4) {
        $this->montant_depassement = str_replace("*", "-", $details[4]);
      }

      // Code association
      if (count($details) > 5) {
        $this->code_association = $details[5];
      }

      // Remboursement
      if (count($details) > 6) {
        $this->rembourse = $details[6];
      }

      // Charges supplémentaires
      if (count($details) > 7) {
        $this->charges_sup = $details[7];
      }

      // Gratuit
      if (count($details) > 8) {
        $this->gratuit = $details[8];
      }

      if (count($details) > 9) {
        $this->code_extension = $details[9];
      }

      if (count($details) > 10) {
        $this->extension_documentaire = $details[10];
      }

      if (count($details) > 11) {
        $this->position_dentaire = str_replace('+', '|', $details[11]);
      }

      if (count($details) > 12) {
        $this->motif_depassement = $details[12];
      }

      if (count($details) > 13) {
        $this->exoneration = $details[13];
      }

      if (count($details) > 14) {
        $this->facturable = $details[14];
        $this->facturable_auto = '0';
      }

      $this->updateFormFields();

      if ($this->facturable === null) {
        $this->facturable = 1;
      }
    }
  }

  /**
   * @see parent::getPrecodeReady()
   */
  public function getPrecodeReady(): bool {
    return $this->code_acte && $this->code_activite && $this->code_phase !== null;
  }

  /**
   * @see parent::updateFormFields()
   */
  public function updateFormFields(): void {
    parent::updateFormFields();
    $this->_modificateurs = str_split($this->modificateurs ?? '');
    CMbArray::removeValue("", $this->_modificateurs);
    $this->_dents         = explode("|", $this->position_dentaire ?? '');
    $this->_shortview = $this->code_acte;
    $this->_view      = "$this->code_acte-$this->code_activite-$this->code_phase-$this->modificateurs";
    $this->_anesth    = ($this->code_activite == 4);
    $this->_total     = round((float)$this->montant_base + (float)$this->montant_depassement, 2);

    // Remboursement exceptionnel
    $code = CDatedCodeCCAM::get($this->code_acte, $this->execution);
    $this->_rembex = $this->rembourse && $code->remboursement == 3 ? '1' : '0';
  }

  /**
   * Calcule le montant de base de l'acte
   *
   * @return float
   */
  public function updateMontantBase(): float {
    return $this->montant_base = $this->getTarif();
  }

  /**
   * Check wether acte is compatible with others already coded
   *
   * @return bool
   */
  function checkCompat() {
    if ($this->object_class == "CConsultation" || $this->_permissive) {
      return null;
    }
    $this->loadRefCodeCCAM();
    $this->getLinkedActes(true, false, true, true);

    /**
    // Cas du nombre d'actes
    // Cas général : 2 actes au plus
    $distinctCodes = array();
    foreach($this->_linked_actes as $_acte) {
      $_acte->loadRefCodeCCAM();
      if(!in_array($_acte->_ref_code_ccam->code, $distinctCodes)) {
        $distinctCodes[] = $_acte->_ref_code_ccam->code;
      }
    }
    if(count($distinctCodes) >= 2) {
      return "Vous ne pouvez pas coder plus de deux actes";
    }
    */

    // Cas des incompatibilités
    if (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") != 'allow') {
      foreach ($this->_linked_actes as $_acte) {
        $_acte->loadRefCodeCCAM();
        $_acte->_ref_code_ccam->getActesIncomp();
        $incomps = CMbArray::pluck($_acte->_ref_code_ccam->incomps, "code");
        if ($this->code_activite == $_acte->code_activite && $this->code_phase == $_acte->code_phase
            && in_array($this->code_acte, $incomps)
        ) {
          $msg = "Acte incompatible avec le codage de " . $_acte->_ref_code_ccam->code;
          if (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'block'
              || (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'blockOperationAlertOthers'
              && $this->object_class === "COperation")
          ) {
            return $msg;
          }
          else {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
            return null;
          }
        }
      }

      $this->getLinkedActes(false, false, false, true);

      // Cas des associations d'anesthésie
      if ($this->_ref_code_ccam->chapitres["1"]["rang"] == "18.01.") {
        $asso_possible = false;
        foreach ($this->_linked_actes as $_acte) {
          $_acte->loadRefCodeCCAM();
          $_acte->_ref_code_ccam->getActivites();
          $activites = CMbArray::pluck($_acte->_ref_code_ccam->activites, "numero");
          $_acte->_ref_code_ccam->getActesAsso();
          if (in_array($this->code_acte, array_keys($_acte->_ref_code_ccam->assos)) || !in_array("4", $activites)) {
              $asso_possible = true;
          }
        }
        $exceptions = explode('|', CAppUI::gconf('dPccam codage display_act_anesth_exceptions'));

        if (!$asso_possible && !in_array($this->code_acte, $exceptions)) {
          $msg = "Aucun acte codé ne permet actuellement d'associer une Anesthésie Complémentaire";
          if (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'block'
              || (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'blockOperationAlertOthers'
              && $this->object_class === "COperation")
          ) {
            return $msg;
          }
          else {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
            return null;
          }
        }
      }

      // Cas du chapitre sur la radiologie vasculaire
      if (isset($this->_ref_code_ccam->chapitres['3'])
          && $this->_ref_code_ccam->chapitres['3']['rang'] == '19.01.09.02.'
          || in_array($this->code_acte, array('YYYY033', 'YYYY300'))
      ) {
        $possible = true;
        foreach ($this->_linked_actes as $_acte) {
          $codes_incompatibles = array('YYYY033', 'YYYY300');
          if (in_array($_acte->code_acte, $codes_incompatibles) && isset($this->_ref_code_ccam->chapitres['3'])
              && $this->_ref_code_ccam->chapitres['3']['rang'] == '19.01.09.02.'
          ) {
            $possible = false;
          }
          elseif (in_array($this->code_acte, $codes_incompatibles) && isset($_acte->_ref_code_ccam->chapitres['3'])
              && $_acte->_ref_code_ccam->chapitres['3']['rang'] == '19.01.09.02.'
          ) {
            $possible = false;
          }
        }
        if (!$possible) {
          $msg = "Un acte du chapitre 19.01.09.02 (Radiologie vasculaire et imagerie interventionnelle) ne peut pas être associé avec
           les actes YYYY030 et YYYY300";
          if (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'block'
              || (CAppUI::gconf("dPsalleOp CActeCCAM check_incompatibility") == 'blockOperationAlertOthers'
              && $this->object_class === "COperation")
          ) {
            return $msg;
          }
          else {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
          }
        }
      }
    }
    return null;
  }

  /**
   * Check wether acte is facturable
   *
   * @return bool
   */
  function checkFacturable() {
    $this->completeField("facturable");

    // Si acte non facturable on met le code d'asso à aucun
    if (!$this->facturable) {
      $this->code_association = "";
      $this->modificateurs = "";
    }

    // Si on repasse le facturable à 1 on remet à la montant base à la valeur de l'acte
    if ($this->fieldModified("facturable", 1)) {
      $this->_calcul_montant_base = true;
    }
    return $this->facturable;
  }

  /**
   * Check if there is only one modifier F, U, P or S coded on the act and it's linked acts
   *
   * @return null|string
   */
  function checkExclusiveModifiers() {
    $this->getLinkedActes(true, true, true, true);

    $exclusive_modifiers = array('F', 'U', 'P', 'S', 'O');

    $count_exclusive_modifiers = count(array_intersect($this->_modificateurs, $exclusive_modifiers));
    foreach ($this->_linked_actes as $_linked_acte) {
      $count_exclusive_modifiers += count(array_intersect($_linked_acte->_modificateurs, $exclusive_modifiers));
    }

    if ($count_exclusive_modifiers > 1) {
      return CAppUI::tr('CActeCCAM-error-FUPSO_exclusive_modifiers');
    }

    $ngap_acts = $this->getLinkedActesNGAP(true, true);
    foreach ($ngap_acts as $ngap_act) {
      if (array_intersect($this->_modificateurs, $exclusive_modifiers)
          && in_array($ngap_act->complement, ['N', 'F'])
      ) {
          return CAppUI::tr('CActeCCAM-error-NGAP_exclusive_modifiers');
      }
    }

    if (in_array('T', $this->_modificateurs) && in_array('K', $this->_modificateurs)) {
      return CAppUI::tr('CActeCCAM-error-KT_exclusive_modifiers');
    }

    return null;
  }

  /**
   * @see parent::store()
   */
  public function store(): ?string {
    // Chargement du oldObject
    $oldObject = new CActeCCAM();
    $oldObject->load($this->_id);
    // On test si l'acte CCAM est facturable
    $this->checkFacturable();

    if (!$this->_id) {
      $this->coding_datetime = CMbDT::dateTime();
    }

    /* Synchronization du champ gratuit et du motif de dépassement */
    if ($this->fieldModified('gratuit')) {
      if ($this->gratuit) {
        $this->motif_depassement = 'g';
      }
      else {
        $this->motif_depassement = '';
      }
    }
    if ($this->fieldModified('motif_depassement')) {
      if ($this->motif_depassement == 'g') {
        $this->gratuit = '1';
      }
      elseif ($this->_old->motif_depassement == 'g') {
        $this->gratuit = '0';
      }
    }

    $this->updateFormFields();
    $this->completeField('object_class');
    if ((!$this->_id || $this->fieldModified('execution')) && $this->object_class !== 'CModelCodage') {
      $this->precodeModifiers(true);
    }

    // Sauvegarde du montant de base
    if ($this->_calcul_montant_base) {
      $this->updateMontantBase();
    }

    // En cas d'une modification autre que signe, on met signe à 0
    if (!$this->signe) {
      // Parcours des objets pour detecter les modifications
      $_modif = 0;
      foreach ($oldObject->getPlainFields() as $propName => $propValue) {
        if (($this->$propName !== null) && ($propValue != $this->$propName)) {
          $_modif++;
        }
      }
      if ($_modif) {
        $this->signe = 0;
      }
    }

    // Vérification de l'existence du codage
    $date = null;
    /* Complete the fields in case of a merge */
    $this->completeField('execution', 'executant_id', 'code_activite');
    if (in_array($this->object_class, ['CSejour', 'CEvenementPatient'])) {
      $date = CMbDT::date(null, $this->execution);
    }

    $codage = CCodageCCAM::get($this->loadRefObject(), $this->executant_id, $this->code_activite, $date);
    if (!$codage->_id) {
      if ($msg = $codage->store()) {
        return $msg;
      }
    }

    /* Propagation des modificateurs */
    if (($this->fieldModified('modificateurs') || !$this->_id) && $this->_spread_modifiers && CAppUI::pref('spread_modifiers')) {
      $this->_modificateurs = str_split($this->modificateurs);
      $old = $this->loadOldObject();
      $old_modifiers = str_split($old->modificateurs);
      /* Récupération des nouveaux modificateurs appliqués */
      $new_modifiers = array_diff($this->_modificateurs, $old_modifiers);

      if (!empty($new_modifiers)) {
        CActeCCAM::spreadModifiers($this, $new_modifiers);
      }
    }
      if($this->_total_saisi) {
          $this->montant_depassement = $this->_total_saisi - $this->_tarif;
      }

      // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }

    // Si on crée un nouvel acte, on relance l'analyse du codage
    if ($this->_update_codage_rule && ((!$oldObject->_id && !$this->code_association)
        || ($oldObject->_id && $this->facturable !== $oldObject->facturable && ($this->facturable_auto || $this->facturable)))
    ) {
      $codage->_update_act_amounts = false;
      $codage->updateRule(true);
      if ($msg = $codage->store()) {
        return $msg;
      }
    }

    /* We create a link between the act and the fse in creation for the linked consultation */
    if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation' && !$oldObject->_id) {
      $this->loadRefObject();
      $fses = CPyxvitalFSE::loadForConsult($this->_ref_object);

      foreach ($fses as $_fse) {
        if ($_fse->state == 'creating') {
          $_link = new CPyxvitalFSEAct();
          $_link->fse_id = $_fse->_id;
          $_link->act_class = $this->_class;
          $_link->act_id = $this->_id;

          if ($msg = $_link->store()) {
            return $msg;
          }
        }
      }
    }

    return null;
  }

  /**
   * Precode the modifiers for the act
   *
   * @return void
   */
  public function precodeModifiers(bool $force_check = false) {
    $this->loadRefCodeCCAM();
    $this->loadRefObject();

    $phase = null;
    foreach ($this->_ref_code_ccam->activites as $activite) {
      if ($activite->numero === $this->code_activite) {
        foreach ($activite->phases as $phase) {
          if ($phase->phase === $this->code_phase) {
            break;
          }
        }
      }
    }

    if ($phase) {
        foreach ($phase->_modificateurs as $modificateur) {
            if (
                is_string($modificateur->code)
                && is_string($this->modificateurs)
                && strpos($this->modificateurs, $modificateur->code) !== false
            ) {
                $modificateur->_checked = $modificateur->code;
            }
      }
      CCodageCCAM::precodeModifiers($phase->_modificateurs, $this, $this->_ref_object, $force_check);

      foreach ($phase->_modificateurs as $modificateur) {
        if ($modificateur->_checked && !in_array($modificateur->code, $this->_modificateurs)) {
          $this->_modificateurs[] = $modificateur->code;
        }
        elseif (in_array($modificateur->code, $this->_modificateurs) && $modificateur->_state === 'forbidden') {
          unset($this->_modificateurs[array_search($modificateur->code, $this->_modificateurs)]);
        }
      }
    }

    $this->modificateurs = implode('', $this->_modificateurs);
  }

  /**
   * Charge le codable asssocié
   *
   * @todo Rename as CActe::loadRefCodable()
   *
   * @return CCodable
   */
  function loadRefObject() {
    return $this->loadTargetObject();
  }

  /**
   * Charge le code CCAM complet tel que décrit par la nomenclature
   *
   * @return CDatedCodeCCAM
   */
  function loadRefCodeCCAM() {
    return $this->_ref_code_ccam = CDatedCodeCCAM::get($this->code_acte, $this->execution);
  }

  /**
   * Charge le codage CCAM associé
   *
   * @param boolean $creation If true, a new CCodageCCAM will be created if none is found
   *
   * @return CCodageCCAM|null
   */
  function loadRefCodageCCAM($creation = true) {
    $this->loadRefObject();
    if (isset($this->_ref_object) && isset($this->executant_id)) {
      return $this->_ref_codage_ccam = CCodageCCAM::get(
        $this->_ref_object,
        $this->executant_id,
        $this->code_activite,
        CMbDT::date(null, $this->execution),
        $creation
      );
    }
    return null;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  public function loadRefsFwd(): void {
    parent::loadRefsFwd();

    $this->loadRefExecutant();
    $this->loadRefCodeCCAM();
    $this->loadRefCodageCCAM();
  }

  /**
   * Load the patient from the linked object
   *
   * If the object is not a CConsultation, a CSejour or a COperation, an empty CPatient object will be returned
   * This function is used for getting the patient's context for getting the act price
   *
   * @return CPatient
   */
  public function loadRefPatient(): ?CPatient {
    $this->loadRefObject();

    if ($this->_ref_object && $this->_ref_object->_id
        && in_array($this->_ref_object->_class, array('CConsultation', 'CSejour', 'COperation'))
    ) {
      $this->_ref_patient = $this->_ref_object->loadRefPatient();
    }
    else {
      $this->_ref_patient = new CPatient();
    }

    return $this->_ref_patient;
  }

  /**
   * Trouve le code CCAM d'anesthésie associée
   *
   * @return string|null
   */
  function getAnesthAssocie() {
    if (!$this->_ref_code_ccam) {
      $this->loadRefsFwd();
    }

    if ($this->code_activite != 4 && !isset($this->_ref_code_ccam->activites[4])) {
      foreach ($this->_ref_code_ccam->assos as $code_anesth) {
        if (substr($code_anesth["code"], 0, 4) == "ZZLP") {
          $this->_anesth_associe = $code_anesth["code"];
          return $this->_anesth_associe;
        }
      }
    }
    return null;
  }

  /**
   * Charge les codes favoris d'un utilisateur
   *
   * @param string $user_id Idenfiant d'utilisateur
   * @param string $class   Classe de contexte codable
   *
   * @return string[]
   */
  function getFavoris($user_id, $class) {
    $condition = ( $class == "" ) ? "executant_id = '$user_id'" : "executant_id = '$user_id' AND object_class = '$class'";
    $sql = "SELECT code_acte, object_class, COUNT(code_acte) as nb_acte
      FROM acte_ccam
      WHERE $condition
      GROUP BY code_acte
      ORDER BY nb_acte DESC
      LIMIT 20";
    $codes = $this->_spec->ds->loadList($sql);
    return $codes;
  }

  /**
   * @inheritDoc
   */
  function getPerm($permType) {
    return $this->loadRefObject()->getPerm($permType);
  }


  /**
   * Charge les autre actes du même codable
   *
   * @param bool $same_executant  Seulement les actes du même exécutant si vrai
   * @param bool $only_facturable Seulement les actes facturables si vrai
   * @param bool $same_activite   Seulement les actes ayant la même activité (4 ou (1,2,3,5))
   * @param bool $same_day        Seulement les actes fait le même jour
   *
   * @return CActeCCAM[]
   */
  function getLinkedActes($same_executant = true, $only_facturable = true, $same_activite = false, $same_day = false) {
    $acte = new CActeCCAM();

    $where = array();
    $where["acte_id"]       = "<> '$this->_id'";
    $where["object_class"]  = "= '$this->object_class'";
    $where["object_id"]     = "= '$this->object_id'";
    if ($only_facturable) {
      $where["facturable"]    = "= '1'";
    }
    if ($same_executant) {
      $where["executant_id"]  = "= '$this->executant_id'";
    }
    if ($same_activite) {
      if ($this->code_activite == 4) {
        $where['code_activite'] = " = 4";
      }
      else {
        $where['code_activite'] = " IN(1, 2, 3, 5)";
      }
    }
    if ($same_day) {
      $begin = CMbDT::format($this->execution, '%Y-%m-%d 00:00:00');
      $end = CMbDT::format($this->execution, '%Y-%m-%d 23:59:59');
      $where['execution'] = " BETWEEN '$begin' AND '$end'";
    }

    $this->_linked_actes = $acte->loadList($where);
    return $this->_linked_actes;
  }

    /**
     * @param bool $same_executant
     * @param bool $same_day
     *
     * @return CActeNGAP[]
     * @throws \Exception
     */
    public function getLinkedActesNGAP(bool $same_executant = true, bool $same_day = false): array
    {
        $act = new CActeNGAP();

        $where = ['object_class' => " = '{$this->object_class}'", 'object_id' => " = '{$this->object_id}'"];

        if ($same_executant) {
            $where['executant_id'] = " = '{$this->executant_id}'";
        }

        if ($same_day) {
            $begin = CMbDT::format($this->execution, '%Y-%m-%d 00:00:00');
            $end = CMbDT::format($this->execution, '%Y-%m-%d 23:59:59');
            $where['execution'] = " BETWEEN '$begin' AND '$end'";
        }

        return $act->loadList($where);
    }

  /**
   * Charge l'acte CCAM de l'activité associée (4 si l'acte est une activité 1, 1 dans le cas contraire)
   *
   * @return CActeCCAM
   */
  public function loadActeActiviteAssociee() {
    $acte = new CActeCCAM();
    $acte->code_acte = $this->code_acte;
    $acte->code_phase = $this->code_phase;
    $acte->object_class = $this->object_class;
    $acte->object_id = $this->object_id;

    if ($this->code_activite == 4) {
      $acte->code_activite = 1;
    }
    else {
      $acte->code_activite = 4;
    }

    $acte->loadMatchingObject();

    return $acte;
  }

  /**
   * Calcule le tarif de base de l'acte
   *
   * @return float
   */
  function getTarifBase() {
    // Tarif de base
    $code = $this->loadRefCodeCCAM();
    $phase = $code->activites[$this->code_activite]->phases[$this->code_phase];

    $this->_tarif_base = $this->getTarifFromPhase($phase);

    return $this->_tarif_base;
  }

  /**
   * Calcul le tarif de l'acte sans association ni charge
   *
   * @param bool $add_forfait Permet de spécifier si les tarifs forfaitaires des modificateurs doivent être ajoutés ou non
   *
   * @return float
   */
  function getTarifSansAssociationNiCharge($add_forfait = true) {
    // Tarif de base
    $code = $this->loadRefCodeCCAM();
    $phase = $code->activites[$this->code_activite]->phases[$this->code_phase];
    $this->_tarif_sans_asso = $this->getTarifFromPhase($phase);
    $this->_tarif_base      = $this->_tarif_sans_asso;

    $coefficient = $this->getCoefficientModificateurs();

    $forfait = 0;
    if ($add_forfait) {
      $forfait = $this->getForfaitModificateurs();
    }

    $this->_tarif_sans_asso  = ($this->_tarif_base * $coefficient) + $forfait;

    return $this->_tarif_sans_asso;
  }

  /**
   * Return the price of the act depending on the practitioner and the patient
   *
   * @param Object $phase The phase object
   *
   * @return float
   */
  public function getTarifFromPhase($phase) {
    /* Allow to use a premade CMediusers (for example in the code selector view for displaying the differents prices) */
    if ($this->executant_id && !$this->_ref_executant) {
      $this->loadRefExecutant();
    }
    elseif (!$this->_ref_executant) {
      $this->_ref_executant = new CMediusers();
    }

    /* Allow to use a premade CPatient (for example in the code selector view for displaying the differents prices) */
    if (!$this->_ref_patient) {
      $this->loadRefPatient();
    }

    if (!$this->execution) {
      $this->execution = CMbDT::dateTime();
    }

    $grid = CContexteTarifaireCCAM::getPriceGrid($this->_ref_executant, $this->_ref_patient, $this->execution);
    $field = "tarif_g{$grid}";

    $tarif = $phase->$field;
    $dom_code = CContexteTarifaireCCAM::getDOMCode($this->_ref_executant);
    /* Application du coefficient pour les DOM/TOM */
    if ($dom_code && array_key_exists($dom_code, $phase->coeff_dom)) {
      $coeff = $phase->coeff_dom[$dom_code];
      $tarif *= $phase->coeff_dom[$dom_code];
    }

    return $tarif;
  }

  /**
   * Sum up the coefficient of all the modifiers of the act
   *
   * @return float
   */
  private function getCoefficientModificateurs() {
    $coefficient = 1.0;

    foreach ($this->_modificateurs as $modif) {
      $result = $this->getTarifModificateur($modif, $this->execution);
      if ($result['coefficient'] > 100) {
        $coefficient += ($result["coefficient"] - 100) / 100;
      }
    }

    return $coefficient;
  }

  /**
   * Sum up the flat charge of all the modifiers of the act
   *
   * @return int
   */
  private function getForfaitModificateurs() {
    $forfait = 0;

    foreach ($this->_modificateurs as $modif) {
      $result = $this->getTarifModificateur($modif, $this->execution);
      if ($result['forfait'] > 0) {
        $forfait += $result["forfait"];
      }
    }

    return $forfait;
  }

  /**
   *
   * @param string $modificateur Lettre clé du modificateur
   * @param string $date         Date de référence
   *
   * @return array
   */
  public function getTarifModificateur($modificateur, $date = null) {
    /* Allow to use a premade CMediusers (for example in the code selector view for displaying the differents prices) */
    if ($this->executant_id && !$this->_ref_executant) {
      $this->loadRefExecutant();
    }
    elseif (!$this->_ref_executant) {
      $this->_ref_executant = new CMediusers();
    }

    /* Allow to use a premade CPatient (for example in the code selector view for displaying the differents prices) */
    if (!$this->_ref_patient) {
      $this->loadRefPatient();
    }

    $date = CMbDT::date($date);

    $code = $this->loadRefCodeCCAM();

    $grid = CContexteTarifaireCCAM::getPriceGrid($this->_ref_executant, $this->_ref_patient, $this->execution);
    return $code->getForfait($modificateur, $grid, $date);
  }

  /**
   * Calcule le montant des modificateurs
   *
   * @param array $modificateurs Les modificateurs de l'acte
   *
   * @return void
   */
  function getMontantModificateurs($modificateurs) {
    if (!is_array($modificateurs)) {
      return;
    }

    $code = $this->loadRefCodeCCAM();
    $phase = $code->activites[$this->code_activite]->phases[$this->code_phase];
    $this->_tarif_base = $this->getTarifFromPhase($phase);

    foreach ($modificateurs as $_modificateur) {
      if ($_modificateur->_double == 1) {
        $tarif_modif = $this->getTarifModificateur($_modificateur->code, $this->execution);
        $_modificateur->_montant = 0;
        if ($tarif_modif['coefficient'] || $tarif_modif['forfait']) {
          $_modificateur->_montant = $this->_tarif_base * ($tarif_modif['coefficient'] - 100) / 100 + $tarif_modif['forfait'];
        }
      }
      else {
        $_montant = 0;
        for ($i = 0; $i < strlen($_modificateur->code); $i++) {
          $tarif_modif = $this->getTarifModificateur($_modificateur->code[$i], $this->execution);
          if ($tarif_modif['coefficient'] || $tarif_modif['forfait']) {
            $_montant += $this->_tarif_base * ($tarif_modif['coefficient'] - 100) / 100 + $tarif_modif['forfait'];
          }
        }
        $_modificateur->_montant = $_montant;
      }

      $_modificateur->_montant = round($_modificateur->_montant, 2);
    }
  }

  /**
   * Calcul le tarif final de l'acte
   *
   * @return float
   */
  function getTarif() {
    // Coefficient d'association
    $code = $this->loadRefCodeCCAM();

    if ($this->code_activite && !$this->gratuit && $this->facturable) {
      $this->_tarif = $this->getTarifSansAssociationNiCharge(false);
      $this->_tarif *= ($code->getCoeffAsso($this->code_association) / 100);
      $this->_tarif += $this->getForfaitModificateurs();
      // Charges supplémentaires
      $phase = $code->activites[$this->code_activite]->phases[$this->code_phase];
      if ($this->charges_sup) {
        $this->_tarif += $phase->charges;
      }
    }
    else {
      $this->_tarif = 0.0;
    }

    return $this->_tarif;
  }

  /**
   * Return the grouping code
   *
   * @return string
   */
  public function getCodeRegroupement() {
    $this->loadRefCodeCCAM();

    $classif = reset($this->_ref_code_ccam->_ref_code_ccam->_ref_activites[$this->code_activite]->_ref_classif);

    $regroupement = '';
    if ($classif instanceof CActiviteClassifCCAM) {
      $regroupement = $classif->code_regroupement;
    }

    return $regroupement;
  }

  /**
   * Création d'un item de facture avec un code ccam
   *
   * @param CFacture $facture La facture
   *
   * @return string
  **/
  function creationItemsFacture($facture) {
    $this->loadRefCodeCCAM();
    $ligne = new CFactureItem();
    $ligne->libelle       = $this->_ref_code_ccam->libelleCourt;
    $ligne->code          = "$this->code_acte $this->modificateurs";
    $ligne->type          = $this->_class;
    $ligne->object_id     = $facture->_id;
    $ligne->object_class  = $facture->_class;
    $ligne->date          = CMbDT::date($this->execution);
    $ligne->montant_base  = $this->montant_base;
    $ligne->montant_depassement = $this->montant_depassement;
    $ligne->quantite      = 1;
    $ligne->coeff         = $facture->_coeff;
    if ($msg = $ligne->store()) {
      return $msg;
    }
    return null;
  }

  /**
   * Spread the modifiers K, R and 7 to the linked acts of the given act
   *
   * @param CActeCCAM $act       The dateTime of the execution of the act
   * @param array     $modifiers The modifiers to spread
   *
   * @return void
   */
  public static function spreadModifiers(&$act, $modifiers) {
    $acts = $act->getLinkedActes(true, true, true, true);
    $codable = $act->loadRefObject();

    foreach ($acts as $_act) {
      $_act->loadRefExecutant();
      $_act->_spread_modifiers = false;
      $_act->loadRefCodeCCAM();

      foreach ($modifiers as $_modifier) {
        if (property_exists($_act->_ref_code_ccam->activites[$_act->code_activite]->phases[$_act->code_phase], '_modificateurs')) {
          $act_modifiers = $_act->_ref_code_ccam->activites[$_act->code_activite]->phases[$_act->code_phase]->_modificateurs;
          if (array_key_exists($_modifier, $act_modifiers)) {
            switch ($_modifier) {
              case '7':
                if ($_act->object_class == 'COperation' && $_act->_ref_executant->isAnesth()
                    && !in_array($_modifier, $_act->_modificateurs)
                ) {
                  $_act->modificateurs .= $_modifier;
                }
                break;
              case 'K':
                if (!$_act->montant_depassement && !in_array($_modifier, $_act->_modificateurs)) {
                  $_act->modificateurs .= $_modifier;
                }
                break;
              case 'R':
                if (!in_array($_modifier, $_act->_modificateurs)) {
                  $_act->modificateurs .= $_modifier;
                }
                break;
              default:
            }
          }
        }
      }

      $_act->store();
    }
  }

  /**
   * Load the related prescription
   *
   * @return CPrescription
   */
  public function loadRefPrescription() {
    return $this->_ref_prescription = $this->loadFwdRef("prescription_id", true);
  }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchActeCCAM($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        $target = $this->loadRefObject();
        if ($target instanceof IGroupRelated) {
            return $target->loadRelGroup();
        } elseif (isset($target->_group_id) && $target->_group_id) {
            return CGroups::get($target->_group_id);
        }

        return null;
    }
}
