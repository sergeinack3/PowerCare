<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Import;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Abstract CMbObject import class, using XML files
 */
abstract class CMbXMLObjectImport extends CMbObjectImport
{
    /** @var CStoredObject[] */
    static $all = [];

    /** @var DOMDocument */
    protected $dom;

    /** @var array */
    protected $map = [];

    /** @var array */
    protected $options = [];

    /** @var DOMXPath */
    protected $xpath;

    protected $import_order = [
        "//object",
    ];

    protected $stop_import = false;

    /**
     * XML based import constructor
     *
     * @param string $filename XML file name
     *
     * @throws CMbException
     */
    function __construct($filename)
    {
        parent::__construct($filename);

        if (!is_readable($this->file_path)) {
            throw new CMbException("File '$this->file_path' is not readable");
        }

        $this->dom = new DOMDocument();

        $xml = file_get_contents($this->file_path);

        // Suppression des caractères invalides pour DOMDocument
        $xml = CMbString::convertHTMLToXMLEntities($xml);

        $this->dom->loadXML($xml);

        $this->xpath = new DOMXPath($this->dom);
    }

    /**
     * Log an error
     *
     * @param string $message Message
     *
     * @return void
     */
    function logError($message)
    {
        CApp::log($message, LoggerLevels::LEVEL_DEBUG);
    }

    /**
     * Import an object from a DOM element
     *
     * @param array $map     Map information
     * @param array $options Options
     *
     * @return void
     */
    function import($map = null, $options = null)
    {
        $this->map     = $map;
        $this->options = $options;

        foreach ($this->import_order as $_xpath) {
            /** @var DOMNodeList|DOMElement[] $objects */
            $objects = $this->xpath->query($_xpath);

            foreach ($objects as $_object) {
                $this->importObject($_object);

                if ($this->stop_import) {
                    break 2;
                }
            }
        }

        $this->afterImport();
    }

    /**
     * Post processing
     *
     * @return void
     */
    function afterImport()
    {
        // Do nothing
    }

    /**
     * Build an object from a DOM element
     *
     * @param DOMElement $element     The DOM element
     * @param CMbObject  $object      Object
     * @param bool       $ignore_refs Do not import references
     *
     * @return CMbObject
     */
    function getObjectFromElement(DOMElement $element, CMbObject $object = null, $ignore_refs = false)
    {
        if (!$object) {
            $class  = $element->getAttribute("class");
            $object = new $class();
        }

        /** @var CMbObject $object */

        $values = self::getValuesFromElement($element);
        foreach ($values as $_field => $_value) {
            if ($_value && isset($object->_specs[$_field]) && $object->_specs[$_field] instanceof CRefSpec
                && $_field !== $object->_spec->key && !$ignore_refs
            ) {
                $this->importObjectByGuid($_value);

                if (isset($this->map[$_value])) {
                    $values[$_field] = self::getIdFromGuid($this->map[$_value]);
                }
            }
        }

        CMbObject::setProperties($values, $object);

        return $object;
    }

    /**
     * Import an object by looking it up in the document, if it exists
     *
     * @param string $guid Guid of the object to import
     *
     * @return void
     */
    function importObjectByGuid($guid)
    {
        /** @var DOMElement $_element */
        $_element = $this->xpath->query("//*[@id='$guid']")->item(0);

        if (!$_element) {
            $this->logError("'$guid' non trouvé");

            return;
        }

        $this->importObject($_element);
    }

    /**
     * Import an object from an XML element
     *
     * @param DOMElement $element The XML element to import the object from
     *
     * @return mixed
     */
    abstract function importObject(DOMElement $element);

    /**
     * Get ID from a GUID
     *
     * @param string $guid The GUID
     *
     * @return int
     */
    static function getIdFromGuid($guid)
    {
        $_guid = explode("-", $guid);

        return (isset($_guid[1])) ? $_guid[1] : null;
    }

