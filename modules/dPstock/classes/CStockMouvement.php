<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Pharmacie\CStockSejour;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Mouvement Stock
 */
class CStockMouvement extends CMbObject {
  public $stock_mvt_id;

  // DB Fields
  public $source_id;
  public $source_class;
  public $cible_id;
  public $cible_class;
  public $type;
  public $quantite;//CIP
  public $code_up;
  public $commentaire;
  public $datetime;
  public $service_id;
  public $etat;

  public $_code_cip;
  public $_code_cis;
  public $_stock_sejour_id;
  public $_sejour_id;
  public $_increment_stock = true;
  public $_datetime_adm;
  public $_bdm;
  public $_cible_sejour_id;
  public $_sens_transfert;
  public $_stock_service_guid;

  /** @var CStockSejour|CProductStockGroup|CProductStockService */
  public $_ref_source;
  /** @var CStockSejour|CProductStockGroup|CProductStockService */
  public $_ref_cible;
  /** @var CMedicamentArticle */
  public $_ref_produit;
  /** @var CPatient */
  public $_ref_patient;
  /** @var CService */
  public $_ref_service;

  public static $_reduction_stock_source = array("administration", "destruction", "disparition", "retour_pharma",
    "apport_service", "retour_service", "transfert_patient", "delivrance", "vaccination", "delivre_patient");

  /** @var string[]  */
    public static $_types_mvt = [
        "administration",
        "apport_service",
        "apport_patient",
        "transfert_patient",
        "disparition",
        "destruction",
        "delivre_patient",
        "retour_service",
        "retour_pharma",
        "delivrance",
        "vaccination",
        "apparition",
    ];

    /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_stock_mouvement';
    $spec->key   = 'stock_mvt_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props['source_id']    = 'ref meta|source_class class|CMbObject cascade back|mvt_source';
    $props['source_class'] = 'enum list|CStockSejour|CProductStockGroup|CProductStockService';
    $props['cible_id']     = 'ref meta|cible_class class|CMbObject cascade back|mvt_cible';
    $props['cible_class']  = 'enum list|CStockSejour|CProductStockGroup|CProductStockService|CAdministration|CInjection';
    $props['type']         = 'enum notNull list|' . implode('|', CStockMouvement::$_types_mvt);
    $props['quantite']     = 'float notNull min|0';
    $props['code_up']      = 'num';
    $props['commentaire']  = 'text';
    $props['datetime']     = 'dateTime notNull';
    $props['service_id']   = 'ref class|CService back|service_mvt_stock';
    $props['etat']         = 'enum list|en_cours|realise default|realise';

    $props['_code_cip']        = 'num';
    $props['_code_cis']        = 'num';
    $props['_stock_sejour_id'] = 'num';
    $props['_sejour_id']       = 'num';
    $props['_cible_sejour_id'] = 'num';
    $props['_sens_transfert']  = 'enum list|for_patient|for_other';
    $props['_stock_service_guid'] = 'str';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("type", "quantite", "cible_id", "cible_class", "source_id", "source_class", "etat");
    if (!$this->type || !$this->quantite || $this->quantite == "0") {
      return CAppUI::tr("CStockMouvement.check_quantite_type");
    }
    if (!$this->_id && !$this->datetime) {
      $this->datetime = CMbDT::dateTime();
    }

    if ($this->_id && $this->fieldModified("etat", "realise")) {
      $this->_increment_stock = true;
    }

