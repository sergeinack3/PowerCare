<?php
/**
 * @package Mediboard\hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;

/**
 * Description
 */
class CHL7v2FieldOBX5 implements IShortNameAutoloadable
{
    // params
    public $sending_application;
    public $type;
    public $context;
    public $dataType;
    public $data;
    public $object_guid;
    public $order_name;
    public $note_libelle;
    public $note_degre;
    public $order_type;
    public $category_id;
    public $eligible_accuse_reception;
    public $eligible_no_concerned;
    public $eligible_signature_electronique;
    public $eligible_alert_sms;
    public $rgpd_value;
    public $comments;
    public $contains_video_link;
    public $file_name;
    public $nature_file;
    public $description;

    public $_order_props;

    public function __construct()
    {
        $this->_order_props = [
            "sending_application",
            "type",
            "context",
            "dataType",
            "data",
            "object_guid",
            "order_name",
            "note_libelle",
            "note_degre",
            "order_type",
            "category_id",
            "eligible_accuse_reception",
            "eligible_no_concerned",
            "eligible_signature_electronique",
            "eligible_alert_sms",
            "rgpd_value",
            "comments",
            "contains_video_link",
            "file_name",
            "nature_file",
            "description"
        ];
    }

    /**
     *
     *
     * @return string
     */
    public function generateOBX5()
    {
        $data = [];
        foreach ($this->_order_props as $_prop) {
            $data[] = $this->{$_prop};
        }

        return implode("^", $data);
    }

    /**
     * @param String $data
     *
     * @return void
     */
    public function parseOBX5($data)
    {
        $OBX5_exploded = explode("^", $data);
        foreach ($this->_order_props as $_index => $_prop) {
            $this->{$_prop} = CMbArray::get($OBX5_exploded, $_index);
        }
    }
}
