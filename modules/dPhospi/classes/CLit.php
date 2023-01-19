<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hotellerie\CBedCleanup;

/**
 * Gère les lits d'hospitalisation
 */
class CLit extends CInternalStructure {
  static $_prefixe = null;

  // DB Table key
  public $lit_id;

  // DB References
  public $chambre_id;

  // DB Fields
  public $nom;
  public $nom_complet;
  public $annule;
  public $rank;
  public $identifie; // Type d'autorisation de lit identifié (dédié) -> pmsi

  // Form Fields
  public $_overbooking;
  public $_selected_item;
  public $_lines = [];
  public $_sexe_other_patient;
  public $_affectation_id;
  public $_sejour_id;
  public $_occupe;
  public $_occupe_dans;
  public $_occupe_dans_friendly;
  public $_prestations;

  /** @var CChambre */
  public $_ref_chambre;

  /** @var CService */
  public $_ref_service;

  /** @var CAffectation[] */
  public $_ref_affectations = [];

  /** @var CAffectation */
  public $_ref_last_dispo;

  /** @var CAffectation */
  public $_ref_next_dispo;

  /** @var CItemLiaison[] */
  public $_ref_liaisons_items = [];

  /** @var  CBedCleanup */
  public $_ref_current_cleanup;

  /** @var  CBedCleanup */
  public $_ref_last_ended_cleanup;

