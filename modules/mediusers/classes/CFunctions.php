<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Erp\Edm\COXDocumentPerm;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;
use Ox\Mediboard\Ssr\CCodeAffectation;


/**
 * The CFunctions Class
 */
class CFunctions extends CMbObject {

    /** @var string */
    public const RESOURCE_TYPE = 'function';

    /** @var string */
    public const FIELDSET_TARGET = 'target';

  // DB Table key
  public $function_id;

  // DB References
  public $group_id;

  // DB Fields
  public $type;
  public $text;
  public $initials;
  public $soustitre;
  public $color;
  public $adresse;
  public $cp;
  public $ville;
  public $tel;
  public $fax;
  public $email;
  public $finess;
  public $siret;
  public $actif;
  public $compta_partagee;
  public $admission_auto;
  public $consults_events_partagees;
  public $quotas;
  public $facturable;
  public $create_sejour_consult;
  public $ean;
  public $rcc;

  /** @var CGroups */
  public $_ref_group;

  /** @var CMediusers[] */
  public $_ref_users;

  /** @var CUniteFonctionnelle */
  public $_ref_uf_medicale;

  /** @var CUniteFonctionnelle[] */
  public $_ref_ufs_medicales;

  /** @var CUniteFonctionnelle[] */
  public $_ref_uf_medicale_secondaire;

  /** @var CCodeAffectation[] */
  public $_refs_codes_affectations;

  /** @var CProtocoleOperatoire[] */
  public $_ref_protocoles_op = [];

  // Form fields
  public $_ref_protocoles = [];
  public $_count_protocoles;

    /** @var CMedimailAccount */
    public $_ref_medimail_account;

  public $_count_doc;

  // Filter fields
  public $_skipped;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'functions_mediboard';
    $spec->key   = 'function_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["group_id"] = "ref notNull class|CGroups back|functions fieldset|target";
    $props["type"]     = "enum notNull list|administratif|cabinet fieldset|extra";
    $props["text"]     = "str notNull confidential seekable fieldset|default";
    $props["initials"] = "str";
    $props["color"]    = "color notNull fieldset|extra";
    $props["adresse"]  = "text";
    [$min_cp, $max_cp] = CPatient::getLimitCharCP();
    $props["cp"]                    = "str minLength|$min_cp maxLength|$max_cp";
    $props["ville"]                 = "str maxLength|50";
    $props["tel"]                   = "phone";
    $props["fax"]                   = "phone";
    $props["email"]                 = "email confidential";
    $props["finess"]                = "numchar length|9 confidential mask|9xS9S99999S9 control|luhn";
    $props["siret"]                 = "str length|14";
    $props["soustitre"]             = "text";
    $props["compta_partagee"]       = "bool default|0 notNull";
    $props["consults_events_partagees"]    = "bool default|1 notNull";
    $props["admission_auto"]        = "bool";
    $props["actif"]                 = "bool default|1";
    $props["quotas"]                = "num pos";
    $props["facturable"]            = "bool default|1";
    $props["create_sejour_consult"] = "bool default|0";
    $props["ean"]                   = "str";
    $props["rcc"]                   = "str";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view      = $this->text;
    $this->_shortview = $this->initials;

