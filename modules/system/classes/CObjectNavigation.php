<?php
/**
 * @package Mediboard\${Module}
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\FieldSpecs\CRefSpec;

/**
 * Description
 */
class CObjectNavigation implements IShortNameAutoloadable
{
    public $class_name;
    public $class_id;
    /** @var  CMbObject */
    public $object_select;


    /**
     * @param string $class_name Nom de la classe de l'objet
     * @param int    $class_id   Identifiant de l'objet
     */
    function __construct($class_name, $class_id)
    {
        if (!$class_name || !$class_id) {
            CAppUI::stepAjax("Il faut un nom de class et un ID.", UI_MSG_ERROR);
        }

        /** @var CMbObject $obj */
        $this->object_select = new $class_name();

        if (!$this->object_select) {
            CAppUI::stepAjax("Impossible de créer l'objet.", UI_MSG_ERROR);
        }
        $this->class_name = $class_name;
        $this->class_id   = $class_id;

        $this->object_select->load($this->class_id);


        if (!$this->object_select || !$this->object_select->_id) {
            CAppUI::stepAjax("Impossible de charger l'objet.", UI_MSG_ERROR);
        }
    }

    /**
     * Trie les propriétés d'un objet et les renvoie sous forme d'un tableau.
     *
     * @return array
     */
    function sortFields()
    {
        if (!$this->object_select) {
            CAppUI::stepAjax("Pas d'objet courant.", UI_MSG_ERROR);
        }

        $fields_map = [
            "plain" => [
                "refs"   => [],
                "fields" => [],
            ],
            "form"  => [
                "refs"   => [],
                "fields" => [],
            ],
        ];

        $fields = [
            'none' => $fields_map
        ];

        $specs = $this->object_select->getSpecs();

        foreach ($specs as $_spec) {
            $fieldset = $_spec->fieldset ?? 'none';
            if(!isset($fields[$fieldset])){
                $fields[$fieldset] = $fields_map;
            }

            if (strpos($_spec->prop, 'password') !== false || preg_match(
                    '/password|passphrase|pass/',
                    $_spec->fieldName
                )) {
                continue;
            }

            if (isset($_spec->class) && $_spec instanceof CRefSpec) {
                // Gestion des meta_objects
                $spec_class = $_spec->class;
                if ($_spec->meta) {
                    $spec_class = $this->object_select->{$_spec->meta};
                }
                if (strpos($_spec->fieldName, '_') === 0) {
                    $fields[$fieldset]['form']['refs'][$_spec->fieldName] = $spec_class;
                } else {
                    $fields[$fieldset]['plain']['refs'][$_spec->fieldName] = $spec_class;
                }
            } else {
                if (strpos($_spec->fieldName, '_') === 0) {
                    $fields[$fieldset]['form']['fields'][$_spec->fieldName] = "";
                } else {
                    $fields[$fieldset]['plain']['fields'][$_spec->fieldName] = "";
                }
            }
        }

        return $fields;
    }
}
