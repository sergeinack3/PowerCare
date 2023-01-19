<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Listes de choix
 */
class CListeChoix extends CMbObject {
  // DB Table key
  public $liste_choix_id;

  // DB References
  public $user_id; // not null when associated to a user
  public $function_id; // not null when associated to a function
  public $group_id; // not null when associated to a group

  // DB fields
  public $nom;
  public $valeurs;
  public $compte_rendu_id;

  // Form fields
  public $_valeurs;
  public $_new;
  public $_del;
  public $_modify;
  public $_owner;
  public $_is_for_instance;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CGroups */
  public $_ref_group;

  /** @var CCompteRendu */
  public $_ref_modele;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'liste_choix';
    $spec->key   = 'liste_choix_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_id"]         = "ref class|CMediusers back|listes_choix";
    $props["function_id"]     = "ref class|CFunctions back|listes_choix";
    $props["group_id"]        = "ref class|CGroups back|listes_choix";
    $props["nom"]             = "str notNull";
    $props["valeurs"]         = "text confidential";
    $props["compte_rendu_id"] = "ref class|CCompteRendu cascade back|listes_choix";
    
    $props["_owner"]           = "enum list|prat|func|etab";
    return $props;
  }

  /**
   * Charge l'utilisateur associé à la liste de choix
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Charge la fonction associée à la liste de choix
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * Charge l'établissement associé associée à la liste de choix
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Charge le propriétaire de la liste
   *
   * @return CMediusers|CFunctions|CGroups
   */
  function loadRefOwner() {
    return CValue::first(
      $this->loadRefUser(),
      $this->loadRefFunction(),
      $this->loadRefGroup()
    );
  }

  /**
   * Charge le modèle associé
   *
   * @return CCompteRendu
   */
  function loadRefModele() {
    return $this->_ref_modele = $this->loadFwdRef("compte_rendu_id", true);
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
    $this->_valeurs = $this->valeurs != "" ? explode("|", $this->valeurs) : array();
    self::orderItems($this->_valeurs);

    if ($this->user_id) {
      $this->_owner = "prat";
    }

    if ($this->function_id) {
      $this->_owner = "func";
    }

    if ($this->group_id) {
      $this->_owner = "etab";
    }

    if (!$this->user_id && !$this->function_id && !$this->group_id) {
      $this->_owner = "instance";
    }

    $this->isForInstance();
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if ($this->_new !== null || $this->_modify !== null || $this->_del !== null) {
      $this->updateFormFields();

      if ($this->_new) {
        $this->_valeurs[] = trim($this->_new);
        self::orderItems($this->_valeurs);
      }
      elseif ($this->_modify) {
        foreach ($this->_valeurs as $key => $value) {
          if (trim($value) === trim($this->_del)) {
            $this->_valeurs[$key] = $this->_modify;
            break;
          }
        }
        self::orderItems($this->_valeurs);
      }
      else {
        foreach ($this->_valeurs as $key => $value) {
          if (trim($this->_del) == trim($value)) {
            unset($this->_valeurs[$key]);
          }
        }
      }
      $this->valeurs = implode("|", $this->_valeurs);
    }
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    $owner = $this->loadRefOwner();
    return $owner->getPerm($permType);
  }

  /**
   * @inheritDoc
   */
  function store() {
    if ($msg = CCompteRendu::checkOwner($this)) {
      return $msg;
    }

    return parent::store();
  }

  /**
   * Charge les listes d'un utilisateur
   *
   * @param int $user_id     User ID
   * @param int $function_id Function ID
   *
   * @return self[]
   */
  static function loadAllFor($user_id, $function_id) {
    $user = CMediusers::get($user_id);

    $function = new CFunctions();
    $function->load($function_id);

    // Accès aux listes de choix de la fonction et de l'établissement
    $module = CModule::getActive("dPcompteRendu");
    $is_admin = $module && $module->canAdmin();
    $access_function = $is_admin || CAppUI::gconf("dPcompteRendu CListeChoix access_function");
    $access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CListeChoix access_group");
    $listes = [];
    if ($user->_id && !$function_id) {
      $listes["prat"] = [];
    }
    if ($access_function) {
      $listes["func"] = [];
    }
    if ($access_group) {
      $listes["etab"] = [];
    }

    $listes["instance"] = [];

    $liste = new self();

    $owner_used = $user->_id && !$function_id ? $user : $function;

    foreach ($owner_used->getOwners() as $type => $owner) {
      if (isset($listes[$type])) {
        if ($type === "instance") {
          $listes[$type] = $liste->loadList(["user_id IS NULL AND function_id IS NULL AND group_id IS NULL"]);
        }
        else {
          $listes[$type] = $owner->loadBackRefs("listes_choix", "nom");
        }
      }
    }

    return $listes;
  }

  /**
   * Tri des valeurs sans prendre en compte les diacritiques
   *
   * @param array $valeurs Liste des valeurs
   *
   * @return void
   */
  static function orderItems(&$valeurs) {
    $order_valeurs = array_map("Ox\Core\CMbString::removeDiacritics", $valeurs);
    array_multisort($order_valeurs, SORT_STRING|SORT_FLAG_CASE, $valeurs);
  }

  /**
   * Détecte si une liste de choix est d'instance
   *
   * @return bool
   */
  public function isForInstance() {
    return $this->_is_for_instance = (!$this->user_id && !$this->function_id && !$this->group_id);
  }
}