    if (!$this->_shortview) {
      $_initials = preg_replace('/[a-z]+\'/', '', $this->text);
      $_initials = str_replace('-', ' ', $_initials);
      $_initials = str_split($_initials);

      $this->_shortview = '';
      foreach ($_initials as $_letter) {
        if (ctype_upper($_letter)) {
          $this->_shortview .= $_letter;
        }
      }

      if (strlen($this->_shortview) < 2) {
        $this->_shortview = CMbString::upper(substr($this->text, 0, 3));
      }
      else {
        $this->_shortview = substr($this->_shortview, 0, 5);
      }
    }
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefsFwd();
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefGroup();
  }

  /**
   * @inheritDoc
   */
  function store() {
    $cache = new Cache('CFunctions', "{$this->_id}", Cache::INNER_OUTER);
    $cache->rem();

    return parent::store();
  }

  /**
   * Load ref Group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    $this->loadRefsUsers();
  }

  /**
   * Load users
   *
   * @param string $type Type
   *
   * @return CMediusers[]
   */
  function loadRefsUsers($type = null) {
    if (!$type) {
      $where = array(
        "function_id" => "= '$this->function_id'",
        "actif"       => "= '1'"
      );
      $ljoin = array(
        "users" => "`users`.`user_id` = `users_mediboard`.`user_id`"
      );
      $order = "`users`.`user_last_name`, `users`.`user_first_name`";

      return $this->_ref_users = $this->loadBackRefs("users", $order, null, null, $ljoin, null, null, $where);
    }

    $user = new CMediusers();

    return $this->_ref_users = $user->loadListFromType($type, PERM_READ, $this->function_id);
  }

  /**
   * Load protocole
   *
   * @param string $type Type
   *
   * @return CProtocole[]
   */
  function loadProtocoles($type = null) {
    $where = array(
      "function_id" => "= '$this->_id'"
    );

    if ($type) {
      $where["type"] = "= '$type'";
    }

    $protocole = new CProtocole();

    return $this->_ref_protocoles = $protocole->loadList($where, "libelle_sejour, libelle, codes_ccam");
  }

  /**
   * Count protocole
   *
   * @param string $type        Type
   * @param bool   $only_interv Seulement les protocoles chirurgicaux
   *
   * @return int
   */
  function countProtocoles($type = null, $only_interv = false) {
    $where = array(
      "function_id" => "= '$this->_id'"
    );

    if ($type) {
      $where["type"] = "= '$type'";
    }

    if ($only_interv) {
      $where["for_sejour"] = "= '0'";
    }

    $protocole = new CProtocole();

    return $this->_count_protocoles = $protocole->countList($where);
  }

  /**
   * @return int
   */
  function countDocs() {
    $where                = array();
    $where['function_id'] = " = $this->_id";
    $where["enabled"]     = " = '1'";
    $where['published']   = " = '1'";




    $document_perm = new COXDocumentPerm();

    return $this->_count_doc = $document_perm->countList(
      $where, null, '`ox_document` ON ox_document_perm.ox_document_id = ox_document.ox_document_id '
    );
  }

  /**
   * Load specialties
   *
   * @param string $perm_type     Type
   * @param int    $include_empty Include empty
   *
   * @return CStoredObject[]
   */
  function loadSpecialites($perm_type = null, $include_empty = 0) {
    $group_id = CGroups::loadCurrent()->_id;
    $where    = array(
      "functions_mediboard.type"     => "= 'cabinet'",
      "functions_mediboard.group_id" => "= '$group_id'"
    );
    $ljoin    = array();
    if (!$include_empty) {
      // Fonctions secondaires actives
      $sec_function                           = new CSecondaryFunction();
      $where_secondary                        = array();
      $ljoin_secondary                        = array();
      $ljoin_secondary["functions_mediboard"] = "functions_mediboard.type = 'cabinet'
                                                 AND functions_mediboard.group_id = '$group_id'
                                                 AND functions_mediboard.function_id = secondary_functions.secondary_function_id";
      $ljoin_secondary["users_mediboard"]     = "users_mediboard.actif = '1'
                                             AND users_mediboard.function_id = secondary_functions.secondary_function_id";
      $group                                  = "secondary_function.function_id";
      $sec_functions                          = $sec_function->loadListWithPerms($perm_type, $where_secondary, null, null, $group, $ljoin);
      $in_functions                           = CSQLDataSource::prepareIn(CMbArray::pluck($sec_functions, "function_id"));

      $ljoin["users_mediboard"] = "users_mediboard.actif = '1' AND users_mediboard.function_id = functions_mediboard.function_id";
      $where[]                  = "users_mediboard.user_id IS NOT NULL OR functions_mediboard.function_id $in_functions";
    }

    return $this->loadListWithPerms($perm_type, $where, "text", null, "functions_mediboard.function_id", $ljoin);
  }

  /**
   * Get disk usage for CFiles and CCompteRendu
   *
   * @return array
   */
  function getDiskUsage() {
    $user_ids = $this->loadBackIds("users");

    /** @var CDocumentItem $doc */
    $file             = new CFile();
    $doc              = new CCompteRendu();
    $disk_usage_user  = array();
    $disk_usage_total = array(
      "docs_count"  => 0,
      "docs_weight" => 0
    );
    foreach ($user_ids as $user_id) {
      $user = new CMediusers();
      $user->load($user_id);
      $user->loadRefFunction();
      $disk_usage_user[$user_id] = array(
        "docs_count"  => 0,
        "docs_weight" => 0,
        "user"        => $user
      );
      $user_details_files        = $file->getDiskUsage($user_id);
      $user_details_docs         = $doc->getDiskUsage($user_id);
      if (count($user_details_files)) {
        $tab                                      = reset($user_details_files);
        $disk_usage_user[$user_id]["docs_count"]  += $tab["docs_count"];
        $disk_usage_total["docs_count"]           += $tab["docs_count"];
        $disk_usage_user[$user_id]["docs_weight"] += $tab["docs_weight"];
        $disk_usage_total["docs_weight"]          += $tab["docs_weight"];
      }
      if (count($user_details_docs)) {
        $tab                                      = reset($user_details_docs);
        $disk_usage_user[$user_id]["docs_count"]  += $tab["docs_count"];
        $disk_usage_total["docs_count"]           += $tab["docs_count"];
        $disk_usage_user[$user_id]["docs_weight"] += $tab["docs_weight"];
        $disk_usage_total["docs_weight"]          += $tab["docs_weight"];
      }
    }

    return array(
      "users" => $disk_usage_user,
      "total" => $disk_usage_total
    );
  }

  /**
   * @see parent::fillTemplate()
   */
  function fillTemplate(&$template) {
    $this->loadRefsFwd();
    $this->_ref_group->fillTemplate($template);

    $cabinet_section = CAppUI::tr('CFunction');
    $template->addProperty("$cabinet_section - " . CAppUI::tr('common-name'), $this->text);
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-soustitre'), $this->soustitre);
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-adresse'), $this->adresse);
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-cp city'), "$this->cp $this->ville");
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-tel'), $this->getFormattedValue("tel"));
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-fax'), $this->getFormattedValue("fax"));
    $template->addProperty("$cabinet_section - " . CAppUI::tr('CFunctions-email'), $this->getFormattedValue("email"));
  }

  /**
   * Tableau comprenant l'utilisateur et son organigramme
   *
   * @return CMbObject[]
   */
  function getOwners() {
    $etab = $this->loadRefGroup();

    return [
      "func"     => $this,
      "etab"     => $etab,
      "instance" => CCompteRendu::getInstanceObject()
    ];
  }

  /**
   * Charge la fonction courante (principale ou secondaire)
   *
   * @return static
   * @throws Exception
   */
  public static function getCurrent() {
    global $f;

    if (!$f) {
      $f = CMediusers::get()->function_id;
    }

    $cache = new Cache('CFunctions', $f, Cache::INNER_OUTER, 60);

    if ($cache->exists()) {
      return $cache->get();
    }

    $function = new static();
    $function->load($f);

    return $cache->put($function);
  }

  /**
   * Charge l'unité fonctionnelle médicale de la fonction
   *
   * @param string $type_sejour Type de séjour optionnel
   *
   * @return void
   */
  function loadRefUfMedicale($type_sejour = null) {
    if (!CModule::getActive("dPhospi")) {
      return;
    }

    $this->loadRefsUfsMedicales($type_sejour);

    $this->_ref_uf_medicale = new CUniteFonctionnelle();

    if (count($this->_ref_ufs_medicales)) {
      $this->_ref_uf_medicale = reset($this->_ref_ufs_medicales);
    }
  }

  /**
   * Charge les unités fonctionnelles médicales de la fonction
   *
   * @param string $type_sejour Type de séjour optionnel
   *
   * @return void
   */
  function loadRefsUfsMedicales($type_sejour = null) {
    if (!CModule::getActive("dPhospi")) {
      return;
    }

    $where = array(
      "uf.type" => "= 'medicale'"
    );

    $ljoin = array(
      "uf" => "uf.uf_id = affectation_uf.uf_id"
    );

    if ($type_sejour) {
      $where["type_sejour"] = "IS NULL OR type_sejour = '$type_sejour'";
    }

    $aff_uf = $this->loadBackRefs("ufs", null, null, null, $ljoin, null, "ufs$type_sejour", $where);

    $this->_ref_ufs_medicales = CStoredObject::massLoadFwdRef($aff_uf, "uf_id");
  }

  /**
   * @return string|void
   * @throws Exception
   */
  function loadRefsCodesAffectations() {
    return $this->_refs_codes_affectations = $this->loadBackRefs("codes_affectations", "code ASC");
  }

  /**
   * Mass loading de l'unité fonctionnelle médicale pour une collection de fonctions
   *
   * @param self[] $functions   Collection des fonctions
   * @param string $type_sejour Type de séjour optionnel
   *
   * @return void
   */
  static function massLoadUfMedicale($functions, $type_sejour = null) {
    if (!CModule::getActive("dPhospi") || !count($functions)) {
      return;
    }

    $where = array(
      "uf.type" => "= 'medicale'"
    );

    $ljoin = array(
      "uf" => "uf.uf_id = affectation_uf.uf_id"
    );

    if ($type_sejour) {
      $where["type_sejour"] = "IS NULL OR type_sejour = '$type_sejour'";
    }

    $aff_ufs = CStoredObject::massLoadBackRefs($functions, "ufs", null, $where, $ljoin, "ufs$type_sejour");
    CStoredObject::massLoadFwdRef($aff_ufs, "uf_id");

    /** @var self $_function */
    foreach ($functions as $_function) {
      $_function->loadRefUfMedicale();
    }
  }

  /**
   * Charge les unités fonctionnelles médicale secondaires du user
   *
   * @param string $type_sejour Type de séjour optionnel
   *
   * @return void
   */
  function loadRefUfMedicaleSecondaire($type_sejour = null) {
    if (!CModule::getActive("dPhospi")) {
      return;
    }

    $where = array(
      "uf.type" => "= 'medicale'"
    );

    $ljoin = array(
      "uf" => "uf.uf_id = affectation_uf_second.uf_id"
    );

    if ($type_sejour) {
      $where["type_sejour"] = "IS NULL OR type_sejour = '$type_sejour'";
    }

    $aff_uf = $this->loadBackRefs("ufs_secondaires", null, null, null, $ljoin, null, "ufs_secondaires$type_sejour", $where);

    $this->_ref_uf_medicale_secondaire = array();

    /** @var CAffectationUfSecondaire $_aff_uf */
    foreach ($aff_uf as $_aff_uf) {
      $this->_ref_uf_medicale_secondaire[$_aff_uf->uf_id] = $_aff_uf->loadRefUniteFonctionnelle();
    }
  }

  /**
   * Mass loading de l'unité fonctionnelle médicale secondaire pour une collection de fonctions
   *
   * @param self[] $functions   Collection des fonctions
   * @param string $type_sejour Type de séjour optionnel
   *
   * @return void
   */
  static function massLoadUfMedicaleSecondaire($functions, $type_sejour = null) {
    if (!CModule::getActive("dPhospi") || !count($functions)) {
      return;
    }

    $where = array(
      "uf.type" => "= 'medicale'"
    );

    $ljoin = array(
      "uf" => "uf.uf_id = affectation_uf_second.uf_id"
    );

    if ($type_sejour) {
      $where["type_sejour"] = "IS NULL OR type_sejour = '$type_sejour'";
    }

    $aff_ufs = CStoredObject::massLoadBackRefs($functions, "ufs_secondaires", null, $where, $ljoin, "ufs_secondaires$type_sejour");
    CStoredObject::massLoadFwdRef($aff_ufs, "uf_id");

    /** @var self $_function */
    foreach ($functions as $_function) {
      $_function->loadRefUfMedicaleSecondaire();
    }
  }

  /**
   * Charge les protocoles opératoires associés au cabinet
   *
   * @param string $limit Limite éventelle
   *
   * @return CProtocoleOperatoire[]
   */
  public function loadRefsProtocolesOperatoires($limit = null) {
    return $this->_ref_protocoles_op = $this->loadBackRefs("protocoles_op", "libelle", $limit);
  }

    /**
     * Load medimail account
     *
     * @return CMedimailAccount
     * @throws Exception
     */
    public function loadRefMedimailAccount(): CMedimailAccount
    {
        $this->_ref_medimail_account = $this->loadUniqueBackRef("medimail_account");
        $this->_ref_medimail_account->function_id = $this->function_id;

        return $this->_ref_medimail_account;
    }
}
