<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbObject;
use Ox\Core\CMbString;

/**
 * Class CObjectifSoin
 */
class CObjectifSoinReeval extends CMbObject {
  public $objectif_reeval_id;

  public $objectif_soin_id;
  public $commentaire;
  public $date;

  /** @var CObjectifSoin */
  public $_ref_objectif_soin;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'objectif_soin_reeval';
    $spec->key   = 'objectif_reeval_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["objectif_soin_id"] = "ref notNull class|CObjectifSoin cascade back|reevaluations";
    $props["commentaire"]      = "text notNull";
    $props["date"]             = "date notNull";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = CMbString::truncate($this->commentaire, 40);
  }

  /**
   * Chargement de l'objectif de soin
   *
   * @return CObjectifSoin
   */
  function loadRefObjectifSoin() {
    return $this->_ref_objectif_soin = $this->loadFwdRef("objectif_soin_id", true);
  }
}