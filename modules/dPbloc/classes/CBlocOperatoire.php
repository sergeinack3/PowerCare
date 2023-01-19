<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CAlert;
use Symfony\Component\Routing\RouterInterface;

/**
 * Bloc opératoire
 * Class CBlocOperatoire
 */
class CBlocOperatoire extends CMbObject {
  /** @var string */
  public const RESOURCE_TYPE = 'bloc';

  public $bloc_operatoire_id;

  // DB references
  public $group_id;

  // DB Fields
  public $nom;
  public $type;
  public $days_locked;
  public $tel;
  public $fax;
  public $use_brancardage;
  public $presence_preop_ambu;
  public $duree_preop_ambu;
  public $checklist_everyday;
  public $actif;

  /** @var CSalle[] */
  public $_ref_salles;

  /** @var CSSPI[] */
  public $_ref_sspis;

  /** @var CGroups */
  public $_ref_group;

  /** @var  CAlert[] */
  public $_alertes_intervs;

  // Form field
  public $_date_min;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'bloc_operatoire';
    $spec->key   = 'bloc_operatoire_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                        = parent::getProps();
    $props["group_id"]            = "ref notNull class|CGroups back|blocs";
    $props["nom"]                 = "str notNull seekable fieldset|default";
    $props["type"]                = "enum notNull list|chir|coro|endo|exte|obst default|chir";
    $props["days_locked"]         = "num min|0 default|0";
    $props["tel"]                 = "phone";
    $props["fax"]                 = "phone";
    $props["use_brancardage"]     = "bool default|1";
    $props["presence_preop_ambu"] = "time";
    $props["duree_preop_ambu"]    = "time";
    $props["checklist_everyday"]  = "bool default|1";
    $props["_date_min"]           = "date";
    $props['actif']               = 'bool default|1';
    return $props;
  }

  /**
   * @inheritDoc
   */
  public function getApiLink(RouterInterface $router): string {
      return $router->generate('bloc_bloc', ["bloc_operatoire_id" => $this->_id]);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Load list overlay for current group
   *
   * @param array  $where      Tableau de clauses WHERE MYSQL
   * @param string $order      paramètre ORDER SQL
   * @param null   $limit      paramètre LIMIT SQL
   * @param null   $groupby    paramètre GROUP BY SQL
   * @param array  $ljoin      Tableau de clauses LEFT JOIN SQL
   * @param array  $whereSalle Tableau de clauses WHERE MYSQL pour les salles
   *
   * @return self[]
   */
  function loadGroupList($where = array(), $order = "nom", $limit = null, $groupby = null, $ljoin = array(), $whereSalle = array()) {
    // Filtre sur l'établissement
    $g = CGroups::loadCurrent();
    $where["group_id"] = "= '$g->_id'";
    /** @var CBlocOperatoire[] $list */
    $blocs = $this->loadListWithPerms(PERM_READ, $where, $order, $limit, $groupby, $ljoin);
    CStoredObject::massLoadBackRefs($blocs, "salles", "nom", $whereSalle);
    foreach ($blocs as $bloc) {
      $bloc->loadRefsSalles($whereSalle);
    }
    return $blocs;
  }

  /**
   * Chargement de l'établissement correspondant
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    return $this->loadRefGroup();
  }

  /**
   * Chargement des salles du bloc
   *
   * @param array $where Tableau de clauses WHERE MYSQL
   *
   * @return CSalle[]
   */
    function loadRefsSalles($where = [])
    {
        $salles = $this->loadBackRefs("salles", "nom", null, null, null, null, "", $where);
        $salles = self::naturalSort($salles, ['nom']);

        return $this->_ref_salles = $salles;
    }

    /**
   * Chargement des SSPIs
   *
   * @return CSSPI[]
   */
  function loadRefsSSPIs() {
    $sspis_links = $this->loadBackRefs("links_sspi");
    return $this->_ref_sspis = CStoredObject::massLoadFwdRef($sspis_links, "sspi_id");
  }

  /**
   * Chargement des salles du bloc
   *
   * @return CSalle[]
   * @deprecated use loadRefsSalles instead
   */
  function loadRefsBack() {
    return $this->loadRefsSalles();
  }

  /**
   * Chargement des alertes sur le bloc
   *
   * @return CAlert[]
   */
  function loadRefsAlertesIntervs() {
    $this->loadRefsSalles();
    $inSalles = CSQLDataSource::prepareIn(array_keys($this->_ref_salles));
    $alerte = new CAlert();
    $ljoin = array();
    $ljoin["operations"] = "operations.operation_id = alert.object_id";
    $ljoin["plagesop"]   = "plagesop.plageop_id = operations.plageop_id";
    $where = array();
    $where["alert.object_class"] = "= 'COperation'";
    $where["alert.tag"] = "= 'mouvement_intervention'";
    $where["alert.handled"]   = "= '0'";
    $where[] = "operations.salle_id ".$inSalles.
      " OR plagesop.salle_id ".$inSalles.
      " OR (plagesop.salle_id IS NULL AND operations.salle_id IS NULL)";
    $order = "operations.date, operations.chir_id";
    return $this->_alertes_intervs = $alerte->loadList($where, $order, null, null, $ljoin);
  }

  /**
   * count the number of alerts for this bloc
   *
   * @param array $key_ids list of salle keys
   *
   * @return int
   */
  static function countAlertesIntervsForSalles($key_ids) {
    if (!count($key_ids)) {
      return 0;
    }
    $inSalles = CSQLDataSource::prepareIn($key_ids);
    $alerte = new CAlert();
    $ljoin = array();
    $ljoin["operations"] = "operations.operation_id = alert.object_id";
    $ljoin["plagesop"]   = "plagesop.plageop_id = operations.plageop_id";
    $where = array();
    $where["alert.object_class"] = "= 'COperation'";
    $where["alert.tag"] = "= 'mouvement_intervention'";
    $where["alert.handled"]   = "= '0'";
    $where[] = "operations.salle_id ".$inSalles.
      " OR plagesop.salle_id ".$inSalles.
      " OR (plagesop.salle_id IS NULL AND operations.salle_id IS NULL)";
    return $alerte->countList($where, null, $ljoin);
  }

  static function vueTopologie(&$salles, &$grille, &$listOperations, &$operations_salle, &$salles_occupe) {
    $salles_occupe = 0;
    $conf_nb_colonnes = CAppUI::gconf("dPhospi vue_topologique nb_colonnes_vue_topologique");

    $grille = array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, 0));

    CStoredObject::massLoadBackRefs($salles, "emplacement_salle");

    foreach ($salles as $_salle) {
      $emplacement_salle = $_salle->loadRefEmplacementSalle();

      if (!$emplacement_salle->_id) {
        unset($salles[$_salle->_id]);
        continue;
      }

      $_salle->loadRefBloc();
      $grille[$emplacement_salle->plan_y][$emplacement_salle->plan_x] = $_salle;

      if ($emplacement_salle->hauteur - 1) {
        for ($a = 0; $a <= $emplacement_salle->hauteur - 1; $a++) {
          if ($emplacement_salle->largeur - 1) {
            for ($b = 0; $b <= $emplacement_salle->largeur - 1; $b++) {
              if ($b != 0) {
                unset($grille[$emplacement_salle->plan_y + $a][$emplacement_salle->plan_x + $b]);
              }
              elseif ($a != 0) {
                unset($grille[$emplacement_salle->plan_y + $a][$emplacement_salle->plan_x + $b]);
              }
            }
          }
          elseif ($a < $emplacement_salle->hauteur - 1) {
            $c = $a + 1;
            unset($grille[$emplacement_salle->plan_y + $c][$emplacement_salle->plan_x]);
          }
        }
      }
      elseif ($emplacement_salle->largeur - 1) {
        for ($b = 1; $b <= $emplacement_salle->largeur - 1; $b++) {
          unset($grille[$emplacement_salle->plan_y][$emplacement_salle->plan_x + $b]);
        }
      }
      if (isset($operations_salle[$_salle->_id])) {
        $listOperations[$_salle->_id] = $operations_salle[$_salle->_id];

        //Nombre de lits occupés
        $salles_occupe += count($operations_salle[$_salle->_id]);
      }
      else {
        $listOperations[$_salle->_id] = array();
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
