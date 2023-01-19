<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Repositories;

use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Repository to fetch CSampleMovie objects.
 */
abstract class AbstractMailRepository
{
    public abstract function countUnreadMails(?CMediusers $user = null): int;
}
