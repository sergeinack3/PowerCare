<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// CLI or die
use Ox\Interop\Hl7\CMLLPServer;

PHP_SAPI === "cli" or die;

$root_dir = __DIR__."/../../..";
require "$root_dir/modules/hl7/classes/CMLLPServer.php";

$tmp_dir = CMLLPServer::getTmpDir();

/**
 * Simple output function
 * 
 * @param string $str The string to output
 * 
 * @return void
 */
function outln($str){
  $stdout = fopen("php://stdout", "w");
  fwrite($stdout, $str.PHP_EOL);
}
