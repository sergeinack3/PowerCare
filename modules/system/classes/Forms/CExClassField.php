<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbFieldSpecFact;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\FieldSpecs\CBoolSpec;
use Ox\Core\FieldSpecs\CNumSpec;
use Ox\Mediboard\Forms\Tag\CExClassFieldTagFactory;
use Ox\Mediboard\Forms\Tag\CExClassFieldTagItem;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * ExClass field
 */
class CExClassField extends CExListItemsOwner
{
    use StandardPermTrait;

    static $_props_exclude = [
        "confidential",
        "mask",
        "format",
        "reported",
        "perm",
        "seekable",
        "pattern",
        "autocomplete",
        "cascade",
        "delimiter",
        "canonical",
        "protected",
        "class",
        "alphaAndNum",
        "byteUnit",
        "length", //  a cause de form.length qui pose probleme
        "loggable",
        "refDate",
        "flag",
        "monitored",
        "numChars",
        "alphaChars",
        "alphaLowChars",
        "specialChars",
        "alphaUpChars",
        'hideDate',
        'group',
        'back',
    ];

    static $_props_boolean = [
        "notNull",
        "vertical",
        "progressive",
        "cascade",
    ];

    public $ex_class_field_id;

    public $ex_group_id;
    public $subgroup_id;
    public $name; // != object_class, object_id, ex_ClassName_event_id,
    public $prop;
    public $disabled;

    //public $report_level;
    public $report_class;
    public $concept_id;
    public $predicate_id;
    public $prefix;
    public $suffix;
    public $show_label;
    public $tab_index;
    public $readonly;
    public $hidden;
    public $in_doc_template;
    public $in_completeness;
    public $update_native_data;
    public $load_native_data;

    public $formula;
    public $_formula;
    public $result_in_title;
    public $result_threshold_high;
    public $result_threshold_low;

    public $auto_increment;

    public $coord_field_x;
    public $coord_field_y;
    //public $coord_field_colspan;
    //public $coord_field_rowspan;

    public $coord_label_x;
    public $coord_label_y;

    // Pixel positionned
    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;

    public $_locale;
    public $_locale_desc;
    public $_locale_court;

    public $_pixel_positionning;

    public $_triggered_data;

    public $_ex_class_field_tags;

    /** @var CExClassFieldGroup */
    public $_ref_ex_group;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CExClassFieldTranslation[] */
    public $_ref_translation;

    /** @var CExClassFieldPredicate[] */
    public $_ref_predicates;

    /** @var CExClassFieldProperty[] */
    public $_ref_properties;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    /** @var CExConcept */
    public $_ref_concept;

    /** @var CExClassFieldTagItem[] */
    public $_ref_ex_class_field_tag_items;

    /** @var bool */
    public $_has_available_tags = false;

    /** @var bool */
    public $_store_tag_items = false;

    /** @var CMbFieldSpec */
    public $_spec_object;

    public $_ex_class_id;
    public $_default_properties;
    public $_no_size          = false;
    public $_make_unique_name = true;
    public $_keep_position    = false;

    public $_dont_drop_column;

    public $_regenerate = false;

    static $_load_lite = false;

    static $_indexed_types = [
        "ref",
        "date",
        "dateTime",
        "time",
    ];

    static $_data_type_groups = [
        ["ipAddress"],
        ["bool"],
        ["enum"],
        ["ref"],
        ["num", "numchar"],
        ["pct", "float", "currency"],
        ["time"],
        ["date", "birthDate"],
        ["dateTime"],
        ["code"],
        ["email"],
        ["password", "str"],
        ["php", "xml", "html", "text"],
    ];

    static $_property_fields_all = [
        "prefix",
        "suffix",
        "tab_index",
        "readonly",
        "hidden",
        "show_label",
        "coord_left",
        "coord_top",
        "coord_width",
        "coord_height",
    ];

    static $_property_fields = [
        "prefix",
        "suffix",
        "tab_index",
        "readonly",
        "hidden",
    ];

    static $_formula_token_re = '/\[([^\]]+)\]/';

    static $_formula_valid_types = [
        "float",
        "num",
        "numchar",
        "pct",
        "currency",
        "date",
        "dateTime",
        "time",
        "bool",
    ];

