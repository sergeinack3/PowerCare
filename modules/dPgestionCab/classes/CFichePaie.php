<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\GestionCab;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;

/**
 * Fiche de paie
 */
class CFichePaie extends CMbObject {
  // DB Table key
  public $fiche_paie_id;

  // DB Fields
  public $params_paie_id;
  public $debut;
  public $fin;
  public $salaire;
  public $heures;
  public $heures_comp;
  public $heures_sup;
  public $precarite;
  public $anciennete;
  public $conges_payes;
  public $prime_speciale;

  public $final_file;

  // Forms Fields
  public $_salaire_base;
  public $_base_heures_sup;
  public $_salaire_heures_comp;
  public $_salaire_heures_sup;
  public $_total_heures;
  public $_prime_precarite;
  public $_prime_anciennete;
  public $_conges_payes;
  public $_salaire_brut;
  public $_base_csg;
  public $_base_csgnis;
  public $_base_csgds;
  public $_base_csgnds;
  public $_csgnis; // CSG non imposable salariale
  public $_csgds; // CSG déductible salariale
  public $_csgnds; // CSG non déductible salariale
  public $_ssms; // sécurité sociale maladie salariale
  public $_ssmp; // sécurité sociale maladie patronale
  public $_ssvs; // sécurité sociale vieillesse salariale
  public $_ssvp; // sécurité sociale vieillesse patronale
  public $_rcs; // retraite complémentaire salariale
  public $_rcp; // retraite complémentaire patronale
  public $_agffs; // AGFF salariale
  public $_agffp; // AGFF patronale
  public $_aps; // assurance prévoyance salariale
  public $_app; // assurance prévoyance patronale
  public $_acs; // assurance chomage salariale
  public $_acp; // assurance chomage patronale
  public $_aatp; // assurance accident de travail patronale
  public $_csp; // contribution solidarité patronale
  public $_reduc_heures_sup_pat;
  public $_reduc_heures_sup_sal;
  public $_reduc_bas_salaires;
  public $_total_retenues;
  public $_total_cot_patr;
  public $_total_heures_sup;
  public $_salaire_a_payer;
  public $_salaire_net;

  // Behaviour fields
  public $_final_store = false;

  /** @var CParamsPaie */
  public $_ref_params_paie;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'fiche_paie';
    $spec->key   = 'fiche_paie_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["params_paie_id"] = "ref notNull class|CParamsPaie back|fiches";
    $props["debut"]          = "date notNull";
    $props["fin"]            = "date notNull moreEquals|debut";
    $props["salaire"]        = "currency notNull min|0";
    $props["heures"]         = "num notNull max|255";
    $props["heures_comp"]    = "num notNull max|255";
    $props["heures_sup"]     = "num notNull max|255";
    $props["anciennete"]     = "pct notNull";
    $props["precarite"]      = "pct notNull";
    $props["conges_payes"]   = "pct notNull";
    $props["prime_speciale"] = "currency notNull min|0";
    $props["final_file"]     = "html";

    $props["_salaire_base"]         = "currency";
    $props["_base_heures_sup"]      = "currency";
    $props["_salaire_heures_comp"]  = "currency";
    $props["_salaire_heures_sup"]   = "currency";
    $props["_total_heures"]         = "currency";
    $props["_prime_precarite"]      = "currency";
    $props["_prime_anciennete"]     = "currency";
    $props["_conges_payes"]         = "currency";
    $props["_salaire_brut"]         = "currency";
    $props["_base_csg"]             = "currency";
    $props["_base_csgnis"]          = "currency";
    $props["_base_csgds"]           = "currency";
    $props["_base_csgnds"]          = "currency";
    $props["_csgnis"]               = "currency";
    $props["_csgds"]                = "currency";
    $props["_csgnds"]               = "currency";
    $props["_ssms"]                 = "currency";
    $props["_ssmp"]                 = "currency";
    $props["_ssvs"]                 = "currency";
    $props["_ssvp"]                 = "currency";
    $props["_rcs"]                  = "currency";
    $props["_rcp"]                  = "currency";
    $props["_agffs"]                = "currency";
    $props["_agffp"]                = "currency";
    $props["_aps"]                  = "currency";
    $props["_app"]                  = "currency";
    $props["_acs"]                  = "currency";
    $props["_acp"]                  = "currency";
    $props["_aatp"]                 = "currency";
    $props["_csp"]                  = "currency";
    $props["_reduc_heures_sup_pat"] = "currency";
    $props["_reduc_heures_sup_sal"] = "currency";
    $props["_reduc_bas_salaires"]   = "currency";
    $props["_total_retenues"]       = "currency";
    $props["_total_cot_patr"]       = "currency";
    $props["_total_heures_sup"]     = "currency";
    $props["_salaire_a_payer"]      = "currency";
    $props["_salaire_net"]          = "currency";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_locked = ($this->final_file !== null);

