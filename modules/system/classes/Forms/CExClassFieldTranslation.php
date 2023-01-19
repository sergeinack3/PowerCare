<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Field translation
 *
 * Traduction du champ dans la langue de l'utilisateur
 */
class CExClassFieldTranslation extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_translation_id;

    public $ex_class_field_id;
    public $lang;

    public $std;
    public $desc;
    public $court;

    /** @var CExClassField */
    public $_ref_ex_class_field;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_class_field_translation";
        $spec->key             = "ex_class_field_translation_id";
        $spec->uniques["lang"] = ["ex_class_field_id", "lang"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["ex_class_field_id"] = "ref notNull class|CExClassField cascade back|field_translations";
        $props["lang"]              = "enum list|fr|en|de|it|fr-be|nl-be"; // @todo: en fonction des repertoires
        $props["std"]               = "str";
        $props["desc"]              = "str";
        $props["court"]             = "str";

        return $props;
    }

    /**
     * Get string key
     *
     * @return string
     */
    function getKey()
    {
        $field = $this->loadRefExClassField();
        $class = $field->loadRefExClass();

        return "CExObject_{$class->_id}-{$field->name}";
    }

    /**
     * Translate field
     *
     * @param integer $field_id Field identifier
     *
     * @return CExClassFieldTranslation
     */
    static function tr($field_id)
    {
        static $cache = [];

        $lang = CAppUI::pref("LOCALE");

        if (isset($cache[$lang][$field_id])) {
            return $cache[$lang][$field_id];
        }

        $trans                    = new self;
        $trans->lang              = $lang;
        $trans->ex_class_field_id = $field_id;

        if ($trans->loadMatchingObject()) {
            $cache[$lang][$field_id] = $trans;
        }

        return $trans;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->std;
    }

    /**
     * Fill std, desc and court with $str
     *
     * @param string $str String that fills
     *
     * @return void
     */
    function fillIfEmpty($str)
    {
        if (!$this->_id) {
            $this->std = $this->desc = $this->court = $str;
            $this->updateFormFields();
            $this->std = $this->desc = $this->court = "";
        }
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExClassField
     */
    function loadRefExClassField($cache = true)
    {
        return $this->_ref_ex_class_field = $this->loadFwdRef("ex_class_field_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClassField($cache);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        CExObject::clearLocales();

        return null;
    }
}
