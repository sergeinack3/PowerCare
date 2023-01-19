<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientSignature;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\System\CAlert;

/**
 * Salle de bloc opératoire
 * Class CSalle
 */
class CSalle extends CMbObject {
  /** @var string */
  public const RESOURCE_TYPE = 'salle';

  public $salle_id;

  // DB references
  public $bloc_id;

  // DB Fields
  public $nom;
  public $code;
  public $stats;
  public $dh;
  public $cheklist_man;
  public $color;
  public $actif;
  public $checklist_defaut_id;
  public $checklist_defaut_has;

  public $_require_check_list;

  /** @var CBlocOperatoire */
  public $_ref_bloc;

  /** @var CPlageOp[] */
  public $_ref_plages = [];

  /** @var COperation[] */
  public $_ref_urgences = [];

  /** @var COperation[] */
  public $_ref_deplacees = [];

  /** @var COperation[] */
  public $_ref_operations = [];

  /** @var  CAlert[] */
  public $_alertes_intervs;

  /** @var CPrescriptionLineElement[] */
  public $_ref_lines_dm = [];

  /** @var CPrescriptionLineElement[] */
  public $_ref_lines_dm_urgence = [];

  /** @var CEmplacementSalle */
  public $_ref_emplacement_salle;

  // Form fields
  public $_blocage = [];

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'sallesbloc';
    $spec->key   = 'salle_id';
    $spec->measureable = true;
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                         = parent::getProps();
    $props["bloc_id"]              = "ref notNull class|CBlocOperatoire back|salles";
    $props["nom"]                  = "str notNull seekable fieldset|default";
    $props["code"]                 = "str maxLength|80 fieldset|extra";
    $props["stats"]                = "bool notNull";
    $props["dh"]                   = "bool notNull default|0";
    $props["cheklist_man"]         = "bool default|0";
    $props['color']                = 'color fieldset|default';
    $props['actif']                = 'bool default|1';
    $props['checklist_defaut_id']  = 'ref class|CDailyCheckListGroup back|salles';
    $props['checklist_defaut_has'] = 'str';
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $bloc = $this->loadRefBloc();

