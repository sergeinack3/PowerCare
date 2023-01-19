<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mpm\CConfigMomentUnitaire;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Repas\CTypeRepas;
use Ox\Mediboard\Repas\CValidationRepas;
use Ox\Mediboard\Urgences\CRPU;
use Symfony\Component\Routing\RouterInterface;

/**
 * Gère les services d'hospitalisation
 * - contient de chambres
 */
class CService extends CInternalStructure {
  /** @var string */
  const RESOURCE_TYPE = 'service';

  // DB Table key
  public $service_id;

  // DB references
  public $group_id;
  public $responsable_id;
  public $secteur_id;

  // DB Fields
  public $nom;
  public $type_sejour;
  public $cancelled;
  public $hospit_jour;
  public $urgence;
  public $uhcd;
  public $externe;
  public $neonatalogie;
  public $radiologie;
  public $obstetrique;
  public $usc;
  public $default_orientation;
  public $default_destination;
  public $is_soins_continue;
  public $use_brancardage;
  public $max_ambu_per_day;
  public $max_hospi_per_day;
  public $tel;

  /** @var CChambre[] */
  public $_ref_chambres = [];

  /** @var CGroups */
  public $_ref_group;
  /** @var CSecteur */
  public $_ref_secteur;

  /** @var CValidationRepas[] */
  public $_ref_validrepas = [];

  /** @var  @var CAffectation[] */
  public $_ref_affectations = [];

  /** @var  @var CAffectation[] */
  public $_ref_affectations_couloir = [];

  /** @var CUniteFonctionnelle[] */
  public $_ref_ufs = [];

  /** @var CUniteFonctionnelle */
  public $_ref_uf_soins;

  /** @var CAffectationUniteFonctionnelle[] */
  public $_ref_affectations_uf;

