<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys\Exceptions;

use Ox\Core\CMbException;

/**
 * Abstract class for key-related exceptions.
 * Mainly used for type-hint simplification.
 */
abstract class KeyException extends CMbException
{
}