    static $_concat_valid_types = [
        "float",
        "num",
        "numchar",
        "pct",
        "currency", /*"date", "dateTime", "time",*/
        "str",
        "text",
        "code",
        "email",
    ];

    static $_formula_constants = [
        "DateCourante",
        "HeureCourante",
        "DateHeureCourante",
    ];

    static $_formula_intervals = [
        "Min" => "Minutes",
        "H"   => "Heures",
        "J"   => "Jours",
        "Sem" => "Semaines",
        "M"   => "Mois",
        "A"   => "Années",
    ];

    static $_prop_escape = [
        " " => "\\x20",
        "|" => "\\x7C",
    ];

    // types pouvant être utilisés pour des calculs / concaténation

    /**
     * Check if a data type can de used in arithemtic formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCanArithmetic($type)
    {
        return in_array($type, self::$_formula_valid_types) ||
            $type === "enum" ||
            $type === "date" ||
            $type === "datetime" || $type === "dateTime" ||
            $type === "time";
    }

    /**
     * Check if a data type can be used in concatenation formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCanConcat($type)
    {
        return in_array($type, self::$_concat_valid_types);
    }

    /**
     * Check if a data type can be used in concatenation ro arithmetic formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCan($type)
    {
        return self::formulaCanConcat($type) || self::formulaCanArithmetic($type);
    }

    // types pouvant herberger des resultats

    /**
     * Check if a data type can be used as result of arithmetic formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCanResultArithmetic($type)
    {
        return in_array($type, self::$_formula_valid_types);
    }

    /**
     * Check if a data type can be used as result of concatenation formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCanResultConcat($type)
    {
        return $type === "text";
    }

    /**
     * Check if a data type can be used as result of arithmetic or concatenation formulas
     *
     * @param string $type Data type
     *
     * @return bool
     */
    static function formulaCanResult($type)
    {
        return self::formulaCanResultConcat($type) || self::formulaCanResultArithmetic($type);
    }

    /**
     * Get all useable data types
     *
     * @return array
     */
    static function getTypes()
    {
        $types = [
            "enum",
            "set",
            "str",
            "text",
            "bool",
            "num",
            "float",
            "date",
            "time",
            "dateTime",
            "pct",
            "phone",
            "birthDate",
            "currency",
            "email",
        ];

        return array_intersect_key(CMbFieldSpecFact::$classes, array_flip($types));
    }

    /**
     * Get property fields
     *
     * @return array
     */
    function getPropertyFields()
    {
        if ($this->_id && $this->loadRefExClass()->pixel_positionning) {
            return self::$_property_fields_all;
        }

        return self::$_property_fields;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_class_field";
        $spec->key             = "ex_class_field_id";
        $spec->uniques["name"] = ["ex_group_id", "name"];

        // should ignore empty values
        //$spec->uniques["coord_label"] = array("ex_group_id", "coord_label_x", "coord_label_y");
        //$spec->uniques["coord_field"] = array("ex_group_id", "coord_field_x", "coord_field_y");
        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["ex_group_id"] = "ref class|CExClassFieldGroup cascade back|class_fields";
        $props["subgroup_id"] = "ref class|CExClassFieldSubgroup nullify back|children_fields";
        $props["concept_id"]  = "ref class|CExConcept autocomplete|name back|class_fields";
        $props["name"]        = "str notNull protected canonical";
        $props["disabled"]    = "bool notNull default|0";
        //$props["report_level"]= "enum list|1|2|host";
        $props["report_class"] = "enum list|" . implode("|", CExClassEvent::getReportableClasses());
        $props["prop"]         = "text notNull";
        $props["predicate_id"] = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_fields";

        $props["prefix"]             = "str";
        $props["suffix"]             = "str";
        $props["show_label"]         = "bool notNull default|1";
        $props["tab_index"]          = "num";
        $props["readonly"]           = "bool notNull default|0";
        $props["hidden"]             = "bool notNull default|0";
        $props["in_doc_template"]    = "bool notNull default|0";
        $props['in_completeness']    = 'bool notNull default|0';
        $props["update_native_data"] = "bool notNull default|0";

        // Par défaut à 1 pour conserver le comportement précédent sur les concepts avec report de valeur de MB
        $props["load_native_data"] = "bool notNull default|1";

        $props["formula"]               = "text"; // canonical tokens
        $props["_formula"]              = "text"; // localized tokens
        $props["result_in_title"]       = "bool notNull default|0";
        $props["result_threshold_high"] = "float";
        $props["result_threshold_low"]  = "float";

        $props['auto_increment'] = 'bool default|0';

        $props["coord_field_x"] = "num min|0 max|100";
        $props["coord_field_y"] = "num min|0 max|100";
        //$props["coord_field_colspan"] = "num min|1 max|100";
        //$props["coord_field_rowspan"] = "num min|1 max|100";

        $props["coord_label_x"] = "num min|0 max|100";
        $props["coord_label_y"] = "num min|0 max|100";

        // Pixel positionned
        $props["coord_left"]   = "num";
        $props["coord_top"]    = "num";
        $props["coord_width"]  = "num min|1";
        $props["coord_height"] = "num min|1";

        $props["_ex_class_id"] = "ref class|CExClass";

        $props["_locale"]       = "str notNull";
        $props["_locale_desc"]  = "str";
        $props["_locale_court"] = "str";

        $props['_ex_class_field_tags'] = 'str';
        $props['_store_tag_items']     = 'bool default|0';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = "$this->name [$this->prop]";

        if (!$this->coord_width && !$this->coord_height) {
            $this->_no_size = true;
        }

        if (!self::$_load_lite) {
            $this->_ex_class_id = $this->loadRefExGroup()->ex_class_id;

            // must be called in the class editor
            if (!CExObject::$_locales_cache_enabled) {
                $this->updateTranslation();
            }
        }
    }

