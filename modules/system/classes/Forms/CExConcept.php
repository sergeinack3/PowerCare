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
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Mediboard\Forms\Traits\HermeticModeTrait;
use Ox\Mediboard\Patients\CConstantesMedicales;

/**
 * Concept class
 *
 * Permet de définit des champs "types", cela permet aussi de regrouper des champs sous le même "concept" au sens
 * métier. C'est utilisé notamment pour le report de valeurs, qui peut alors se faire d'un formulaire à l'autre, pour
 * peu que les champs soient basés sur le même concept.
 */
class CExConcept extends CExListItemsOwner implements FormComponentInterface
{
    use HermeticModeTrait;

    public $ex_concept_id;

    public $ex_list_id;
    public $name; // != object_class, object_id, ex_ClassName_event_id,
    public $prop;
    public $native_field;

    /** @var CExList */
    public $_ref_ex_list;

    /** @var CExClassField[] */
    public $_ref_class_fields;

    /** @var CMbFieldSpec */
    public $_concept_spec;

    public $_native_field_view;

    static $_options_order = [
        "list",
        "notNull",
        "typeEnum",
        "length",
        "maxLength",
        "minLength",
        "min",
        "max",
        "pos",
        "progressive",

        "ccam",
        "cim10",
        "adeli",
        "insee",
        "rib",
        "siret",
        "order_number",

        "class",
        "cascade",
    ];

