<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\AppFine\Client\CAppFineClientOrderPackProtocole;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\Routing\RouterInterface;

/**
 * Classe des protocoles
 */
class CProtocole extends CMbObject {
  /** @var string  */
  const RESOURCE_TYPE = 'protocole';

  // DB Table key
  public $protocole_id;

  // DB References
  public $chir_id;
  public $function_id;
  public $group_id;
  public $uf_hebergement_id; // UF de responsabilité d'hébergement
  public $uf_medicale_id;    // UF de responsabilité médicale
  public $uf_soins_id;       // UF de responsabilité de soins
  public $charge_id;

  // For sejour/intervention
  public $for_sejour; // Sejour / Operation

  // DB Fields Sejour
  public $type;
  public $DP;
  public $DR;
  public $convalescence;
  public $rques_sejour; // Sejour->rques
  public $pathologie;
  public $septique;
  public $type_pec;
  public $facturable;
  public $RRAC;
  public $time_entree_prevue;
  public $circuit_ambu;
  public $admission;

  // DB Fields Operation
  public $codes_ccam;
  public $libelle;
  public $cote;
  public $temp_operation;
  public $examen;
  public $materiel;
  public $exam_per_op;
  public $duree_hospi;
  public $duree_heure_hospi;
  public $rques_operation; // Operation->rques
  public $depassement;
  public $forfait;
  public $fournitures;
  public $service_id;
  public $libelle_sejour;
  public $duree_uscpo;
  public $duree_preop;
  public $presence_preop;
  public $presence_postop;
  public $exam_extempo;
  public $type_anesth;
  public $duree_bio_nettoyage;
  public $hospit_de_jour;
  public $facturation_rapide;
  public $codage_ccam_chir;
  public $codage_ccam_anesth;
  public $codage_ngap_sejour;
  public $actif;
  public $code_EDS;


  // DB fields linked protocols
  public $protocole_prescription_chir_id;
  public $protocole_prescription_chir_class;
  public $protocole_prescription_anesth_id;
  public $protocole_prescription_anesth_class;

  // Form fields
  public $_owner;
  public $_time_op;
  public $_codes_ccam = [];
  public $_types_ressources_ids;
  public $_pack_appFine_ids;
  /** @var bool */
  public $_pack_already_linked;
  public $_docitems_guid_sejour;
  public $_docitems_guid_operation;
  public $_temps_median;
  public $_ecart_tps_median;
  public $_count_docitems_sejour = 0;
  public $_count_docitems_operation = 0;
  public $_code_EDS;

  /** @var string */
  public $_codage_ngap_formatted;

  /** @var CMediusers */
  public $_ref_chir;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CGroups */
  public $_ref_group;

  /** @var CPrescription */
  public $_ref_protocole_prescription_chir;

  /** @var CPrescription */
  public $_ref_protocole_prescription_anesth;

  /** @var CUniteFonctionnelle */
  public $_ref_uf_hebergement;

  /** @var CUniteFonctionnelle */
  public $_ref_uf_medicale;

  /** @var CUniteFonctionnelle */
  public $_ref_uf_soins;

  /** @var CService */
  public $_ref_service;

  /** @var CBesoinRessource[] */
  public $_ref_besoins = [];

  /** @var CAppFineClientOrderPackProtocole[] */
  public $_ref_packs_appFine = [];

  /** @var CChargePriceIndicator */
  public $_ref_charge_price_indicator;

  /** @var CProtocoleOperatoireDHE[] */
  public $_refs_links_protocoles_op = [];

  /** @var array CProtocoleOperatoire[] */
  public $_ref_protocoles_op = [];

  // External references
  public $_ext_codes_ccam;
  public $_ext_code_cim;
  public $_ext_code_cim_DR;

  // Form fields
  public $_ids_protocoles_op = [];
  
