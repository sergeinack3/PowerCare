<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Repository;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Mediusers repository class
 */
class MediusersRepository implements IShortNameAutoloadable {
  /** @var CSQLDataSource */
  private $ds;

    /**
     * MediusersRepository constructor.
     *
     * @param CSQLDataSource|null $ds
     */
  public function __construct(?CSQLDataSource $ds = null)
  {
      $this->ds = $ds ?: (new CMediusers())->getDS();
  }

    /**
     * Find all practicioner
     *
     * @return array
     * @throws Exception
     */
    public function findAllPracticioner(bool $use_group = false): array
    {
        return (new CMediusers())->loadPraticiens(
            PERM_READ,
            null,
            null,
            false,
            true,
            $use_group,
            null
        );
    }
}
