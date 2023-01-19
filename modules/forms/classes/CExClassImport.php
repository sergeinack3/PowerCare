<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use DOMElement;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassFieldPredicate;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExList;
use Ox\Mediboard\System\Forms\CExListItem;

class CExClassImport extends CMbXMLObjectImport
{
    /** @var string|null */
    private $group_id;

    protected $name_suffix;

    protected $imported = [];

    /** @var CExClassFieldPredicate[] */
    protected $predicates_to_fix = [];

    protected $import_order = [
        "//object[@class='CExClass']",
        "//object[@class='CExList']",
        "//object[@class='CExConcept']",
        "//object",
    ];

    /**
     * @param string|null $group_id
     */
    public function setGroupId(?string $group_id): void
    {
        $this->group_id = $group_id;
    }

    /**
     * @inheritdoc
     */
    function logError($message)
    {
        CAppUI::stepAjax($message, UI_MSG_WARNING);
    }

    /**
     * @inheritdoc
     */
    function afterImport()
    {
        foreach ($this->predicates_to_fix as $_predicate) {
            $value = explode('|', $_predicate->value);

            $values = [];
            foreach ($value as $_value) {
                $values[] = $this->getIdFromGuid($this->map["CExListItem-" . $_value]);
            }

            $_predicate->value = implode('|', $values);
            if ($msg = $_predicate->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function importObject(DOMElement $element)
    {
        $id = $element->getAttribute("id");

        if (isset($this->imported[$id])) {
            return;
        }

        $this->name_suffix = " (import du " . CMbDT::dateTime() . ")";

        $map_to = isset($this->map[$id]) ? $this->map[$id] : null;

        switch ($element->getAttribute("class")) {
            // --------------------
            case "CExClass":
                $values = self::getValuesFromElement($element);

                $ex_class       = new CExClass();
                $ex_class->name = $this->options["ex_class_name"];

                if (CExClass::inHermeticMode(false)) {
                    $ex_class->group_id = $this->group_id;
                } else {
                    $ex_class->group_id = CGroups::loadCurrent()->_id;
                }

                $ex_class->pixel_positionning         = $values["pixel_positionning"];
                $ex_class->native_views               = $values["native_views"];
                $ex_class->_dont_create_default_group = true;

                if ($msg = $ex_class->store()) {
                    throw new Exception($msg);
                }

                CAppUI::stepAjax("Formulaire '%s' créé", UI_MSG_OK, $ex_class->name);

                $map_to = $ex_class->_guid;
                break;

            // --------------------
            case "CExList":
                if ($map_to == "__create__") {
                    /** @var CExList $_ex_list */
                    $_ex_list = $this->getObjectFromElement($element);

                    if (CExClass::inHermeticMode(false)) {
                        $_ex_list->group_id = $this->group_id;
                    } else {
                        $_ex_list->group_id = CGroups::loadCurrent()->_id;
                    }

                    if ($msg = $_ex_list->store()) {
                        $_ex_list->name .= $this->name_suffix;
                    }

                    if ($msg = $_ex_list->store()) {
                        CAppUI::stepAjax($msg, UI_MSG_WARNING);
                        break;
                    }
                    CAppUI::stepAjax("Liste '%s' créée", UI_MSG_OK, $_ex_list);

                    $_elements = $this->getElementsByFwdRef("CExListItem", "list_id", $id);
                    foreach ($_elements as $_element) {
                        $_list_item = new CExListItem();
                        CMbObject::setProperties(self::getValuesFromElement($_element), $_list_item);
                        $_list_item->list_id = $_ex_list->_id;

                        if ($msg = $_list_item->store()) {
                            CAppUI::stepAjax($msg, UI_MSG_WARNING);
                            break;
                        }
                        CAppUI::stepAjax("Elément de liste '%s' créé", UI_MSG_OK, $_list_item);

                        $_item_id = $_element->getAttribute("id");

                        $this->map[$_item_id]      = $_list_item->_guid;
                        $this->imported[$_item_id] = true;
                    }

                    $map_to = $_ex_list->_guid;
                } else {
                    /** @var CExList $ex_list */
                    $ex_list = CStoredObject::loadFromGuid($map_to);

                    $_elements = $this->getElementsByFwdRef("CExListItem", "list_id", $id);
                    foreach ($_elements as $_element) {
                        $_list_item = new CExListItem();
                        CMbObject::setProperties(self::getValuesFromElement($_element), $_list_item);
                        $_list_item->list_id = $ex_list->_id;
                        $_list_item->code    = null;

                        $_list_item->loadMatchingObjectEsc();

                        if ($_list_item->_id) {
                            $_item_id             = $_element->getAttribute("id");
                            $this->map[$_item_id] = $_list_item->_guid;
                        }
                    }
                }
                break;

            // --------------------
            case "CExConcept":
                if ($map_to == "__create__") {
                    /** @var CExConcept $_ex_concept */
                    $_ex_concept = $this->getObjectFromElement($element);

                    if (CExClass::inHermeticMode(false)) {
                        $_ex_concept->group_id = $this->group_id;
                    } else {
                        $_ex_concept->group_id = CGroups::loadCurrent()->_id;
                    }

                    if ($_ex_concept->ex_list_id) {
                        $_ex_concept->updatePropFromList();
                    }

                    $_ex_concept->prop = $_ex_concept->updateFieldProp($_ex_concept->prop);

                    if ($msg = $_ex_concept->store()) {
                        $_ex_concept->name .= $this->name_suffix;
                    }

                    if ($msg = $_ex_concept->store()) {
                        CAppUI::stepAjax($msg, UI_MSG_WARNING);
                        break;
                    }

                    CAppUI::stepAjax("Concept '%s' créé", UI_MSG_OK, $_ex_concept);

                    $_elements = $this->getElementsByFwdRef("CExListItem", "concept_id", $id);

                    foreach ($_elements as $_element) {
                        $_list_item = new CExListItem();
                        CMbObject::setProperties(self::getValuesFromElement($_element), $_list_item);
                        $_list_item->concept_id = $_ex_concept->_id;

                        if ($msg = $_list_item->store()) {
                            CAppUI::stepAjax($msg, UI_MSG_WARNING);
                            break;
                        }
                        CAppUI::stepAjax("Elément de liste '%s' créé", UI_MSG_OK, $_list_item);

                        $_item_id = $_element->getAttribute("id");

                        $this->map[$_item_id]      = $_list_item->_guid;
                        $this->imported[$_item_id] = true;
                    }

                    $map_to = $_ex_concept->_guid;
                }
                break;

            case "CExClassField":
                /** @var CExClassField $_ex_field */
                $_ex_field = $this->getObjectFromElement($element);
                if ($this->options["ignore_disabled_fields"] && $_ex_field->disabled) {
                    break;
                }
                $_ex_field->_make_unique_name = false;

                // Met à jour default|XXX des champs enum pour garder la bonne référence
                // @FIXME Ne fonctionne pas à cause du fait qu'il y a un concept_id ....
                $_spec_obj = $_ex_field->getSpecObject();
                if ($_spec_obj instanceof CEnumSpec && $_spec_obj->default) {
                    $_new_default    = $this->getIdFromGuid($this->map["CExListItem-$_spec_obj->default"]);
                    $_ex_field->prop = preg_replace('/ default\|\d+/', " default|$_new_default", $_ex_field->prop);
                }

                // Conservation de l'ordre des éléments de liste
                if ($_spec_obj instanceof CEnumSpec) {
                    // Récupération de l'ordre de la liste à importer
                    $list_ids = $_spec_obj->_list;

                    $new_list_ids = [];
                    foreach ($list_ids as $_list_id) {
                        if (isset($this->map["CExListItem-{$_list_id}"])) {
                            // Récupération de l'ID du nouvel élément de liste importé
                            $new_list_ids[] = $this->getIdFromGuid($this->map["CExListItem-{$_list_id}"]);
                        } else {
                            $_list_owner = $_ex_field->getRealListOwner();
                            $_ds         = $_list_owner->getDS();

                            $_list_item_element = $this->xpath->query("//*[@id='CExListItem-$_list_id']")->item(0);
                            $_list_item_values  = self::getValuesFromElement($_list_item_element);

                            $where = [
                                "name" => $_ds->prepare("= ?", $_list_item_values["name"]),
                            ];

                            if ($_list_owner instanceof CExClassField) {
                                $where["field_id"] = $_ds->prepare(
                                    "= ?",
                                    $this->getIdFromGuid(
                                        $this->map[$_list_item_values["field_id"]]
                                    )
                                );
                            } elseif ($_list_owner instanceof CExConcept) {
                                $where["concept_id"] = $_ds->prepare(
                                    "= ?",
                                    $this->getIdFromGuid(
                                        $this->map[$_list_item_values["concept_id"]]
                                    )
                                );
                            } elseif ($_list_owner instanceof CExList) {
                                $where["list_id"] = $_ds->prepare(
                                    "= ?",
                                    $this->getIdFromGuid(
                                        $this->map[$_list_item_values["list_id"]]
                                    )
                                );
                            }

                            $_item = new CExListItem();
                            if ($_item->loadObject($where)) {
                                $new_list_ids[] = $_item->_id;
                            }
                        }
                    }
                    $_new_list_prop = implode('|', $new_list_ids);

                    // Remplacement de la prop
                    $_ex_field->prop = preg_replace('/ list(\|\d+)+/', " list|{$_new_list_prop}", $_ex_field->prop);
                }

                if ($msg = $_ex_field->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                    break;
                }
                CAppUI::stepAjax("Champ '%s' créé", UI_MSG_OK, $_ex_field);

                $map_to = $_ex_field->_guid;
                break;

            // --------------------
            case "CExClassFieldGroup":
            case "CExClassFieldSubgroup":
            case "CExClassFieldTranslation":
            case "CExClassMessage":
            case "CExClassHostField":
            case "CExClassFieldProperty":
            case "CExClassFieldTagItem":
                $_object = $this->getObjectFromElement($element);

                if ($msg = $_object->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                    break;
                }
                CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' créé", UI_MSG_OK, $_object);

                $map_to = $_object->_guid;
                break;

            case "CExClassFieldPredicate":
                /** @var CExClassFieldPredicate $_object */
                $_object = $this->getObjectFromElement($element);

                if ($_object->value) {
                    $_field = $_object->loadRefExClassField();
                    if ($_field->getSpecObject() instanceof CEnumSpec) {
                        $this->predicates_to_fix[] = $_object;
                    }
                }

                if ($msg = $_object->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                    break;
                }
                CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' créé", UI_MSG_OK, $_object);

                $map_to = $_object->_guid;
                break;

            case 'CExClassPicture':
                /** @var CExClassPicture $_object */
                $_object = $this->getObjectFromElement($element);

                if ($msg = $_object->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                    break;
                }
                CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' créé", UI_MSG_OK, $_object);

                $map_to = $_object->_guid;

                if ($_object->_base64_content) {
                    $file               = new CFile();
                    $file->file_name    = 'file.jpg';
                    $file->author_id    = CMediusers::get()->_id;
                    $file->file_date    = CMbDT::dateTime();
                    $file->file_type    = $_object->_file_type;
                    $file->object_class = $_object->_class;
                    $file->object_id    = $_object->_id;

                    $file->fillFields();
                    $file->updateFormFields();

                    $file->setContent(base64_decode($_object->_base64_content));

                    if ($msg = $file->store()) {
                        CAppUI::stepAjax($msg, UI_MSG_WARNING);
                        break;
                    }
                }
                break;

            default:
                // Ignore object
                break;
        }

        $this->map[$id] = $map_to;

        $this->imported[$id] = true;
    }

    function getSimilarFromElement(DOMElement $element, $fields = [])
    {
        $class = $element->getAttribute("class");

        $values = self::getValuesFromElement($element);

        /** @var CStoredObject $object */
        $object = new $class();

        if (!empty($fields)) {
            $object->_spec->uniques = [$fields];
        }

        if (CExClass::inHermeticMode(false) && ($object instanceof CExList || $object instanceof CExConcept)) {
            $object->group_id = $this->group_id;

            return $this->getSimilarEx($object, $values);
        }

        return $object->getSimilar($values);
    }

    static function getValuesFromElement(DOMElement $element)
    {
        $values = parent::getValuesFromElement($element);

        array_walk(
            $values,
            function (&$value): void {
                if ($value === '') {
                    $value = null;
                }
            }
        );

        return $values;
    }

    /**
     * @param CExList|CExConcept $object
     * @param array              $values
     *
     * @return null
     */
    private function getSimilarEx($object, $values)
    {
        $spec = $object->_spec;

        if (empty($spec->uniques)) {
            return null;
        }

        $first_unique = reset($spec->uniques);

        if (empty($first_unique)) {
            return null;
        }

        $where = [];
        foreach ($first_unique as $field_name) {
            if (!array_key_exists($field_name, $values)) {
                continue;
            }

            $where[$field_name] = $spec->ds->prepare("=%", $values[$field_name]);
        }

        if ($object->group_id) {
            $where['group_id'] = $spec->ds->prepare('= ?', $object->group_id);
        } else {
            $where['group_id'] = 'IS NULL';
        }

        return $object->loadList($where);
    }
}
