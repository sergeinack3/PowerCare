<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CCommuneCp extends CMbObject {
  /** @var integer Primary key */
  public $commune_cp_id;
  public $code_postal;
  public $commune_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'INSEE';
    $spec->loggable = false;
    $spec->table    = "communes_cp";
    $spec->key      = "commune_cp_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props['code_postal'] = 'str minLength|4 maxLength|5 notNull';
    $props['commune_id'] = 'ref class|CCommuneFrance notNull back|cp';

    return $props;
  }
}
