<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

/**
 * Note concernant une hiérarchie CsARR
 */
class CNoteHierarchieCsARR extends CCsARRObject {

  public $hierarchie;
  public $idnote;
  public $typenote;
  public $niveau;
  public $libelle;
  public $ordre;
  public $hierarchie_exclue;
  public $code_exclu;

  /** @var CHierarchieCsARR */
  public $_ref_hierarchie;
  /** @var CHierarchieCsARR */
  public $_ref_hierarchie_exclue;
  /** @var CActiviteCsARR */
  public $_ref_activite_exclue;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'note_hierarchie';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["hierarchie"]        = "str notNull length|11 seekable";
    $props["idnote"]            = "str notNull length|10";
    $props["typenote"]          = "enum notNull list|aut_note|avec_sans|codage|compr_tit|def|exclusion|inclus";
    $props["niveau"]            = "num show|0";
    $props["libelle"]           = "text notNull seekable";
    $props["ordre"]             = "num show|0";
    $props["hierarchie_exclue"] = "str length|11 seekable";
    $props["code_exclu"]        = "str length|7 seekable";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = "$this->hierarchie ($this->typenote): $this->libelle";
    $this->_shortview = $this->idnote;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefHierarchie();
    $this->loadRefHierarchieExlue();
    $this->loadRefCodeExclu();
  }

  /**
   * Charge la hiérarchie associée
   *
   * @return CHierarchieCsARR
   */
  function loadRefHierarchie() {
    return $this->_ref_hierarchie = CHierarchieCsARR::get($this->hierarchie);
  }

  /**
   * Charge la hiérarchie exclue
   *
   * @return CHierarchieCsARR
   */
  function loadRefHierarchieExlue() {
    return $this->_ref_hierarchie_exclue = CHierarchieCsARR::get($this->code_exclus);
  }

  /**
   * Charge l'activité exclue
   *
   * @return CActiviteCsARR
   */
  function loadRefCodeExclu() {
    return $this->_ref_activite_exclue = CActiviteCsARR::get($this->code_exclus);
  }
}
