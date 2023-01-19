<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CMbObject;

/**
 * Class of specialty Asip
 */
class CSpecialtyAsip extends CMbObject {

  public $libelle;
  public $oid;
  public $code;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'ASIP';
    $spec->table    = 'authorspecialty_20121112';
    $spec->key      = 'table_id';
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @see parent::getProps
   */
  function getProps() {
    $props = parent::getProps();

    $props['code']    = 'str';
    $props['oid']     = 'str';
    $props['libelle'] = 'str';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    if ($this->libelle) {
      $this->_view = $this->libelle;
    }
  }
}