    if ($this->_increment_stock && ($this->_stock_sejour_id || $this->_sejour_id)) {
      if ($this->type != "administration") {
        //Stock du séjour
        if ($this->_stock_sejour_id) {
          $stock_sejour = new CStockSejour();
          $stock_sejour->load($this->_stock_sejour_id);
        }
        else {
          $stock_sejour           = CStockSejour::getFromCIP($this->_code_cip, $this->_sejour_id, $this->_bdm);
          $stock_sejour->code_cis = $this->_code_cis;
          $article                = CMedicamentArticle::get($this->_code_cip);
          $stock_sejour->code_ucd = $article->getCodeUCD();
          if (!$stock_sejour->_id) {
            $stock_sejour->quantite_reelle = 0;
          }
          if ($msg = $stock_sejour->store()) {
            return $msg;
          }
        }

        //Source : Stock du séjour
        if (in_array($this->type, array("destruction", "disparition", "retour_pharma", "retour_service", "delivre_patient"))) {
          if ($stock_sejour->quantite_reelle < $this->quantite) {
            return CAppUI::tr("CAdministration.stock_negatif");
          }
          $this->setSource($stock_sejour);
        }
        if ($this->type == "transfert_patient" && !$this->_id) {
          if (!$this->_cible_sejour_id) {
            return CAppUI::tr("CStockMouvement.stock_cible_empty");
          }
          if ($stock_sejour->quantite_reelle < $this->quantite && $this->_sens_transfert == "for_other") {
            return CAppUI::tr("CAdministration.stock_negatif");
          }

          //Stock du l'autre patient
          $stock_cible           = CStockSejour::getFromCIP($this->_code_cip, $this->_cible_sejour_id, $this->_bdm);
          $stock_cible->code_cis = $this->_code_cis;
          $article               = CMedicamentArticle::get($this->_code_cip);
          $stock_cible->code_ucd = $article->getCodeUCD();
          if ($this->_sens_transfert == "for_patient" && (!$stock_cible->_id || $stock_cible->quantite_reelle < $this->quantite)) {
            return CAppUI::tr("CStockMouvement.stock_cible_too_low");
          }
          if (!$stock_cible->_id) {
            $stock_cible->quantite_reelle = 0;
            if ($msg = $stock_cible->store()) {
              return $msg;
            }
          }

          $this->setSource($this->_sens_transfert == "for_other" ? $stock_sejour : $stock_cible);
          $this->setCible($this->_sens_transfert == "for_other" ? $stock_cible : $stock_sejour);
        }

        //Cible : Stock du séjour
        if (in_array($this->type, array("apparition", "apport_patient", "apport_service"))) {
          $this->setCible($stock_sejour);
        }
        elseif ($this->type == "retour_pharma") {
          $stock_group = CProductStockGroup::getFromCode($this->_code_cip);
          $this->setCible($stock_group);
        }

        //Dans le cas de l'apport ou du retour au service, récupération du stock service
        if (in_array($this->type, array("apport_service", "retour_service"))) {
          if (!$this->service_id) {
            $sejour = new CSejour();
            $sejour->load($this->_sejour_id);
            $this->service_id = $sejour->service_id;
          }
          if ($this->_stock_service_guid) {
            $stock_service = CMbObject::loadFromGuid($this->_stock_service_guid);
          }
          else {
            $stock_service = CAppUI::gconf("dispensation general use_dispentation_ucd") ?
              CProductStockService::getFromCIS($this->_code_cis, $this->service_id) :
              CProductStockService::getFromCode($this->_code_cip, $this->service_id);
          }

          if (!$this->service_id) {
            return "Le séjour n'est pas placé dans un service";
          }
          if ($this->type == "apport_service") {
            if (!$stock_service->_id) {
              return CAppUI::tr("CProductStockService.none_conditionnement");
            }
            if ($stock_service->quantity < $this->quantite) {
              return CAppUI::tr("CAdministration.stock_negatif_service");
            }
            $this->setSource($stock_service);
          }
          else {
            //Dans le cas où le stock service n'existe pas, nous le créons
            if (!$stock_service->_id) {
              $produit       = new CProduct();
              $produit->code = $this->_code_cip;
              $produit->bdm  = $this->_bdm;
              $produit->loadMatchingObject();
              $stock_service                      = new CProductStockService();
              $stock_service->product_id          = $produit->_id;
              $stock_service->object_id           = $this->service_id;
              $stock_service->object_class        = "CService";
              $stock_service->quantity            = 0;
              $stock_service->order_threshold_min = 0;
              $default_location                   = CProductStockLocation::getDefaultLocation($this->loadRefService(), $produit);
              $stock_service->location_id         = $default_location->_id;
              if ($msg = $stock_service->store()) {
                return $msg;
              }
            }
            $this->setCible($stock_service);
          }
        }
      }
    }
    $use_validation_mvt = CAppUI::gconf("dPstock CProductStockGroup use_validation_mvt");
    if (!$this->_id && $this->source_class == "CProductStockGroup" && $use_validation_mvt) {
      $this->etat = "en_cours";
      $this->_increment_stock = false;
    }
    if ($msg = $this->changeStock()) {
        return $msg;
    }