    /**
     * Get default styling
     *
     * @param bool $cache Cache results
     *
     * @return array
     */
    function getDefaultProperties($cache = true)
    {
        if ($cache && $this->_default_properties !== null) {
            return $this->_default_properties;
        }

        return $this->_default_properties = CExClassFieldProperty::getDefaultPropertiesFor($this);
    }

    /**
     * @inheritdoc
     */
    function getAutocompleteList(
        $keywords,
        $where = null,
        $limit = null,
        $ljoin = null,
        $order = null,
        $group_by = null,
        bool $strict = true
    ) {
        /** @var self[] $list */
        $list = $this->loadList($where, null, null, null, $ljoin, null, null, $strict);

        $real_list = [];
        $re        = preg_quote($keywords, "/");
        $re        = CMbString::allowDiacriticsInRegexp($re);
        $re        = str_replace("/", "\\/", $re);
        $re        = "/($re)/i";

        foreach ($list as $_ex_field) {
            if ($keywords === "%" || $keywords == "" || preg_match($re, $_ex_field->_view)) {
                $_ex_field->updateTranslation();
                $_group           = $_ex_field->loadRefExGroup();
                $_ex_field->_view = "$_group->_view - $_ex_field->_view";

                $real_list[$_ex_field->_id] = $_ex_field;
            }
        }

        return $real_list;
    }

    /**
     * Get all the fields' names
     *
     * @param bool $name_as_key Put the names in the keys
     * @param bool $all_groups  Load all groups
     *
     * @return string[] List of field names
     */
    function getFieldNames($name_as_key = true, $all_groups = true)
    {
        $ds = $this->_spec->ds;

        $req = new CRequest();
        $req->addTable($this->_spec->table);
        $req->addSelect("ex_class_field.name, ex_class_field_translation.std AS locale");

        $ljoin = [
            "ex_class_field_translation" => "ex_class_field_translation.ex_class_field_id = ex_class_field.ex_class_field_id",
        ];
        $req->addLJoin($ljoin);

        $this->completeField("ex_group_id");

        $where = [];
        if ($all_groups) {
            $ex_group             = $this->loadRefExGroup();
            $where_ids            = [
                "ex_class_id" => $ds->prepare("= %", $ex_group->ex_class_id),
            ];
            $ids                  = $ex_group->loadIds($where_ids);
            $where["ex_group_id"] = $ds->prepareIn($ids);
        } else {
            $where["ex_group_id"] = $ds->prepare("= %", $this->ex_group_id);
        }
        $req->addWhere($where);

        $results = $ds->loadList($req->makeSelect());

        if ($name_as_key) {
            return array_combine(CMbArray::pluck($results, "name"), CMbArray::pluck($results, "locale"));
        }

        return array_combine(CMbArray::pluck($results, "locale"), CMbArray::pluck($results, "name"));
    }

