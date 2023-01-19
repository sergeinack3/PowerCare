<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Numeric reference to a CStoredObject
 */
class CRefSpec extends CMbFieldSpec
{
    public $class;
    public $cascade;
    public $unlink;
    public $nullify;
    public $meta;
    public $purgeable;

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "ref";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "INT(11) UNSIGNED";
    }

    /**
     * @inheritdoc
     */
    public function getPHPSpec(): string
    {
        return parent::PHP_TYPE_INT;
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'class'     => 'class',
                'cascade'   => 'bool',
                'unlink'    => 'bool',
                'nullify'   => 'bool',
                'meta'      => 'field',
                'purgeable' => 'bool',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        /** @var CStoredObject $object */

        $tooltip = CMbArray::extract($params, "tooltip");
        $ref     = $object->loadFwdRef($this->fieldName, true);

        if ($ref && $ref->_id && $this->fieldName != $object->_spec->key) {
            return $tooltip ?
                "<span onmouseover=\"ObjectTooltip.createEx(this, '$ref->_guid')\">$ref->_view</span>" :
                $ref->_view;
        }

        return $object->{$this->fieldName};
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        if ($this->notNull && $this->nullify) {
            return "Spécifications de propriété incohérentes entre 'notNull' et 'nullify'";
        }

        $fieldName = $this->fieldName;
        $propValue = $this->getPropValue($object);

        if ($propValue === null || $object->$fieldName === "") {
            return "N'est pas une référence (format non numérique)";
        }

        if ($propValue == 0 && empty($this->options["allow_zero"])) {
            return "ne peut pas être une référence nulle";
        }

        if ($propValue < 0) {
            return "N'est pas une référence (entier négatif)";
        }

        if (!$this->class and !$this->meta) {
            return "Type d'objet cible on défini";
        }

        $class = $this->getObjectClass($object);

        if ($msg = $this->checkObjectExistence($class, $propValue)) {
            return $msg;
        }

        return null;
    }

    /**
     * @param object $object
     *
     * @return mixed
     */
    private function getPropValue($object)
    {
        $fieldName = $this->fieldName;

        return CMbFieldSpec::checkNumeric($object->$fieldName, true);
    }

    /**
     * @param object $object
     *
     * @return string
     */
    private function getObjectClass($object): string
    {
        $class = $this->class;

        if ($meta = $this->meta) {
            $class = $object->$meta;

            // debug cview moke stdclass
            if (get_class($object) != 'stdClass') {
                $metaSpec = $object->_specs[$meta];

                if ($metaSpec instanceof CRefSpec && $metaSpec->class === 'CObjectClass') {
                    $class = $metaSpec->class;
                }
            }
        }

        return $class;
    }

    /**
     * @param string $class
     * @param mixed  $prop_value
     *
     * @return string|null
     * @throws Exception
     */
    private function checkObjectExistence(string $class, $prop_value): ?string
    {
        $must_load = ($prop_value != 0 || ($prop_value == 0 && empty($this->options["allow_zero"])));

        // Gestion des objets étendus ayant une pseudo-classe
        $ex_object = CExObject::getValidObject($class);

        if ($ex_object) {
            if ($must_load && !$this->unlink && !$ex_object->load($prop_value)) {
                return "Objet référencé de type '$class' introuvable";
            }
        } else {
            if (!is_subclass_of($class, CStoredObject::class)) {
                return "La classe '$class' n'est pas une classe d'objet enregistrée";
            }

            /** @var CStoredObject $ref */
            $ref = new $class();
            if ($must_load && !$this->unlink && !$ref->idExists($prop_value)) {
                return "Objet référencé de type '$class' introuvable";
            }
        }

        return null;
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function checkPermission($object, int $perm): bool
    {
        $ref = $this->getObject($object);

        return $this->checkObjectPermission($ref, $perm);
    }

    /**
     * @param object $object
     *
     * @return CStoredObject|null
     * @throws Exception
     */
    private function getObject($object): ?CStoredObject
    {
        $class = $this->getObjectClass($object);

        $ref = CExObject::getValidObject($class);

        if (!$ref instanceof CExObject) {
            $ref = new $class();
        }

        $ref->load($this->getPropValue($object));

        if ($ref && $ref->_id) {
            return $ref;
        }

        return null;
    }

    private function checkObjectPermission(?CStoredObject $object, int $perm): bool
    {
        if (!$object instanceof CStoredObject) {
            return true;
        }

        return $object->getPerm($perm);
    }

    /**
     * @param array $params Template params:
     *                      - options : array of objects with IDs
     *                      - choose  : string alternative for Choose default option
     *                      - size    : interger for size of text input
     *
     * @return string
     * @see classes/CMbFieldSpec#getFormHtmlElement($object, $params, $value, $className)
     *
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $options = CMbArray::extract($params, "options");

        if (is_countable($options)) {
            $field     = CMbString::htmlSpecialChars($this->fieldName);
            $className = CMbString::htmlSpecialChars(trim("$className $this->prop"));
            $name      = CMbArray::extract($params, 'name');
            $extra     = CMbArray::makeXmlAttributes($params);
            $choose    = CMbArray::extract($params, "choose", "Choose");
            $choose    = CAppUI::tr($choose);

            $name = $name ?: $field;
            $html = "\n<select name=\"$name\" class=\"$className\" $extra>";
            $html .= "\n<option value=\"\">&mdash; $choose</option>";
            foreach ($options as $_option) {
                $selected = $value == $_option->_id ? "selected=\"selected\"" : "";
                $html     .= "\n<option value=\"$_option->_id\" $selected>$_option->_view</option>";
            }
            $html .= "\n</select>";

            return $html;
        }

        CMbArray::defaultValue($params, "size", 25);

        return $this->getFormElementText($object, $params, $value, $className);
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        return "Référence de classe, identifiant. " . parent::getLitteralDescription();
    }
}
