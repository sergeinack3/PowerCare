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
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Hospi\CService;

/**
 * Service repository class
 */
class ServiceRepository implements IShortNameAutoloadable {
  /** @var CSQLDataSource */
  private $ds;

    /**
     * SejourRepository constructor.
     *
     * @param CSQLDataSource|null $ds
     */
  public function __construct(?CSQLDataSource $ds = null)
  {
      $this->ds = $ds ?: (new CService())->getDS();
  }

    /**
     * Find all services not cancelled with perms
     *
     * @param int $permType
     *
     * @return mixed|null
     * @throws Exception
     */
  public function findAllNotCancelledWithPerms(int $permType = PERM_READ) {
      $service    = new CService();
      $where      = [
          "cancelled" => $this->ds->prepare("= '0'"),
      ];

      return $service->loadListWithPerms($permType, $where);
  }
}
