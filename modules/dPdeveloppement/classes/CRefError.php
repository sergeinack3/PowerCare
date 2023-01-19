<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;

/**
 * Description
 */
class CRefError extends CMbObject {
  /** @var integer Primary key */
  public $ref_error_id;

  public $ref_check_field_id;
  public $missing_id;
  public $count_use;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "ref_errors";
    $spec->key      = "ref_error_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['ref_check_field_id'] = 'ref class|CRefCheckField notNull cascade back|errors';
    $props['missing_id']         = 'num';
    $props['count_use']          = 'num default|0';

    return $props;
  }

  /**
   * Count the errors for a field
   *
   * @param int $field_id The CRefCheckField id
   *
   * @return int
   */
  static function countErrorsByField($field_id) {
    $ds = CSQLDataSource::get('std');

    $query = new CRequest();
    $query->addSelect('SUM(count_use)');
    $query->addTable('ref_errors');
    $query->addWhere(
      array(
        'ref_check_field_id' => $ds->prepare('= ?', $field_id)
      )
    );

    return ($ds->loadResult($query->makeSelect())) ?: 0;
  }
}
