<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Qualite;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Fiches d'évènements indésirables
 *
 * Class CFicheEi
 */
class CFicheEi extends CMbObject {
  // DB Table key
  public $fiche_ei_id;

  // DB Fields
  public $user_id;
  public $valid_user_id;
  public $date_fiche;
  public $date_incident;
  public $date_validation;
  public $evenements;
  public $lieu;
  public $type_incident;
  public $elem_concerne;
  public $elem_concerne_detail;
  public $autre;
  public $descr_faits;
  public $mesures;
  public $descr_consequences;
  public $gravite;
  public $vraissemblance;
  public $plainte;
  public $commission;
  public $deja_survenu;
  public $degre_urgence;
  public $service_valid_user_id;
  public $service_date_validation;
  public $service_actions;
  public $service_descr_consequences;
  public $qualite_user_id;
  public $qualite_date_validation;
  public $qualite_date_verification;
  public $qualite_date_controle;
  public $suite_even;
  public $suite_even_descr;
  public $annulee;
  public $remarques;

  // Object References
  /** @var CMediusers */
  public $_ref_user;
  /** @var CMediusers */
  public $_ref_user_valid;
  /** @var CMediusers */
  public $_ref_service_valid;
  /** @var CMediusers */
  public $_ref_qualite_valid;

  // Form fields
  public $_ref_evenement;
  public $_ref_items;
  public $_etat_actuel;
  public $_criticite;
  public $_unvalidate;

  static $criticite_matrice = array(
    1 => array(1 => 1, 2 => 1, 3 => 1, 4 => 2, 5 => 2),
    2 => array(1 => 1, 2 => 2, 3 => 2, 4 => 2, 5 => 3),
    3 => array(1 => 1, 2 => 2, 3 => 2, 4 => 3, 5 => 3),
    4 => array(1 => 2, 2 => 2, 3 => 3, 4 => 3, 5 => 3),
    5 => array(1 => 3, 2 => 3, 3 => 3, 4 => 3, 5 => 3),
  );

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'fiches_ei';
    $spec->key   = 'fiche_ei_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                         = parent::getProps();
    $specs["user_id"]              = "ref notNull class|CMediusers back|users";
    $specs["date_fiche"]           = "dateTime notNull";
    $specs["date_incident"]        = "dateTime notNull";
    $specs["evenements"]           = "str notNull maxLength|255";
    $specs["lieu"]                 = "str notNull maxLength|50";
    $specs["type_incident"]        = "enum notNull list|inc|ris";
    $specs["elem_concerne"]        = "enum notNull list|pat|vis|pers|med|mat|autre";
    $specs["elem_concerne_detail"] = "text notNull";
    $specs["autre"]                = "text";
    $specs["descr_faits"]          = "text notNull";
    $specs["mesures"]              = "text notNull";
    $specs["descr_consequences"]   = "text notNull";
    $specs["suite_even"]           = "enum notNull list|trans|plong|deces|autre";
    $specs["suite_even_descr"]     = "text";
    $specs["deja_survenu"]         = "enum list|non|oui";

    //Prise en charge de la fiche
    $specs["degre_urgence"]  = "enum list|1|2|3|4";
    $specs["gravite"]        = "enum list|1|2|3|4|5";
    $specs["vraissemblance"] = "enum list|1|2|3|4|5";
    $specs["plainte"]        = "enum list|non|oui";
    $specs["commission"]     = "enum list|non|oui";
    $specs["annulee"]        = "bool";
    $specs["remarques"]      = "text";

    //1ere Validation Qualité
    $specs["valid_user_id"]   = "ref class|CMediusers back|valid_users";
    $specs["date_validation"] = "dateTime";

    //Validation Chef de Projet
    $specs["service_valid_user_id"]      = "ref class|CMediusers back|service_valid_users";
    $specs["service_date_validation"]    = "dateTime";
    $specs["service_actions"]            = "text";
    $specs["service_descr_consequences"] = "text";

    //2nde Validation Qualité
    $specs["qualite_user_id"]           = "ref class|CMediusers back|qualite_users";
    $specs["qualite_date_validation"]   = "dateTime";
    $specs["qualite_date_verification"] = "date";
    $specs["qualite_date_controle"]     = "date";

