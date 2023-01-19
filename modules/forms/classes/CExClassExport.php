<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\System\Forms\CExClass;

/**
 * ExClass export class
 */
class CExClassExport implements IShortNameAutoloadable
{
    public $export;

    static $backrefs_tree = [
        "CExClass" => [
            "field_groups",
        ],

        "CExClassFieldGroup" => [
            "class_fields",
            "host_fields",
            "class_messages",
            "subgroups",
            "class_pictures",
        ],

        "CExClassField" => [
            "field_translations",
            "list_items",
            "properties",
            "predicates",
            'ex_class_field_tag_items',
        ],

        'CExClassPicture' => [],

        "CExList" => [
            "list_items",
        ],

        "CExConcept" => [
            "list_items",
        ],

        "CExClassMessage" => [
            "properties",
        ],

        "CExClassFieldPredicate" => [
            "properties",
        ],

        "CExClassFieldSubgroup" => [
            "properties",
        ],

        "CExClassFieldProperty" => [],
    ];

    static $fwdrefs_tree = [
        "CExClassFieldGroup"       => [
            "ex_class_id",
        ],
        "CExClassField"            => [
            "ex_group_id",
            "concept_id",
            "predicate_id",
            "subgroup_id",
        ],
        "CExClassMessage"          => [
            "ex_group_id",
            "subgroup_id",
            "predicate_id",
        ],
        "CExClassHostField"        => [
            "ex_group_id",
            "subgroup_id",
        ],
        "CExClassFieldTranslation" => [
            "ex_class_field_id",
        ],

        'CExClassPicture' => [
            'ex_group_id',
            'subgroup_id',
            'predicate_id',
            'triggered_ex_class_id',
        ],

        "CExConcept" => [
            "ex_list_id",
        ],

        "CExListItem" => [
            "list_id",
            "concept_id",
            "field_id",
        ],

        "CExClassFieldProperty" => [
            "predicate_id",
            "object_id",
        ],

        "CExClassFieldPredicate" => [
            "ex_class_field_id",
        ],

        "CExClassFieldSubgroup" => [
            "predicate_id",
            "parent_id",
        ],

        'CExClassFieldTagItem' => [
            'ex_class_field_id',
        ],
    ];

    /**
     * CExClassExport constructor.
     *
     * @param int $ex_class_id The ExClass ID
     */
    function __construct($ex_class_id)
    {
        $ex_class = new CExClass();
        $ex_class->load($ex_class_id);

        try {
            $export = new CMbObjectExport($ex_class, self::$backrefs_tree);

            $export->setForwardRefsTree(self::$fwdrefs_tree);

            $this->export = $export;
        } catch (CMbException $e) {
            $e->stepAjax(UI_MSG_ERROR);
        }
    }

    /**
     * Stream the XML file
     *
     * @return void
     */
    function stream()
    {
        $this->export->streamXML();
    }

    /**
     * Get XML content
     *
     * @return string
     */
    function getContent()
    {
        return $this->export->toDOM()->saveXML();
    }
}