    /**
     * Get formula values
     *
     * @return string[]
     */
    function getFormulaValues()
    {
        $ret = true;

        if ($this->concept_id) {
            $concept = $this->loadRefConcept();
            if (!$concept->ex_list_id) {
                return $ret;
            }

            $list = $concept->loadRefExList();
            if (!$list->coded) {
                return $ret;
            }

            $items = $list->loadRefItems(true);
            $ret   = array_combine(CMbArray::pluck($items, "ex_list_item_id"), CMbArray::pluck($items, "code"));
        }

        return $ret;
    }

    /**
     * Validate formula
     *
     * @param string $formula Formula
     *
     * @return void
     */
    function validateFormula($formula)
    {
    }

    /**
     * Check formula tokens, must have at least one
     *
     * @param string $formula The formula as text
     * @param array  $matches The matches
     *
     * @return bool
     */
    function checkFormulaTokens($formula, &$matches)
    {
        $matches    = [];
        $has_fields = preg_match_all(self::$_formula_token_re, $formula, $matches);

        $has_constant = false;
        foreach (self::$_formula_constants as $_constant) {
            if (strpos($formula, $_constant) !== false) {
                $has_constant = true;
                break;
            }
        }

        return $has_constant || $has_fields;
    }

    /**
     * Convert formula to be stored in DB
     *
     * @param bool $update Update $this->formula
     *
     * @return string|null
     */
    function formulaToDB($update = true)
    {
        if ($this->_formula === null) {
            return null;
        }

        if ($this->_formula === "") {
            $this->formula = "";

            return null;
        }

        $field_names = $this->getFieldNames(false);
        $formula     = $this->_formula;

        $matches = [];
        if (!$this->checkFormulaTokens($formula, $matches)) {
            return "Formule invalide";
        }

        $msg = [];

        foreach ($matches[1] as $_match) {
            $_trimmed = trim($_match);
            if (!array_key_exists($_trimmed, $field_names)) {
                $msg[] = "\"$_match\"";
            } else {
                $formula = str_replace("[$_match]", "[" . $field_names[$_trimmed] . "]", $formula);
            }
        }

        if (empty($msg)) {
            if ($update) {
                $this->formula = $formula;
            }

            return null;
        }

        return "Des éléments n'ont pas été reconnus dans la formule: " . implode(", ", $msg);
    }

    /**
     * Convert formula from DB, to be used in the form
     *
     * @return string|null Message if error
     */
    function formulaFromDB()
    {
        //$this->completeField("formula"); memory limit :(

        if (!$this->formula) {
            return null;
        }

        $field_names = $this->getFieldNames(true);

        $formula = $this->formula;

        $matches = [];
        if (!$this->checkFormulaTokens($formula, $matches)) {
            return "Formule invalide";
        }

        foreach ($matches[1] as $_match) {
            $_trimmed = trim($_match);
            if (array_key_exists($_trimmed, $field_names)) {
                $formula = str_replace($_match, $field_names[$_trimmed], $formula);
            }
        }

        $this->_formula = $formula;

        return null;
    }

    /**
     * Check formula
     *
     * @return null|string
     */
    function checkFormula()
    {
        return $this->formulaToDB(false);
    }

    /**
     * @inheritdoc
     */
    function check()
    {
        if ($msg = $this->checkFormula(false)) {
            return $msg;
        }

        // verification des coordonnées
        /*$where = array(
          $this->_spec->key => "!= '$this->_id'",
          "coord_field_x" => "NOT BETWEEN coord_field_x AND coord_field_x + coord_field_colspan",
          "coord_field_y" => "NOT BETWEEN coord_field_y AND coord_field_y + coord_field_rowspan",
        );*/

        $this->formulaToDB(true);

        if (!$this->_id) {
            $this->loadRefConcept(true);

            if (!$this->_ref_concept->canDo()->read) {
                return 'access-forbidden';
            }
        }

        return parent::check();
    }

    /**
     * Load trigger data
     *
     * @return void
     */
    function loadTriggeredData()
    {
        $triggers = $this->loadBackRefs("ex_triggers");

        $this->_triggered_data = [];

        if (!count($triggers)) {
            return;
        }

        $keys   = CMbArray::pluck($triggers, "trigger_value");
        $values = CMbArray::pluck($triggers, "ex_class_triggered_id");

        $this->_triggered_data = array_combine($keys, $values);
    }

