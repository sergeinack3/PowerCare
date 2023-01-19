<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Atih\CUniteMedicaleInfos;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CUniteMedicale;
use Symfony\Component\Routing\RouterInterface;

/**
 * Unité fonctionnelle
 */
class CUniteFonctionnelle extends CMbObject {
  const RESOURCE_TYPE = 'uf';

  // DB Table key
  public $uf_id;

  // DB Fields
  public $group_id;
  public $code;
  public $libelle;
  public $description;
  public $type;
  public $type_sejour;
  public $date_debut;
  public $date_fin;
  public $type_autorisation_um;

  /** @var CGroups */
  public $_ref_group;

  /** @var CAffectationUniteFonctionnelle[] */
  public $_ref_affectations_uf;

  /** @var CMediusers[] */
  public $_ref_praticiens;

  /** @var CLit[] */
  public $_ref_lits;

  /** @var CChambre */
  public $_ref_chambre;

  /** @var CService */
  public $_ref_service;

  /** @var CUniteMedicale */
  public $_ref_um;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'uf';
    $spec->key   = 'uf_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('hospi_uf', ["uf_id" => $this->_id]);
    }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref class|CGroups notNull back|unites_fonctionnelles";
    $props["code"]        = "str notNull seekable";
    $props["libelle"]     = "str notNull seekable fieldset|default";
    $props["description"] = "text";
    $props["type"]        = "enum list|hebergement|medicale|soins default|hebergement";
    $props["type_sejour"] = "enum list|comp|ambu|exte|seances|ssr|psy|urg|consult";
    $props["date_debut"]  = "date";
    $props["date_fin"]    = "date";
    if (class_exists("CUniteMedicaleInfos")) {
      $props["type_autorisation_um"] = "ref class|CUniteMedicaleInfos back|um_infos";
    }

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->libelle;
  }

  /**
   * @return CUniteMedicaleInfos
   */
  function loadRefUm() {
    if (!CModule::getActive("atih")) {
      return null;
    }

    return $this->_ref_um = $this->loadFwdRef("type_autorisation_um", true);
  }

  /**
   * Récupération de l'uf
   *
   * @param string $code_uf  code de l'uf
   * @param string $type     type de l'uf
   * @param int    $group_id group
   * @param string $date_deb date de début
   * @param string $date_fin date de fin
   *
   * @return CUniteFonctionnelle
   */
  static function getUF($code_uf, $type = null, $group_id = null, $date_deb = null, $date_fin = null) {
    $uf = new self;

    if (!$code_uf) {
      return $uf;
    }

    $group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

    $where["code"]     = " = '$code_uf'";
    $where["type"]     = " = '$type'";
    $where["group_id"] = " = '$group_id'";

    if ($date_fin) {
      $where[] = "uf.date_debut IS NULL OR uf.date_debut < '" . CMbDT::date($date_fin) . "'";
    }
    if ($date_deb) {
      $where[] = "uf.date_fin IS NULL OR uf.date_fin > '" . CMbDT::date($date_deb) . "'";
    }

    $uf->loadObject($where);

    return $uf;
  }

  /**
   * Chargement des types d'ufs
   *
   * @param string $object   Objet concerné
   * @param string $group_id Etablissement éventuel
   * @param array  $where    Conditions optionnelles
   *
   * @return array()
   */
  static function getUFs($object = null, $group_id = null, $where = array()) {
    $uf = new self;

    $tab_ufs = array(
      "hebergement" => array(),
      "medicale"    => array(),
      "soins"       => array()
    );

    if ($object && in_array($object->_class, array("CService", "CChambre", "CLit"))) {
      unset($tab_ufs["medicale"]);
    }
    elseif ($object && in_array($object->_class, array("CMediusers", "CFunctions"))) {
      unset($tab_ufs["hebergement"]);
      unset($tab_ufs["soins"]);
    }

    if ($object && ($object instanceof CSejour || $object instanceof CAffectation)) {
      if ($object->entree) {
        $where["date_debut"] = "IS NULL OR date_debut <= '" . CMbDT::date($object->entree) . "'";
      }
      else {
        $where["date_debut"] = "IS NULL OR date_debut <= '" . CMbDT::date() . "'";
      }

      if ($object->sortie) {
        $where["date_fin"] = "IS NULL OR date_fin >= '" . CMbDT::date($object->sortie) . "'";
      }
      else {
        $where["date_fin"] = "IS NULL OR date_fin >= '" . CMbDT::date() . "'";
      }
    }

    $where["group_id"] = "= '" . ($group_id ?: CGroups::loadCurrent()->_id) . "'";

    foreach ($tab_ufs as $type => $_tab_ufs) {
      $where["type"]  = "= '$type'";
      $tab_ufs[$type] = $uf->loadList($where, "libelle");
    }

    return $tab_ufs;
  }

  /**
   * Calcul les alertes pour un séjour ou une affectation
   * par rapport à la date d'ouverture et de fermeture des UFs
   *
   * @param CSejour|CAffectation $object
   *
   * @return void
   */
  static function getAlertesUFs($object) {
    if (!$object instanceof CSejour && !$object instanceof CAffectation) {
      return;
    }

    $object->loadRefUFHebergement();
    $object->loadRefUFMedicale();
    $object->loadRefUFSoins();

    $entree = CMbDT::date($object->entree);
    $sortie = CMbDT::date($object->sortie);

    foreach (
      array(
        "uf_hebergement_id" => $object->_ref_uf_hebergement,
        "uf_medicale_id"    => $object->_ref_uf_medicale,
        "uf_soins_id"       => $object->_ref_uf_soins
      ) as $key_uf => $_uf) {
      if (!$_uf->_id) {
        continue;
      }

      if (!$_uf->date_debut && !$_uf->date_fin) {
        continue;
      }

      $alerte = false;

      // Expiration si l'uf commence strictement après l'entrée du séjour / affectation
      if ($_uf->date_debut) {
        $alerte = $_uf->date_debut > $entree;
      }

      if (!$alerte && !$_uf->date_fin) {
        continue;
      }

      // Expiration si :
      // - fin inférieure à l'entrée du séjour / affectation
      // - fin inférieure strictement à la sortie du séjour / affectation (une fin simultanée ne pose pas de souci)
      $alerte = $alerte || ($_uf->date_fin <= $entree) || ($_uf->date_fin < $sortie);

      if ($alerte) {
        $object->_alertes_ufs[] = CAppUI::tr(
          "CUniteFonctionnelle-alerte_expiration_uf",
          CAppUI::tr("CAffectation-$key_uf"),
          $_uf->libelle,
          strtolower(CAppUI::tr($object->_class))
        );
      }
    }
  }
}