    $this->_view      = $bloc->nom.' - '.$this->nom;
    $this->_shortview = $this->nom;
  }

  /**
   * Load list overlay for current group
   *
   * @see parent::loadGroupList()
   *
   */
  function loadGroupList($where = array(), $order = 'bloc_id, nom', $limit = null, $groupby = null, $ljoin = array()) {
    $list_blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, false);

    // Filtre sur l'établissement
    $where[] = "bloc_id ".CSQLDataSource::prepareIn(array_keys($list_blocs));

    return $this->loadList($where, $order, $limit, $groupby, $ljoin);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    $this->loadRefBloc();
    return $this->_ref_bloc->getPerm($permType) && parent::getPerm($permType);
  }

  /**
   * Chargement du bloc opératoire
   *
   * @return CBlocOperatoire
   */
  function loadRefBloc() {
    return $this->_ref_bloc = $this->loadFwdRef("bloc_id", true);
  }

  /**
   * @see parent::loadRefsFwd()
   * @deprecated
   */
  function loadRefsFwd(){
    $this->loadRefBloc();
  }

  /**
   * Charge la liste de plages et opérations pour un jour donné
   * Analogue à CMediusers::loadRefsForDay
   *
   * @param string $date        Date to look for
   * @param bool   $second_chir Use chir_2, chir_3 and chir_4
   * @param bool   $cancelled   Add the cancelled operations (for displaying them in the room timeline)
   *
   * @return void
   */
  function loadRefsForDay($date, $second_chir = false, $cancelled = false) {
    // Liste des fonctions
    $function      = new CFunctions();
    $listFunctions = $function->loadListWithPerms(PERM_READ);

    // Plages d'opérations
    $plage = new CPlageOp();
    $conf_chambre_operation = CAppUI::gconf("dPbloc affichage chambre_operation");
    $ljoin = array();

    $where = array();
    $where["plagesop.date"]     = "= '$date'";
    $where["plagesop.salle_id"] = "= '$this->_id'";
    $where[]                    = "`plagesop`.`spec_id` IS NULL
      OR `plagesop`.`spec_id` ".CSQLDataSource::prepareIn(array_keys($listFunctions));

    $order = "debut";

    $this->_ref_plages = $plage->loadList($where, $order, null, "plageop_id", $ljoin);

    // Chargement d'optimisation

    CStoredObject::massLoadFwdRef($this->_ref_plages, "plageop_id");
    CStoredObject::massLoadFwdRef($this->_ref_plages, "chir_id");
    CStoredObject::massLoadFwdRef($this->_ref_plages, "anesth_id");
    CStoredObject::massLoadFwdRef($this->_ref_plages, "spec_id");
    CStoredObject::massLoadFwdRef($this->_ref_plages, "salle_id");

    CMbObject::massLoadRefsNotes($this->_ref_plages);
    CStoredObject::massCountBackRefs($this->_ref_plages, "affectations_personnel");

    foreach ($this->_ref_plages as $_plage) {
      /** @var CPlageOp $_plage */
      if (!$_plage->loadRefChir()->canDo()->read) {
        unset($this->_ref_plages[$_plage->_id]);
        continue;
      }

      $_plage->loadRefAnesth();
      $_plage->loadRefSpec();
      $_plage->loadRefSalle();
      $_plage->makeView();
      $_plage->loadRefsOperations();
      $_plage->loadRefsNotes();
      $_plage->loadAffectationsPersonnel();
      $_plage->_unordered_operations = array();

      // Chargement d'optimisation

      CStoredObject::massLoadFwdRef($_plage->_ref_operations, "chir_id");
      $sejours = CStoredObject::massLoadFwdRef($_plage->_ref_operations, "sejour_id");
      $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        foreach ($patients as $_patient) {
            /** @var CPatient $_patient */
            $_patient->_homonyme = count($_patient->getPhoning($date));
        }
      CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
      CMbObject::massLoadRefsNotes($_plage->_ref_operations);

      foreach ($_plage->_ref_operations as $operation) {
        $operation->loadRefChirs();

        if ($second_chir
            && !$operation->_ref_chir->canDo()->read
            && !$operation->_ref_chir_2->canDo()->read
            && !$operation->_ref_chir_3->canDo()->read
            && !$operation->_ref_chir_4->canDo()->read
        ) {
          unset($_plage->_ref_operations[$operation->_id]);
          continue;
        }

        $operation->loadRefAnesth();
        $operation->loadRefPatient()->updateBMRBHReStatus($operation);
        $operation->loadExtCodesCCAM();
        $operation->loadRefPlageOp();
        $operation->loadRefsNotes();
        $operation->computeStatusPanier();
        if ($conf_chambre_operation) {
          $operation->loadRefAffectation()->updateView();
        }

        // Extraire les interventions non placées
        if ($operation->rank == 0 && !($cancelled && $operation->annulee && CAppUI::pref('planning_bloc_show_cancelled_operations'))) {
          $_plage->_unordered_operations[$operation->_id] = $operation;
          unset($_plage->_ref_operations[$operation->_id]);
        }
      }
    }

    // Interventions déplacés
    $deplacee = new COperation();
    $ljoin = array();
    $ljoin["plagesop"] = "operations.plageop_id = plagesop.plageop_id";
    $where = array();
    $where["operations.plageop_id"] = "IS NOT NULL";
    $where["plagesop.salle_id"]     = "!= operations.salle_id";
    $where["plagesop.date"]         = "= '$date'";
    $where["operations.salle_id"]   = "= '$this->_id'";
    $where[]                        = "`plagesop`.`spec_id` IS NULL
      OR `plagesop`.`spec_id` ".CSQLDataSource::prepareIn(array_keys($listFunctions));
    $order = "operations.time_operation";
    $this->_ref_deplacees = $deplacee->loadList($where, $order, null, "operation_id", $ljoin);

    // Chargement d'optimisation
    CStoredObject::massLoadFwdRef($this->_ref_deplacees, "chir_id");
    $sejours_deplacees = CStoredObject::massLoadFwdRef($this->_ref_deplacees, "sejour_id");
    CStoredObject::massLoadFwdRef($sejours_deplacees, "patient_id");
    CStoredObject::massLoadBackRefs($this->_ref_deplacees, "notes");
    foreach ($this->_ref_deplacees as $_deplacee) {
      /** @var COperation $_deplacee */
      $_deplacee->loadRefChirs();

      if ($second_chir
          && !$_deplacee->_ref_chir->canDo()->read
          && !$_deplacee->_ref_chir_2->canDo()->read
          && !$_deplacee->_ref_chir_3->canDo()->read
          && !$_deplacee->_ref_chir_4->canDo()->read
      ) {
        unset($this->_ref_deplacees[$_deplacee->_id]);
        continue;
      }

      $_deplacee->loadRefPatient();
      $_deplacee->loadExtCodesCCAM();
      $_deplacee->loadRefPlageOp();
      $_deplacee->loadRefsNotes();
    }

    // Hors plage
    $urgence = new COperation();
    $ljoin = array();
    $ljoin["plagesop"] = "operations.plageop_id = plagesop.plageop_id";
    $where = array();
    $where["operations.date"]     = "= '$date'";
    $where["operations.plageop_id"] = "IS NULL";
    $where["operations.salle_id"] = "= '$this->_id'";

    $order = "time_operation, chir_id";
    $this->_ref_urgences = $urgence->loadList($where, $order, null, "operation_id");

    // Chargement d'optimisation
    CStoredObject::massLoadFwdRef($this->_ref_urgences, "chir_id");
    $sejours_urgences = CStoredObject::massLoadFwdRef($this->_ref_urgences, "sejour_id");
    $patients = CStoredObject::massLoadFwdRef($sejours_urgences, "patient_id");
    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
    CMbObject::massLoadRefsNotes($this->_ref_urgences);

    foreach ($this->_ref_urgences as $_urgence) {
      /** @var COperation $_urgence */
      $_urgence->loadRefChirs();

      if ($second_chir
          && !$_urgence->_ref_chir->canDo()->read
          && !$_urgence->_ref_chir_2->canDo()->read
          && !$_urgence->_ref_chir_3->canDo()->read
          && !$_urgence->_ref_chir_4->canDo()->read
      ) {
        unset($this->_ref_urgences[$_urgence->_id]);
        continue;
      }

      $_urgence->loadRefsNotes();
      $_urgence->loadRefPatient()->updateBMRBHReStatus($_urgence);
      $_urgence->loadExtCodesCCAM();
      $_urgence->loadRefPlageOp();
      $_urgence->computeStatusPanier();

      if ($conf_chambre_operation) {
        $_urgence->loadRefAffectation()->updateView();
      }
    }
  }

  /**
   * Récupération des alertes sur les interventions de la salle
   *
   * @return CAlert[]
   */
  function loadRefsAlertesIntervs() {
    $alerte = new CAlert();
    $ljoin = array();
    $ljoin["operations"] = "operations.operation_id = alert.object_id";
    $ljoin["plagesop"]   = "plagesop.plageop_id = operations.plageop_id";
    $where = array();
    $where["alert.object_class"] = "= 'COperation'";
    $where["alert.tag"] = "= 'mouvement_intervention'";
    $where["alert.handled"]   = "= '0'";
    $where[] = "operations.salle_id = '$this->salle_id'
      OR plagesop.salle_id = '$this->salle_id'
      OR (plagesop.salle_id IS NULL AND operations.salle_id IS NULL)";
    $order = "operations.date, operations.chir_id";
    return $this->_alertes_intervs = $alerte->loadList($where, $order, null, null, $ljoin);
  }

  /**
   * Récupération des blocages de la salle
   *
   * @param string $date Date de vérification des blocages
   *
   * @return CBlocage[]
   */
  function loadRefsBlocages($date = "now") {
    if (!$this->_id) {
      return array();
    }

    if ($date == "now") {
      $date = CMbDT::date();
    }

    $where = array();
    $where["salle_id"] = "= '$this->_id'";
    $where[] = "'$date' BETWEEN DATE(deb) AND DATE(fin)";

    $blocage = new CBlocage();
    return $blocage->loadList($where);
  }

  /**
   * Récupération des salles activers pour les stats
   *
   * @param int $salle_id Limitation du retour à une seule salle
   * @param int $bloc_id  Limitation du retour à un seul bloc
   *
   * @return self[]
   */
  static function getSallesStats($salle_id = null, $bloc_id = null) {
    $group_id = CGroups::loadCurrent()->_id;

    $where = array();
    $where['stats'] = " = '1'";

    $ljoin = array();

    if ($salle_id) {
      $where['salle_id'] = " = '$salle_id'";
    }
    elseif ($bloc_id) {
      $where['bloc_id'] = "= '$bloc_id'";
    }
    else {
      $where['bloc_operatoire.group_id'] = "= '$group_id'";
      $ljoin['bloc_operatoire'] = 'bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id';
    }

    $salle = new self;
    return $salle->loadList($where, null, null, null, $ljoin);
  }

  /**
   * Checklist requise pour une salle
   *
   * @param string $date  Date
   * @param string $type  Type de checklist
   * @param bool   $multi ouverture en modale de la checklist
   *
   * @return bool
   */
  function requireChecklist($date, $type, $multi = false) {
    $conf_required = $this->loadRefBloc()->checklist_everyday;

    $require_check_list = ($conf_required || $this->cheklist_man) && $date >= CMbDT::date() ? 1 : 0;

    if ($require_check_list) {
      [$check_list_not_validated] = CDailyCheckList::getCheckLists($this, $date, $type, $multi);

      if ($this->cheklist_man) {
        $check_list_not_validated = 0;
      }
      if ($check_list_not_validated == 0) {
        $require_check_list = 0;
      }
    }

    return $this->_require_check_list = $require_check_list;
  }

  /**
   * Get the room ids from the preference
   *
   * @param array $salles_ids
   *
   * @return array
   * @throws Exception
   */
  static function getSallesIdsPref($salles_ids = array()) {
    if (!$salles_ids) {
      $group           = CGroups::loadCurrent();
      $pref_salles_ids = json_decode(CAppUI::pref("salles_ids_hospi"));

      // Si la préférence existe, alors on la charge
      if (isset($pref_salles_ids->{"g$group->_id"})) {
        $salles_ids = $pref_salles_ids->{"g$group->_id"};
        $salles_ids = explode("|", $salles_ids);
        CMbArray::removeValue("", $salles_ids);
      }
      // Sinon, chargement de la liste des salles en accord avec le droit de lecture
      else {
        $where             = array();
        $where["group_id"] = "= '$group->_id'";
        $where["actif"]    = "= '1'";

        $bloc_operatoire = new CBlocOperatoire();
        $blocs           = $bloc_operatoire->loadListWithPerms(PERM_READ, $where, "nom");

        foreach ($blocs as $_bloc) {
          $salles = $_bloc->loadRefsSalles(array("actif" => "= '1'"));

          foreach ($salles as $_salle) {
            $salles_ids[] = $_salle->_id;
          }
        }
      }
    }

    if (is_array($salles_ids)) {
      CMbArray::removeValue("", $salles_ids);
    }

    return $salles_ids;
  }

  /**
   * Load the room emplacements
   *
   * @return CEmplacementSalle
   */
  function loadRefEmplacementSalle() {
    $emplacement_salle = new CEmplacementSalle();
    $emplacement_salle->salle_id = $this->salle_id;
    $emplacement_salle->loadMatchingObject();

    return $this->_ref_emplacement_salle = $emplacement_salle;
  }
}