  /** @var  CBedCleanup */
  public $_ref_last_cleanup;

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct();
    CLit::$_prefixe = CAppUI::gconf("dPhospi CLit prefixe");
  }

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec              = parent::getSpec();
    $spec->table       = 'lit';
    $spec->key         = 'lit_id';
    $spec->measureable = true;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["user_id"]    .= " back|lits";
    $props["chambre_id"]  = "ref notNull class|CChambre seekable back|lits";
    $props["nom"]         = "str notNull seekable";
    $props["nom_complet"] = "str seekable";
    $props["annule"]      = "bool default|0";
    $props["rank"]        = "num max|999";
    $props["identifie"]   = "bool default|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function mapEntityTo() {
    $this->_name       = $this->nom;
    $this->description = $this->nom_complet;
  }

  /**
   * @inheritdoc
   */
  function mapEntityFrom() {
    if ($this->_name != null) {
      $this->nom = $this->_name;
    }
    if ($this->description != null) {
      $this->nom_complet = $this->description;
    }
  }

  /**
   * Load affectations
   *
   * @param string $date Date
   *
   * @return void
   */
  function loadAffectations($date = null) {
    if (!$date) {
      $date = CMbDT::date();
    }

    $where = array(
      "lit_id" => "= '$this->lit_id'",
      "entree" => "<= '$date 23:59:59'",
      "sortie" => ">= '$date 00:00:00'"
    );
    $order = "sortie DESC";

    $that                    = new CAffectation;
    $this->_ref_affectations = $that->loadList($where, $order);
    $this->checkDispo($date);
  }

    /**
     * @param string|null $datetime
     *
     * @return CAffectation|null
     * @throws Exception
     */
    public function loadCurrentAffectations(?string $datetime = null): ?CAffectation
    {
        if (!$datetime) {
            $datetime = CMbDT::dateTime();
        }

        $where = [
            "lit_id" => "= '$this->lit_id'",
            "entree" => "<= '$datetime'",
            "sortie" => ">= '$datetime'",
        ];
        $order = "sortie DESC";

        $affectation = new CAffectation();
        $affectation->loadObject($where, $order);

        return $affectation->_id ? $affectation : null;
    }

    function loadCurrentCleanup($date) {
    $cleanup         = new CBedCleanup();
    $order           = "cleanup_bed_id DESC";
    $where["lit_id"] = " = '$this->_id'";
    $where["date"]   = " = '$date'";
    $cleanup->loadObject($where, $order);

    return $this->_ref_current_cleanup = $cleanup;
  }

  /**
   * Charge le dernier nettoyage terminé du lit
   *
   * @return CBedCleanup
   */
  function loadLastEndedCleanup() {
    $cleanup = new CBedCleanup();
    $order   = "cleanup_bed_id DESC";
    $where   = array("lit_id" => " = '$this->_id' ");
    $where[] = " datetime_end IS NOT NULL";
    $cleanup->loadObject($where, $order);

    return $this->_ref_last_ended_cleanup = $cleanup;
  }

  /**
   * Charge le dernier nettoyage du lit
   *
   * @param string $date Date maximum
   *
   * @return CBedCleanup
   */
  function loadLastCleanup($date = null) {
    if (!CModule::getActive('hotellerie')) {
      return;
    }

    if (!$date) {
      $date = CMbDT::date();
    }

    $cleanup = new CBedCleanup();
    $order   = "cleanup_bed.date DESC, cleanup_bed.cleanup_bed_id DESC";
    $where   = array("lit_id" => " = '$this->_id' ");
    $where[] = "cleanup_bed.date <= '$date'";
    $cleanup->loadObject($where, $order);

    // Pas besoin d'afficher le lit en vert si le nettoyage est terminé depuis plus d'un jour
    if ($cleanup->status_room === "propre" && $cleanup->date !== $date) {
      $cleanup = new CBedCleanup();
    }

    // Si aucun nettoyage est trouvé, on vérifie si un nettoyage n'a pas été terminé ce jour
    if (!$cleanup->_id) {
      $where[] = "cleanup_bed.datetime_end BETWEEN '$date 00:00:00' AND '$date 23:59:59'";
      $cleanup->loadObject($where, $order);
    }

    return $this->_ref_last_cleanup = $cleanup;
  }

  function loadView() {
    parent::loadView();

    $this->loadRefService();
    $this->loadAffectations();
    $this->checkDispo();

    if (CModule::getActive('hotellerie')) {
      $this->loadLastEndedCleanup();
      $this->loadLastCleanup(CMbDT::date());
    }

    $this->loadRefsLiaisonsItems();
    foreach ($this->_ref_liaisons_items as $_lit_liaison_item) {
      $_lit_liaison_item->loadRefItemPrestation();
      $this->_prestations[] = $_lit_liaison_item->_ref_item_prestation->_view;
    }
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_shortview = $this->_view = self::$_prefixe . ($this->nom_complet ? $this->nom_complet : $this->nom);
  }

  /**
   * @inheritdoc
   */
  function loadCompleteView() {
    $chambre     = $this->loadRefChambre();
    $service     = $chambre->loadRefService();
    $this->_view = $this->nom_complet ?
      self::$_prefixe . $this->nom_complet :
      "{$service->_view} $chambre->_view - $this->_shortview";
  }

  /**
   * Load chambre
   *
   * @return CChambre
   */
  function loadRefChambre() {
    $this->_ref_chambre = $this->loadFwdRef("chambre_id", true);
    $this->_view        = $this->nom_complet ? self::$_prefixe . $this->nom_complet : "{$this->_ref_chambre->_view} - $this->_shortview";

    return $this->_ref_chambre;
  }

  /**
   * Load service
   *
   * @return CService
   */
  function loadRefService() {
    if (!$this->_ref_chambre) {
      $this->loadRefChambre();
    }

    return $this->_ref_service = $this->_ref_chambre->loadRefService();
  }

  /**
   * @inheritdoc
   * @deprecated
   */
  function loadRefsFwd() {
    $this->loadRefChambre();
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    return $this->loadRefChambre()->getPerm($permType);
  }

  /**
   * Check overbooking
   *
   * @return void
   */
  function checkOverBooking() {
    assert($this->_ref_affectations !== null);
    $this->_overbooking = 0;
    $listAff            = $this->_ref_affectations;

    foreach ($this->_ref_affectations as $aff1) {
      foreach ($listAff as $aff2) {
        /** Pas de collision si les affectations sont liées (mère et bébé) */
        if ($aff1->parent_affectation_id || $aff2->parent_affectation_id) {
          continue;
        }
        if ($aff1->affectation_id != $aff2->affectation_id) {
          if ($aff1->collide($aff2)) {
            $this->_overbooking++;
          }
        }
      }
    }
    $this->_overbooking = $this->_overbooking / 2;
  }

  /**
   * Check dispo
   *
   * @param string $date Date
   *
   * @return void
   */
  function checkDispo($date = null) {
    assert($this->_ref_affectations !== null);

    $index = "lit_id";

    if (!$date) {
      $date = CMbDT::date();
    }

    // Last Dispo
    $where = array(
      "lit_id" => "= '$this->lit_id'",
      "sortie" => "<= '$date 23:59:59'",
    );
    $order = "sortie DESC";

    $this->_ref_last_dispo = new CAffectation;
    $this->_ref_last_dispo->loadObject($where, $order, null, null, $index);
    $this->_ref_last_dispo->checkDaysRelative($date);

    // Next Dispo
    $where = array(
      "lit_id" => "= '$this->lit_id'",
      "entree" => ">= '$date 00:00:00'",
    );
    $order = "entree ASC";

    $this->_ref_next_dispo = new CAffectation;
    $this->_ref_next_dispo->loadObject($where, $order, null, null, $index);
    $this->_ref_next_dispo->checkDaysRelative($date);
  }

  static function massCheckDispo($lits = array(), $date = null) {
    if (!count($lits)) {
      return;
    }

    if (!$date) {
      $date = CMbDT::date();
    }

    // Mass loading des dispos désactivé pour cause de non performance sur myqsl <= 5.5
    /** @var CLit $_lit */
    foreach ($lits as $_lit) {
      $_lit->checkDispo($date);
    }

    return;

    $affectation = new CAffectation();
    $lits_ids = CMbArray::pluck($lits, "_id");

    // Last Dispo
    $common_where = array(
      "lit_id" => CSQLDataSource::prepareIn($lits_ids),
      "sortie" => "<= '$date 23:59:59'"
    );

    $request = new CRequest();
    $request->addSelect("MAX(sortie), lit_id");
    $request->addTable("affectation");
    $request->addWhere($common_where);
    $request->addGroup("lit_id");

    $where = $common_where;
    $where[] = "(sortie, lit_id) IN (" . $request->makeSelect() . ")";

    foreach ($affectation->loadList($where) as $_aff) {
      $_aff->checkDaysRelative($date);
      $lits[$_aff->lit_id]->_ref_last_dispo = $_aff;
    }

    // Next Dispo
    $common_where = array(
      "lit_id" => CSQLDataSource::prepareIn($lits_ids),
      "entree" => ">= '$date 00:00:00'"
    );

    $request = new CRequest();
    $request->addSelect("MIN(entree), lit_id");
    $request->addTable("affectation");
    $request->addWhere(
      array(
        "lit_id" => CSQLDataSource::prepareIn($lits_ids),
        "entree" => ">= '$date 00:00:00'"
      )
    );
    $request->addGroup("lit_id");

    $where = $common_where;
    $where[] = "(entree, lit_id) IN (" . $request->makeSelect() . ")";

    foreach ($affectation->loadList($where) as $_aff) {
      $_aff->checkDaysRelative($date);
      $lits[$_aff->lit_id]->_ref_next_dispo = $_aff;
    }

    foreach ($lits as $_lit) {
      if (!$_lit->_ref_last_dispo) {
        $_lit->_ref_last_dispo = new CAffectation();
      }
      if (!$_lit->_ref_next_dispo) {
        $_lit->_ref_next_dispo = new CAffectation();
      }
    }
  }

  /**
   * Load liaisons items
   *
   * @return CStoredObject[]|null
   */
  function loadRefsLiaisonsItems() {
    return $this->_ref_liaisons_items = $this->loadBackRefs("liaisons_items");
  }

  /**
   * Construit le tag Lit en fonction des variables de configuration
   *
   * @param int $group_id Permet de charger l'id externe d'un lit pour un établissement donné si non null
   *
   * @return string|null
   */
  static function getTagLit($group_id = null) {
    // Pas de tag Lit
    if (null == $tag_lit = CAppUI::gconf("dPhospi CLit tag")) {
      return null;
    }

    // Permettre des id externes en fonction de l'établissement
    $group = CGroups::loadCurrent();
    if (!$group_id) {
      $group_id = $group->_id;
    }

    return str_replace('$g', $group_id, $tag_lit);
  }

  /**
   * @inheritdoc
   */
  function getDynamicTag() {
    return $this->gconf("tag");
  }
}
