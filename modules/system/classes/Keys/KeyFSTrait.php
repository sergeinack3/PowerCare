<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys;

use Ox\Core\CAppUI;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotGetKeysFromStorage;
use Throwable;

/**
 * Simple trait for key on FS manipulations.
 */
trait KeyFSTrait
{
    /**
     * @param string $name
     *
     * @return string
     * @throws CouldNotGetKeysFromStorage
     */
    protected function getKeyPath(string $name): string
    {
        return $this->getDirectoryPath() . $name . DIRECTORY_SEPARATOR . 'key';
    }

    /**
     * @return string
     * @throws CouldNotGetKeysFromStorage
     */
    protected function getDirectoryPath(): string
    {
        try {
            $dir = CAppUI::conf('system KeyChain directory_path', 'static');
        } catch (Throwable $t) {
            throw CouldNotGetKeysFromStorage::unableToRetrieveStorage();
        }

        if (!$dir) {
            throw CouldNotGetKeysFromStorage::unableToRetrieveStorage();
        }

        if (!is_dir($dir)) {
            throw CouldNotGetKeysFromStorage::storageIsNotADirectory($dir);
        }

        if (!is_writeable($dir)) {
            throw CouldNotGetKeysFromStorage::storageIsNotWriteable($dir);
        }

        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