    /**
     * @return CExClassFieldPredicate[]
     */
    function loadRefPredicates()
    {
        return $this->_ref_predicates = $this->loadBackRefs("predicates");
    }

    /**
     * @return CExClassFieldProperty[]
     */
    function loadRefProperties()
    {
        return $this->_ref_properties = $this->loadBackRefs("properties");
    }

    /**
     * @param bool $cache Use cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate($cache = true)
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id", $cache);
    }

    /**
     * Load ex field group
     *
     * @param bool $cache Use cache
     *
     * @return CExClassFieldGroup
     */
    function loadRefExGroup($cache = true)
    {
        if ($cache && $this->_ref_ex_group && $this->_ref_ex_group->_id) {
            return $this->_ref_ex_group;
        }

        return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
    }

    /**
     * Load Ex Class
     *
     * @param bool $cache Use object cache
     *
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        $this->_ref_ex_class       = $this->loadRefExGroup($cache)->loadRefExClass($cache);
        $this->_pixel_positionning = $this->_ref_ex_class->pixel_positionning;

        return $this->_ref_ex_class;
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }

    /**
     * Load concept
     *
     * @param bool $cache Use cache
     *
     * @return CExConcept
     */
    function loadRefConcept($cache = true)
    {
        return $this->_ref_concept = $this->loadFwdRef("concept_id", $cache);
    }

    /**
     * Load translation object
     *
     * @param bool $cache Use cache
     *
     * @return CExClassFieldTranslation
     */
    function loadRefTranslation($cache = true)
    {
        if ($cache && $this->_ref_translation) {
            return $this->_ref_translation;
        }

        $trans = CExClassFieldTranslation::tr($this->_id);
        $trans->fillIfEmpty($this->name);

        return $this->_ref_translation = $trans;
    }

    /**
     * Load enum translations
     *
     * @return CExClassFieldEnumTranslation[]
     */
    function loadRefEnumTranslations()
    {
        $trans                    = new CExClassFieldEnumTranslation;
        $trans->lang              = CAppUI::pref("LOCALE");
        $trans->ex_class_field_id = $this->_id;

        return $trans->loadMatchingList();
    }

    /**
     * Update locales array
     *
     * @return void
     */
    function updateTranslation()
    {
        if (!$this->_id) {
            return;
        }

        $items = $this->getRealListOwner()->getItemNames();

        $ex_class = $this->loadRefExClass();

        $key         = ".$this->name";
        $_class_name = $ex_class->getExClassName();

        CAppUI::addLocale($_class_name, "$key.", CAppUI::tr("Undefined"));

        foreach ($items as $_id => $_item) {
            CAppUI::addLocale($_class_name, "$key.$_id", $_item);
        }

        $trans = null;

        $local_key = "$key-$this->name";
        if (isset(CAppUI::$locales[$_class_name][$local_key])) {
            $this->_locale = CAppUI::$locales[$_class_name][$local_key];
        } else {
            $trans         = $trans ? $trans : $this->loadRefTranslation();
            $this->_locale = $trans->std;
        }

        $local_key = "$key-$this->name-desc";
        if (isset(CAppUI::$locales[$_class_name][$local_key])) {
            $this->_locale_desc = CAppUI::$locales[$_class_name][$local_key];
        } else {
            $trans              = $trans ? $trans : $this->loadRefTranslation();
            $this->_locale_desc = $trans->desc;
        }

        $local_key = "$key-$this->name-court";
        if (isset(CAppUI::$locales[$_class_name][$local_key])) {
            $this->_locale_court = CAppUI::$locales[$_class_name][$local_key];
        } else {
            $trans               = $trans ? $trans : $this->loadRefTranslation();
            $this->_locale_court = $trans->court;
        }

        $this->_view = $this->_locale;
    }

    function getTableName()
    {
        return $this->loadRefExClass()->getTableName();
    }

    /**
     * Get Spec object
     *
     * @return CMbFieldSpec
     */
    function getSpecObject()
    {
        CBoolSpec::$_default_no = false;
        $this->_spec_object     = @CMbFieldSpecFact::getSpecWithClassName("CExObject", $this->name, $this->prop);
        CBoolSpec::$_default_no = true;

        return $this->_spec_object;
    }

