<?php
/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Tests\Fixtures;

use Throwable;

/**
 * Fixtures exceptions.
 */
class FixturesException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(strip_tags($message), $code, $previous);
    }
}