  public $_list_libelles_protocoles_op = [];

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec               = parent::getSpec();
    $spec->table        = 'protocole';
    $spec->key          = 'protocole_id';
    $spec->xor["owner"] = array("chir_id", "function_id", "group_id");
    return $spec;
  }

  /**
   * @inheritDoc
   */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('planning_protocole', ["protocole_id" => $this->_id]);
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                        = parent::getProps();
    $sejour                       = new CSejour();
    $props["chir_id"]             = "ref class|CMediusers seekable back|protocoles fieldset|default";
    $props["function_id"]         = "ref class|CFunctions seekable back|protocoles fieldset|default";
    $props["group_id"]            = "ref class|CGroups seekable back|protocoles fieldset|default";
    $props["uf_hebergement_id"]   = "ref class|CUniteFonctionnelle seekable back|protocoles_hebergement";
    $props["uf_medicale_id"]      = "ref class|CUniteFonctionnelle seekable back|protocoles_medical fieldset|extra";
    $props["uf_soins_id"]         = "ref class|CUniteFonctionnelle seekable back|protocoles_soin fieldset|extra";
    $props["charge_id"]           = "ref class|CChargePriceIndicator autocomplete|libelle show|0 back|protocoles fieldset|extra";
    $props["for_sejour"]          = "bool notNull default|0";
    $props["type"]                = "enum list|comp|ambu|exte|seances|ssr|psy default|comp fieldset|extra";
    $props["DP"]                  = "code cim10";
    $props["DR"]                  = "code cim10";
    $props["convalescence"]       = "text fieldset|extra";
    $props["rques_sejour"]        = "text fieldset|extra";
    $props["libelle"]             = "str seekable fieldset|default";
    $props["cote"]                = "enum list|droit|gauche|haut|bas|bilatéral|total|inconnu|non_applicable fieldset|extra";
    $props["libelle_sejour"]      = "str seekable fieldset|default";
    $props["service_id"]          = "ref" . (CAppUI::conf("dPplanningOp CSejour service_id_notNull") == 1 ? ' notNull' : '') . " class|CService seekable back|protocoles fieldset|extra";
    $props["examen"]              = "text confidential seekable fieldset|extra";
    $props["materiel"]            = "text confidential seekable fieldset|extra";
    $props["exam_per_op"]         = "text confidential seekable fieldset|extra";
    $props["duree_hospi"]         = "num notNull min|0 max|36500 fieldset|extra";
    $props["duree_heure_hospi"]   = "num min|0 max|23 default|0";
    $props["rques_operation"]     = "text confidential fieldset|extra";
    $props["depassement"]         = "currency min|0 confidential fieldset|extra";
    $props["forfait"]             = "currency min|0 confidential fieldset|extra";
    $props["fournitures"]         = "currency min|0 confidential fieldset|extra";
    $props["pathologie"]          = "str length|3";
    $props["septique"]            = "bool";
    $props["codes_ccam"]          = "str seekable fieldset|extra";
    $props["temp_operation"]      = "time fieldset|extra";
    $props["type_pec"]            = $sejour->getPropsWitouthFieldset("type_pec");
    $props["facturable"]          = "bool notNull default|1 show|0 fieldset|extra";
    $props["duree_uscpo"]         = "num min|0 default|0 fieldset|extra";
    $props["duree_preop"]         = "time show|0";
    $props["presence_preop"]      = "time show|0 fieldset|extra";
    $props["presence_postop"]     = "time show|0 fieldset|extra";
    $props["exam_extempo"]        = "bool fieldset|extra";
    $props["type_anesth"]         = "ref class|CTypeAnesth back|protocole fieldset|extra";
    $props["duree_bio_nettoyage"] = "time show|0 fieldset|extra";
    $props["hospit_de_jour"]      = "bool default|0 fieldset|extra";
    $props['facturation_rapide']  = 'bool default|0';
    $props['codage_ccam_chir']    = 'str';
    $props['codage_ccam_anesth']  = 'str';
    $props['codage_ngap_sejour']  = 'str';
    $props['actif']               = 'bool default|1';
    $props['RRAC']                = 'bool default|0';
    $props['time_entree_prevue']  = 'time fieldset|extra';
    $props["circuit_ambu"]        = "enum list|court|moyen|long";
    $props["admission"]           = "enum list|veille|jour fieldset|extra";
    $props["code_EDS"]            = "enum list|1|2|3";

    $props["protocole_prescription_chir_id"]      = "ref class|CMbObject meta|protocole_prescription_chir_class back|protocoles_op_chir";
    $props["protocole_prescription_chir_class"]   = "enum list|CPrescription|CPrescriptionProtocolePack";
    $props["protocole_prescription_anesth_id"]    = "ref class|CMbObject meta|protocole_prescription_anesth_class back|protocoles_op_anesth";
    $props["protocole_prescription_anesth_class"] = "enum list|CPrescription|CPrescriptionProtocolePack";

    $props["_time_op"]          = "time";
    $props["_owner"]            = "enum list|user|function|group";
    $props["_temps_median"]     = "time";
    $props["_ecart_tps_median"] = "num";
    $props["_code_EDS"]         = "enum list|1|2|3";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->codes_ccam = strtoupper($this->codes_ccam);
    if ($this->codes_ccam) {
      $this->_codes_ccam = explode("|", $this->codes_ccam);
    }
    else {
      $this->_codes_ccam = array();
    }

    $this->_time_op = $this->temp_operation;

    if ($this->libelle_sejour) {
      $this->_view = $this->libelle_sejour;
    }
    elseif ($this->libelle) {
      $this->_view = $this->libelle;
    }
    else {
      $this->_view = $this->codes_ccam;
    }

    if ($this->chir_id) {
      $this->_owner = "user";
    }
    if ($this->function_id) {
      $this->_owner = "function";
    }
    if ($this->group_id) {
      $this->_owner = "group";
    }
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    if ($this->codes_ccam) {
      $this->codes_ccam = strtoupper($this->codes_ccam);
      $codes_ccam       = explode("|", $this->codes_ccam);
      $XPosition        = true;
      while ($XPosition !== false) {
        $XPosition = array_search("-", $codes_ccam);
        if ($XPosition !== false) {
          array_splice($codes_ccam, $XPosition, 1);
        }
      }
      $this->codes_ccam = implode("|", $codes_ccam);
    }
    if ($this->_time_op !== null) {
      $this->temp_operation = $this->_time_op;
    }
  }

  /**
   * @return CMediusers
   */
  function loadRefChir() {
    return $this->_ref_chir = $this->loadFwdRef("chir_id", true);
  }

  /**
   * @return CFunctions
   */
  function loadRefFunction() {
    $this->_ref_function = $this->loadFwdRef("function_id", true);
    if ($this->_ref_function) {
      $this->_ref_function->loadRefGroup();
    }
    return $this->_ref_function;
  }

  /**
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @return CPrescription
   */
  function loadRefPrescriptionChir() {
    return $this->_ref_protocole_prescription_chir = $this->loadFwdRef("protocole_prescription_chir_id", true);
  }

  /**
   * @return CPrescription
   */
  function loadRefPrescriptionAnesth() {
    return $this->_ref_protocole_prescription_anesth = $this->loadFwdRef("protocole_prescription_anesth_id", true);
  }

  function loadExtCodesCCAM() {
    $this->_ext_codes_ccam = array();
    foreach ($this->_codes_ccam as $code) {
      $this->_ext_codes_ccam[] = CDatedCodeCCAM::get($code);
    }
  }

  function loadExtCodeCIM() {
    $this->_ext_code_cim    = CCodeCIM10::get($this->DP);
    $this->_ext_code_cim_DR = CCodeCIM10::get($this->DR);
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefChir();
    $this->loadRefFunction();
    $this->loadRefGroup();
    $this->loadRefPrescriptionChir();
    $this->loadRefPrescriptionAnesth();
    $this->loadExtCodesCCAM();
    $this->loadExtCodeCIM();
    $this->loadRefUfs();
    $this->loadRefService();
    $this->_view = "";
    if ($this->libelle_sejour) {
      $this->_view .= "$this->libelle_sejour";
    }
    elseif ($this->libelle) {
      $this->_view .= "$this->libelle";
    }
    else {
      foreach ($this->_ext_codes_ccam as $ccam) {
        $this->_view .= " - $ccam->code";
      }
    }
    if ($this->chir_id) {
      $this->_view .= " &mdash; Dr {$this->_ref_chir->_view}";
    }
    elseif ($this->function_id) {
      $this->_view .= " &mdash; Fonction {$this->_ref_function->_view}";
    }
    elseif ($this->chir_id) {
      $this->_view .= " &mdash; Etablissement {$this->_ref_group->_view}";
    }
  }

  /**
   * Chargement des besoins en matériel
   *
   * @return CBesoinRessource[]
   */
  function loadRefsBesoins() {
    return $this->_ref_besoins = $this->loadBackRefs("besoins_ressources");
  }

  /**
   * Chargement des packs de demandes Appfine
   *
   * @return CAppFineClientOrderPackProtocole[]
   */
  function loadRefsPacksAppFine() {
    return $this->_ref_packs_appFine = $this->loadBackRefs("pack_appFine");
  }

  /**
   * Chargement de l'ensemble des UFs du protocole
   *
   * @param bool $cache cache
   *
   * @return void
   */
  function loadRefUfs($cache = true) {
    $this->loadRefUFHebergement($cache);
    $this->loadRefUFMedicale($cache);
    $this->loadRefUFSoins($cache);
  }

  /**
   * Chargement de l'UF d'hébergement
   *
   * @param bool $cache cache
   *
   * @return CUniteFonctionnelle
   */
  function loadRefUFHebergement($cache = true) {
    return $this->_ref_uf_hebergement = $this->loadFwdRef("uf_hebergement_id", $cache);
  }

  /**
   * Chargement de l'UF médicale
   *
   * @param bool $cache cache
   *
   * @return CUniteFonctionnelle
   */
  function loadRefUFMedicale($cache = true) {
    return $this->_ref_uf_medicale = $this->loadFwdRef("uf_medicale_id", $cache);
  }

  /**
   * Chargement de l'UF de soins
   *
   * @param bool $cache cache
   *
   * @return CUniteFonctionnelle
   */
  function loadRefUFSoins($cache = true) {
    return $this->_ref_uf_soins = $this->loadFwdRef("uf_soins_id", $cache);
  }

  /**
   * Charge le service
   *
   * @param bool $cache cache
   *
   * @return CService
   */
  public function loadRefService($cache = true) {
    return $this->_ref_service = $this->loadFwdRef('service_id', $cache);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if ($this->chir_id) {
      if (!$this->_ref_chir) {
        $this->loadRefChir();
      }
      return $this->_ref_chir->getPerm($permType);
    }
    if ($this->function_id) {
      if (!$this->_ref_function) {
        $this->loadRefFunction();
      }
      return $this->_ref_function->getPerm($permType);
    }
    if ($this->group_id) {
      if (!$this->_ref_group) {
        $this->loadRefGroup();
      }
      return $this->_ref_group->getPerm($permType);
    }
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id && ($this->_types_ressources_ids || $this->_pack_appFine_ids)) {
      if ($msg = parent::store()) {
        return $msg;
      }

      if ($this->_types_ressources_ids) {
        $types_ressources_ids = explode(",", $this->_types_ressources_ids);

        foreach ($types_ressources_ids as $_type_ressource_id) {
          $besoin = new CBesoinRessource;
          $besoin->type_ressource_id = $_type_ressource_id;
          $besoin->protocole_id = $this->_id;
          if ($msg = $besoin->store()) {
            return $msg;
          }
        }
      }

      if (CModule::getActive("appFineClient")) {
        CAppFineClientOrderPackProtocole::save($this);
      }
    }

    return parent::store();
  }

  /**
   * Calcule le temps médian d'un protocole basé sur les 10 dernières interventions
   *
   * @param CProtocole[] $protocoles
   *
   * @return array
   */
  static function computeMedian($protocoles = array()) {
    if (!count($protocoles)) {
      return array();
    }

    $pct_ecart_tps_median = CAppUI::gconf('dPplanningOp CProtocole pct_ecart_tps_median');
    CStoredObject::massCountBackRefs($protocoles, "operations");

    $where = array(
      "entree_salle" => "IS NOT NULL",
      "sortie_salle" => "IS NOT NULL"
    );

    /** @var CProtocole $_protocole */
    foreach ($protocoles as $_protocole_id => $_protocole) {
      $operations = $_protocole->loadBackRefs("operations", "operations.date DESC", 10, null, null, null, null, $where);
      if (count($operations) != 10) {
        if ($pct_ecart_tps_median) {
          unset($protocoles[$_protocole_id]);
        }
        continue;
      }

      $times = array();

      /** @var COperation $_operation */
      foreach ($operations as $_operation) {
        $times[] = CMbDT::minutesRelative($_operation->entree_salle, $_operation->sortie_salle);
      }

      $median = CMbArray::median($times);

      $hours                         = intval($median / 60);
      $minutes                       = intval($median - $hours * 60);
      $_protocole->_temps_median     = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":00";
      $_protocole->_ecart_tps_median = CMbDT::minutesRelative($_protocole->temp_operation, $_protocole->_temps_median);

      $pct = ((CMbDT::minutesRelative("00:00:00", $_protocole->_temps_median) / CMbDT::minutesRelative("00:00:00", $_protocole->temp_operation)) - 1) * 100;
      if (abs($pct) < $pct_ecart_tps_median || (!$pct_ecart_tps_median && !$_protocole->_temps_median)) {
        unset($protocoles[$_protocole_id]);
      }
    }

    $order_temps = CMbArray::pluck($protocoles, "_temps_median");
    array_multisort($order_temps, SORT_DESC, $protocoles);
    return $protocoles;
  }

  /**
   * Vérifie si la recherche sur le multi établissement est utilisé
   *
   * @param CFunctions $function Fonction optionnelle
   *
   * @return array where et ljoin pour la requête
   */
  static function checkMultiEtab($function = null) {
    $group                      = CGroups::loadCurrent();
    $use_protocole_current_etab = CAppUI::conf('dPplanningOp CProtocole use_protocole_current_etab', $group);
    $functions                  = $function ? array($function->_id) : array();
    $ljoinSecondary             = $whereSecondary = array();
    if ($use_protocole_current_etab) {
      if ($function && $function->group_id != $group->_id) {
        $functions = array();
      }
      $ljoinSecondary["functions_mediboard"]          = "functions_mediboard.function_id = secondary_function.function_id";
      $whereSecondary["functions_mediboard.group_id"] = " = '$group->_id'";
    }
    return array($ljoinSecondary, $whereSecondary, $functions);
  }

  /**
   * Charge l'indicateur de prix
   *
   * @return CChargePriceIndicator
   */
  function loadRefChargePriceIndicator() {
    return $this->_ref_charge_price_indicator = $this->loadFwdRef("charge_id", true);
  }

  /**
   * Charge les protocoles opératoires associés
   *
   * @return CProtocoleOperatoire[]
   */
  public function loadRefsProtocolesOp() {
    $this->loadRefsLinksProtocolesOp();

    $this->_ref_protocoles_op = CStoredObject::massLoadFwdRef($this->_refs_links_protocoles_op, "protocole_operatoire_id");
    $this->_ids_protocoles_op = array_keys($this->_ref_protocoles_op);
    $this->_list_libelles_protocoles_op = CMbArray::pluck($this->_ref_protocoles_op, "libelle");

    foreach ($this->_refs_links_protocoles_op as $_link) {
      $_link->loadRefProtocoleOperatoire();
    }

    return $this->_ref_protocoles_op;
  }

  /**
   * Charge les lignes vers les protocoles opératoires
   *
   * @return CProtocoleOperatoireDHE[]
   */
  public function loadRefsLinksProtocolesOp() {
    return $this->_refs_links_protocoles_op = $this->loadBackRefs("links_protocoles_op");
  }
    /**
     * @return string|null
     */
    public function loadEpisodeSoin(): ?string
    {
        $idex               = new CIdSante400();
        $idex->object_class = $this->_class;
        $idex->object_id    = $this->_id;
        $idex->tag          = "CODE_EDS";
        $idex->loadMatchingObjectEsc();
        if ($idex->_id) {
            return $this->_code_EDS = $idex->id400;
        }
        return $this->_code_EDS = null;
    }
    /**
     * @throws Exception
     */
    public function manageEdsIdex(): void
    {
        $idex               = new CIdSante400();
        $idex->object_class = $this->_class;
        $idex->object_id    = $this->_id;
        $idex->tag          = "CODE_EDS";
        $idex->loadMatchingObjectEsc();
        if ($this->_code_EDS) {
            $idex->id400 = $this->_code_EDS;
            $idex->store();
        } elseif ($idex->_id) {
            $idex->delete();
        }
    }

    public function formatCodageNGAP(): void
    {
        if ($this->codage_ngap_sejour) {
            $codages                      = explode('|', $this->codage_ngap_sejour);
            foreach ($codages as $key => $codage) {
                $ngap = new CActeNGAP();
                $ngap->setFullCode($codage);
                $codages[$key] = $ngap->_shortview;
            }
            $this->_codage_ngap_formatted = implode(' ', $codages);
        }
    }
    
    /**
     * Determine if a protocol is mandatory
     *
     * @return bool
     */
    public static function isProtocoleMandatory(): int
    {
        $pref   = CAppUI::pref('protocole_mandatory');
        $config = CAppUI::gconf('dPplanningOp COperation protocole_mandatory');
        if ($pref !== 'config') {
            return (int) $pref;
        } else {
            return (int) $config;
        }
    }
}
