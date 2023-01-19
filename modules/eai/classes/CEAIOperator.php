<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Class CMessageSupported
 * Operator EAI
 */

class CEAIOperator implements IShortNameAutoloadable {
  /**
   * Event method
   * 
   * @param CExchangeDataFormat $data_format Exchange Data Format
   * 
   * @return void
   */ 
  function event(CExchangeDataFormat $data_format) {
  }
}

