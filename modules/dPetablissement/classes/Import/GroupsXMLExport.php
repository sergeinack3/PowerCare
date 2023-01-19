<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Import;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Group exporting utility class
 */
class GroupsXMLExport
{
    public const GROUP_BACKREFS_TREE = [
        "CGroups"         => [
            "functions",
            "blocs",
            "services",
            "secteurs",
            "unites_fonctionnelles",
        ],
        "CFunctions"      => [
            "users",
        ],
        "CBlocOperatoire" => [
            "salles",
        ],
        "CService"        => [
            "chambres",
        ],
        "CChambre"        => [
            "lits",
        ],
    ];

    public const GROUP_FWREFS_TREE = [
        "CMediusers" => [
            "user_id",
        ],
    ];

    public const FUNCTION_BACKREFS_TREE = [
        "CFunctions" => [
            "users",
        ],
    ];

    public const FUNCTION_FWREFS_TREE = [
        "CMediusers" => [
            "user_id",
        ],
        "CFunctions" => [
            "group_id",
        ],
    ];

    private CMbObjectExport $object_export;

    /**
     * Group export constructor
     *
     * @param CMbObject $object Object to export
     *
     * @throws CMbException
     * @throws Exception
     */
    public function __construct(CMbObject $object)
    {
        if ($object instanceof CGroups) {
            $this->object_export = new CMbObjectExport($object, self::GROUP_BACKREFS_TREE);
            $this->object_export->setForwardRefsTree(self::GROUP_FWREFS_TREE);
        } elseif ($object instanceof CFunctions) {
            $this->object_export = new CMbObjectExport($object, self::FUNCTION_BACKREFS_TREE);
            $this->object_export->setForwardRefsTree(self::FUNCTION_FWREFS_TREE);
        } else {
            throw new Exception(CAppUI::tr('mod-dPetablissement-msg-Object not group or function'));
        }

        $this->object_export->empty_values = false;
    }

    public function streamXML(): void
    {
        $this->object_export->streamXML();
    }
}
