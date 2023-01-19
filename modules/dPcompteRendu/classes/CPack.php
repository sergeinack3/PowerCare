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
use Ox\Core\CRequest;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Gestion de packs de documents
 */
class CPack extends CMbObject {
  // DB Table key
  public $pack_id;

  // DB References
  public $user_id;
  public $function_id;
  public $group_id;
  public $category_id;

  // DB fields
  public $nom;
  public $object_class;
  public $fast_edit;
  public $fast_edit_pdf;
  public $merge_docs;
  public $is_eligible_selection_document;
  
  // Form fields
  public $_modeles;
  public $_new;
  public $_del;
  public $_source;
  public $_object_class;
  public $_owner;
  public $_header_found;
  public $_footer_found;
  public $_modeles_ids;
  public $_is_for_instance;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CGroups */
  public $_ref_group;

  /** @var CMediusers|CFunctions|CGroups */
  public $_ref_owner;

  /** @var CFilesCategory */
  public $_ref_categorie;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'pack';
    $spec->key   = 'pack_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["user_id"]                        = "ref class|CMediusers back|packs";
    $specs["function_id"]                    = "ref class|CFunctions back|packs";
    $specs["group_id"]                       = "ref class|CGroups back|packs";
    $specs["category_id"]                    = "ref class|CFilesCategory back|categorized_packs";
    $specs["nom"]                            = "str notNull seekable confidential";
    $specs["object_class"]                   = "enum notNull list|CPatient|CConsultAnesth|COperation|CConsultation|CSejour|CEvenementPatient default|COperation";
    $specs["fast_edit"]                      = "bool default|0";
    $specs["fast_edit_pdf"]                  = "bool default|0";
    $specs["merge_docs"]                     = "bool default|1";
    $specs["_owner"]                         = "enum list|user|func|etab";
    $specs["is_eligible_selection_document"] = "bool default|0";
    return $specs;
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

  function loadRefOwner() {
    $this->_ref_user     = $this->loadFwdRef("user_id", true);
    $this->_ref_function = $this->loadFwdRef("function_id", true);
    $this->_ref_group    = $this->loadFwdRef("group_id", true);

    if ($this->_ref_user->_id) {
      $this->_ref_owner = $this->_ref_user;
    }
    elseif ($this->_ref_function->_id) {
      $this->_ref_owner = $this->_ref_function;
    }
    elseif ($this->_ref_group->_id) {
      $this->_ref_owner = $this->_ref_group;
    }
    
    return $this->_ref_owner;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefOwner();
  }
 function loadRefCategory() {
    return $this->merge_docs && $this->category_id ? $this->_ref_categorie = CFilesCategory::findOrFail($this->category_id):null;
 }
  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
    if ($this->user_id) {
      $this->_owner = "user";
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

    if (!$this->_object_class) {
      $this->_object_class = "COperation";
    }
    $this->loadRefCategory();
  }
  
  /**
   * Réunit les contenus des modèles pour constituer la source html du pack
   * 
   * @return void
   */
  function loadContent() {
    $this->_source = "";
    $this->loadBackRefs("modele_links", "modele_to_pack_id");

    if (count($this->_back['modele_links']) > 0) {
      $links_modele = array_keys($this->_back['modele_links']);
      $last_key = end($links_modele);

      /** @var CModeleToPack $_modeletopack */
      foreach ($this->_back['modele_links'] as $key => $_modeletopack) {
        $modele = $_modeletopack->_ref_modele;
        $modele->loadContent();
        $modele->loadIntroConclusion();
        
        if (!$this->_object_class) {
          $this->_object_class = $modele->object_class;
        }
        
        if ($modele->_ref_preface->_id) {
          $preface = $modele->_ref_preface;
          $preface->loadContent();
          $modele->_source = $preface->_source . "<br />" . $modele->_source;
        }
        
        if ($modele->_ref_ending->_id) {
          $ending = $modele->_ref_ending;
          $ending->loadContent();
          $modele->_source .= "<br />" . $ending->_source;
        }
        
        $this->_source .= $modele->_source;
        
        // Si on est au dernier modèle, pas de page break
        if ($key === $last_key) {
          break;
        }
        $this->_source .= '<hr class="pagebreak" />';
      }
    }
  }
  
