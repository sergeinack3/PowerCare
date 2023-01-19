<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
/**
 * Class CObjectifSoin
 */
class CObjectifSoin extends CMbObject {
  public $objectif_soin_id;

  public $sejour_id;
  public $libelle;
  public $statut;
  public $date;            // Date de création
  public $user_id;         // Utilisateur ayant créé l'objectif de soin
  public $cloture_date;    // Date de cloture
  public $cloture_user_id; // Utilisateur ayant clôturé l'objectif
  public $moyens;          // Les moyens définis
  public $delai;           // Délai d'accomplissement
  public $resultat;
  public $objectif_soin_categorie_id;
  public $priorite;
  public $intervenants;
  public $commentaire;
  public $alerte;

  /** @var  CSejour */
  public $_ref_sejour;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CMediusers */
  public $_ref_cloture_user;

  /** @var CObjectifSoinReeval[] */
  public $_ref_reevaluations;

  /** @var CObjectifSoinCategorie */
  public $_ref_categorie;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'objectif_soin';
    $spec->key   = 'objectif_soin_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                               = parent::getProps();
    $props["sejour_id"]                  = "ref notNull class|CSejour back|objectifs_soins";
    $props["libelle"]                    = "text notNull helped";
    $props["statut"]                     = "enum list|ouvert|atteint|non_atteint default|ouvert";
    $props["date"]                       = "dateTime notNull";
    $props["user_id"]                    = "ref notNull class|CMediusers back|user";
    $props["cloture_date"]               = "dateTime";
    $props["cloture_user_id"]            = "ref class|CMediusers back|cloture_user";
    $props["moyens"]                     = "text helped";
    $props["delai"]                      = "date";
    $props["resultat"]                   = "text";
    $props["objectif_soin_categorie_id"] = "ref class|CObjectifSoinCategorie back|objectifs";
    $props["priorite"]                   = "bool";
    $props["intervenants"]               = "text helped";
    $props["commentaire"]                = "text helped";
    $props["alerte"]                     = "bool";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = CMbString::truncate($this->libelle, 40);
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if ($this->fieldModified("statut")) {
      if ($this->statut == "ouvert") {
        $this->cloture_date    = "";
        $this->cloture_user_id = "";
      }
      else {
        $this->cloture_date    = CMbDT::dateTime();
        $this->cloture_user_id = CAppUI::$user->_id;
      }
    }
  }

  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  function loadRefsCible() {
    return $this->_ref_cibles = $this->loadBackRefs("cibles");
  }

  /**
   * Chargement des réévaluations de l'objectif
   *
   * @return CObjectifSoinReeval[]
   */
  function loadRefsReevaluations() {
    return $this->_ref_reevaluations = $this->loadBackRefs("reevaluations", "date");
  }

  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id");
  }

  function loadRefClotureUser() {
    return $this->_ref_cloture_user = $this->loadFwdRef("cloture_user_id");
  }

  /**
   *Chargement de la catégorie d'objectif de soin
   *
   * @return CObjectifSoinCategorie
   */

  function loadRefCategorie() {
    return $this->_ref_categorie = $this->loadFwdRef("objectif_soin_categorie_id");
  }
}
