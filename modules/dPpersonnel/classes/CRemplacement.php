<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CRemplacement
 */
class CRemplacement extends CMbObject {
  // DB Table key
  public $remplacement_id;

  // DB Fields
  public $debut;
  public $fin;
  public $remplace_id;
  public $remplacant_id;
  public $libelle;
  public $description;

  // Object References
  /** @var CMediusers */
  public $_ref_remplace;
  /** @var CMediusers */
  public $_ref_remplacant;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $specs        = parent::getSpec();
    $specs->table = "remplacement";
    $specs->key   = "remplacement_id";

    return $specs;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["debut"]         = "dateTime notNull";
    $props["fin"]           = "dateTime moreEquals|debut notNull";
    $props["remplace_id"]   = "ref class|CMediusers notNull back|user_remplace";
    $props["remplacant_id"] = "ref class|CMediusers notNull back|user_remplacant";
    $props["libelle"]       = "str notNull";
    $props["description"]   = "text";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_shortview = $this->_view = $this->libelle;
  }

  /**
   * Chargement du remplaçant
   *
   * @return CMediusers
   */
  function loadRefRemplacant() {
    $this->_ref_remplacant = $this->loadFwdRef("remplacant_id", true);
    $this->_ref_remplacant->loadRefFunction();

    return $this->_ref_remplacant;
  }

  /**
   * Chargement du remplacé
   *
   * @return CMediusers
   */
  function loadRefRemplace() {
    $this->_ref_remplace = $this->loadFwdRef("remplace_id", true);
    $this->_ref_remplace->loadRefFunction();

    return $this->_ref_remplace;
  }

  /**
   * @see parent::check()
   */
  function check() {
    $this->completeField("debut", "fin", "remplace_id");

    $duree_max = CAppUI::gconf("personnel CRemplacement duree_max");
    if ($duree_max && CMbDT::hoursRelative($this->debut, $this->fin) > $duree_max) {
      return CAppUI::tr("CRemplacement-depassement_duree_max %s", $duree_max);
    }

    if (!$this->_id) {
      $where["remplace_id"] = " = '$this->remplace_id'";
      $where[]              = "debut BETWEEN '$this->debut' AND '$this->fin'
                               OR fin BETWEEN '$this->debut' AND '$this->fin'";
      $remplacement         = new CRemplacement();
      $remplacement->loadObject($where);
      if ($remplacement->_id) {
        return CAppUI::tr("CRemplacement-conflit %s", $remplacement->_view);
      }
    }

    return parent::check();
  }
}
