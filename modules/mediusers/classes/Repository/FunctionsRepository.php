<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Repository;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Functions repository class
 */
class FunctionsRepository implements IShortNameAutoloadable
{
    /** @var CSQLDataSource */
    private $ds;

    /**
     * FunctionsRepository constructor.
     *
     * @param CSQLDataSource|null $ds
     */
    public function __construct(?CSQLDataSource $ds = null)
    {
        $this->ds = $ds ?: (new CFunctions())->getDS();
    }

    /**
     * Find all specialties
     *
     * @param int|null $perm_type
     * @param int      $include_empty
     *
     * @return array
     */
    public function findAllSpecialties(?int $perm_type = null, int $include_empty = 0): array
    {
        return (new CFunctions())->loadSpecialites($perm_type, $include_empty);
    }
}
