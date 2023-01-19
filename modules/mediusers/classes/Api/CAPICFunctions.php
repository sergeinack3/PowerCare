<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Api;

/**
 * Class CAPICFunctions
 */
class CAPICFunctions extends CAPIObject {
  public $id;
  public $nom;

  static $fields = array(
    'function_id' => 'id',
    'text'        => 'nom',
  );
}