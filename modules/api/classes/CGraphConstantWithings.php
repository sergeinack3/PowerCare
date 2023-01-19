<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CAppUI;

/**
 * Description
 */
class CGraphConstantWithings extends CGraphConstant {

  /**
   * CGraphConstantWithings constructor.
   */
  public function __construct() {
    $this->_value_state_sleep = array(
      array("5" => CAppUI::tr("CStateInterval.state.hourlysleep.0")),
      array("4" => CAppUI::tr("CStateInterval.state.hourlysleep.1")),
      array("2" => CAppUI::tr("CStateInterval.state.hourlysleep.2")),
    );
  }
}
