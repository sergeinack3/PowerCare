<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Mediboard\Admin\CTokenValidator;

/**
 * Valid handle_commit params
 */
class CWSDLTokenValidator extends CTokenValidator {
  protected $patterns = array(
    'get' => array(
      'm'               => array('/(webservices|appFineClient|appFine|pyxvitalManager|tammFactu|ecap|oxFacturation|porteDocuments|tecsante|oxCabinetSIH|context)/', 'webservices'),
      'a'               => array('/soap_server/', 'soap_server'),
      'token'           => array('/.*/',           false),
      'wsdl'            => array('/(\d)*/',        false),
      'class'           => array('/.*/',           'CEAISoapHandler'),
      'wsdl_mode'       => array('/.*/',           'CWSDLRPCEncoded'),
      'suppressHeaders' => array('/(\d)*/',        false),
    ),
    'post' => array(
    )
  );

  protected $authorized_methods = array(
    'get',
    'post',
  );
}
