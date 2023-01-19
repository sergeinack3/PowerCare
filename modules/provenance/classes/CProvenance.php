<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CProvenance
 *
 * @package Ox\Mediboard\Provenance
 */
class CProvenance extends CMbObject {
  // Table Key
  public $provenance_id;

  public $group_id;
  public $libelle;
  public $desc;
  public $actif;

  /** @var CGroups */
  public $_ref_group;

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec = parent::getSpec();

    $spec->table = 'provenance';
    $spec->key   = 'provenance_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['group_id'] = 'ref notNull class|CGroups back|provenances';
    $props['libelle']  = 'str notNull maxLength|50 seekable';
    $props['desc']     = 'text';
    $props['actif']    = 'bool notNull default|1';

    return $props;
  }

  /**
   * @inheritDoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Chargement de l'établissement associé
   *
   * @return CStoredObject|null
   * @throws Exception
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

}