    /**
     * Build SQL spec
     *
     * @param bool $union Build full SQL spec
     *
     * @return string
     */
    function getSQLSpec($union = true)
    {
        $spec_obj          = $this->getSpecObject();
        $spec_obj->default = null;

        // Don't add notNull
        $notNull           = $spec_obj->notNull;
        $spec_obj->notNull = false;

        $db_spec = $spec_obj->getFullDBSpec();

        if ($union) {
            $ds        = $this->_spec->ds;
            $db_parsed = CMbFieldSpec::parseDBSpec($db_spec, true);

            if ($db_parsed['type'] === "ENUM") {
                $prop_parsed = $ds->getDBstruct($this->getTableName(), $this->name, true);

                if (isset($prop_parsed[$this->name])) {
                    $db_parsed['params'] = array_merge($db_parsed['params'], $prop_parsed['params']);
                }

                $db_parsed['params'] = array_unique($db_parsed['params']);

                $spec_obj->list = implode("|", $db_parsed['params']);
                $db_spec        = $spec_obj->getFullDBSpec();
            }
        }

        // Add notNull back
        $spec_obj->notNull = $notNull;

        return $db_spec;
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        if (!$this->_keep_position) {
            $reset_position = $this->fieldModified("ex_group_id") || $this->fieldModified(
                    "disabled"
                ) || $this->fieldModified("hidden");

            // If we change its group, we need to reset its coordinates
            if ($reset_position) {
                $this->coord_field_x = "";
                $this->coord_field_y = "";
                $this->coord_label_x = "";
                $this->coord_label_y = "";
                $this->subgroup_id   = "";
            }

            $subgroup_modified = $this->fieldModified("subgroup_id");
            if ($reset_position || $subgroup_modified) {
                if (!$this->fieldModified("coord_left")) {
                    $this->coord_left = "";
                }

                if (!$this->fieldModified("coord_top")) {
                    $this->coord_top = "";
                }
            }
        }

        parent::updatePlainFields();
    }

