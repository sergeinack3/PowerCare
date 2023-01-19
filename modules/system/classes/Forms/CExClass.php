<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Forms\Traits\HermeticModeTrait;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Classe de défintion d'un formulaire type
 *
 * Cette classe définit le libellé et des aspects généraux d'un formulaire.
 */
class CExClass extends CMbObject implements FormComponentInterface
{
    use HermeticModeTrait;

    public $ex_class_id;

    public $name;
    public $conditional;
    public $native_views;
    public $cross_context_class;
    public $category_id;
    public $allow_create_in_column;
    public $pixel_positionning;
    public $permissions;

    /** @var int */
    public $nb_columns;

    /** @var CExClassField[] */
    public $_ref_fields;

    /** @var CExClassEvent[] */
    public $_ref_events;

    /** @var CExClassFieldGroup[] */
    public $_ref_groups;

    /** @var CExClassCategory */
    public $_ref_category;

    /** @var CExClassFieldNotification[] */
    public $_ref_ex_notifications;

    public $_fields_by_name;

    /** @var bool Don't create the default group when creating a CExClass */
    public $_dont_create_default_group;

    /** @var bool Duplicate $this */
    public $_duplicate;
    public $_formula_field;
    public $_icon_name;
    public $_permissions;

    private $_latest_ex_object_cache = [];

    public $_duplication_mapping = [];

    /** @var self[] */
    static $_list_cache = [];

    /**
     * Which contexts can use native views
     * @var array
     */
    public static array $_native_views = [
        "atcd"       => ['CSejour', 'CGrossesse'],
        "constantes" => ['CSejour'],
        "corresp"    => ['CPatient'],
        "fiches"     => ['CSejour'],
    ];

    static $_permission_types = [
        10000 => "Par défaut (si aucune autre règle ne s'applique)",
        -10   => "Quelqu'un d'autre que l'auteur",
    ];

    static $_permission_field = [
        "c" => 0x4, // create
        "e" => 0x3, // edit. Someone who can edit can also view (3 = 2+1)
        "v" => 0x1, // view
    ];

    static $_permission_mask = [
        "c" => 0x4, // create
        "e" => 0x2, // edit
        "v" => 0x1, // view
    ];

    /** @var CExObject */
    public $_ex_object;

    /** @var array */
    public $_grid;

    /** @var array */
    public $_out_of_grid;

    /** @var CMbObject[] */
    public $_host_objects;

    /** @var CExClassCategory[] */
    public $_categories;

    /** @var string[] */
    public $_wrong_formulas;

