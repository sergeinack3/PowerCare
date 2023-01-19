<?php
/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Generators;

use Ox\Core\CAppUI;
use Ox\Core\Generators\CObjectGenerator;
use Ox\Mediboard\Ccam\CModelCodage;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;


/**
 * CModelCodageGenerator
 */
class CModelCodageGenerator extends CObjectGenerator {
  static $mb_class = CModelCodage::class;
  static $dependances = array(CMediusers::class);

  /** @var CModelCodage */
  protected $object;

  /**
   * @inheritdoc
   */
  function generate(?string $objects_guid = null, ?string $codes_ccam = null) {
      $praticien = (new CMediusersGenerator())->generate('Chirurgien');

    if ($this->force) {
      $obj = null;
    }
    else {
        $where = [
            "praticien_id" => "= '$praticien->_id'",
        ];

        if ($objects_guid) {
            $where["objects_guid"] = "= '$objects_guid'";
        }

        if ($codes_ccam) {
            $where["codes_ccam"] = "= '$codes_ccam'";
        }

        $obj = $this->getRandomObject($this->getMaxCount(), $where);
    }

    if ($obj && $obj->_id) {
      $this->object = $obj;
      $this->trace(static::TRACE_LOAD);
    }
    else {
      $this->object->praticien_id = $praticien->_id;
      $this->object->objects_guid = $objects_guid;
      $this->object->codes_ccam   = $codes_ccam;
      $this->object->date         = "now";

      if ($msg = $this->object->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("CModelCodage-msg-create", UI_MSG_OK);
      }
    }

    return $this->object;
  }
}
