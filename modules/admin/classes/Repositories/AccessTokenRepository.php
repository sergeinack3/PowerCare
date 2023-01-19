<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Repositories;

use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CViewAccessToken;

/**
 * Access Token Repository.
 */
class AccessTokenRepository
{
    /** @var CViewAccessToken */
    private $model;

    /** @var CSQLDataSource */
    private $ds;

    public function __construct()
    {
        $this->model = new CViewAccessToken();
        $this->ds    = $this->model->getDS();
    }

    public function findByHash(string $hash): ?CViewAccessToken
    {
        if (!$hash) {
            return null;
        }

        $token       = clone $this->model;
        $token->hash = $hash;

        $token->loadMatchingObjectEsc();

        if (!$token->_id) {
            return null;
        }

        return $token;
    }
}