    /**
     * Compare values with each other with a comparison operator
     *
     * @param string|float $a        Operand A
     * @param string       $operator Operator
     * @param string|float $b        Operand B
     *
     * @return bool
     */
    static function compareValues($a, $operator, $b)
    {
        // =|!=|>|>=|<|<=|startsWith|endsWith|contains default|=
        switch ($operator) {
            default:
            case "=":
                return $a == $b;

            case "!=":
                return $a != $b;

            case ">":
                return $a > $b;

            case ">=":
                return $a >= $b;

            case "<":
                return $a < $b;

            case "<=":
                return $a <= $b;

            case "startsWith":
                return strpos($a, $b) === 0;

            case "endsWith":
                return substr($a, -strlen($b)) == $b;

            case "contains":
                return strpos($a, $b) !== false;

            case "hasValue":
                return $a != "";

            case "hasNoValue":
                return $a == "";

            case "in":
                return in_array($a, $b);

            case "notIn":
                return !in_array($a, $b);
        }
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                      = parent::getSpec();
        $spec->table               = "ex_class";
        $spec->key                 = "ex_class_id";
        $spec->uniques["ex_class"] = ["group_id", "name"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                           = parent::getProps();
        $props["name"]                   = "str notNull seekable";
        $props["conditional"]            = "bool notNull default|0";
        $props["pixel_positionning"]     = "bool notNull default|0";
        $props["group_id"]               = "ref class|CGroups back|ex_classes";
        $props["native_views"]           = "set vertical list|" . implode("|", array_keys(self::$_native_views));
        $props["cross_context_class"]    = "enum list|CPatient";
        $props["category_id"]            = "ref class|CExClassCategory back|ex_classes";
        $props["allow_create_in_column"] = "bool notNull default|0";
        $props["permissions"]            = "text show|0";
        $props['nb_columns']             = 'num';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadEditView()
    {
        parent::loadEditView();

        CExObject::initLocales();

        CExObject::$_locales_cache_enabled = false;

        $ds = $this->getDS();

        // Recherche des formules qui contiennent un champ désactivé ou supprimé
        $query    = "SELECT ex_class_field.ex_class_field_id, formula FROM ex_class_field
              LEFT JOIN ex_class_field_group ON ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id
              WHERE formula IS NOT NULL AND ex_class_field_group.ex_class_id = '$this->_id' AND ex_class_field.disabled = '0'";
        $formulas = $ds->loadHashList($query);

        $query       = "SELECT ex_class_field.ex_class_field_id, ex_class_field.name FROM ex_class_field
              LEFT JOIN ex_class_field_group ON ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id
              WHERE ex_class_field_group.ex_class_id = '$this->_id' AND ex_class_field.disabled = '0'";
        $field_names = $ds->loadHashList($query);

        $missing = [];
        foreach ($formulas as $ex_class_field_id => $_formula) {
            $matches = [];
            if (preg_match_all('/\[([^\]]+)\]/', $_formula, $matches)) {
                $_fields = $matches[1];
                if (array_intersect($_fields, $field_names) != $_fields) {
                    $missing[$ex_class_field_id] = $_formula;
                }
            }
        }

        $this->_wrong_formulas = $missing;

        if ($this->pixel_positionning) {
            $grid        = null;
            $out_of_grid = null;
            $this->getPixelGrid(true);

            foreach ($this->_ref_groups as $_ex_group) {
                $_ex_group->loadRefsSubgroups(true);
                $_pictures = $_ex_group->loadRefsPictures(true);
                foreach ($_pictures as $_picture) {
                    $_picture->loadRefTriggeredExClass();
                }
                $_subgroups = $_ex_group->loadRefsSubgroups(true);
                foreach ($_subgroups as $_subgroup) {
                    $_subgroup->countBackRefs("subgroups");
                    $_subgroup->countBackRefs("children_fields");
                    $_subgroup->countBackRefs("children_messages");
                }
            }
        } else {
            [$grid, $out_of_grid] = $this->getGrid(4, 60, false, true);
        }

        $events = $this->loadRefsEvents();
        foreach ($events as $_event) {
            $_event->countBackRefs("constraints");
            $_event->countBackRefs("mandatory_constraints");
        }

        $this->loadAvailableGroups();
        $this->_ex_object = $this->getExObjectInstance();

        $this->_grid        = $grid;
        $this->_out_of_grid = $out_of_grid;

        if (!$this->_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        $classes   = CExClassEvent::getReportableClasses();
        $instances = [];
        foreach ($classes as $_class) {
            /** @var CMbObject $_instance */
            $_instance          = new $_class();
            $instances[$_class] = [];

            foreach ($_instance->getProps() as $_field => $_spec) {
                $_show_1 = (strpos($_spec, 'show|1') !== false);
                $_show_0 = (strpos($_spec, 'show|0') !== false);

                if ($_field[0] === '_') {
                    if ($_field == '_view' || $_show_1) {
                        $instances[$_class][$_field] = ($_field == '_view') ? 'Vue' : CAppUI::tr("{$_class}-{$_field}");
                    }
                } elseif (!$_show_0) {
                    $instances[$_class][$_field] = CAppUI::tr("{$_class}-{$_field}");
                }
            }

            CMbArray::naturalSort($instances[$_class]);

            if (isset($instances[$_class]['_view'])) {
                $_view = $instances[$_class]['_view'];
                unset($instances[$_class]['_view']);
                $instances[$_class] = array_merge(['_view' => $_view], $instances[$_class]);
            }
        }

        $this->_host_objects = $instances;

        $category          = new CExClassCategory();
        $this->_categories = $category->loadList(null, "title");
    }

    /**
     * @inheritdoc
     */
    function getExportedBackRefs()
    {
        $export                       = parent::getExportedBackRefs();
        $export["CExClass"]           = ["field_groups"];
        $export["CExClassFieldGroup"] = ["class_fields", "host_fields", "class_messages"];
        $export["CExConcept"]         = ["list_items"];
        $export["CExList"]            = ["list_items"];

        return $export;
    }

    /**
     * Builds a WHERE statement from search data
     *
     * @param array $search Structure containign search data
     *
     * @return array
     */
    function getWhereConceptSearch($search)
    {
        $comp_map = [
            "eq"  => "=",
            "lte" => "<=",
            "lt"  => "<",
            "gte" => ">=",
            "gt"  => ">",
        ];

        $_fields = $this->loadRefsAllFields();
        $_table  = $this->getTableName();
        $where   = [];

        $same_concepts = [];
        foreach ($_fields as $_field) {
            if ($_field->concept_id) {
                if (!array_key_exists($_field->concept_id, $same_concepts)) {
                    $same_concepts[$_field->concept_id] = ['count' => 1, 'where' => []];
                } else {
                    $same_concepts[$_field->concept_id]['count']++;
                }
            }
        }

        foreach ($_fields as $_field) {
            if (!isset($search[$_field->concept_id])) {
                continue;
            }

            $_ds  = $_field->_spec->ds;
            $_col = "$_table.$_field->name";

            foreach ($search[$_field->concept_id] as $_val) {
                $_val_a = $_val['a'];
                $_comp  = $_val['comp'];

                if ($same_concepts[$_field->concept_id]['count'] > 1) {
                    if (isset($comp_map[$_comp])) {
                        $same_concepts[$_field->concept_id]['where'][] = "$_col " . $_ds->prepare(
                                $comp_map[$_comp] . "%",
                                $_val_a
                            );
                    } else {
                        switch ($_comp) {
                            case "contains":
                                $same_concepts[$_field->concept_id]['where'][] = "$_col " . $_ds->prepareLike(
                                        "%$_val_a%"
                                    );
                                break;
                            case "begins":
                                $same_concepts[$_field->concept_id]['where'][] = "$_col " . $_ds->prepareLike(
                                        "$_val_a%"
                                    );
                                break;
                            case "ends":
                                $same_concepts[$_field->concept_id]['where'][] = "$_col " . $_ds->prepareLike(
                                        "%$_val_a"
                                    );
                                break;
                            case "between":
                                $_val_b                                        = $_val['b'];
                                $same_concepts[$_field->concept_id]['where'][] = "$_col " . $_ds->prepare(
                                        "BETWEEN ?1 AND ?2",
                                        $_val_a,
                                        $_val_b
                                    );
                                break;
                            case "inSet":
                                $same_concepts[$_field->concept_id]['where'][] = sprintf(
                                    '(%s OR %s OR %s OR %s)',
                                    "$_col " . $_ds->prepare('= ?', $_val_a),
                                    "$_col " . $_ds->prepareLike("$_val_a|%"),
                                    "$_col " . $_ds->prepareLike("%|$_val_a"),
                                    "$_col " . $_ds->prepareLike("%|$_val_a|%")
                                );
                                break;
                            default:
                                // Do nothing
                        }
                    }
                } else {
                    if (isset($comp_map[$_comp])) {
                        $where[$_col] = $_ds->prepare($comp_map[$_comp] . "%", $_val_a);
                    } else {
                        switch ($_comp) {
                            case "contains":
                                $where[$_col] = $_ds->prepareLike("%$_val_a%");
                                break;
                            case "begins":
                                $where[$_col] = $_ds->prepareLike("$_val_a%");
                                break;
                            case "ends":
                                $where[$_col] = $_ds->prepareLike("%$_val_a");
                                break;
                            case "between":
                                $_val_b       = $_val['b'];
                                $where[$_col] = $_ds->prepare("BETWEEN ?1 AND ?2", $_val_a, $_val_b);
                                break;
                            case "inSet":
                                $where[] = sprintf(
                                    '(%s OR %s OR %s OR %s)',
                                    "$_col " . $_ds->prepare('= ?', $_val_a),
                                    "$_col " . $_ds->prepareLike("$_val_a|%"),
                                    "$_col " . $_ds->prepareLike("%|$_val_a"),
                                    "$_col " . $_ds->prepareLike("%|$_val_a|%")
                                );
                                break;
                            default:
                                // Do nothing
                        }
                    }
                }
            }
        }

        foreach ($same_concepts as $_concept) {
            if ($_concept['where']) {
                $where[] = implode(' OR ', $_concept['where']);
            }
        }

        return $where;
    }

    /**
     * Get a CExObject instance
     *
     * @param bool $cache Use cache
     *
     * @return CExObject
     */
    function getExObjectInstance($cache = false)
    {
        static $instances = [];

        if ($cache && isset($instances[$this->_id])) {
            return clone $instances[$this->_id];
        }

        $ex_object = new CExObject($this->_id);

        if ($cache) {
            $instances[$this->_id] = $ex_object;
        }

        return $ex_object;
    }

    /**
     * Gets the latest CExObject for the given CMbObject host object
     *
     * @param CMbObject $object The host object
     *
     * @return CExObject The resolved CExObject
     */
    function getLatestExObject(CMbObject $object)
    {
        if (isset($this->_latest_ex_object_cache[$object->_class][$object->_id])) {
            return $this->_latest_ex_object_cache[$object->_class][$object->_id];
        }

        // Todo: Enable after ref checking
        //    $ex_object = new CExObject($this->_id);
        //    $ds        = $ex_object->getDS();
        //
        //    // Getting from 'object' level
        //    $where_object = array(
        //      'object_class' => $ds->prepare('= ?', $object->_class),
        //      'object_id'    => $ds->prepare('= ?', $object->_id),
        //    );
        //
        //    $ex_object->loadObject($where_object, 'ex_object_id DESC');
        //
        //    // Getting from 'ref1' level if newer than previous one
        //    $where_ref1 = array(
        //      'reference_class' => $ds->prepare('= ?', $object->_class),
        //      'reference_id'    => $ds->prepare('= ?', $object->_id),
        //    );
        //
        //    if ($ex_object && $ex_object->_id) {
        //      $where_ref1['ex_object_id'] = $ds->prepare('> ?', $ex_object->_id);
        //    }
        //
        //    $ex_object->loadObject($where_ref1, 'ex_object_id DESC');
        //
        //    // Getting from 'ref2' level if newer than previous one
        //    $where_ref2 = array(
        //      'reference2_class' => $ds->prepare('= ?', $object->_class),
        //      'reference2_id'    => $ds->prepare('= ?', $object->_id),
        //    );
        //
        //    if ($ex_object && $ex_object->_id) {
        //      $where_ref2['ex_object_id'] = $ds->prepare('> ?', $ex_object->_id);
        //    }
        //
        //    $ex_object->loadObject($where_ref2, 'ex_object_id DESC');

        $ex_link = new CExLink();
        $where   = [
            "object_class" => " = '$object->_class'",
            "object_id"    => " = '$object->_id'",
            "ex_class_id"  => " = '$this->_id'",
            "level"        => $ex_link->getDS()->prepareIn(["object", "ref1", "ref2"]),
        ];
        $ex_link->loadObject($where, "ex_object_id DESC", null, null, "object");

        $ex_object = $ex_link->loadRefExObject();

        return $this->_latest_ex_object_cache[$object->_class][$object->_id] = $ex_object;
    }

    /**
     * Get completeness level of latest form. according to given context
     *
     * @param CMbObject $object Form. context
     *
     * @return null|string
     */
    function getLastestExObjectCompleteness(CMbObject $object)
    {
        $ex_object = $this->getLatestExObject($object);

        if (!$ex_object || !$ex_object->_id) {
            return null;
        }

        return $ex_object->completeness_level;
    }

    /**
     * Get number of fields in alert of latest form. according to given context
     *
     * @param CMbObject $object Form. context
     *
     * @return null|int
     */
    function getLastestExObjectAlertFieldsNumber(CMbObject $object)
    {
        $ex_object = $this->getLatestExObject($object);

        if (!$ex_object || !$ex_object->_id) {
            return null;
        }

        return $ex_object->nb_alert_fields;
    }

    /**
     * Get completeness and number of alerts of a form for a given event name
     *
     * @param CMbObject $object     Object
     * @param string    $event_name Name of the event
     *
     * @return array
     */
    static function getLatestCompletenessByEvent(CMbObject $object, $event_name)
    {
        $completeness = [];

        if (!$object || !$object->_id || !$event_name) {
            return $completeness;
        }

        $group_id = CGroups::loadCurrent()->_id;
        $ds       = CSQLDataSource::get('std');

        $where = [
            "group_id = '$group_id' OR group_id IS NULL",
            "ex_class_event.disabled"   => "= '0'",
            "ex_class.conditional"      => "= '0'",
            "ex_class_event.event_name" => $ds->prepare('= ?', $event_name),
        ];

        $ljoin = [
            "ex_class_event" => "ex_class_event.ex_class_id = ex_class.ex_class_id",
        ];

        $ex_class = new static();

        /** @var CExClass[] $ex_classes */
        $ex_classes = $ex_class->loadList($where, null, null, 'ex_class.ex_class_id', $ljoin);

        /** @var CExClass[] $ex_classes_filtered */
        $ex_classes_filtered = [];

        foreach ($ex_classes as $_ex_class_id => $_ex_class) {
            if (!$_ex_class->canPerm("c")) {
                unset($ex_classes[$_ex_class_id]);
                continue;
            }

            $ex_classes_filtered[$_ex_class_id] = $_ex_class;
        }

        foreach ($ex_classes_filtered as $_ex_class_id => $_ex_class) {
            $completeness[$_ex_class_id] = [
                'ex_object'    => $_ex_class->getLatestExObject($object),
                'completeness' => $_ex_class->getLastestExObjectCompleteness($object),
                'nb_alerts'    => $_ex_class->getLastestExObjectAlertFieldsNumber($object),
            ];
        }

        return $completeness;
    }

    /**
     * Get the CExObject class name
     *
     * @return string
     */
    function getExClassName()
    {
        return "CExObject_{$this->_id}";
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->getFormulaField();

        $this->_view = $this->name;
    }

    /**
     * Get the list of formula fields of the form
     *
     * @return string|null
     */
    function getFormulaField()
    {
        static $list = null;

        if ($list === null) {
            $ds = $this->getDS();

            $request = new CRequest();
            $request->addSelect(["ex_class_field_group.ex_class_id", "ex_class_field.name"]);
            $request->addTable("ex_class_field");
            $where = [
                "ex_class_field.result_in_title" => "= '1'",
                "ex_class_field.disabled"        => "= '0'",
            ];
            $request->addWhere($where);
            $ljoin = [
                "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
            ];
            $request->addLJoin($ljoin);

            $list = $ds->loadHashList($request->makeSelect());
        }

        return $this->_formula_field = CValue::read($list, $this->_id, null);
    }

    /**
     * Get the formula ExField
     *
     * @return CExClassField[]
     */
    function getFormulaExFields()
    {
        static $list = [];

        if (!$this->_id) {
            return [];
        }

        if (!array_key_exists($this->_id, $list)) {
            $_ex_class_field = new CExClassField();
            $where           = [
                "ex_class_field_group.ex_class_id" => "= '$this->_id'",
                "ex_class_field.formula"           => "IS NOT NULL",
            ];
            $ljoin           = [
                "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
            ];

            $list[$this->_id] = $_ex_class_field->loadList($where, null, null, null, $ljoin);
        }

        return $list[$this->_id];
    }

    /**
     * Get the formula result, from the result field name
     *
     * @param string $field_name Field name
     * @param array  $where      The WHERE statement
     *
     * @return array|null
     */
    function getFormulaResult($field_name, $where)
    {
        $ds    = $this->getDS();
        $table = $this->getTableName();

        $where["ex_link.ex_class_id"] = "= '$this->_id'";

        $ljoin = [
            "$table" => "ex_link.ex_object_id = $table.ex_object_id",
        ];

        $request = new CRequest();
        $request->addSelect($field_name);
        $request->addTable("ex_link");
        $request->addWhere($where);
        $request->addLJoin($ljoin);
        $request->addForceIndex("object"); // Index "object" de la table ex_link
        $request->addOrder("ex_link.ex_object_id DESC");

        return $ds->loadResult($request->makeSelect());
    }

    /**
     * Load the "field_groups" back refs
     *
     * @return CExClassFieldGroup[]
     */
    function loadRefsGroups()
    {
        static $groups_cache = [];

        if (isset($groups_cache[$this->_id])) {
            return $this->_ref_groups = $groups_cache[$this->_id];
        }

        $this->_ref_groups = $this->loadBackRefs("field_groups", "rank, ex_class_field_group_id");

        $groups_cache[$this->_id] = $this->_ref_groups;

        return $this->_ref_groups;
    }

    /**
     * Load the fields
     *
     * @return CExClassField[]
     */
    function loadRefsAllFields()
    {
        $cache = new Cache('CExClass.loadRefsAllFields', $this->_id, Cache::INNER);
        if (!$cache->exists()) {
            $groups = $this->loadRefsGroups();

            CStoredObject::massLoadBackRefs(
                $groups,
                "class_fields",
                "IF(tab_index IS NULL, 10000, tab_index), ex_class_field_id",
                null,
                null,
                null,
                false
            );

            $fields = [];
            foreach ($groups as $_group) {
                $_fields = $_group->loadRefsFields();
                $fields  += $_fields;
            }

            $cache->put([$groups, $fields]);

            return $fields;
        }

        [$groups, $fields] = $cache->get();
        $this->_ref_groups = $groups;

        return $fields;
    }

    /**
     * Load the "events" back refs
     *
     * @return CExClassEvent[]
     */
    function loadRefsEvents()
    {
        return $this->_ref_events = $this->loadBackRefs("events");
    }

    /**
     * Load the category
     *
     * @return CExClassCategory
     */
    function loadRefCategory()
    {
        return $this->_ref_category = $this->loadFwdRef("category_id");
    }

    /**
     * Load the notifications
     *
     * @return CExClassFieldNotification[]
     */
    function loadRefsNotifications()
    {
        $notif = new CExClassFieldNotification();
        $where = [
            "ex_class_field_group.ex_class_id" => "= '$this->_id'",
        ];
        $ljoin = [
            "ex_class_field_predicate" => "ex_class_field_predicate.ex_class_field_predicate_id = ex_class_field_notification.predicate_id",
            "ex_class_field"           => "ex_class_field.ex_class_field_id                     = ex_class_field_predicate.ex_class_field_id",
            "ex_class_field_group"     => "ex_class_field_group.ex_class_field_group_id         = ex_class_field.ex_group_id",
        ];

        return $this->_ref_ex_notifications = $notif->loadList($where, null, null, null, $ljoin);
    }

    /**
     * Get the table name
     *
     * @return string
     */
    function getTableName()
    {
        return "ex_object_{$this->_id}";
    }

    /**
     * Load CExObjects and inject the CExObject instance into $ex_object
     *
     * @param CMbObject $object    The host object
     * @param null      $ex_object The variable where the CExObject will be injected
     *
     * @return CExObject[]
     */
    function loadExObjects(CMbObject $object, &$ex_object = null)
    {
        $ex_object = new CExObject($this->_id);
        $ex_object->setObject($object);

        /** @var CExObject[] $list */
        $list = $ex_object->loadMatchingList();

        foreach ($list as $_object) {
            $_object->_ex_class_id = $this->_id;
            $_object->setExClass();
        }

        return $list;
    }

    /**
     * Load all the elements to be pu on the pixel grid
     *
     * @param bool $include_disabled Include disabled fields
     *
     * @return CExClassFieldGroup[]
     */
    function getPixelGrid($include_disabled = false)
    {
        $all_groups = $this->loadRefsGroups();

        $groups = [];
        if (!$include_disabled) {
            foreach ($all_groups as $_ex_group) {
                if ($_ex_group->disabled) {
                    continue;
                }

                $groups[$_ex_group->_id] = $_ex_group;
            }
        } else {
            $groups = $all_groups;
        }

        foreach ($groups as $_ex_group) {
            // Subgroups
            $_ex_group->loadRefsSubgroups(true);
            CStoredObject::massCountBackRefs($_ex_group->_ref_subgroups, "properties");

            foreach ($_ex_group->_ref_subgroups as $_ex_subgroup) {
                $_ex_subgroup->getDefaultProperties();
                $_ex_subgroup->recursiveLoadSubGroupsProperties();
            }

            // Fields
            $_ex_group->loadRefsRootFields();

            CStoredObject::massCountBackRefs($_ex_group->_ref_fields, "properties");
            CStoredObject::massLoadBackRefs($_ex_group->_ref_fields, 'hypertext_links');

            foreach ($_ex_group->_ref_fields as $_ex_field) {
                $_ex_field->loadRefsHyperTextLink();
                $_ex_field->getSpecObject();
                $_ex_field->getDefaultProperties();
            }

            // Messages
            $_ex_group->loadRefsRootMessages();

            CStoredObject::massCountBackRefs($_ex_group->_ref_messages, "properties");
            CStoredObject::massLoadBackRefs($_ex_group->_ref_messages, 'hypertext_links');

            foreach ($_ex_group->_ref_messages as $_ex_message) {
                $_ex_message->loadRefsHyperTextLink();
                $_ex_message->getDefaultProperties();
            }

            // Pictures
            $_pictures = $_ex_group->loadRefsRootPictures();
            foreach ($_pictures as $_picture) {
                $_picture->loadRefFile();
            }

            // Host fields
            $_ex_group->loadRefsRootHostFields();

            // Action buttons
            $_ex_group->loadRefsRootActionButtons();

            // Widgets
            $_ex_group->loadRefsRootWidgets();
        }

        return $groups;
    }

    /**
     * Get the width of the disposition grid
     *
     * @return int
     */
    public function getGridWidth()
    {
        return ($this->nb_columns > 1) ? $this->nb_columns : 4;
    }

    /**
     * Build the grid
     *
     * @param int  $w                Grid width
     * @param int  $h                Grid height
     * @param bool $reduce           Reduced the grid if it contains empty rows or empty columns
     * @param bool $include_disabled Include disabled fields
     *
     * @return array
     */
    function getGrid($w = 4, $h = 60, $reduce = true, $include_disabled = false)
    {
        $w = $this->getGridWidth();

        $big_grid        = [];
        $big_out_of_grid = [];
        $all_groups      = $this->loadRefsGroups();
        $empty_cell      = ["type" => null, "object" => null];

        $groups = [];
        if (!$include_disabled) {
            foreach ($all_groups as $_ex_group) {
                if ($_ex_group->disabled) {
                    continue;
                }

                $groups[$_ex_group->_id] = $_ex_group;
            }
        } else {
            $groups = $all_groups;
        }

        foreach ($groups as $_ex_group) {
            $grid = array_fill(0, $h, array_fill(0, $w, $empty_cell));

            $out_of_grid = [
                "field"         => [],
                "label"         => [],
                "message_title" => [],
                "message_text"  => [],
            ];

            $_fields = $_ex_group->loadRefsFields();

            CStoredObject::massCountBackRefs($_fields, "properties");
            CStoredObject::massLoadBackRefs($_fields, 'hypertext_links');

            foreach ($_fields as $_ex_field) {
                $_ex_field->loadRefsHyperTextLink();
                $_ex_field->getSpecObject();
                $_ex_field->getDefaultProperties();

                $label_x = $_ex_field->coord_label_x;
                $label_y = $_ex_field->coord_label_y;

                $field_x = $_ex_field->coord_field_x;
                $field_y = $_ex_field->coord_field_y;

                // label
                if ($label_x === null || $label_y === null) {
                    $out_of_grid["label"][$_ex_field->name] = $_ex_field;
                } else {
                    $grid[$label_y][$label_x] = [
                        "type"   => "label",
                        "object" => $_ex_field,
                    ];
                }

                // field
                if ($field_x === null || $field_y === null) {
                    $out_of_grid["field"][$_ex_field->name] = $_ex_field;
                } else {
                    $grid[$field_y][$field_x] = [
                        "type"   => "field",
                        "object" => $_ex_field,
                    ];
                }
            }

            // Host fields
            $_host_fields = $_ex_group->loadRefsHostFields();
            foreach ($_host_fields as $_host_field) {
                if ($_host_field->type) {
                    continue;
                }

                $label_x = $_host_field->coord_label_x;
                $label_y = $_host_field->coord_label_y;

                $value_x = $_host_field->coord_value_x;
                $value_y = $_host_field->coord_value_y;

                // label
                if ($label_x !== null && $label_y !== null) {
                    $grid[$label_y][$label_x] = [
                        "type"   => "label",
                        "object" => $_host_field,
                    ];
                }

                // value
                if ($value_x !== null && $value_y !== null) {
                    $grid[$value_y][$value_x] = [
                        "type"   => "value",
                        "object" => $_host_field,
                    ];
                }
            }

            // Messages
            $_ex_messages = $_ex_group->loadRefsMessages();

            CStoredObject::massCountBackRefs($_ex_messages, "properties");
            CStoredObject::massLoadBackRefs($_ex_messages, 'hypertext_links');

            foreach ($_ex_messages as $_message) {
                $_message->loadRefsHyperTextLink();
                $_message->getDefaultProperties();

                $title_x = $_message->coord_title_x;
                $title_y = $_message->coord_title_y;

                $text_x = $_message->coord_text_x;
                $text_y = $_message->coord_text_y;

                // label
                if ($title_x === null || $title_y === null) {
                    $out_of_grid["message_title"][$_message->_id] = $_message;
                } else {
                    $grid[$title_y][$title_x] = [
                        "type"   => "message_title",
                        "object" => $_message,
                    ];
                }

                // value
                if ($text_x === null || $text_y === null) {
                    $out_of_grid["message_text"][$_message->_id] = $_message;
                } else {
                    $grid[$text_y][$text_x] = [
                        "type"   => "message_text",
                        "object" => $_message,
                    ];
                }
            }

            if ($reduce) {
                $max_filled = 0;

                foreach ($grid as $_y => $_line) {
                    $n_filled = 0;
                    $x_filled = 0;

                    foreach ($_line as $_x => $_cell) {
                        if ($_cell !== $empty_cell) {
                            $n_filled++;
                            $x_filled = max($_x, $x_filled);
                        }
                    }

                    if ($n_filled == 0) {
                        unset($grid[$_y]);
                    }

                    $max_filled = max($max_filled, $x_filled);
                }

                if (empty($out_of_grid)) {
                    foreach ($grid as $_y => $_line) {
                        $grid[$_y] = array_slice($_line, 0, $max_filled + 1);
                    }
                }
            }

            $big_grid       [$_ex_group->_id] = $grid;
            $big_out_of_grid[$_ex_group->_id] = $out_of_grid;
        }

        return [
            $big_grid,
            $big_out_of_grid,
            $groups,
            "grid"        => $big_grid,
            "out_of_grid" => $big_out_of_grid,
            "groups"      => $groups,
        ];
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($error = $this->checkGroupStoring()) {
            return $error;
        }

        if ($this->_id && $this->_duplicate) {
            $this->_duplicate = null;

            return $this->duplicate();
        }

        if ($msg = $this->check()) {
            return $msg;
        }

        $is_new = !$this->_id;

        if ($is_new) {
            if ($msg = parent::store()) {
                return $msg;
            }

            // Groupe par défaut
            if (!$this->_dont_create_default_group) {
                $ex_group              = new CExClassFieldGroup();
                $ex_group->name        = "Groupe général";
                $ex_group->ex_class_id = $this->_id;
                $ex_group->store();
            }

            $table_name = $this->getTableName();
            $query      = "CREATE TABLE `$table_name` (
        `ex_object_id`     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `group_id`         INT(11) UNSIGNED NOT NULL,
        
        `object_id`        INT(11) UNSIGNED NOT NULL,
        `object_class`     VARCHAR(80) NOT NULL,
        
        `reference_id`     INT(11) UNSIGNED NOT NULL,
        `reference_class`  VARCHAR(80) NOT NULL,
        
        `reference2_id`    INT(11) UNSIGNED NOT NULL,
        `reference2_class` VARCHAR(80) NOT NULL,

        `additional_id`    INT (11) UNSIGNED,
        `additional_class` VARCHAR(80),

        `datetime_create` DATETIME,
        `datetime_edit`   DATETIME,
        `owner_id`        INT(11) UNSIGNED,
        `completeness_level` ENUM('none', 'some', 'all') DEFAULT NULL,
        `nb_alert_fields`    SMALLINT UNSIGNED           DEFAULT NULL,
        
        INDEX ( `group_id` ),
        INDEX `object`     ( `object_class`,     `object_id` ),
        INDEX `reference1` ( `reference_class`,  `reference_id` ),
        INDEX `reference2` ( `reference2_class`, `reference2_id` ),
        INDEX `additional` ( `additional_class`, `additional_id` ),
        INDEX ( `owner_id` ),
        INDEX ( `datetime_create` )
      ) /*! ENGINE=MyISAM */;";

            $ds = $this->_spec->ds;
            if (!$ds->query($query)) {
                return "La table '$table_name' n'a pas pu être créée (" . $ds->error() . ")";
            }
        }

        return parent::store();
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        if ($msg = $this->canDeleteEx()) {
            return $msg;
        }

        // suppression des objets des champs sans supprimer les colonnes de la table
        $fields = $this->loadRefsAllFields();
        foreach ($fields as $_field) {
            $_field->_dont_drop_column = true;
            $_field->delete();
        }

        // Keep ex_object_XXX table, just in case ....
        /*$table_name = $this->getTableName();
        $query = "DROP TABLE `$table_name`";

        $ds = $this->_spec->ds;
        if (!$ds->query($query)) {
          return "La table '$table_name' n'a pas pu être supprimée (".$ds->error().")";
        }*/

        return parent::delete();
    }

    /**
     * Duplicates the object
     *
     * - field_groups
     *   - class_fields
     *     - field_translations
     *     - list_items
     *     - ex_triggers
     *     - (predicates)
     *
     *   - host_fields
     *   - class_messages
     *
     * - constraints
     * - ex_triggers
     *
     * @return null|string Store-like message
     */
    function duplicate()
    {
        if (!$this->_id) {
            return null;
        }

        // Load all field values
        $this->load();

        $new = new self;
        $new->cloneFrom($this);

        $new->name                       .= " (Copie)";
        $new->_dont_create_default_group = true;

        $this->_duplication_mapping = [];

        if ($msg = $new->store()) {
            return $msg;
        }

        // field_groups
        foreach ($this->loadRefsGroups() as $_group) {
            if ($msg = $this->duplicateExObject($_group, "ex_class_id", $new->_id, $_new_group)) {
                continue;
            }

            $fwd_field = "ex_group_id";
            $fwd_value = $_new_group->_id;

            // class_fields
            foreach ($_group->loadRefsFields() as $_field) {
                $_exclude_fields = ["predicate_id", "subgroup_id"];
                if ($msg = $this->duplicateExObject(
                    $_field,
                    "ex_group_id",
                    $_new_group->_id,
                    $_new_field,
                    $_exclude_fields
                )) {
                    continue;
                }

                $_fwd_field = "ex_class_field_id";
                $_fwd_value = $_new_field->_id;

                // field_translations
                $this->duplicateExBackRefs($_field, "field_translations", $_fwd_field, $_fwd_value);

                // list_items
                $this->duplicateExBackRefs($_field, "list_items", "field_id", $_fwd_value);

                // ex_triggers
                $this->duplicateExBackRefs($_field, "ex_triggers", $_fwd_field, $_fwd_value);

                // predicates
                $this->duplicateExBackRefs($_field, "predicates", $_fwd_field, $_fwd_value);
            }

            $where = ["subgroup_id" => "IS NULL"];
            // class_messages
            $this->duplicateExBackRefs($_group, "class_messages", $fwd_field, $fwd_value, ["predicate_id"], $where);

            // host_fields
            $this->duplicateExBackRefs($_group, "host_fields", $fwd_field, $fwd_value, [], $where);

            // subgroups
            $this->duplicateExBackRefs($_group, "subgroups", "parent_id", $fwd_value, ["predicate_id", "subgroup_id"]);

            /** @var CExClassFieldSubgroup[] $_subgroups */
            $_subgroups = $_group->loadBackRefs("subgroups");
            foreach ($_subgroups as $_subgroup) {
                [$_new_class, $_new_id] = explode('-', $this->_duplication_mapping[$_subgroup->_guid]);
                $this->duplicateSubgroups($_subgroup, $_new_id, $fwd_value);
            }
        }

        // Duplication of the display_conditions
        // field_groups
        foreach ($this->loadRefsGroups() as $_group) {
            // class_fields
            foreach ($_group->loadRefsFields() as $_field) {
                if ($_field->predicate_id) {
                    $_predicate_guid = $this->_duplication_mapping["CExClassFieldPredicate-$_field->predicate_id"];
                    [$_precidate_class, $_predicate_id] = explode("-", $_predicate_guid);

                    /** @var CExClassField $_new_field */
                    $_new_field                 = CStoredObject::loadFromGuid(
                        $this->_duplication_mapping[$_field->_guid]
                    );
                    $_new_field->predicate_id   = $_predicate_id;
                    $_new_field->_keep_position = true;
                    $_new_field->store();
                }

                if ($_field->subgroup_id) {
                    $_subgroup_guid = $this->_duplication_mapping["CExClassFieldSubgroup-$_field->subgroup_id"];
                    [$_subgroup_class, $_subgroup_id] = explode("-", $_subgroup_guid);

                    /** @var CExClassField $_new_field */
                    $_new_field                 = CStoredObject::loadFromGuid(
                        $this->_duplication_mapping[$_field->_guid]
                    );
                    $_new_field->subgroup_id    = $_subgroup_id;
                    $_new_field->_keep_position = true;
                    $_new_field->store();
                }
            }

            // Also duplicate predicates and sub_groups for messages
            foreach ($_group->loadRefsMessages() as $_message) {
                if ($_message->predicate_id) {
                    $_predicate_guid = $this->_duplication_mapping["CExClassFieldPredicate-$_message->predicate_id"];
                    [$_precidate_class, $_predicate_id] = explode("-", $_predicate_guid);

                    /** @var CExClassMessage $new_message */
                    $new_message               = CStoredObject::loadFromGuid(
                        $this->_duplication_mapping[$_message->_guid]
                    );
                    $new_message->predicate_id = $_predicate_id;
                    $new_message->store();
                }

                if ($_message->subgroup_id) {
                    $_subgroup_guid = $this->_duplication_mapping["CExClassFieldSubgroup-$_message->subgroup_id"];
                    [$_subgroup_class, $_subgroup_id] = explode("-", $_subgroup_guid);

                    /** @var CExClassMessage $_new_field */
                    $_new_message              = CStoredObject::loadFromGuid(
                        $this->_duplication_mapping[$_message->_guid]
                    );
                    $_new_message->subgroup_id = $_subgroup_id;
                    $_new_message->store();
                }
            }
        }

        // ex_triggers
        $this->duplicateExBackRefs($this, "ex_triggers", "ex_class_triggered_id", $new->_id);

        CExObject::clearLocales();

        $this->_duplication_mapping = [];

        return null;
    }

    /**
     * Duplicate a subgroup and its subgroups
     *
     * @param CExClassFieldSubgroup $object       Subgroup to duplicate, childs subgroups will be duplicate aswell
     * @param int                   $fwd_value    Value of the new field
     * @param int                   $parent_value ex_group_id to use
     *
     * @return void
     */
    function duplicateSubgroups(CExClassFieldSubgroup $object, $fwd_value, $parent_value)
    {
        $this->duplicateExBackRefs($object, "subgroups", "parent_id", $fwd_value, ["predicate_id"]);

        $fields = ["subgroup_id" => $fwd_value, "ex_group_id" => $parent_value];
        $this->duplicateExBackRefs($object, "children_messages", $fields, null, ["predicate_id"]);
        $this->duplicateExBackRefs($object, "children_host_field", $fields);

        $_subgroups = $object->loadBackRefs("subgroups");
        /** @var CExClassFieldSubgroup $_subgroup */
        foreach ($_subgroups as $_subgroup) {
            [$_new_class, $_new_id] = explode('-', $this->_duplication_mapping[$_subgroup->_guid]);
            $this->duplicateSubgroups($_subgroup, $_new_id, $parent_value);
        }
    }

    /**
     * Duplicates an ex object
     *
     * @param CMbObject $object         The object to duplicate
     * @param string    $fwd_field      Forward field
     * @param mixed     $fwd_value      Forward value
     * @param CMbObject $new            The new object (input)
     * @param array     $exclude_fields Excluded fields
     *
     * @return null|string
     */
    function duplicateExObject(CMbObject $object, $fwd_field, $fwd_value = null, &$new = null, $exclude_fields = [])
    {
        if (isset($this->_duplication_mapping[$object->_guid])) {
            return null;
        }

        $class = $object->_class;

        /** @var CExObject $new */
        $new = new $class;
        $new->cloneFrom($object);

        foreach ($exclude_fields as $_field) {
            $new->$_field = null;
        }

        if (is_array($fwd_field)) {
            foreach ($fwd_field as $_field => $_fw_value) {
                $new->$_field = $_fw_value;
            }
        } else {
            $new->$fwd_field = $fwd_value;
        }

        if ($new instanceof CExClassField) {
            $new->_make_unique_name = false;
        }

        if ($msg = $new->store()) {
            return $msg;
        }

        $this->_duplication_mapping[$object->_guid] = $new->_guid;

        return $msg;
    }

    /**
     * Duplicate back refs
     *
     * @param CMbObject    $object         Object to duplicate back refs of
     * @param string       $backname       Back reference name
     * @param string|array $fwd_field      Forward field name
     * @param mixed        $fwd_value      Forward field value
     * @param array        $exclude_fields Excluded fields
     * @param array        $where          Condition to load back refs
     *
     * @return void
     */
    function duplicateExBackRefs(
        CMbObject $object,
                  $backname,
                  $fwd_field,
                  $fwd_value = null,
                  $exclude_fields = [],
                  $where = []
    ) {
        $new = null;
        foreach ($object->loadBackRefs($backname, null, null, null, null, null, null, $where) as $_back) {
            /** @var CMbObject $_back */
            $this->duplicateExObject($_back, $fwd_field, $fwd_value, $new, $exclude_fields);
        }
    }

    function makeIconName()
    {
        $file = new CFile();

        return $this->_icon_name = $file->makeIconName($this->name);
    }

    /**
     * Get all permission types, except "patient"
     *
     * @return array
     */
    static function getAllPermTypes()
    {
        $types = self::$_permission_types + CUser::$types;

        // Type patient
        unset($types[22]);

        ksort($types);

        return $types;
    }

    /**
     * Get permission schema for $this
     *
     * @param bool $fill Fill the schema with empty values
     *
     * @return bool[][]
     */
    function getPermissions($fill = false)
    {
        $this->completeField("permissions");
        $permissions = $this->permissions ? json_decode($this->permissions, true) : [];

        $all_types = self::getAllPermTypes();

        unset($all_types[0]);

        foreach ($all_types as $_type => $_label) {
            if ($fill || isset($permissions[$_type])) {
                $permissions[$_type] = [
                    "c" => CValue::read($permissions[$_type], "c", false),
                    "e" => CValue::read($permissions[$_type], "e", false),
                    "v" => CValue::read($permissions[$_type], "v", false),
                    "d" => CValue::read($permissions[$_type], "d", false),
                ];
            }
        }

        ksort($permissions);

        return $this->_permissions = $permissions;
    }

    /**
     * Make the permission bit-field for the permission structure
     *
     * @param array $permissions Array of the form [c => true, e => false, v => true, d => false]
     *
     * @return int|mixed
     */
    static function getPermissionBits($permissions)
    {
        $bits = 0;
        foreach ($permissions as $_perm => $_bool) {
            if ($_bool) {
                $bits |= CExClass::$_permission_field[$_perm];
            }
        }

        return $bits;
    }

    /**
     * Checks a permission definition vs a permission level
     *
     * @param array  $permissions Permission definition
     * @param string $perm        Permission level required
     *
     * @return bool
     */
    static function checkPermission($permissions, $perm)
    {
        $perm_mask = CValue::read(self::$_permission_mask, $perm);

        if ($permissions["d"]) {
            return false;
        }

        return ($perm_mask & CExClass::getPermissionBits($permissions)) > 0;
    }

    /**
     * Tells if the user has enough permission for this object
     *
     * @param string         $perm      Permission required
     * @param CExObject|null $ex_object The CExObject to test against
     *
     * @return bool
     */
    function canPerm($perm, CExObject $ex_object = null)
    {
        if ($ex_object && $ex_object->_id) {
            return $ex_object->canPerm($perm);
        }

        $permissions = $this->permissions;

        if (!$permissions || $permissions === "{}") {
            return true;
        }

        $permissions = $this->getPermissions();

        $me   = CMediusers::get();
        $type = $me->_user_type;

        foreach ($permissions as $_type => $_permissions) {
            if ($_type == $type || $_type == 10000) {
                return CExClass::checkPermission($_permissions, $perm);
            }
        }

        return false;
    }

    /**
     * Check if the fields exists in DB
     *
     * @return array
     */
    function checkFields()
    {
        $fields_check = [];
        $ex_object    = $this->getExObjectInstance();
        $ds           = $this->getDS();

        foreach ($this->_ref_groups as $_group) {
            if (!array_key_exists($_group->name, $fields_check)) {
                $fields_check[$_group->name] = [];
            }

            foreach ($_group->_ref_fields as $_field) {
                if ($ds->hasTable($ex_object->getTableName())) {
                    $fields_check[$_group->name][$_field->name] = ($ds->hasField(
                        $ex_object->getTableName(),
                        $_field->name
                    ));
                }
            }
        }

        return $fields_check;
    }

    /**
     * Return True if forms are in hermetic mode (separated by group).
     *
     * @param bool $check_admin If True and the current User is an Admin, tell that we are not in hermetic mode.
     *
     * @return bool
     */
    public static function inHermeticMode(bool $check_admin): bool
    {
        if ($check_admin && CAppUI::$user->isAdmin()) {
            return false;
        }

        return (bool)CAppUI::isGroup();
    }
}