  /**
   * Charge les packs pour un propriétaire donné
   * 
   * @param int    $id           identifiant du propriétaire
   * @param string $owner        [optional]
   * @param string $object_class [optional]
   * 
   * @todo: refactor this to be in a super class
   * 
   * @return array
   */
  static function loadAllPacksFor($id, $owner = 'user', $object_class = null) {
    // Accès aux packs de modèles de la fonction et de l'établissement
    $module = CModule::getActive("dPcompteRendu");
    $is_admin = $module && $module->canAdmin();
    $access_function = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_function");
    $access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_group");
    $packs = array();
    $packs["prat"] = array();
    if ($access_function) {
      $packs["func"] = array();
    }
    if ($access_group) {
      $packs["etab"] = array();
    }

    // Clauses de recherche
    $pack = new CPack();
    $where = array();
    
    if ($object_class) {  
      $where["object_class"] = "= '$object_class'";
    }
    
    $order = "object_class, nom";

    switch ($owner) {
      case "prat": // Modèle du praticien
        $user = new CMediusers();
        if (!$user->load($id)) {
          return $packs;
        }
        $user->loadRefFunction();

        $where["user_id"]     = "= '$user->_id'";
        $where["function_id"] = "IS NULL";
        $where["group_id"]    = "IS NULL";
        $packs["prat"] = $pack->loadlist($where, $order);
        
      case "func": // Modèle de la fonction
        if (isset($packs["func"])) {
          if (isset($user)) {
            $func_id = $user->function_id;
          }
          else {
            $func = new CFunctions();
            if (!$func->load($id)) {
              return $packs;
            }

            $func_id = $func->_id;
          }

          $where["user_id"]     = "IS NULL";
          $where["function_id"] = "= '$func_id'";
          $where["group_id"]    = "IS NULL";
          $packs["func"] = $pack->loadlist($where, $order);
        }
        
      case "etab": // Modèle de l'établissement
        if (isset($packs["etab"])) {
          $etab_id = CGroups::loadCurrent()->_id;
          if ($owner == 'etab') {
            $etab = new CGroups();
            if (!$etab->load($id)) {
              return $packs;
            }
            $etab_id = $etab->_id;
          }
          else if (isset($func)) {
            $etab_id = $func->group_id;
          }
          else if (isset($func_id)) {
            $func = new CFunctions();
            $func->load($func_id);

            $etab_id = $func->group_id;
          }

          $where["user_id"]     = "IS NULL";
          $where["function_id"] = "IS NULL";
          $where["group_id"]    = " = '$etab_id'";
          $packs["etab"] = $pack->loadlist($where, $order);
        }

      case "instance":
        $where["function_id"] = "IS NULL";
        $where["group_id"] = "IS NULL";
        $packs["instance"] = $pack->loadlist($where, $order);
        break;

      default:
        trigger_error("Wrong type '$owner'", E_WARNING);
    }
    
    return $packs;
  }
  
  function loadHeaderFooter() {
    if (!isset($this->_back['modele_links'])) {
      $this->loadBackRefs("modele_links", "modele_to_pack_id");
    }
    
    $header_id = null;
    $footer_id = null;
    
    foreach ($this->_back['modele_links'] as $mod) {
      $modele = $mod->_ref_modele;
      
      if ($modele->header_id || $modele->footer_id) {
        $header_id = $modele->header_id;
        $footer_id = $modele->footer_id;
      }
      if (!$header_id && $modele->header_id) {
        $header_id = $modele->header_id;
      }
      if (!$footer_id && $modele->footer_id) {
        $footer_id = $modele->footer_id;
      }
      if ($header_id && $footer_id) {
        break;
      }
    }
    
    $this->_header_found = new CCompteRendu();
    if ($header_id) {
      $this->_header_found->load($header_id);
    }
    
    $this->_footer_found = new CCompteRendu();
    if ($footer_id) {
      $this->_footer_found->load($footer_id);
    }
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_user) {
      $this->loadRefsFwd();
    }
    return $this->_ref_user->getPerm($permType);
  }
  
  function getModelesIds() {
    $ds = $this->getDS();
    
    $request = new CRequest();
    $request->addSelect("modele_id");
    $request->addTable("modele_to_pack");
    $request->addWhere("pack_id = '$this->_id'");
    $this->_modeles_ids = $ds->loadColumn($request->makeSelect());
  }

  /**
   * Détecte si un pack est d'instance
   *
   * @return bool
   */
  public function isForInstance() {
    return $this->_is_for_instance = (!$this->user_id && !$this->function_id && !$this->group_id);
  }
}
