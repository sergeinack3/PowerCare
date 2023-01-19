<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Notification class
 */
class CExClassFieldNotification extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_notification_id;

    public $predicate_id;
    public $target_user_id;
    public $subject;
    public $body;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    /** @var CMediusers */
    public $_ref_target_user;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "ex_class_field_notification";
        $spec->key   = "ex_class_field_notification_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                   = parent::getProps();
        $props["predicate_id"]   = "ref class|CExClassFieldPredicate notNull back|ex_notifications";
        $props["target_user_id"] = "ref class|CMediusers notNull back|ex_notifications";
        $props["subject"]        = "str notNull";
        $props["body"]           = "text notNull markdown";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->subject;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        $target_user = $this->loadRefTargetUser();
        if (!$target_user->_user_email) {
            CAppUI::setMsg("CExClassFieldNotification-msg-User has no email address", UI_MSG_WARNING);
        }

        return null;
    }

    /**
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate()
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id");
    }

    /**
     * @return CMediusers
     */
    function loadRefTargetUser()
    {
        return $this->_ref_target_user = $this->loadFwdRef("target_user_id");
    }

    /**
     * Get fields list, from CModeleEtiquette::$fields
     *
     * @return array
     */
    static function getFields()
    {
        $fields = [
            "CExObject" => [
                "NOM CHAMP",
                "VALEUR CHAMP",
            ],
        ];

        $all_fields = CModeleEtiquette::getFields();
        foreach ($all_fields as $_key => $_fields) {
            $all_fields[$_key] = array_filter(
                $_fields,
                function ($_v) {
                    return strpos($_v, "CODE BARRE") !== 0;
                }
            );
        }

        CMbArray::removeValue([], $all_fields);

        return array_merge($fields, $all_fields);
    }

    /**
     * Sends the email
     *
     * @param CExObject $ex_object The ExObject containing the data
     *
     * @return void
     */
    function sendEmail(CExObject $ex_object)
    {
        $predicate = $this->loadRefPredicate();
        $field     = $predicate->loadRefExClassField();
        $value     = $ex_object->{$field->name};

        if ($predicate->checkValue($value)) {
            // Build fields
            $fields = [];
            $params = [
                "field_name"  => CAppUI::tr("$ex_object->_class-$field->name"),
                "field_value" => $ex_object->getFormattedValue($field->name),
            ];

            foreach (self::getFields() as $_class => $_fields) {
                if ($_class === "General") {
                    (new CModeleEtiquette())->completeLabelFields($fields, $params);
                    continue;
                }

                if ($_class === "CExObject") {
                    $_obj = $ex_object;
                } else {
                    $_obj = $ex_object->getReferenceObject($_class);
                }

                if ($_obj && $_obj->_id) {
                    $_obj->completeLabelFields($fields, $params);
                }
            }

            // Replace fields
            $subject = $this->subject;
            $body    = $this->body;

            foreach ($fields as $_field => $_value) {
                $_search = "[$_field]";
                $body    = str_replace($_search, $_value, $body);
                $subject = str_replace($_search, $_value, $subject);
            }

            $body = CMbString::markdown($body);

            // Send email
            CApp::sendEmail($subject, $body, $this->target_user_id);
        }
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefPredicate();
    }
}