    return parent::store();
  }

  /**
   * Changement des quantité de stocks
   *
   * @param bool $store origine of change
   *
   * @return null|string null if successful, an error message otherwise
   */
  function changeStock($store = true) {
    if (!$this->_increment_stock || $this->etat == "en_cours") {
      return;
    }
    if ($this->type == "delivrance"
      && (!CAppUI::gconf("dPstock CProductStockGroup use_validation_mvt") || $this->etat == "en_cours")) {
      return null;
    }
    $this->loadRefSource();
    $this->loadRefCible();
    $reduction_stock_source = in_array($this->type, self::$_reduction_stock_source);
    if ($this->_ref_source) {
      $quantite_source = ($reduction_stock_source && $store) ? -$this->quantite : $this->quantite;
      if ($this->source_class == "CStockSejour") {
        $this->_ref_source->quantite_reelle += $quantite_source;
        $this->_ref_source->datetime        = ($this->type == "administration" && $store) ? $this->_datetime_adm : CMbDT::dateTime();
      }
      else {
        $this->_ref_source->quantity += $quantite_source;
      }
      if ($msg = $this->_ref_source->store()) {
        return $msg;
      }
    }
    if ($this->_ref_cible && $this->cible_class != "CAdministration") {
      $quantite_cible = $store ? $this->quantite : -$this->quantite;
      if ($this->_ref_cible instanceof CStockSejour) {
        $this->_ref_cible->quantite_reelle += $quantite_cible;
        $this->_ref_cible->datetime        = CMbDT::dateTime();
      }
      elseif (!$this->_ref_cible instanceof CInjection) {
        $this->_ref_cible->quantity += $quantite_cible;
      }
      if ($msg = $this->_ref_cible->store()) {
        return $msg;
      }
    }

    return null;
  }


  /**
   * @inheritdoc
   */
  function delete() {
    $this->completeField("source_id", "source_class", "cible_id", "cible_class", "quantite");
    $this->changeStock(false);

    return parent::delete();
  }

  /**
   * Change cible
   *
   * @return void
   */
  function setCible($object) {
    $this->cible_id    = $object->_id;
    $this->cible_class = $object->_class;
  }

  /**
   * Change source
   *
   * @return void
   */
  function setSource($object) {
    $this->source_id    = $object->_id;
    $this->source_class = $object->_class;
  }

  /**
   * Load cible
   *
   * @return null|CStockSejour|CProductStockGroup|CProductStockService cible
   */
  function loadRefCible() {
    if (!$this->cible_id) {
      return null;
    }
    /* @var CStockSejour|CProductStockGroup|CProductStockService $cible */
    $cible = new $this->cible_class;
    $cible->load($this->cible_id);

    return $this->_ref_cible = $cible;
  }

  /**
   * Load source
   *
   * @return null|CStockSejour|CProductStockGroup|CProductStockService source
   */
  function loadRefSource() {
    if (!$this->source_id) {
      return null;
    }
    /* @var CStockSejour|CProductStockGroup|CProductStockService $source */
    $source = new $this->source_class;
    $source->load($this->source_id);

    return $this->_ref_source = $source;
  }

  /**
   * Load produit
   *
   * @return CMedicamentArticle article
   */
  function loadRefProduit() {
    /* @var CStockSejour $stock_sejour */
    if ($this->type == "administration") {
      $stock_sejour = $this->loadRefSource();
    }
    else {
      $stock_sejour = in_array($this->source_class, ["CStockSejour", 'CProductStockService']) ? $this->loadRefSource() : $this->loadRefCible();
    }

    $code_cip     = $stock_sejour instanceof CStockSejour ? $stock_sejour->code_cip : $stock_sejour->loadRefProduct()->code;
    $code_up_adm  = $stock_sejour->loadRefProduct()->code_up_adm;
    $code_up_disp = $stock_sejour->_ref_product->code_up_disp;
    $bdm          = $stock_sejour->_ref_product->bdm;
    $article      = CMedicamentArticle::get($code_cip, $code_up_adm, $code_up_disp, $bdm);

    return $this->_ref_produit = $article;
  }


  /**
   * Récupère le patient du mouvement
   * Il peut être différent pour une administration utilisant le stock d'un autre patient
   *
   * @return void
   */
  function loadAdministrationRefPatient($type_view = "administration") {
    $patient = null;
    if ($this->type == "administration") {
      if ($type_view == "administration") {
        $source = $this->loadRefSource();
        if ($source instanceof CStockSejour) {
          $patient = $source->loadRefSejour()->loadRefPatient();
        }
      }
      else {
        $cible = $this->loadRefCible();
        if ($cible instanceof CStockSejour) {
          $patient = $cible->loadTargetObject()->loadRefPrescription()->loadRefObject()->loadRefPatient();
        }
      }

      return $this->_ref_patient = $patient;
    }
  }

  /**
   * Service de retour
   *
   * @return CService
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * Retourne les mouvements de stocks pour un stock séjour
   *
   * @param CStockSejour $stock_sejour
   * @param string       $type
   * @param string       $etat
   *
   * @return self[]
   */
  static function getFromStockSejour($stock_sejour, $type = "delivrance", $etat = "en_cours") {
    $stock_mvt              = new self();
    $stock_mvt->cible_class = $stock_sejour->_class;
    $stock_mvt->cible_id    = $stock_sejour->_id;
    $stock_mvt->type        = $type;
    $stock_mvt->etat        = $etat;

    return $stock_mvt->loadMatchingList();
  }
}
