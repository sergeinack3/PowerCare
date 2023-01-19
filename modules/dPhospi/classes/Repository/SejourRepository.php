<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Repository;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Sejour repository class
 */
class SejourRepository implements IShortNameAutoloadable {
  /** @var CSQLDataSource */
  private $ds;

    /**
     * SejourRepository constructor.
     *
     * @param CSQLDataSource|null $ds
     */
  public function __construct(?CSQLDataSource $ds = null)
  {
      $this->ds = $ds ?: (new CSejour())->getDS();
  }

    /**
     * @param array $ljoin
     * @param array $where
     *
     * @return mixed|null
     * @throws Exception
     */
  public function findIdsByUserAction(array $ljoin = [], array $where = []) {
      $request     = new CRequest();
      $request->addSelect("object_id, object_id");
      $request->addTable("user_action");
      $request->addLJoin($ljoin);
      $request->addWhere($where);

      return $this->ds->loadHashList($request->makeSelect());
  }
}