    /**
     * Get an associative array of the raw values from the DOM element
     *
     * @param DOMElement $element The DOM element
     *
     * @return array
     */
    static function getValuesFromElement(DOMElement $element)
    {
        $values = [];
        /** @var DOMElement $_element */
        foreach ($element->childNodes as $_element) {
            if ($_element->nodeType !== XML_ELEMENT_NODE || $_element->nodeName !== "field") {
                continue;
            }

            $values[$_element->getAttribute("name")] = self::getNodeValue($_element);
        }

        foreach ($element->attributes as $_attribute) {
            $_name = $_attribute->name;
            if (in_array($_name, ["id", "class"])) {
                continue;
            }

            /** @var DOMAttr $_attribute */
            $values[$_name] = $_attribute->value;
        }

        return $values;
    }

    /**
     * Get similar object from a DOM element
     *
     * @param DOMElement $element The DOM element
     * @param array      $fields  The fields to search on
     *
     * @return CStoredObject[]|null
     */
    function getSimilarFromElement(DOMElement $element, $fields = [])
    {
        $class = $element->getAttribute("class");

        $values = self::getValuesFromElement($element);

        /** @var CStoredObject $object */
        $object = new $class;
        if (!empty($fields)) {
            $object->_spec->uniques = [$fields];
        }

        return $object->getSimilar($values);
    }

    /**
     * Get a value from an element
     *
     * @param DOMElement $element DOM element
     * @param string     $name    Field name
     *
     * @return null|string
     */
    function getNamedValueFromElement(DOMElement $element, $name)
    {
        $fields = $this->xpath->query("field[@name='$name']", $element);

        if ($fields->length == 0) {
            return null;
        }

        return self::getNodeValue($fields->item(0));
    }

    /**
     * @param DOMElement $element DOM element to get value of
     *
     * @return string
     */
    static function getNodeValue(DOMElement $element)
    {
        return utf8_decode($element->nodeValue);
    }

    /**
     * Get DOM elements by class name
     *
     * @param string $class Class name
     *
     * @return DOMNodeList|DOMElement[]
     */
    function getElementsByClass($class)
    {
        return $this->xpath->query("//object[@class='$class']");
    }

    /**
     * Get the count of objects of type $class to import in the fine
     *
     * @param string $class Class to search
     *
     * @return int
     */
    function getCount($class)
    {
        return $this->getElementsByClass($class)->length;
    }

    /**
     * Get DOM elements by class name / field name / field value
     *
     * @param string $class       Class name
     * @param string $field_name  Field name
     * @param string $field_value Field value
     *
     * @return DOMElement[]|DOMNodeList
     */
    function getElementsByFwdRef($class, $field_name, $field_value)
    {
        return $this->xpath->query("//object[@class='$class' and @$field_name='$field_value']");
    }

    /**
     * Get an object guid of an object from a fwd ref name
     *
     * @param CMbObject $object Ovject
     * @param string    $fwd    Fwd ref name
     *
     * @return CMbObject|null|string
     */
    function getObjectGuidByFwdRef(CMbObject $object, $fwd)
    {
        // Primary key
        if ($fwd === "id" || $fwd === $object->_spec->key) {
            return $object;
        }

        /** @var CRefSpec $spec */
        $spec = $object->_specs[$fwd]; // We assume it's always a CRefSpec

        $class = $spec->meta ? $object->{$spec->meta} : $spec->class;

        if (!$class) {
            return null;
        }

        $id = $object->$fwd;

        return "$class-$id";
    }