    /**
     * Parse a search string, from the search form
     *
     * @param string $concept_search The keywords to search for
     *
     * @return array
     */
    static function parseSearch($concept_search)
    {
        $concept_search = utf8_encode($concept_search);
        $args           = json_decode($concept_search);

        $search = [];
        foreach ($args as $_key => $_val) {
            $matches = [];

            if (preg_match('/^cv(\d+)_(\d+)_([a-z]+)$/', $_key, $matches)) {
                [, $concept_id, $i, $k] = $matches;
                if (!isset($search[$concept_id])) {
                    $search[$concept_id] = [];
                }

                $search[$concept_id][$i][$k] = $_val;
            }
        }

        return $search;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_concept";
        $spec->key             = "ex_concept_id";
        $spec->uniques["name"] = ["name", 'group_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["ex_list_id"]   = "ref class|CExList back|concepts";
        $props["name"]         = "str notNull seekable";
        $props["prop"]         = "str notNull show|0";
        $props["native_field"] = "str show|0";
        $props['group_id']     = 'ref class|CGroups back|ex_concepts';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->name;

        $this->updatePropFromList();

        if ($this->ex_list_id) {
            $list        = $this->loadRefExList();
            $this->_view .= " [$list->_view]";
        } else {
            $spec_type   = $this->loadConceptSpec()->getSpecType();
            $this->_view .= " [" . CAppUI::tr("CMbFieldSpec.type.$spec_type") . "]";
        }
    }

    /**
     * Get the native field view
     *
     * @return null|string
     */
    function getNativeFieldView()
    {
        $this->_native_field_view = null;

        // Vue du "native field"
        if ($this->native_field) {
            [$class, $field] = explode(" ", $this->native_field, 2);

            $this->_native_field_view = "";
            if (strpos($field, "CONNECTED_USER") === false) {
                $this->_native_field_view = CAppUI::tr($class) . " / ";
            }

            $this->_native_field_view .= CAppUI::tr("$class-$field");
        }

        return $this->_native_field_view;
    }

    public function getNativeFieldClass(): ?string
    {
        if (!$this->native_field) {
            return null;
        }

        return explode(' ', $this->native_field, 2)[0];
    }

    public function getNativeFieldName(): ?string
    {
        if (!$this->native_field) {
            return null;
        }

        return explode(' ', $this->native_field, 2)[1];
    }

    /**
     * @inheritdoc
     */
    function loadEditView()
    {
        parent::loadEditView();

        $this->loadAvailableGroups();

        $this->getNativeFieldView();

        $fields = $this->loadRefClassFields();
        foreach ($fields as $_field) {
            $_field->loadRefExClass();
        }
    }

    /**
     * @return void
     */
    function updatePropFromList()
    {
        $spec = $this->loadConceptSpec();
        if (!$spec instanceof CEnumSpec) {
            return;
        }

        if ($this->ex_list_id) {
            $list = $this->loadRefExList();
            $ids  = $list->getItemsKeys();
        } else {
            $ids = $this->getItemsKeys();
        }

        $suffix  = " list|" . implode("|", $ids);
        $pattern = '/( list\|[^ ]+)/';

        if (!preg_match($pattern, $this->prop)) {
            $this->prop .= $suffix;
        } else {
            $this->prop = preg_replace($pattern, $suffix, $this->prop);
        }
    }

    /**
     * Update field property, by moving list|, default| and vertical| to the end
     *
     * @param string $prop The property to update
     *
     * @return string
     */
    function updateFieldProp($prop)
    {
        //$concept_spec = $this->loadConceptSpec();
        $list_re     = '/(\slist\|[^\s]+)/';
        $default_re  = '/(\sdefault\|[^\s]+)/';
        $vertical_re = '/(\svertical(?:\|[^\s]+)?)/';

        $list_prop     = "";
        $default_prop  = "";
        $vertical_prop = "";

        $new_prop = preg_replace($vertical_re, "", $this->prop);

        // extract $prop's list|XXX
        $matches = [];
        if (preg_match($list_re, $prop, $matches)) {
            $list_prop = $matches[1];
            $new_prop  = preg_replace($list_re, "", $new_prop);

            // extract $prop's default|XXX
            $matches = [];
            if (preg_match($default_re, $prop, $matches)) {
                $default_prop = $matches[1];
                $new_prop     = preg_replace($default_re, "", $new_prop);
            }
        }

        // extract $prop's vertical
        $matches = [];
        if (preg_match($vertical_re, $prop, $matches)) {
            $vertical_prop = $matches[1];
            $new_prop      = preg_replace($vertical_re, "", $new_prop);
        }

        return $new_prop . $list_prop . $default_prop . $vertical_prop;
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExList
     */
    function loadRefExList($cache = true)
    {
        return $this->_ref_ex_list = $this->loadFwdRef("ex_list_id", $cache);
    }

    /**
     * @return CExClassField[]
     */
    function loadRefClassFields()
    {
        return $this->_ref_class_fields = $this->loadBackRefs("class_fields");
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();
        $this->loadConceptSpec();
        $this->loadRefClassFields();
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
        $list = parent::getAutocompleteList($keywords, $where, $limit, $ljoin, $order, $group_by, $strict);

        /** @var self $_item */
        foreach ($list as $_item) {
            $_item->loadRefsTagItems();
        }

        return $list;
    }

    /**
     * @return CMbFieldSpec
     */
    function loadConceptSpec()
    {
        if ($this->_concept_spec) {
            return $this->_concept_spec;
        }

        return $this->_concept_spec = self::getConceptSpec($this->prop);
    }

    /**
     * Get reportable fields
     *
     * @param bool $allow_form_fields Allow form fields in the list
     *
     * @return array
     */
    static function getReportableFields($allow_form_fields = false)
    {
        $list = [];

        $classes   = CExClassEvent::getReportableClasses();
        $classes[] = "CConstantesMedicales";
        $classes[] = "CTransmissionMedicale";
        $classes[] = "CSejour";
        $classes[] = "CConsultation";
        $classes[] = "CBMRBHRe";
        $classes[] = "CNaissance";

        $full = false;

        foreach ($classes as $_class) {
            $ex_class_event             = new CExClassEvent();
            $ex_class_event->host_class = $_class;
            $list                       = array_merge(
                $list,
                $ex_class_event->buildHostFieldsList($_class, $allow_form_fields)
            );
        }

        if (!$full) {
            $_fields = [
                "CPatient _annees",
                "CPatient _poids",
                "CPatient _taille",

                'CPatient situation_famille',
                'CPatient tutelle',
                'CPatient activite_pro',

                'CBMRBHRe hospi_etranger',

                'CNaissance date_time',

                "CTransmissionMedicale text",

                "CSejour libelle",

                "CSejour _latest_chung_score",
                "CSejour _obs_entree_motif",
                "CSejour _obs_entree_histoire_maladie",
                "CSejour _obs_entree_examen",
                "CSejour _obs_entree_rques",
                "CSejour _obs_entree_conclusion",

                "CConsultation motif",
                "CConsultation rques",
                "CConsultation examen",
                "CConsultation traitement",
                "CConsultation histoire_maladie",
                "CConsultation brancardage",
                "CConsultation conclusion",
            ];

            foreach (CConstantesMedicales::$list_constantes as $_field => $_params) {
                if (!empty($_params["formfields"])) {
                    foreach ($_params["formfields"] as $_form_field) {
                        $_fields[] = "CConstantesMedicales $_form_field";
                    }
                } else {
                    $_fields[] = "CConstantesMedicales $_field";
                }
            }

            $select = array_flip($_fields);

            $list = array_intersect_key($list, $select);
        }

        return $list;
    }

    /**
     * Order spec options regarding the CExConcept::$_options_order
     *
     * @param array $options Options
     *
     * @return void
     */
    static function orderSpecs(&$options)
    {
        $compare = function ($a, $b) {
            $options = CExConcept::$_options_order;

            return (isset($options[$a]) ? $options[$a] : 1000) - (isset($options[$b]) ? $options[$b] : 1000);
        };

        uksort($options, $compare);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($error = $this->checkGroupStoring()) {
            return $error;
        }

        $prop_changed = $this->fieldModified("prop");

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($prop_changed) {
            $fields = $this->loadRefClassFields();
            foreach ($fields as $_field) {
                $new_prop = $this->updateFieldProp($_field->prop);
                $modif    = ($_field->prop != $new_prop);

                $_field->prop = $new_prop;

                if ($msg = $_field->store()) {
                    continue;
                }

                if ($modif) {
                    $_field->updateTranslation();
                    CAppUI::displayMsg($msg, "Champ <strong>$_field->_view</strong> mis à jour");
                }
            }
        }

        return null;
    }

    /**
     * Get a dummy concept spec from a field prop
     *
     * @param string $prop The field pro as a string
     *
     * @return CMbFieldSpec
     */
    static function getConceptSpec($prop)
    {
        if ($prop == "mbField") {
            $prop = "";
        }

        $field = "dummy";

        $object                 = new CMbObject();
        $object->$field         = null;
        $object->_props[$field] = $prop;
        @$object->_specs = $object->getSpecs();

        $spec    = @CMbFieldSpecFact::getSpec($object, $field, $prop);
        $options = $spec->getOptions();

        $invalid = [
            "moreThan",
            "moreEquals",
            "sameAs",
            "notContaining",
            "notNear",
            "dependsOn",
            "helped",
            "aidesaisie",
            "callable",
        ];
        foreach ($invalid as $_invalid) {
            unset($options[$_invalid]);
        }

        self::orderSpecs($options);

        $spec->_options = $options;

        return $spec;
    }

    /**
     * @return CExList|CExListItemsOwner
     */
    function getRealListOwner()
    {
        if ($this->ex_list_id) {
            return $this->loadRefExList();
        }

        return parent::getRealListOwner();
    }
}

CExConcept::$_options_order = array_flip(CExConcept::$_options_order);
