<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Persister;

use Ox\Core\CStoredObject;

/**
 * Object persister for import
 */
interface PersisterVisitorInterface
{
    public function persistObject(CStoredObject $object): CStoredObject;
}