    /**
     * Make a unique field name
     *
     * @return string
     */
    static function getUniqueName()
    {
        $sibling = new self;

        do {
            $uniqid = uniqid("f");
            $where  = [
                "name" => "= '$uniqid'",
            ];
            $sibling->loadObject($where);
        } while ($sibling->_id);

        return $uniqid;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        // !$this->prop pour la conservation de l'ordre de liste lors de la duplication (ne pas récupérer celle du concept associé)
        if (!$this->_id && $this->concept_id && !$this->prop) {
            $this->prop = $this->loadRefConcept()->prop;
        }

        if (!$this->_id && $this->_make_unique_name) {
            $this->name = self::getUniqueName();
        }

        if ($msg = $this->check()) {
            return $msg;
        }

        $ds = $this->_spec->ds;

        if (!$this->_id || $this->_regenerate) {
            $table_name = $this->getTableName();
            $sql_spec   = $this->getSQLSpec(false);
            $query      = "ALTER TABLE `$table_name` ADD `$this->name` $sql_spec";

            $spec_type = $this->_spec_object->getSpecType();

            if (!$ds->query($query)) {
                return "Le champ '$this->name' n'a pas pu être ajouté à la table '$table_name' (" . $ds->error() . ")";
            }

            if (in_array($spec_type, self::$_indexed_types)) {
                $query = "ALTER TABLE `$table_name` ADD INDEX (`$this->name`);";
                if (!$ds->query($query)) {
                    CApp::log($ds->error());
                }
            }
        } else {
            if ($this->fieldModified("name") || $this->fieldModified("prop")) {
                $table_name = $this->getTableName();
                $sql_spec   = $this->getSQLSpec();
                $query      = "ALTER TABLE `$table_name` CHANGE `{$this->_old->name}` `$this->name` $sql_spec";

                if (!$ds->query($query)) {
                    return "Le champ '$this->name' n'a pas pu être mis à jour (" . $ds->error() . ")";
                }
            }
        }

        $this->completeField("formula", "result_threshold_low", "result_threshold_high");
        if (!$this->formula) {
            $this->result_threshold_low  = "";
            $this->result_threshold_high = "";
        }

        $locale         = $this->_locale;
        $locale_desc    = $this->_locale_desc;
        $locale_court   = $this->_locale_court;
        $triggered_data = $this->_triggered_data;

        if ($msg = parent::store()) {
            return $msg;
        }

        // form triggers
        if ($triggered_data) {
            $triggered_object = json_decode($triggered_data, true);

            if (is_array($triggered_object)) {
                foreach ($triggered_object as $_value => $_class_trigger_id) {
                    $trigger                    = new CExClassFieldTrigger();
                    $trigger->ex_class_field_id = $this->_id;
                    $trigger->trigger_value     = $_value;
                    $trigger->loadMatchingObject();

                    if ($_class_trigger_id) {
                        $trigger->ex_class_triggered_id = $_class_trigger_id;
                        $trigger->store();
                    } else {
                        $trigger->delete();
                    }
                }
            }
        }

        // self translations
        if ($locale || $locale_desc || $locale_court) {
            $trans        = $this->loadRefTranslation();
            $trans->std   = $locale;
            $trans->desc  = $locale_desc;
            $trans->court = $locale_court;
            if ($msg = $trans->store()) {
                CApp::log($msg, CClassMap::getInstance()->getShortName($this));
            }
        }

        if ($this->_store_tag_items) {
            $this->storeTagItems();
        }

        return null;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function storeTagItems(): void
    {
        $tags = [];
        if ($this->_ex_class_field_tags) {
            $tags = explode('|', $this->_ex_class_field_tags);
        }

        $field_tags = [];

        if ($field_tag_items = $this->loadFieldTagItems()) {
            $field_tags = CMbArray::pluck($field_tag_items, 'tag');
        }

        $tags_to_add    = array_diff($tags, $field_tags);
        $tags_to_remove = array_diff($field_tags, $tags);

        foreach ($tags_to_remove as $_tag) {
            $_tag_item                    = new CExClassFieldTagItem();
            $_tag_item->ex_class_field_id = $this->_id;
            $_tag_item->tag               = $_tag;

            if ($_tag_item->loadMatchingObjectEsc()) {
                $_tag_item->delete();
            }
        }

        foreach ($tags_to_add as $_tag) {
            $_tag_item                    = new CExClassFieldTagItem();
            $_tag_item->ex_class_field_id = $this->_id;
            $_tag_item->tag               = $_tag;

            if (!$_tag_item->loadMatchingObjectEsc()) {
                $_tag_item->store();
            }
        }
    }

    /**
     * @return CExListItemsOwner
     */
    function getRealListOwner()
    {
        if ($this->concept_id) {
            return $this->loadRefConcept()->getRealListOwner();
        }

        return parent::getRealListOwner();
    }

    /**
     * @param $str
     *
     * @return string
     */
    static function escapeProp($str)
    {
        return strtr($str, self::$_prop_escape);
    }

    /**
     * @param $str
     *
     * @return string
     */
    static function unescapeProp($str)
    {
        return strtr($str ?? '', array_flip(self::$_prop_escape));
    }

    /**
     * Gets field label traduction
     *
     * @param string $type Type of locale (null|court|desc)
     *
     * @return string
     */
    function getLabel($type = null)
    {
        $suffix = ($type) ? "-{$type}" : '';

        return CAppUI::tr($this->loadRefExClass()->getExClassName() . '-' . $this->name . $suffix);
    }

    function canIncrement()
    {
        return ($this->_id && ($this->getSpecObject() instanceof CNumSpec));
    }

    function shouldAutoIncrement()
    {
        return (!$this->disabled && $this->canIncrement() && $this->auto_increment);
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function loadFieldTagItems(): ?array
    {
        $this->_ref_ex_class_field_tag_items = $this->loadBackRefs('ex_class_field_tag_items');

        if ($this->_ref_ex_class_field_tag_items) {
            $this->_ex_class_field_tags = CMbArray::pluck($this->_ref_ex_class_field_tag_items, 'tag');
        }

        return $this->_ref_ex_class_field_tag_items;
    }

    /**
     * @return bool
     */
    public function hasAvailableTags(): bool
    {
        if (!$this->_id) {
            return $this->_has_available_tags = false;
        }

        foreach (CExClassFieldTagFactory::getTags() as $_tag) {
            try {
                $_object = CExClassFieldTagFactory::getTag($_tag);

                if ($_object->validate($this)) {
                    return $this->_has_available_tags = true;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $this->_has_available_tags = false;
    }
}
