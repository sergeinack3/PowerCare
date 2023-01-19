<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CAppUI;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbFieldSpecFact;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Forms\Constraints\ExClassConstraintOperatorFactory;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class event
 */
class CExClassEvent extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    private const EXTENDABLE_CLASSES = [
        "CPrescriptionLineElement",
        "CPrescriptionLineMedicament",
        "CPrescriptionLineMixItem",
        "COperation",
        "CSejour",
        "CConsultation",
        "CConsultAnesth",
        "CAdministration",
        "CRPU",
        "CGrossesse",
        "CBilanSSR",
        "CAppelSejour",
    ];

    /** @var int */
    public $ex_class_event_id;

    /** @var int */
    public $ex_class_id;

    /** @var string */
    public $host_class;

    /** @var string */
    public $event_name;

    /** @var bool */
    public $disabled;

    /** @var string */
    public $unicity;

    /** @var string */
    public $tab_name;

    /** @var int */
    public $tab_rank;

    /** @var bool */
    public $tab_show_header;

    /** @var bool */
    public $tab_show_subtabs; // not used

    /** @var string */
    public $constraints_logical_operator;

    /** @var string */
    public $mandatory_constraints_logical_operator;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CExClassConstraint[] */
    public $_ref_constraints;

    /** @var CExClassMandatoryConstraint[] */
    public $_ref_mandatory_constraints;

    /** @var CExClassFieldTrigger[] */
    public $_ref_triggers;

    /** @var CMbFieldSpec[] */
    public $_host_class_fields;

    /** @var array */
    public $_host_class_options;

    /** @var array */
    public $_available_native_views;

    /** @var CMbObject */
    public $_host_object;

    /** @var CExClassConstraint[] */
    public $_quick_access;

    /** @var string */
    public $_quick_access_creation;

    /** @var array */
    public $_tab_actions = [];

    /** @var CMbObject */
    public $_ref_constraint_object;

    /**
     * Get extendable specs
     *
     * @return array
     */
    public static function getExtendableSpecs(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $classes = self::EXTENDABLE_CLASSES;
        $specs   = [];

        foreach ($classes as $_class) {
            if (!class_exists($_class)) {
                continue;
            }

            $instance = new $_class();
            if (!empty($instance->_spec->events) && $instance->_ref_module && $instance->_ref_module->mod_active) {
                $specs[$_class] = $instance->_spec->events;
            }
        }

        return $cache = $specs;
    }

    /**
     * Get reportable classes
     *
     * @return string[]
     */
    public static function getReportableClasses(): array
    {
        $classes   = array_merge(["CPatient", "CSejour"], self::EXTENDABLE_CLASSES);
        $classes[] = "CMediusers";

        return array_filter($classes, "class_exists");
    }

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec                   = parent::getSpec();
        $spec->table            = "ex_class_event";
        $spec->key              = "ex_class_event_id";
        $spec->uniques["event"] = ["ex_class_id", "host_class", "event_name"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                     = parent::getProps();
        $props["ex_class_id"]      = "ref notNull class|CExClass cascade back|events";
        $props["host_class"]       = "str notNull protected";
        $props["event_name"]       = "str notNull protected canonical";
        $props["disabled"]         = "bool notNull default|1";
        $props["unicity"]          = "enum notNull list|no|host default|no vertical";
        $props["tab_name"]         = "str";
        $props["tab_rank"]         = "num";
        $props["tab_show_header"]  = "bool notNull default|1";
        $props["tab_show_subtabs"] = "bool notNull default|1"; // not used

        $props['constraints_logical_operator']           = 'enum list|and|or default|or notNull';
        $props['mandatory_constraints_logical_operator'] = 'enum list|and|or default|and notNull';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = CAppUI::tr($this->host_class) . " - " . CAppUI::tr("$this->host_class-event-$this->event_name");
    }

    /**
     * Get available native views
     */
    public function getAvailableNativeViews(): array
    {
        if (!$this->_id) {
            return $this->_available_native_views = [];
        }

        $options         = $this->getHostClassOptions();
        $available_views = [];
        $levels          = [1, 2, "host"];

        foreach (CExClass::$_native_views as $_name => $_classes) {
            foreach ($_classes as $_class) {
                foreach ($levels as $_level) {
                    if ($_level === "host") {
                        $ref_class = $this->host_class;
                    } else {
                        [$ref_class] = CValue::read($options, "reference$_level");
                    }

                    if ($_class === $ref_class) {
                        $available_views[$_name] = $_class;
                    }
                }
            }
        }

        return $this->_available_native_views = $available_views;
    }

    /**
     * Get class options
     *
     * @return null|string[]
     */
    public function getHostClassOptions()
    {
        if (!$this->host_class || !$this->event_name || $this->event_name === "void") {
            return null;
        }

        $object = new $this->host_class();

        return $this->_host_class_options = $object->_spec->events[$this->event_name];
    }

    /**
     * Load ex class
     *
     * @param bool $cache Use cache
     *
     * @return CExClass
     */
    public function loadRefExClass($cache = true)
    {
        return $this->_ref_ex_class = $this->loadFwdRef("ex_class_id", $cache);
    }

    /**
     * Returns an instance of CExObject which corresponds to the unicity
     *
     * @param CMbObject $host            Host object
     * @param bool      $check_old_forms Load old forms event with no unicity
     *
     * @return CExObject|CExObject[]
     */
    public function getExObjectForHostObject(CMbObject $host, $check_old_forms = true)
    {
        if (!$host->_id) {
            return [];
        }

        $this->completeField("disabled", "unicity");

        $existing = $this->loadRefExClass()->loadExObjects($host, $ex_object);
        $disabled = $this->disabled || !$this->checkConstraints($host);

        switch ($this->unicity) {
            case "no":
                // Si un formulaire est ouvert depuis un autre formulaire
                // et que son évènement déclencheur est désactivé ou que ses contraintes
                // sont non validée alors on charge le premier formulaire de ce type pour l'host courant.
                // On ne veut pas ce comportement dans l'ouverture normale
                // des formulaires (uniquement dans adminssions/urgences/...).
                if (!$check_old_forms) {
                    return [];
                }

                if (!$disabled) {
                    array_unshift($existing, $ex_object);
                }

                return $existing;

            case "host":
                if (count($existing)) {
                    return $existing;
                }

                if (!$disabled) {
                    return [$ex_object];
                }
            /*
            case "reference2": $level++;
            case "reference1":
            $reference_object = $this->resolveReferenceObject($host, $level);
            return array($this->getLatestExObject($reference_object, $level));*/

            default:
                // Do nothing
        }

        return [];
    }

    /**
     * Get ExObject instance
     *
     * @param bool $cache Use cache
     *
     * @return CExObject
     */
    public function getExObjectInstance($cache = false)
    {
        return $this->loadRefExClass()->getExObjectInstance($cache);
    }

    /**
     * Resolve reference object
     *
     * @param CMbObject $object MbObject
     * @param integer   $level  Object's level (1 or 2)
     *
     * @return CMbObject|null
     */
    public function resolveReferenceObject(CMbObject $object, $level = 1)
    {
        $options = $this->getHostClassOptions();
        [$ref_class, $path] = CValue::read($options, "reference$level");

        if (!$object->_id) {
            return new $ref_class();
        }

        if (!$path) {
            return null;
        }

        $parts = explode(".", $path);

        $reference = $object;
        foreach ($parts as $_fwd) {
            $reference = $reference->loadFwdRef($_fwd, true);
        }

        return $reference;
    }

    /**
     * Load constraints list
     *
     * @param bool $cache Use cache
     *
     * @return CExClassConstraint[]
     */
    public function loadRefsConstraints($cache = false)
    {
        if ($cache && !empty($this->_ref_constraints)) {
            return $this->_ref_constraints;
        }

        return $this->_ref_constraints = $this->loadBackRefs("constraints");
    }

    /**
     * Load mandatory constraints list
     *
     * @param bool $cache Use cache
     *
     * @return CExClassMandatoryConstraint[]|CStoredObject[]|null
     */
    public function loadRefsMandatoryConstraints($cache = true)
    {
        if ($cache && !empty($this->_ref_mandatory_constraints)) {
            return $this->_ref_mandatory_constraints;
        }

        return $this->_ref_mandatory_constraints = $this->loadBackRefs('mandatory_constraints');
    }

    /**
     * Tell if a form. CAN be opened according to constraints
     *
     * @param CMbObject $object Check constraints
     *
     * @return bool
     */
    public function checkConstraints(CMbObject $object): bool
    {
        $constraints = $this->loadRefsConstraints(true);

        if (empty($constraints)) {
            return true;
        }

        if ($this->_quick_access) {
            [$matching, $creation] = $this->getMatchingObject($object);

            if ($matching) {
                $this->_ref_constraint_object = $matching;
                $this->_quick_access_creation = $creation;

                return true;
            }
        }

        $constraint_checker = ExClassConstraintOperatorFactory::create($this->constraints_logical_operator);

        return $constraint_checker->checkConstraints($object, $constraints);
    }

    /**
     * Tells if a form. MUST be completed according to mandatory constraints
     *
     * @param CMbObject $object Check constraints
     *
     * @return bool
     */
    public function checkMandatoryConstraints(CMbObject $object): bool
    {
        $constraints = $this->loadRefsMandatoryConstraints();

        if (empty($constraints)) {
            return false;
        }

        $constraint_checker = ExClassConstraintOperatorFactory::create($this->mandatory_constraints_logical_operator);

        return $constraint_checker->checkConstraints($object, $constraints);
    }

    /**
     * Finds an object matching one the constraints
     *
     * @param CMbObject $object The object to find from
     *
     * @return array|null A two entries array, or null
     */
    public function getMatchingObject(CMbObject $object)
    {
        $matches = [];

        foreach ($this->_quick_access as $_quick_access_constraint) {
            $_event_class = $_quick_access_constraint->_ref_ex_class_event->host_class;
            $_field       = $_quick_access_constraint->field;

            switch ($object->_class) {
                case "CSejour":
                    /** @var CSejour $object */
                    switch ($_event_class) {
                        case "CPrescriptionLineElement":
                            $prescription  = $object->loadRefPrescriptionSejour();
                            $lines_element = $prescription->loadRefsLinesElement(
                                false,
                                null,
                                false,
                                null,
                                null,
                                true,
                                false,
                                false,
                                true,
                                ""
                            );

                            foreach ($lines_element as $_line) {
                                $_spec = $_line->_specs[$_field];
                                if (
                                    $_spec instanceof CRefSpec
                                    && $_spec->class . "-" . $_line->{$_field} == $_quick_access_constraint->value
                                ) {
                                    $matches[] = [$_line, $_quick_access_constraint->_quick_access_creation];
                                }
                            }
                            break;

                        default:
                            // ?
                    }
                    break;

                default:
                    // ?
            }
        }

        // Return only if one match
        if (count($matches) == 1) {
            return reset($matches);
        }

        return null;
    }

    /**
     * Get the constraints with quick access configured
     *
     * @param string $class The class name to find the quick accesses of
     *
     * @return array
     */
    public function getQuickAccess($class)
    {
        $all_constraints = $this->loadRefsConstraints();

        $all_events = CExClassEvent::getExtendableSpecs();

        /** @var CExClassConstraint[] $constraints */
        $constraints = [];

        foreach ($all_constraints as $_constraint) {
            foreach ($all_events as $_by_class) {
                foreach ($_by_class as $_event) {
                    if (isset($_event["quick_access"][$_constraint->field][$class])) {
                        $_constraint->_quick_access_creation = $_event["quick_access"][$_constraint->field][$class];
                        $constraints[$_constraint->_id]      = $_constraint;
                    }
                }
            }
        }

        return $this->_quick_access = $constraints;
    }

    /**
     * Get all classes this event will be able to open a form on.
     * By default it will be only $this->host_class, but for a few events,
     * it can be extended to open forms on other objects (quick_access)
     *
     * @return array
     */
    public function getCreationClasses()
    {
        $classes = [$this->host_class];

        /** @var CExClassConstraint[] $constraints */
        $constraints = $this->loadBackRefs("constraints");

        if (count($constraints)) {
            $all_events = CExClassEvent::getExtendableSpecs();

            foreach ($constraints as $_constraint) {
                if (!$_constraint->quick_access) {
                    continue;
                }

                foreach ($all_events as $_by_class) {
                    foreach ($_by_class as $_event) {
                        if (
                            array_key_exists("quick_access", $_event)
                            && array_key_exists($_constraint->field, $_event["quick_access"])
                        ) {
                            $classes = array_merge($classes, array_keys($_event["quick_access"][$_constraint->field]));
                        }
                    }
                }
            }
        }

        return array_unique($classes);
    }

    /**
     * Get host object's specs list
     *
     * @param CMbObject $object Object
     *
     * @return CMbFieldSpec[]
     */
    public static function getHostObjectSpecs(CMbObject $object)
    {
        $specs                   = [];
        $specs["CONNECTED_USER"] = self::getConnectedUserSpec();

        return array_merge($specs, $object->_specs);
    }

    /**
     * Get "connected user" spec
     *
     * @return CMbFieldSpec
     */
    public static function getConnectedUserSpec()
    {
        static $spec;

        if (!isset($spec)) {
            $mediuser = new CMediusers();
            $spec     = CMbFieldSpecFact::getSpec($mediuser, "CONNECTED_USER", "ref class|CMediusers show|1");
        }

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        if ($this->fieldModified("host_class")) {
            $count_constraints = $this->countBackRefs("constraints");
            if ($count_constraints > 0) {
                return "Impossible de changer le type d'objet hôte de ce formulaire car il possède $count_constraints contrainte(s)";
            }
        }

        return null;
    }

    /**
     * Get available fields for the object
     *
     * @param CMbObject $object            Object
     * @param array     $class_fields      Class fields
     * @param bool      $allow_form_fields Allow form fields in the list
     *
     * @return array|CMbFieldSpec[]|null
     */
    public static function getAvailableFieldsOfObject(
        CMbObject $object,
        $class_fields = null,
        $allow_form_fields = false
    ) {
        if ($class_fields === null) {
            $class_fields = $object->_specs;
        }

        foreach ($class_fields as $_field => $_spec) {
            if ($_field == $object->_spec->key) {
                unset($class_fields[$_field]);
                continue;
            }

            /*if ($_spec instanceof CRefSpec && $_spec->meta) {
              unset($class_fields[$_spec->meta]);
              continue;
            }*/

            // LEVEL 1
            if (
                // form field
                ($_field[0] === "_" && !$allow_form_fields && ($_spec->show === null || $_spec->show == 0)) ||
                // not shown
                !($_spec->show === null || $_spec->show == 1) ||
                // not a finite meta class field
                $_spec instanceof CRefSpec && $_spec->meta && !$class_fields[$_spec->meta] instanceof CEnumSpec
            ) {
                unset($class_fields[$_field]);
                continue;
            }

            // LEVEL 2
            if ($_spec instanceof CRefSpec) {
                // LEVEL 2 + Class list
                if ($_spec->meta && $class_fields[$_spec->meta] instanceof CEnumSpec) {
                    unset($class_fields[$_field]);

                    // boucle sur les classes du enum
                    $classes = $class_fields[$_spec->meta]->_list;

                    foreach ($classes as $_class) {
                        $_key = "$_field.$_class";

                        $_target = new $_class();

                        $class_fields[$_key]            = new CRefSpec($object->_class, $_field, "ref class|$_class");
                        $class_fields[$_key]->_subspecs = [];

                        foreach ($_target->_specs as $_subfield => $_subspec) {
                            if (!$_subfield || $_subfield === $_target->_spec->key) {
                                continue;
                            }

                            if (
                                $_subfield[0] === "_" && ($_subspec->show === null || $_subspec->show == 0)// form field
                                || !($_subspec->show === null || $_subspec->show == 1) // not shown
                                || $_subspec instanceof CRefSpec && $_subspec->meta // not a finite meta class field
                                && !$_target->_specs[$_subspec->meta] instanceof CEnumSpec
                            ) {
                                continue;
                            }

                            $class_fields[$_key]->_subspecs[$_subfield] = $_subspec;
                        }
                    }
                } else {
                    // LEVEL 2 + Single class
                    $_key                           = $_field;
                    $class_fields[$_key]->_subspecs = [];

                    $_class = $_spec->class;
                    if (!$_class) {
                        continue;
                    }

                    $_target = new $_class();

                    foreach ($_target->_specs as $_subfield => $_subspec) {
                        if (!$_subfield || $_subfield === $_target->_spec->key) {
                            continue;
                        }

                        if (
                            // form field
                            $_subfield[0] === "_" ||
                            // not shown
                            !($_subspec->show === null || $_subspec->show == 1) ||
                            // not a finite meta class field
                            $_subspec instanceof CRefSpec && $_subspec->meta &&
                            isset($object->_specs[$_subspec->meta]) &&
                            !$object->_specs[$_subspec->meta] instanceof CEnumSpec
                        ) {
                            continue;
                        }

                        $class_fields[$_key]->_subspecs[$_subfield] = $_subspec;
                    }
                }
            }
        }

        return $class_fields;
    }

    /**
     * Get available fields
     *
     * @param bool $allow_form_fields Allow form fields in the list
     *
     * @return CMbFieldSpec[]
     */
    public function getAvailableFields($allow_form_fields = false)
    {
        $object = new $this->host_class();

        $class_fields = self::getHostObjectSpecs($object);
        $class_fields = self::getAvailableFieldsOfObject($object, $class_fields, $allow_form_fields);

        return $this->_host_class_fields = $class_fields;
    }

    /**
     * Build host fields list
     *
     * @param string $prefix            Prefix, to restrain list
     * @param bool   $allow_form_fields Allow form fields in the list
     *
     * @return array
     */
    public function buildHostFieldsList($prefix = null, $allow_form_fields = false)
    {
        $this->getAvailableFields($allow_form_fields);

        $list = [];
        foreach ($this->_host_class_fields as $_field => $_spec) {
            $host_class = $this->host_class;

            if ("CONNECTED_USER" === $_field) {
                $host_class = "CMediusers";
            }

            $element = [
                "prop"     => $_spec,
                "title"    => null,
                "view"     => null,
                "longview" => null,
                "type"     => null,
                "level"    => 0,
                "field"    => null,
                "class"    => $host_class,
            ];

            $_subfield = explode(".", $_field);

            // Level 1 title
            if ($_spec instanceof CRefSpec && $_spec->class) {
                if ($_spec->meta) {
                    /** @var CEnumSpec $_meta_spec */
                    $_meta_spec      = $this->_host_class_fields[$_spec->meta];
                    $element["type"] = implode(" OU ", $_meta_spec->_locales);
                } else {
                    $element["type"] = CAppUI::tr($_spec->class);
                }
            } else {
                $element["type"] = CAppUI::tr("CMbFieldSpec.type." . $_spec->getSpecType());
            }

            // Level 1 type
            if (count($_subfield) > 1) {
                $element["title"] = CAppUI::tr("$host_class-$_subfield[0]") . " de type "
                    . CAppUI::tr("$_subfield[1]");

                $element["longview"] = CAppUI::tr("$host_class-$_subfield[0]-desc") . " de type "
                    . CAppUI::tr("$_subfield[1]");

                $element["field"] = "$host_class-$_subfield[0]";
            } else {
                $element["title"]    = CAppUI::tr("$host_class-$_field");
                $element["longview"] = CAppUI::tr("$host_class-$_field-desc");
                $element["field"]    = "$host_class-$_field";
            }

            $element["view"] = $element["title"];
            $parent_view     = $element["view"];

            $list[($prefix ? "$prefix " : "") . $_field] = $element;

            // Level 2
            if ($_spec instanceof CRefSpec) {
                foreach ($_spec->_subspecs as $_key => $_subspec) {
                    $_subfield = explode(".", $_key);
                    $_subfield = reset($_subfield);

                    $element = [
                        "prop"  => $_subspec,
                        "title" => null,
                        "type"  => null,
                        "level" => 1,
                        "class" => $host_class,
                    ];

                    if ($_subspec instanceof CRefSpec && $_subspec->class) {
                        if (!$_subspec->meta) {
                            $element["type"] = CAppUI::tr("$_subspec->class");
                        }
                    } else {
                        $element["type"] = CAppUI::tr("CMbFieldSpec.type." . $_subspec->getSpecType());
                    }

                    $element["view"]     = $parent_view . " / " . CAppUI::tr("$_subspec->className-$_subfield");
                    $element["longview"] = $parent_view . " / " . CAppUI::tr("$_subspec->className-$_subfield-desc");
                    $element["title"]    = " |- " . CAppUI::tr("$_subspec->className-$_subfield");
                    $element["field"]    = "$_subspec->className-$_subfield";

                    $list[($prefix ? "$prefix " : "") . "$_field-$_key"] = $element;
                }
            }
        }

        return $list;
    }

    /**
     * Check if we can create a new instance of the ExObject
     *
     * @param CMbObject $host Host object
     *
     * @return bool
     */
    public function canCreateNew(CMbObject $host): bool
    {
        switch ($this->unicity) {
            default:
            case "no":
                return true;

            case "host":
                // Host
                $ex_object = new CExObject($this->_id);
                $ex_object->setObject($host);

                if ($ex_object->countMatchingList() > 0) {
                    return false;
                }
            /*
            // Reférence 1
            $ex_object = new CExObject($this->_id);
            $ex_object->setReferenceObject_1($host);

            if ($ex_object->countMatchingList() > 0) {
              return false;
            }

            // Référence 2
            $ex_object = new CExObject($this->_id);
            $ex_object->setReferenceObject_2($host);

            if ($ex_object->countMatchingList() > 0) {
              return false;
            }*/
        }

        return true;
    }

    /**
     * Build JS code for the trigger
     *
     * @param CExClassEvent[] $ex_class_events List of events
     *
     * @return string
     */
    public static function getJStrigger($ex_class_events)
    {
        if (count($ex_class_events) == 0) {
            return "";
        }

        $forms = self::getFormsStruct($ex_class_events);

        return "
    <script type='text/javascript'>
      (window.ExObject || window.opener.ExObject).triggerMulti(" . json_encode($forms) . ");
    </script>";
    }

    /**
     * Get forms structure, to be used in self::getJStrigger
     *
     * @param CExClassEvent[] $ex_class_events List of events
     *
     * @return array
     */
    public static function getFormsStruct($ex_class_events)
    {
        $forms = [];

        foreach ($ex_class_events as $_ex_class_event) {
            // We may have more than one form per exclass
            $forms[] = [
                "ex_class_event_id" => $_ex_class_event->_id,
                "ex_class_id"       => $_ex_class_event->ex_class_id,
                "object_guid"       => $_ex_class_event->_host_object->_guid,
                "event_name"        => $_ex_class_event->event_name,
            ];
        }

        return array_values($forms);
    }

    /**
     * Get events for an object
     *
     * @param CMbObject|string $object                     Object or GUID
     * @param string           $event_name                 Event name
     * @param string           $type                       Type: required, disabled or conditional
     * @param array            $exclude_ex_class_event_ids List of class events' ids
     *
     * @return CExClassEvent[]
     */
    public static function getForObject($object, $event_name, $type = "required", $exclude_ex_class_event_ids = [])
    {
        if (is_string($object)) {
            $object = CMbObject::loadFromGuid($object);
        }

        $ex_class_events = static::getForClass($object, $event_name, $type, $exclude_ex_class_event_ids);

        foreach ($ex_class_events as $_id => $_ex_class_event) {
            if (isset($exclude_ex_class_event_ids[$_id]) || !$_ex_class_event->checkConstraints($object)) {
                unset($ex_class_events[$_id]);
            } else {
                $_ex_class_event->_host_object = $object;
            }
        }

        return $ex_class_events;
    }

    /**
     * Get events for an object class
     *
     * @param CMbObject $object     Object
     * @param string    $event_name Event name
     * @param string    $type       Type: required, disabled or conditional
     *
     * @return CExClassEvent[]
     */
    public static function getForClass($object, $event_name, $type = "required")
    {
        static $events_cache = [];

        if ($type == "required" && !CValue::read($object->_spec->events[$event_name], "auto", false)) {
            return [];
        }

        $ex_class_event = new self();

        $group_id = CGroups::loadCurrent()->_id;
        $ds       = $ex_class_event->_spec->ds;

        $key = "$object->_class/$event_name/$group_id/$type";

        if (isset($events_cache[$key])) {
            $ex_class_events = $events_cache[$key];
        } else {
            $where = [
                "ex_class_event.host_class" => $ds->prepare("=%", $object->_class),
                "ex_class_event.event_name" => $ds->prepare("=%", $event_name),
                "ex_class_event.disabled"   => $ds->prepare("=%", 0),
                "ex_class.conditional"      => $ds->prepare("=%", 0),
                $ds->prepare("ex_class.group_id = % OR group_id IS NULL", $group_id),
            ];

            $ljoin = [
                "ex_class" => "ex_class.ex_class_id = ex_class_event.ex_class_id",
            ];

            switch ($type) {
                case "disabled":
                    $where["ex_class_event.disabled"] = 1;
                    break;

                case "conditional":
                    $where["ex_class.conditional"] = 1;
                    break;

                default:
                    // No $where to add
            }

            /** @var CExClassEvent[] $ex_class_events */
            $ex_class_events = $ex_class_event->loadList($where, null, null, null, $ljoin);

            $events_cache[$key] = $ex_class_events;
        }

        return $ex_class_events;
    }

    /**
     * Get events for an object class
     *
     * @param string $object_class               Object class
     * @param string $event_name                 Event name
     * @param string $type                       Type: required, disabled or conditional
     * @param array  $exclude_ex_class_event_ids List of class events' ids
     *
     * @return integer
     */
    public static function countForClass(
        $object_class,
        $event_name,
        $type = "required",
        $exclude_ex_class_event_ids = []
    ) {
        static $events_cache = [];

        $object = new $object_class();

        if ($type == "required" && !CValue::read($object->_spec->events[$event_name], "auto", false)) {
            return null;
        }

        $ex_class_event = new self();

        $group_id = CGroups::loadCurrent()->_id;
        $ds       = $ex_class_event->_spec->ds;

        $key = "$object->_class/$event_name/$group_id/$type";

        if (isset($events_cache[$key])) {
            $ex_class_events = $events_cache[$key];
        } else {
            $where = [
                "ex_class_event.host_class" => $ds->prepare("=%", $object->_class),
                "ex_class_event.event_name" => $ds->prepare("=%", $event_name),
                "ex_class_event.disabled"   => $ds->prepare("=%", 0),
                "ex_class.conditional"      => $ds->prepare("=%", 0),
                $ds->prepare("ex_class.group_id = % OR group_id IS NULL", $group_id),
            ];

            if ($exclude_ex_class_event_ids) {
                $where['ex_class_event.ex_class_event_id'] = $ds->prepareNotIn(array_keys($exclude_ex_class_event_ids));
            }

            $ljoin = [
                "ex_class" => "ex_class.ex_class_id = ex_class_event.ex_class_id",
            ];

            switch ($type) {
                case "disabled":
                    $where["ex_class_event.disabled"] = 1;
                    break;

                case "conditional":
                    $where["ex_class.conditional"] = 1;
                    break;

                default:
                    // No $where to add
            }

            /** @var integer $ex_class_events */
            $ex_class_events = $ex_class_event->countList($where, null, $ljoin);

            $events_cache[$key] = $ex_class_events;
        }

        return $ex_class_events;
    }

    /**
     * Load host mbObject depending on the event type
     *
     * @param CMbObject $object Major MbObject
     *
     * @return void
     */
    public function getTabHostObject(CMbObject $object): void
    {
        $classes = self::getExtendableSpecs();

        if (!isset($classes[$this->host_class][$this->event_name])) {
            return;
        }

        $_event = $classes[$this->host_class][$this->event_name];
        if (!isset($_event["tab"]) || !$_event["tab"]) {
            trigger_error("Event '[$this->event_name' is not a tab event");

            return;
        }

        // We look for the host object for the event
        if ($object) {
            if ($object->_class === $this->host_class) {
                $this->_host_object = $object;
            } else {
                $reference = $this->resolveReferenceObject($object, 1);
                if ($reference && $reference->_class === $this->host_class) {
                    $this->_host_object = $reference;
                } else {
                    $reference = $this->resolveReferenceObject($object, 2);
                    if ($reference && $reference->_class === $this->host_class) {
                        $this->_host_object = $reference;
                    }
                }
            }
        }

        if ((!$this->_host_object || !$this->_host_object->_id) && isset($_event["tab_actions"])) {
            $this->_tab_actions = $_event["tab_actions"];
        }
    }

    /**
     * Get tab events
     *
     * @param array $objects An array of couples : [["event_name", associated object], ...]
     *
     * @return CExClassEvent[][]
     */
    public static function getTabEvents($objects)
    {
        $events = [
            "before" => [],
            "after"  => [],
        ];

        $group_id = CGroups::loadCurrent()->_id;

        $ex_class_event = new self();
        $ds             = $ex_class_event->getDS();

        /** @var self[] $ex_class_events */
        $ex_class_events = [];

        foreach ($objects as $_object) {
            [$event_name, $object] = $_object;

            $where = [
                "ex_class.group_id IS NULL OR ex_class.group_id = '$group_id'",
                "ex_class_event.event_name" => $ds->prepare('=?', $event_name),
                "ex_class_event.disabled"   => "= '0'",
            ];

            $ljoin = [
                "ex_class" => "ex_class.ex_class_id = ex_class_event.ex_class_id",
            ];

            $_ex_class_events = $ex_class_event->loadList($where, "ex_class_event.tab_name", null, null, $ljoin);

            foreach ($_ex_class_events as $_k => $_ex_class_event) {
                if (!$_ex_class_event->checkConstraints($object)) {
                    unset($_ex_class_events[$_k]);
                    continue;
                }

                $_ex_class_event->getTabHostObject($object);
            }

            $ex_class_events = array_merge($ex_class_events, $_ex_class_events);
        }

        usort(
            $ex_class_events,
            function ($a, $b) {
                if ($a->tab_rank == $b->tab_rank) {
                    return 0;
                }

                if ($a->tab_rank > $b->tab_rank) {
                    return 1;
                }

                return -1;
            }
        );

        CStoredObject::massLoadFwdRef($ex_class_events, "ex_class_id");

        foreach ($ex_class_events as $_ex_class_event) {
            $_ex_class_event->loadRefExClass();

            $_ex_class_event->_count_ex_links = null;

            if ($_ex_class_event->_host_object && $_ex_class_event->_host_object->_id) {
                $host = $_ex_class_event->_host_object;

                $where                         = [];
                $where["ex_link.object_class"] = "= '$host->_class'";
                $where["ex_link.object_id"]    = "= '$host->_id'";
                $where["ex_link.ex_class_id"]  = "= '$_ex_class_event->ex_class_id'";
                $where["ex_link.level"]        = "= 'object'";

                /** @var CExLink[] $links */
                $ex_link                          = new CExLink();
                $_ex_class_event->_count_ex_links = $ex_link->countList($where);
            }

            if ($_ex_class_event->tab_rank < 0) {
                $events["before"][] = $_ex_class_event;
            } else {
                $events["after"][] = $_ex_class_event;
            }
        }

        return $events;
    }

    /**
     * Get events (and ex_classes) matching to mandatory constraints
     *
     * @param CMbObject $object
     *
     * @return array
     */
    public static function loadMandatoryEvents(CMbObject $object): array
    {
        $group_id = CGroups::loadCurrent()->_id;

        $where = [
            "group_id = '$group_id' OR group_id IS NULL",
            "ex_class_event.disabled" => "= '0'",
            "ex_class.conditional"    => "= '0'",
        ];

        $ljoin = [
            "ex_class_event" => "ex_class_event.ex_class_id = ex_class.ex_class_id",
        ];

        $ex_class = new CExClass();

        /** @var CExClass[] $ex_classes */
        $ex_classes = $ex_class->loadList($where, null, null, 'ex_class.ex_class_id', $ljoin);

        // Loading the events
        $ex_classes_filtered = [];

        foreach ($ex_classes as $_ex_class_id => $_ex_class) {
            if (!$_ex_class->canPerm("c")) {
                unset($ex_classes[$_ex_class_id]);
                continue;
            }

            $ex_classes_filtered[$_ex_class_id] = $_ex_class;
        }

        /** @var CExClassEvent[] $ex_class_events */
        $ex_class_events = CStoredObject::massLoadBackRefs($ex_classes_filtered, "events", null, ["disabled = '0'"]);

        if (!$ex_class_events) {
            return [];
        }

        CStoredObject::massLoadBackRefs($ex_class_events, "constraints");
        CStoredObject::massLoadBackRefs($ex_class_events, "mandatory_constraints");
        CStoredObject::massLoadFwdRef($ex_class_events, "ex_class_id");

        /** @var CExClassEvent[] $mandatory_ex_classes */
        $mandatory_ex_classes = [];

        foreach ($ex_class_events as $_id => $_ex_class_event) {
            $_classes = $_ex_class_event->getCreationClasses();

            if (!in_array($object->_class, $_classes)) {
                continue;
            }

            if ($_ex_class_event->host_class === $object->_class && $_ex_class_event->checkConstraints($object)) {
                if ($_ex_class_event->checkMandatoryConstraints($object)) {
                    $mandatory_ex_classes[$_ex_class_event->ex_class_id][] = $_ex_class_event;
                }
            }
        }

        $ex_events = [];

        foreach ($mandatory_ex_classes as $_ex_class_id => $_ex_class_events) {
            foreach ($_ex_class_events as $_ex_class_event) {
                $_ex_class = $_ex_class_event->loadRefExClass();

                $_ex_object = $_ex_class->getLatestExObject($object);

                if (!$_ex_object || !$_ex_object->_id) {
                    $ex_events[$_ex_class_event->ex_class_id][] = $_ex_class_event;
                }
            }
        }

        return $ex_events;
    }

    /**
     * Get events (and ex_classes) matching to mandatory constraints
     *
     * @param array $objects
     *
     * @return array
     */
    public static function massLoadMandatoryEvents(array $objects): array
    {
        if (!$objects) {
            return [];
        }

        $objects_by_guid = [];

        foreach ($objects as $_object) {
            $objects_by_guid[$_object->_guid] = $_object;
        }

        $group_id = CGroups::loadCurrent()->_id;

        $where = [
            "group_id = '$group_id' OR group_id IS NULL",
            "ex_class_event.disabled" => "= '0'",
            "ex_class.conditional"    => "= '0'",
        ];

        $ljoin = [
            "ex_class_event" => "ex_class_event.ex_class_id = ex_class.ex_class_id",
        ];

        $ex_class = new CExClass();

        /** @var CExClass[] $ex_classes */
        $ex_classes = $ex_class->loadList($where, null, null, 'ex_class.ex_class_id', $ljoin);

        // Loading the events
        $ex_classes_filtered = [];

        foreach ($ex_classes as $_ex_class_id => $_ex_class) {
            if (!$_ex_class->canPerm("c")) {
                unset($ex_classes[$_ex_class_id]);
                continue;
            }

            $ex_classes_filtered[$_ex_class_id] = $_ex_class;
        }

        /** @var CExClassEvent[] $ex_class_events */
        $ex_class_events = CStoredObject::massLoadBackRefs($ex_classes_filtered, "events", null, ["disabled = '0'"]);
        CStoredObject::massLoadBackRefs($ex_class_events, "constraints");
        CStoredObject::massLoadBackRefs($ex_class_events, "mandatory_constraints");
        CStoredObject::massLoadFwdRef($ex_class_events, "ex_class_id");

        /** @var CExClassEvent[] $mandatory_ex_classes */
        $mandatory_ex_classes = [];

        foreach ($ex_class_events as $_id => $_ex_class_event) {
            $_classes = $_ex_class_event->getCreationClasses();

            foreach ($objects_by_guid as $_object) {
                if (!isset($mandatory_ex_classes[$_object->_guid])) {
                    $mandatory_ex_classes[$_object->_guid] = [];
                }

                if (!in_array($_object->_class, $_classes)) {
                    continue;
                }

                if ($_ex_class_event->host_class === $_object->_class && $_ex_class_event->checkConstraints($_object)) {
                    if ($_ex_class_event->checkMandatoryConstraints($_object)) {
                        $mandatory_ex_classes[$_object->_guid][$_ex_class_event->ex_class_id][] = $_ex_class_event;
                    }
                }
            }
        }

        $ex_events = [];

        foreach ($mandatory_ex_classes as $_object_guid => $_ex_class_events_by_object) {
            $ex_events[$_object_guid] = [];

            foreach ($_ex_class_events_by_object as $_ex_class_id => $_ex_class_events) {
                foreach ($_ex_class_events as $_ex_class_event) {
                    $_ex_class = $_ex_class_event->loadRefExClass();

                    $_ex_object = $_ex_class->getLatestExObject($objects_by_guid[$_object_guid]);

                    if (!$_ex_object || !$_ex_object->_id) {
                        $ex_events[$_object_guid][$_ex_class_event->ex_class_id][] = $_ex_class_event;
                    }
                }
            }
        }

        return $ex_events;
    }

    /**
     * Tells if event has mandatory fields set
     *
     * @return bool
     */
    public function hostHasMandatoryFields(): bool
    {
        if (!$this->host_class) {
            return false;
        }

        /** @var CMbObject $object */
        $object = new $this->host_class();

        return (isset($object->_spec->events[$this->event_name]['mandatory_fields'])
            && $object->_spec->events[$this->event_name]['mandatory_fields']);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }
}