    $this->_view = "Fiche de paie du ".
      CMbDT::format($this->debut, CAppUI::conf("date"))." au ".
      CMbDT::format($this->fin, CAppUI::conf("date"));

    if ($this->fiche_paie_id) {
      // On charge cette référence dès le load
      $this->_ref_params_paie = new CParamsPaie();
      $this->_ref_params_paie->load($this->params_paie_id);
      $this->_ref_params_paie->loadRefsFwd();
      $this->_total_heures        = $this->heures + $this->heures_comp + $this->heures_sup;
      $this->_salaire_base        = $this->salaire * $this->heures;
      $this->_salaire_brut        = $this->_salaire_base;
      $this->_salaire_heures_comp = $this->salaire * $this->heures_comp;
      $this->_salaire_brut       += $this->_salaire_heures_comp;
      $this->_base_heures_sup     = $this->salaire * 1.25;
      $this->_salaire_heures_sup  = $this->_base_heures_sup * $this->heures_sup;
      $this->_salaire_brut       += $this->_salaire_heures_sup;
      $this->_total_heures_sup    = $this->_salaire_heures_comp + $this->_salaire_heures_sup;
      $this->_prime_precarite     = ($this->precarite / 100) *
                                    ($this->_salaire_base + $this->_total_heures_sup);
      $this->_salaire_brut       += $this->_prime_precarite;
      $this->_prime_anciennete    = ($this->anciennete / 100) *
                                    ($this->_salaire_base + $this->_total_heures_sup);
      $this->_salaire_brut       += $this->_prime_anciennete;
      $this->_conges_payes        = ($this->conges_payes / 100) *
                                    ($this->_salaire_base +
                                     $this->_total_heures_sup +
                                     $this->_prime_precarite +
                                     $this->_prime_anciennete);
      $this->_salaire_brut       += $this->_conges_payes;
      $this->_salaire_brut       += $this->prime_speciale;
      $this->_ssms                = $this->_salaire_brut * $this->_ref_params_paie->ssms / 100;
      $this->_total_retenues      = $this->_ssms;
      $this->_ssmp                = $this->_salaire_brut * $this->_ref_params_paie->ssmp / 100;
      $this->_total_cot_patr      = $this->_ssmp;
      $this->_ssvs                = $this->_salaire_brut * $this->_ref_params_paie->ssvs / 100;
      $this->_total_retenues     += $this->_ssvs;
      $this->_ssvp                = $this->_salaire_brut * $this->_ref_params_paie->ssvp / 100;
      $this->_total_cot_patr     += $this->_ssvp;
      $this->_rcs                 = $this->_salaire_brut * $this->_ref_params_paie->rcs / 100;
      $this->_total_retenues     += $this->_rcs;
      $this->_rcp                 = $this->_salaire_brut * $this->_ref_params_paie->rcp / 100;
      $this->_total_cot_patr     += $this->_rcp;
      $this->_agffs               = $this->_salaire_brut * $this->_ref_params_paie->agffs / 100;
      $this->_total_retenues     += $this->_agffs;
      $this->_agffp               = $this->_salaire_brut * $this->_ref_params_paie->agffp / 100;
      $this->_total_cot_patr     += $this->_agffp;
      $this->_aps                 = $this->_salaire_brut * $this->_ref_params_paie->aps / 100;
      $this->_total_retenues     += $this->_aps;
      $this->_app                 = $this->_salaire_brut * $this->_ref_params_paie->app / 100;
      $this->_total_cot_patr     += $this->_app;
      // On peut calculer ici la CSG/RDS
      $this->_base_csgnis     = ($this->_salaire_brut
                                 - $this->_salaire_heures_sup
                                 - $this->_salaire_heures_comp
                                 + $this->_app + $this->_ref_params_paie->mp) * 0.97;
      $this->_csgnis          = $this->_base_csgnis * $this->_ref_params_paie->csgnis / 100;
      $this->_total_retenues += $this->_csgnis;
      $this->_base_csgnds     = $this->_base_csgnis;
      $this->_csgnds          = $this->_base_csgnds * $this->_ref_params_paie->csgnds / 100;
      $this->_total_retenues += $this->_csgnds;
      $this->_base_csgds      = ($this->_total_heures_sup) * 0.97;
      $this->_csgds           = $this->_base_csgds * $this->_ref_params_paie->csgds / 100;
      $this->_total_retenues += $this->_csgds;
      // On reviens à nos cotisations classiques
      $this->_acs             = $this->_salaire_brut * $this->_ref_params_paie->acs / 100;
      $this->_total_retenues += $this->_acs;
      $this->_acp             = $this->_salaire_brut * $this->_ref_params_paie->acp / 100;
      $this->_total_cot_patr += $this->_acp;
      $this->_aatp            = $this->_salaire_brut * $this->_ref_params_paie->aatp / 100;
      $this->_total_cot_patr += $this->_aatp;
      $this->_csp             = $this->_salaire_brut * $this->_ref_params_paie->csp / 100;
      $this->_total_cot_patr += $this->_csp;
      // Mutuelle
      $this->_total_retenues += $this->_ref_params_paie->ms;
      $this->_total_cot_patr += $this->_ref_params_paie->mp;
      // Réductions bas salaires
      $this->_reduc_bas_salaires = (0.281/0.6) * (1.6 * ($this->_ref_params_paie->smic * $this->heures / $this->_salaire_brut) - 1);
      $this->_reduc_bas_salaires = min(0.281, $this->_reduc_bas_salaires) * $this->_salaire_brut;
      $this->_reduc_bas_salaires = max(0, $this->_reduc_bas_salaires);
      $this->_total_cot_patr    -= $this->_reduc_bas_salaires;
      // Défiscalisation des heures sup
      $this->_reduc_heures_sup_sal = $this->_total_heures_sup * 0.215;
      $this->_total_retenues      -= $this->_reduc_heures_sup_sal;
      $this->_reduc_heures_sup_pat = $this->heures_sup * 1.5;
      $this->_total_cot_patr      -= $this->_reduc_heures_sup_pat;
      $this->_salaire_a_payer      = $this->_salaire_brut - $this->_total_retenues;
      $this->_salaire_net          = $this->_salaire_a_payer + $this->_csgnds - $this->_total_heures_sup;
    }
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->_ref_params_paie = new CParamsPaie;
    $this->_ref_params_paie->load($this->params_paie_id);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_params_paie) {
      $this->loadRefsFwd();
    }

    return ($this->_ref_params_paie->getPerm($permType));
  }

  /**
   * @see parent::store()
   */
  function store() {
    // Must store to get all fields
    if ($this->_final_store) {
      $this->loadRefsFwd();
      $this->_ref_params_paie->loadRefsFwd();
      $this->updateFormFields();

      // Création du template
      $smarty = new CSmartyDP();
      $smarty->assign("fichePaie" , $this);

      $this->final_file = $smarty->fetch("print_fiche.tpl");
      file_put_contents("tmp/fichePaie.htm", $this->final_file);
    }

    return parent::store();
  }
}