  /** @var CAffectationUserService */
  public $_ref_responable_jour;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'service';
    $spec->key   = 'service_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('hospi_service', ["service_id" => $this->_id]);
    }

  /**
   * @inheritDoc
   *
   * ATTENTION : Ne pas appeler de configurations par établissement dans cette fonction
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["user_id"]       .= " back|services_entity";
    $props["group_id"]       = "ref notNull class|CGroups back|services";
    $props["responsable_id"] = "ref class|CMediusers back|services";
    $props["secteur_id"]     = "ref class|CSecteur back|services";

    $props["type_sejour"] = 'enum list|' . implode("|", CSejour::$types) . ' default|ambu';

    $props["nom"]                 = "str notNull seekable fieldset|default";
    $props["urgence"]             = "bool default|0";
    $props["uhcd"]                = "bool default|0";
    $props["hospit_jour"]         = "bool default|0";
    $props["externe"]             = "bool default|0";
    $props["cancelled"]           = "bool default|0";
    $props["neonatalogie"]        = "bool default|0";
    $props["radiologie"]          = "bool default|0";
    $props["obstetrique"]         = "bool default|0";
    $props["usc"]                 = "bool default|0";
    $props["default_orientation"] = "enum list|" . implode("|", CRPU::$orientation_value);
    $props["default_destination"] = "enum list|" . implode("|", CSejour::$destination_values);
    $props["is_soins_continue"]   = "bool default|0 notNull";
    $props["use_brancardage"]     = "bool default|1";
    $props['max_ambu_per_day']    = 'num';
    $props['max_hospi_per_day']   = 'num';
    $props["tel"]                 = "numchar";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * @see parent::mapEntityTo()
   */
  function mapEntityTo() {
    $this->_name   = $this->nom;
    $this->user_id = $this->responsable_id;
  }

  /**
   * @see parent::mapEntityFrom()
   */
  function mapEntityFrom() {
    if ($this->_name != null) {
      $this->nom = $this->_name;
    }
    if ($this->user_id != null) {
      $this->responsable_id = $this->user_id;
    }
  }

  /**
   * @see parent::store()
   */
  function store() {
    $is_new = !$this->_id;

    if ($msg = parent::store()) {
      return $msg;
    }

    if ($is_new) {
      CConfigMomentUnitaire::emptySHM();
    }

    return null;
  }

  /**
   * Load list overlay for current group
   *
   * @param array  $where   Where clause
   * @param string $order   Order clause
   * @param null   $limit   Limit clause
   * @param null   $groupby Group by clause
   * @param array  $ljoin   Left join clause
   *
   * @return self[]
   */
  function loadGroupList($where = array(), $order = 'nom', $limit = null, $groupby = null, $ljoin = array()) {
    // Filtre sur l'établissement
    $group             = CGroups::loadCurrent();
    $where["group_id"] = "= '$group->_id'";

    return $this->loadList($where, $order, $limit, $groupby, $ljoin);
  }

  /**
   * Chargements des chambres du service
   *
   * @param bool $annule Charge les chambres desactivées aussi
   *
   * @return CChambre[]
   */
  function loadRefsChambres($annule = true) {
    $chambre = new CChambre();
    $where   = array(
      "service_id" => "= '$this->_id'",
    );

    if (!$annule) {
      $where["annule"] = "= '0'";
    }
    $order = "ISNULL(rank), rank, nom";

    return $this->_ref_chambres = $this->_back["chambres"] = $chambre->loadList($where, $order, null, null, null, null, null, false);
  }

  /**
   *
   */
  function loadRefsLits($annule = false) {
    $lit = new CLit();

    $where = array();
    $ljoin = array();

    $where["chambre.service_id"] = "= '$this->_id'";

    $ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";

    if (!$annule) {
      $where["lit.annule"]     = "= '0'";
      $where["chambre.annule"] = "= '0'";
    }

    $order = "ISNULL(chambre.rank), chambre.rank, chambre.nom, ISNULL(lit.rank), lit.rank,lit.nom ";

    $lits                = $lit->loadList($where, $order, null, null, $ljoin, null, null, false);
    $this->_ref_chambres = self::massLoadFwdRef($lits, "chambre_id", null, true);

    foreach ($lits as $_lit) {
      $_chambre                        = $_lit->loadRefChambre();
      $_chambre->_ref_service          = $this;
      $_chambre->_ref_lits[$_lit->_id] = $_lit;
    }

    return $lits;
  }

  /**
   * Load affectations
   *
   * @param string $date               Date
   * @param bool   $with_effectue      Avec effectue
   * @param bool   $with_couloir       Avec couloir
   * @param bool   $with_sortie_reelle Avec sortie reelle
   *
   * @return CAffectation[]
   */
  function loadRefsAffectations($date, $with_effectue = true, $with_couloir = true, $with_sortie_reelle = false) {
    $ljoin = array();
    $where = array(
      "affectation.service_id" => "= '$this->_id'",
      "affectation.entree"     => (strpos($date, ":") === false) ? "<= '$date 23:59:59'" : "<= '$date'",
      "affectation.sortie"     => (strpos($date, ":") === false) ? ">= '$date 00:00:00'" : ">= '$date'"
    );

    if (!$with_effectue) {
      if ($with_sortie_reelle) {
        $complement = "";
        if ($date == CMbDT::date()) {
          $ljoin["sejour"] = "affectation.sejour_id = sejour.sejour_id";
          $complement      = "OR (sejour.sortie_reelle >= '" . CMbDT::dateTime() . "' AND affectation.sortie >= '" . CMbDT::dateTime() . "')";
        }
        $where[] = "affectation.effectue = '0' $complement";
      }
      else {
        $where["affectation.effectue"] = "= '0'";
      }
    }

    if (!$with_couloir) {
      $where["affectation.lit_id"] = "IS NOT NULL";
    }

    $order = "affectation.sortie DESC";

    return $this->_ref_affectations = $this->loadBackRefs('affectations', $order, null, null, $ljoin, null, '', $where);
  }

  /**
   * Charge les affectations situées dans un couloir du service
   *
   * @param string $date               Date
   * @param bool   $with_effectue      Avec effectue
   * @param bool   $with_sortie_reelle Avec sortie reelle
   *
   * @return CAffectation[]
   */
  function loadRefsAffectationsCouloir($date, $with_effectue = true, $with_sortie_reelle = false) {
    $ljoin = array();
    $where = array(
      "affectation.service_id" => "= '$this->_id'",
      "affectation.entree"     => "<= '$date 23:59:59'",
      "affectation.sortie"     => ">= '$date 00:00:00'"
    );

    if (!$with_effectue) {
      if ($with_sortie_reelle) {
        $complement = "";
        if ($date == CMbDT::date()) {
          $ljoin["sejour"] = "affectation.sejour_id = sejour.sejour_id";
          $complement      = "OR (sejour.sortie_reelle >= '" . CMbDT::dateTime() . "' AND affectation.sortie >= '" . CMbDT::dateTime() . "')";
        }
        $where[] = "affectation.effectue = '0' $complement";
      }
      else {
        $where["affectation.effectue"] = "= '0'";
      }
    }

    $where["affectation.lit_id"] = "IS NULL";

    $order = "affectation.sortie DESC";

    $affectation = new CAffectation();

    return $this->_ref_affectations_couloir = $affectation->loadList($where, $order, null, null, $ljoin);
  }

  /**
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @return CSecteur
   */
  function loadRefSecteur() {
    return $this->_ref_secteur = $this->loadFwdRef("secteur_id", true);
  }

  /**
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    $this->loadRefsChambres();
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefGroup();
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_group) {
      $this->loadRefsFwd();
    }

    return (CPermObject::getPermObject($this, $permType) && $this->_ref_group->getPerm($permType));
  }

  function validationRepas($date, $listTypeRepas = null) {
    $this->_ref_validrepas[$date] = array();
    $validation                   =& $this->_ref_validrepas[$date];
    if (!$listTypeRepas) {
      $listTypeRepas = new CTypeRepas;
      $order         = "debut, fin, nom";
      $listTypeRepas = $listTypeRepas->loadList(null, $order);
    }

    $where               = array();
    $where["date"]       = $this->_spec->ds->prepare(" = %", $date);
    $where["service_id"] = $this->_spec->ds->prepare(" = %", $this->service_id);
    foreach ($listTypeRepas as $keyType => $typeRepas) {
      $where["typerepas_id"] = $this->_spec->ds->prepare("= %", $keyType);
      $validrepas            = new CValidationRepas;
      $validrepas->loadObject($where);
      $validation[$keyType] = $validrepas;
    }
  }

  /**
   * Charge les services d'urgence de l'établissement courant
   *
   * @return CService[]
   */
  static function loadServicesUrgence() {
    $service            = new CService();
    $service->group_id  = CGroups::loadCurrent()->_id;
    $service->urgence   = "1";
    $service->cancelled = "0";
    /** @var CService[] $services */
    $services = $service->loadMatchingList();
    foreach ($services as $_service) {
      $_service->loadRefsChambres(false);
      foreach ($_service->_ref_chambres as $_chambre) {
        $_chambre->loadRefsLits();
      }
    }

    return $services;
  }

  /**
   * Charge les services d'UHCD de l'établissement courant
   *
   * @return CService[]
   */
  static function loadServicesUHCD() {
    $service            = new CService();
    $service->group_id  = CGroups::loadCurrent()->_id;
    $service->uhcd      = "1";
    $service->cancelled = "0";
    /** @var CService[] $services */
    $services = $service->loadMatchingList();
    foreach ($services as $_service) {
      $_service->loadRefsChambres(false);
      foreach ($_service->_ref_chambres as $_chambre) {
        $_chambre->loadRefsBack();
      }
    }

    return $services;
  }

  /**
   * Charge les services d'obstétriquede l'établissement courant
   *
   * @param boolean $with_lits Charge les lits
   * @param int $group_id      Etablissement optionnel
   *
   * @return CService[]
   */
  static function loadServicesObstetrique($with_lits = true, $group_id = null) {
    $service              = new CService();
    $service->group_id    = $group_id ?: CGroups::loadCurrent()->_id;
    $service->obstetrique = "1";
    $service->cancelled   = "0";
    /** @var CService[] $services */
    $services = $service->loadMatchingList("nom");

    if ($with_lits) {
      foreach ($services as $_service) {
        $_service->loadRefsChambres(false);
        foreach ($_service->_ref_chambres as $_chambre) {
          $_chambre->loadRefsLits();
        }
      }
    }

    return $services;
  }

  /**
   * Charge les services d'UHCD de l'établissement courant
   *
   * @return CService[]
   */
  static function loadServicesImagerie() {
    $service             = new CService();
    $service->group_id   = CGroups::loadCurrent()->_id;
    $service->radiologie = "1";
    $service->cancelled  = "0";
    /** @var CService[] $services */
    $services = $service->loadMatchingList('nom ASC');

    foreach ($services as $_service) {
      $chambres = $_service->loadRefsChambres(false);
      foreach ($chambres as $_chambre) {
        $_chambre->loadRefsLits();
      }
    }

    return $services;
  }

  /**
   * Charge les services d'UHCD et d'urgence de l'établissement courant
   *
   * @return CService[]
   */
  static function loadServicesUHCDRPU() {
    $where              = array();
    $clause             = "uhcd = '1' OR urgence = '1'";
    $where[]            = $clause;
    $where["cancelled"] = " = '0'";
    $service            = new CService();
    /** @var CService[] $services */
    $services = $service->loadGroupList($where);
    foreach ($services as $_service) {
      $_service->loadRefsChambres(false);
      foreach ($_service->_ref_chambres as $_chambre) {
        $_chambre->loadRefsLits();
      }
    }

    return $services;
  }

  /**
   * Charge les services externes de l'établissement
   *
   * @param string $group_id Group
   *
   * @return CService
   */
  static function loadServiceExterne($group_id = null) {
    $service            = new CService();
    $service->group_id  = $group_id ? $group_id : CGroups::loadCurrent()->_id;
    $service->externe   = "1";
    $service->cancelled = "0";
    $service->loadMatchingObject();

    return $service;
  }

  /**
   * Charge le service de radiologie de l'établissement
   *
   * @param string $group_id Group
   *
   * @return CService
   */
  static function loadServiceRadiologie($group_id = null) {
    $service             = new CService();
    $service->group_id   = $group_id ? $group_id : CGroups::loadCurrent()->_id;
    $service->radiologie = "1";
    $service->cancelled  = "0";
    $service->loadMatchingObject();

    return $service;
  }

  function loadListWithPerms($permType = PERM_READ, $where = array(), $order = "nom", $limit = null, $group = null, $ljoin = null, bool $strict = true) {
    if ($where !== null && !isset($where["group_id"])) {
      $where["group_id"] = "='" . CGroups::loadCurrent()->_id . "'";
    }

    return parent::loadListWithPerms($permType, $where, $order, $limit, $group, $ljoin, $strict);
  }

  /**
   * Construit le tag Service en fonction des variables de configuration
   *
   * @param int $group_id Permet de charger l'id externe d'un Service pour un établissement donné si non null
   *
   * @return string
   */
  static function getTagService($group_id = null) {
    // Pas de tag Mediusers
    if (null == $tag_service = CAppUI::gconf("dPhospi General tag_service")) {
      return;
    }

    // Permettre des id externes en fonction de l'établissement
    $group = CGroups::loadCurrent();
    if (!$group_id) {
      $group_id = $group->_id;
    }

    return str_replace('$g', $group_id, $tag_service);
  }

  /**
   * @see parent::getDynamicTag
   */
  function getDynamicTag() {
    return CAppUI::gconf("dPhospi General tag_service");
  }

  /**
   * @return CAffectationUniteFonctionnelle[]
   */
  function loadRefAffectationsUF() {
    /** @var CAffectationUniteFonctionnelle[] $affectations_uf */
    $affectations_uf = $this->loadBackRefs("ufs");
    CStoredObject::massLoadFwdRef($affectations_uf, "uf_id");

    foreach ($affectations_uf as $_aff_uf) {
      $_aff_uf->loadRefUniteFonctionnelle();
    }

    return $this->_ref_affectations_uf = $affectations_uf;
  }

  /**
   * @return CUniteFonctionnelle[]
   */
  function loadRefsUFs() {
    if ($this->_ref_ufs === null) {
      $affectations_uf = $this->loadRefAffectationsUF();

      $this->_ref_ufs = array();
      foreach ($affectations_uf as $_affectation_uf) {
        $_uf                       = $_affectation_uf->_ref_uf;
        $this->_ref_ufs[$_uf->_id] = $_uf;
      }
    }

    return $this->_ref_ufs;
  }

  /**
   * @return CUniteFonctionnelle|null
   */
  function loadRefUFSoins() {
    $ufs = $this->loadRefsUFs();

    $this->_ref_uf_soins = null;
    foreach ($ufs as $_uf) {
      if ($_uf->type === "soins") {
        $this->_ref_uf_soins = $_uf;
      }
    }

    return $this->_ref_uf_soins;
  }

  /**
   * @param $date string Date du jour où l'on veut récupérer le responsable
   *
   * @return CAffectationUserService
   * @throws \Exception
   */
  function loadRefResponsable($date) {
    $responsable             = new CAffectationUserService();
    $responsable->date       = $date;
    $responsable->service_id = $this->_id;
    $responsable->loadMatchingObject();
    $responsable->loadRefUser()->loadRefFunction();

    return $this->_ref_responable_jour = $responsable;
  }

  static function getServicesIdsPref($services_ids = array()) {
    global $m;

    // Détection du changement d'établissement
    $group_id = CValue::get("g");

    if (!$services_ids || $group_id) {
      $group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

      $pref_services_ids = json_decode(CAppUI::pref("services_ids_hospi"));

      // Si la préférence existe, alors on la charge
      if (isset($pref_services_ids->{"g$group_id"})) {
        $services_ids = $pref_services_ids->{"g$group_id"};
        $services_ids = explode("|", $services_ids);
        CMbArray::removeValue("", $services_ids);
      }
      // Sinon, chargement de la liste des services en accord avec le droit de lecture
      else {
        $service            = new CService();
        $where              = array();
        $where["group_id"]  = "= '" . CGroups::loadCurrent()->_id . "'";
        $where["cancelled"] = "= '0'";
        $services_ids       = array_keys($service->loadListWithPerms(PERM_READ, $where, "externe, nom"));

        // On ne tient pas compte de cette liste si on souhaite être présélectionné depuis le dossier de soins
        if ($m == "soins" && CAppUI::pref("preselect_me_care_folder")) {
          $services_ids = array();
        }
      }
    }

    if (is_array($services_ids)) {
      CMbArray::removeValue("", $services_ids);
    }

    $save_m = $m;
    foreach (array("dPhospi", "dPadmissions", "soins", "appFineClient", "dPboard", "ssr") as $_module) {
      $m = $_module;
      CValue::setSession("services_ids", $services_ids);
    }
    $m = $save_m;

    return $services_ids;
  }

  static function vueTopologie(&$chambres, &$grille, &$listSejours, &$sejours_chambre, &$lits_occupe) {
    $lits_occupe = 0;
    $conf_nb_colonnes = CAppUI::gconf("dPhospi vue_topologique nb_colonnes_vue_topologique");

    $grille = array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, 0));

    $to_mass_load = array_filter($chambres, function ($chambre): bool {
        return $chambre instanceof CChambre;
    });

    CStoredObject::massLoadBackRefs($to_mass_load, "emplacement");
    CStoredObject::massLoadBackRefs($chambres, 'lits', "ISNULL(lit.rank), lit.rank", ["annule" => " ='0'"], [], '', false);

    foreach ($chambres as $chambre_id => $chambre) {
        $save_chambre = $chambre;

        if (!$chambre instanceof CChambre) {
            $chambre = CChambre::findOrNew($chambre_id);
        }

        $chambre->loadRefsLits();
        $chambre->loadRefEmplacement();
        if (!count($chambre->_ref_lits) || !$chambre->_ref_emplacement->_id) {
            unset($chambres[$chambre->_id]);
            continue;
        }
        $chambre->loadRefService();

        // $save_chambre can be a string (e.g. blocked bedroom)
        if (!$save_chambre instanceof CChambre) {
            $grille[$chambre->_ref_emplacement->plan_y][$chambre->_ref_emplacement->plan_x] = $save_chambre;
        } else {
            $grille[$chambre->_ref_emplacement->plan_y][$chambre->_ref_emplacement->plan_x] = $chambre;
        }

      $emplacement = $chambre->_ref_emplacement;

      if ($emplacement->hauteur - 1) {
        for ($a = 0; $a <= $emplacement->hauteur - 1; $a++) {
          if ($emplacement->largeur - 1) {
            for ($b = 0; $b <= $emplacement->largeur - 1; $b++) {
              if ($b != 0) {
                unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
              }
              elseif ($a != 0) {
                unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
              }
            }
          }
          elseif ($a < $emplacement->hauteur - 1) {
            $c = $a + 1;
            unset($grille[$emplacement->plan_y + $c][$emplacement->plan_x]);
          }
        }
      }
      elseif ($emplacement->largeur - 1) {
        for ($b = 1; $b <= $emplacement->largeur - 1; $b++) {
          unset($grille[$emplacement->plan_y][$emplacement->plan_x + $b]);
        }
      }
      if (isset($sejours_chambre[$chambre->_id])) {
        $listSejours[$chambre->_id] = $sejours_chambre[$chambre->_id];

        //Nombre de lits occupés
        $lits_occupe += count($sejours_chambre[$chambre->_id]);

        // Retrait des réservations
        if ($chambre->_ref_service->urgence || $chambre->_ref_service->uhcd) {
          $lit_ids_resa = CMbArray::pluck($sejours_chambre[$chambre->_id], "_ref_rpu", "_ref_reservation", "lit_id");
          CMbArray::removeValue("", $lit_ids_resa);
          foreach (array_keys($chambre->_ref_lits) as $_lit_id) {
            // le 2ème paramètre permet de retrouver le nombre d'occurrences du lit
            $lits_occupe -= count(array_keys($lit_ids_resa, $_lit_id));
          }
        }
      }
      else {
        $listSejours[$chambre->_id] = array();
      }
    }

    //Traitement des lignes vides
    $nb    = 0;
    $total = 0;

    foreach ($grille as $j => $value) {
      $nb = 0;
      foreach ($value as $i => $valeur) {
        if ($valeur == "0") {
          if ($j == 0 || $j == 9) {
            $nb++;
          }
          elseif (
            !isset($grille[$j - 1][$i]) ||
            $grille[$j - 1][$i] == "0" ||
            !isset($grille[$j + 1][$i]) ||
            $grille[$j + 1][$i] == "0"
          ) {
            $nb++;
          }
        }
      }

      //suppression des lignes inutiles
      if ($nb == $conf_nb_colonnes) {
        unset($grille[$j]);
      }
    }

    //Traitement des colonnes vides
    for ($i = 0; $i < $conf_nb_colonnes; $i++) {
      $nb    = 0;
      $total = 0;
      for ($j = 0; $j < $conf_nb_colonnes; $j++) {
        $total++;
        if (!isset($grille[$j][$i]) || $grille[$j][$i] == "0") {
          if ($i == 0 || $i == 9) {
            $nb++;
          }
          elseif (
            !isset($grille[$j][$i - 1]) ||
            $grille[$j][$i - 1] == "0" ||
            !isset($grille[$j][$i + 1]) ||
            $grille[$j][$i + 1] == "0"
          ) {
            $nb++;
          }
        }
      }
      //suppression des colonnes inutiles
      if ($nb == $total) {
        for ($a = 0; $a < $conf_nb_colonnes; $a++) {
          unset($grille[$a][$i]);
        }
      }
    }
  }
}
