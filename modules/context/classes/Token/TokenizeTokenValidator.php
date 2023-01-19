<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context\Token;

use Ox\Mediboard\Admin\CTokenValidator;

/**
 * Valid handle_commit params
 */
class TokenizeTokenValidator extends CTokenValidator {
  protected $patterns = array(
    'post' => array(),
    'get'  => array(
      'm'          => array('/context/', false),
      'raw'        => array('/tokenize/', false),
      'name'       => array('/.*/', false),
      'firstname'  => array('/.*/', false),
      'birthdate'  => array('/^([0-9]{4})-((?!00)[0-9]{1,2})-((?!00)[0-9]{1,2})$/', false),
      'rpps'       => array('/[a-zA-Z0-9]+/', false),
      'view'       => array('/.*/', false),
      'g'          => array('/[0-9]+/', false),
      'cabinet_id' => array('/[0-9]+/', false),
      'ext_patient_id' => array('/[0-9]+/', false),
      'context_guid' => array('/.*/', false),
    ),
  );
  protected $authorized_methods = array(
    'get',
  );
}