    /**
     * Get objects list
     *
     * @param string $class         Class name
     * @param string $compare_field Search and view field
     * @param bool   $load_all      Load all objects from the current group
     * @param bool   $allow_create  Allow object creation
     *
     * @return array
     */
    function getObjectsList($class, $compare_field, $load_all = true, $allow_create = true, $guid = false)
    {
        $elements = $this->getElementsByClass($class);

        /** @var CMbObject $object */
        $object = new $class();
        $ds     = $object->getDS();

        /** @var CMbObject[] $all_objects */
        $all_objects = [];
        if ($load_all) {
            if ($guid) {
                /** @var CUser $object */
                $all_objects = $object->loadGroupListGuid(null, $compare_field);
            } else {
                $all_objects = $object->loadGroupList(null, $compare_field);
            }
        }

        $objects = [];
        foreach ($elements as $_element) {
            $_id = $_element->getAttribute("id");

            $_values = CMbXMLObjectImport::getValuesFromElement($_element);

            if ($class == 'CUser' && isset($_values['user_first_name']) && isset($_values['user_last_name'])) {
                $where = [
                    'user_first_name' => $ds->prepare('= ?', $_values['user_first_name']),
                    'user_last_name'  => $ds->prepare('= ?', $_values['user_last_name']),
                ];
            } else {
                /** @var CMbObject[] $_similar */
                $where = [
                    $compare_field => $ds->prepare("=?", $_values[$compare_field]),
                ];
            }

            $_similar = $object->loadGroupList($where);

            if ($class == 'CUser') {
                $_similar = reset($_similar);
            }

            $objects[$_id] = [
                "values"  => $_values,
                "similar" => $_similar,
            ];
        }

        $sortfunc = function ($a, $b) use ($compare_field) {
            return strcasecmp($a["values"][$compare_field], $b["values"][$compare_field]);
        };
        uasort($objects, $sortfunc);

        return [
            "all_objects"  => $all_objects,
            "objects"      => $objects,
            "class"        => $class,
            "field"        => $compare_field,
            "allow_create" => $allow_create,
        ];
    }

    /**
     * Lookup an object already imported
     *
     * @param string $guid Guid of the object to lookup
     * @param string $tag  Tag of it's Idex
     *
     * @return CIdSante400
     */
    function lookupObject($guid, $tag = "migration", $class = null)
    {
        if ($class === null) {
            [$class,] = explode("-", $guid);
        }

        $idex = CIdSante400::getMatch($class, $tag, $guid);

        return $idex;
    }

    /**
     * Store a CMbObject
     *
     * @param CStoredObject $object  Object to store
     * @param DOMElement    $element XML element the object is from
     *
     * @return bool
     */
    function storeObject(CStoredObject $object, $element = null, bool $repair = false)
    {
        $is_new   = !$object->_id;
        $modified = false;

        if (!$is_new) {
            $modified = $object->objectModified();
        }

        if ($repair) {
            if ($repaired_fields = $object->repair()) {
                $this->logRepair($repaired_fields, $element);
            }
        }

        if ($msg = $object->store()) {
            $this->writeLog($msg, $element, UI_MSG_WARNING);

            return false;
        }

        if ($is_new) {
            CAppUI::stepAjax("common-import-object-create", UI_MSG_OK, CAppUI::tr($object->_class), $object->_view);
        } elseif ($modified) {
            CAppUI::stepAjax("common-import-object-modify", UI_MSG_OK, CAppUI::tr($object->_class), $object->_view);
        }

        return true;
    }

    /**
     * Write a log
     *
     * @param string     $msg     Message to log
     * @param DOMElement $element DOM element to use
     * @param int        $type    Log type
     *
     * @return void
     */
    protected function writeLog(string $msg, ?DOMElement $element = null, int $type = UI_MSG_OK)
    {
        CAppUI::stepAjax($msg, $type);
    }

    /**
     * Get the import tag
     *
     * @return mixed|string
     */
    function getImportTag()
    {
        $import_tag = "migration";
        if (CModule::getActive("importTools") && $tag = CAppUI::gconf("importTools import import_tag")) {
            $import_tag = $tag;
        } elseif ($tag = CAppUI::conf("dPpatients import_tag")) {
            $import_tag = $tag;
        }

        return $import_tag;
    }

    /**
     * @param bool $value Stop the import by putting false
     *
     * @return void
     */
    protected function setStop($value = false)
    {
        $this->stop_import = $value;
    }

    protected function logRepair($repaired_fields, $element): void
    {
        $id = null;

        if ($element) {
            $id = $element->getAttribute("id");
        }

        foreach ($repaired_fields as $_field => $_msg) {
            CApp::log(
                'Repaired : '  .$id . ' : ' . $_field . ' : ' . $_msg,
                null,
                LoggerLevels::LEVEL_NOTICE
            );
        }
    }
}

