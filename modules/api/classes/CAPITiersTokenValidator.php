<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Mediboard\Admin\CTokenValidator;

/**
 * Description
 */
class CAPITiersTokenValidator extends CTokenValidator {

  protected $authorized_methods = array(
    "get",
    "post"
  );

  protected $patterns = array(
    "get"  => array(
      'm'      => array('/appFine/', false),
      "dosql"      => array("(do_treat_notification_withings|do_treat_notification_fitbit)", false),
      'verify' => array("/(\d|\w)*/", false)
    ),
    "post" => array(
      'm'         => array('/appFine/', false),
      "dosql"     => array("(do_treat_notification_withings|do_treat_notification_fitbit)", false),

      // action Withings
      'startdate' => array('/\d*/', false),
      'enddate'   => array('/\d*/', false),
      'date'      => array('/\d{4}\-\d{2}\-\d{2}/', false),
      'action'    => array('/(delete|unlink)/', false),
      'appli'     => array('/\d*/', false),
      'userid'    => array('/\d*/', false),
    )
  );
}