    return $specs;
  }

  /**
   * Chargement de l'auteur de la fiche
   *
   * @return CMediusers
   * @throws \Exception
   */
  function loadRefsAuthor() {
    $this->_ref_user = new CMediusers();
    $this->_ref_user->load($this->user_id);
    $this->_ref_user->loadRefFunction();

    return $this->_ref_user;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    // Forward references
    $this->loadRefsAuthor();

    $this->_ref_user_valid = new CMediusers();
    if ($this->valid_user_id) {
      $this->_ref_user_valid->load($this->valid_user_id);
    }
    $this->_ref_service_valid = new CMediusers();
    if ($this->service_valid_user_id) {
      $this->_ref_service_valid->load($this->service_valid_user_id);
    }
    $this->_ref_qualite_valid = new CMediusers();
    if ($this->qualite_user_id) {
      $this->_ref_qualite_valid->load($this->qualite_user_id);
    }
  }

  /**
   * Calcul de la criticité
   *
   * @return int
   */
  function loadCriticite() {
    if ($this->gravite && $this->vraissemblance) {
      $this->_criticite = self::$criticite_matrice[$this->gravite][$this->vraissemblance];
    }

    return $this->_criticite;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    if ($this->evenements) {
      $this->_ref_evenement = explode('|', $this->evenements);
    }

    if ($this->qualite_date_controle) {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-CTRL_OK");
    }
    elseif (!$this->service_date_validation && $this->service_valid_user_id) {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-ATT_CS_adm");
    }
    elseif (!$this->qualite_user_id) {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-ATT_QUALITE_adm");
    }
    elseif (!$this->qualite_date_validation) {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-ATT_QUALITE_adm");
    }
    elseif (!$this->qualite_date_verification) {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-ATT_VERIF");
    }
    else {
      $this->_etat_actuel = CAppUI::tr("_CFicheEi_acc-ATT_CTRL");
    }

    $this->loadCriticite();

    $this->_view = sprintf("%03d - %s", $this->fiche_ei_id, CMbDT::dateToLocale(substr($this->date_fiche, 0, 10)));
  }

  /**
   * @see parent::store()
   */
  function store() {
    if ($this->_unvalidate) {
      $this->valid_user_id           = "";
      $this->date_validation         = "";
      $this->service_valid_user_id   = "";
      $this->service_date_validation = "";
      $this->qualite_user_id         = "";
      $this->qualite_date_validation = "";
      $this->_unvalidate             = null;
    }

    return parent::store();
  }

  function loadRefItems() {
    $this->_ref_items = array();
    foreach ($this->_ref_evenement as $evenement) {
      $ext_item = new CEiItem();
      $ext_item->load($evenement);
      $this->_ref_items[] = $ext_item;
    }
  }

  /**
   * @see parent::canDeleteEx()
   */
  function canDeleteEx() {
    return CAppUI::tr("CFicheEi-msg-canDelete");
  }

  /**
   * Load list overlay for current group
   *
   * @param array  $where     tablea de paramètres WHERE SQL
   * @param string $order     paramètre ORDER SQL
   * @param string $limit     paramètre LIMIT SQL
   * @param string $group     paramètre GROUP BY SQL
   * @param array  $ljoin     tableau de paramètres LEFT JOIN SQL
   * @param bool   $countOnly retourne seulement le nombre de résultats
   *
   * @return CFicheEi[]|int
   * @throws \Exception
   */
  function loadGroupList($where = array(), $order = null, $limit = null, $group = null, $ljoin = array(), $countOnly = false) {
    $ljoin["users_mediboard"]     = "users_mediboard.user_id = fiches_ei.user_id";
    $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";
    // Filtre sur l'établissement
    $g                                     = CGroups::loadCurrent();
    $where["functions_mediboard.group_id"] = "= '$g->_id'";

    return $countOnly ?
      $this->countList($where, $group, $ljoin) :
      $this->loadList($where, $order, $limit, $group, $ljoin);
  }

  /**
   * Chargement des fiches selon l'état
   *
   * @param string $etat          état de la fiche
   * @param int    $user_id       rédacteur de la fiche
   * @param array  $where_termine liste de paramètres WHERE SQL
   * @param int    $annule        inclusion des annulés
   * @param bool   $countOnly     retourner uniquement le nombre de fiches
   * @param int    $first         début de la liste
   * @param bool   $noLimit       pas de paramètre LIMIT SQL
   *
   * @return CFicheEi[]|int
   * @throws \Exception
   */
  static function loadFichesEtat(
      $etat, $user_id = null, $where_termine = array(), $annule = 0,
      $countOnly = false, $first = 0, $noLimit = false
  ) {
    $where            = array();
    $where["annulee"] = "= '$annule'";

    switch ($etat) {
      case "AUTHOR":
        $where["fiches_ei.user_id"] = "= '$user_id'";
        break;
      case "VALID_FICHE":
        $where["fiches_ei.date_validation"] = " IS NULL";
        break;
      case "ATT_CS":
        $where["fiches_ei.date_validation"]         = " IS NOT NULL";
        $where["fiches_ei.service_date_validation"] = " IS NULL";
        if ($user_id) {
          $where["fiches_ei.service_valid_user_id"] = "= '$user_id'";
        }
        break;
      case "ATT_QUALITE":
        $where["fiches_ei.service_date_validation"] = " IS NOT NULL";
        $where["fiches_ei.qualite_date_validation"] = " IS NULL";
        if ($user_id) {
          $where["fiches_ei.service_valid_user_id"] = "= '$user_id'";
        }
        break;
      case "ATT_VERIF":
        $where["fiches_ei.qualite_date_validation"]   = " IS NOT NULL";
        $where["fiches_ei.qualite_date_verification"] = " IS NULL";
        $where["fiches_ei.qualite_date_controle"]     = " IS NULL";
        break;
      case "ATT_CTRL":
        $where["fiches_ei.qualite_date_verification"] = " IS NOT NULL";
        $where["fiches_ei.qualite_date_controle"]     = " IS NULL";
        break;
      case "ALL_TERM":
        if ($user_id) {
          $where["fiches_ei.service_valid_user_id"]   = "= '$user_id'";
          $where["fiches_ei.qualite_date_validation"] = " IS NOT NULL";
        }
        else {
          $where["fiches_ei.qualite_date_controle"] = " IS NOT NULL";
        }
        break;
      case "ANNULE":
        $where["annulee"] = "= '1'";
        break;
    }
    $where = array_merge($where, $where_termine);
    $order = "fiches_ei.date_incident DESC, fiches_ei.fiche_ei_id DESC";
    $fiche = new CFicheEi();
    if ($countOnly) {
      return $fiche->loadGroupList($where, null, null, null, null, true);
    }
    else {
      $listFiches = $fiche->loadGroupList($where, $order, $noLimit ? null : ($first + 0) . ',20');
      foreach ($listFiches as $_fiche) {
        $_fiche->loadRefsFwd();
      }

      return $listFiches;
    }
  }
}
