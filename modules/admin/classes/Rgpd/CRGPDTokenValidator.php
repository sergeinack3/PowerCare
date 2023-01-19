<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;

use Ox\Mediboard\Admin\CTokenValidator;

/**
 * Description
 */
class CRGPDTokenValidator extends CTokenValidator {
  protected $patterns = array(
    'get' => array(
      'consent' => array('/(0|1)/', null),
    ),
  );

  protected $authorized_methods = array(
    'get',
  );
}