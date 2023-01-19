<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

/**
 * File access check helper
 * Responsibilities:
 *  - path and description of path
 *  - checking
 */
class PathAccess extends Prerequisite
{
    public $path        = "";
    public $description = "";

    /**
     * Actually check path is writable
     *
     * @param bool $strict Check also warnings
     *
     * @return bool
     */
    function check($strict = true)
    {
        $root = dirname(__DIR__, 3);

        return is_writable($root . DIRECTORY_SEPARATOR . $this->path);
    }

    /**
     * @see parent::getAll()
     */
    function getAll()
    {
        $pathAccesses = [];

        $pathAccess              = new PathAccess();
        $pathAccess->path        = "tmp/";
        $pathAccess->description = "Répertoire des fichiers temporaires";

        $pathAccesses[] = $pathAccess;

        $pathAccess              = new PathAccess();
        $pathAccess->path        = "files/";
        $pathAccess->description = "Répertoire de tous les fichiers attachés";

        $pathAccesses[] = $pathAccess;

        $pathAccess              = new PathAccess();
        $pathAccess->path        = "lib/";
        $pathAccess->description = "Répertoire d'installation des bibliothèques tierces";

        $pathAccesses[] = $pathAccess;

        $pathAccess              = new PathAccess();
        $pathAccess->path        = "includes/";
        $pathAccess->description = "Répertoire du fichier de configuration du système";

        $pathAccesses[] = $pathAccess;

        $pathAccess              = new PathAccess();
        $pathAccess->path        = "vendor/";
        $pathAccess->description = "Répertoire des bibliotheques tierce";

        $pathAccesses[] = $pathAccess;

        return $pathAccesses;
    }
}
