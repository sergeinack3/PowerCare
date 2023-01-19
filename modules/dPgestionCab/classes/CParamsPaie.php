<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\GestionCab;

use Ox\Core\CMbObject;

/**
 * The CParamsPaie Class
 */
class CParamsPaie extends CMbObject {
  // DB Table key
  public $params_paie_id;

  // DB Fields
  public $employecab_id;

  // Fiscalit�
  public $smic;   // valeur du smic horaire
  public $csgnis; // CSG non imposable salariale
  public $csgds;  // CSG d�ductible salariale
  public $csgnds; // CSG non d�ductible salariale
  public $ssms;   // s�curit� sociale maladie salariale
  public $ssmp;   // s�curit� sociale maladie patronale
  public $ssvs;   // s�curit� sociale vieillesse salariale
  public $ssvp;   // s�curit� sociale vieillesse patronale
  public $rcs;    // retraite compl�mentaire salariale
  public $rcp;    // retraite compl�mentaire patronale
  public $agffs;  // AGFF salariale
  public $agffp;  // AGFF patronale
  public $aps;    // assurance pr�voyance salariale
  public $app;    // assurance pr�voyance patronale
  public $acs;    // assurance chomage salariale
  public $acp;    // assurance chomage patronale
  public $aatp;   // assurance accident de travail patronale
  public $csp;    // contribution solidarit� patronnale
  public $ms;     // mutuelle salariale
  public $mp;     // mutuelle patronale

  // Employeur
  public $nom;
  public $adresse;
  public $cp;
  public $ville;
  public $siret;
  public $ape;

  // Utilisateur
  public $matricule; // num�ro de s�curit� sociale

  /** @var CEmployeCab */
  public $_ref_employe;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'params_paie';
    $spec->key   = 'params_paie_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["employecab_id"] = "ref notNull class|CEmployeCab back|params_paie";
    $props["smic"]          = "currency notNull|min|0";
    $props["csgnis"]        = "pct notNull";
    $props["csgds"]         = "pct notNull";
    $props["csgnds"]        = "pct notNull";
    $props["ssms"]          = "pct notNull";
    $props["ssmp"]          = "pct notNull";
    $props["ssvs"]          = "pct notNull";
    $props["ssvp"]          = "pct notNull";
    $props["rcs"]           = "pct notNull";
    $props["rcp"]           = "pct notNull";
    $props["agffs"]         = "pct notNull";
    $props["agffp"]         = "pct notNull";
    $props["aps"]           = "pct notNull";
    $props["app"]           = "pct notNull";
    $props["acs"]           = "pct notNull";
    $props["acp"]           = "pct notNull";
    $props["aatp"]          = "pct notNull";
    $props["csp"]           = "pct notNull";
    $props["ms"]            = "currency notNull min|0";
    $props["mp"]            = "currency notNull min|0";
    $props["nom"]           = "str notNull confidential";
    $props["adresse"]       = "text confidential";
    $props["cp"]            = "str length|5 confidential";
    $props["ville"]         = "str confidential";
    $props["siret"]         = "numchar length|14 confidential";
    $props["ape"]           = "str maxLength|6 confidential";
    $props["matricule"]     = "code insee confidential mask|9S99S99S9xS999S999S99";
    return $props;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->_ref_employe = new CEmployeCab;
    $this->_ref_employe->load($this->employecab_id);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_employe) {
      $this->loadRefsFwd();
    }
    return $this->_ref_employe->getPerm($permType);
  }

  /**
   * Charge un param�tre � partir d'un utilisateur
   *
   * @param int $employecab_id Employ� cabinet ID
   *
   * @return void
   */
  function loadFromUser($employecab_id) {
    $where = array();
    $where["employecab_id"] = "= '$employecab_id'";
    $this->loadObject($where);
  }
}
