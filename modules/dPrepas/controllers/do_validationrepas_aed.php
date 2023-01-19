<?php

use Ox\Core\CDoObjectAddEdit;

/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
class CDoValidationRepasAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CValidationRepas", "validationrepas_id");
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    $this->_obj->resetModifications();
    parent::doStore();
  }
}

$do = new CDoValidationRepasAddEdit;
$do->doIt();
