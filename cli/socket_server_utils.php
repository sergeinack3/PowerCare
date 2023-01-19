<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// CLI or die
use Ox\Core\CSocketBasedServer;

PHP_SAPI === "cli" or die;

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../core/classes/CSocketBasedServer.php";
require __DIR__."/style.php";

$server_type = "";
$server_class = "";
$class_path = "";
switch (strtolower($argv[1])) {
  case "dicom" :
    $server_type = "Dicom";
    $server_class = "CDicomServer";
    $class_path = __DIR__ . "/../modules/dicom/classes/$server_class.php";
    break;
  case "mllp" :
    $server_type = "MLLP";
    $server_class = "CMLLPServer";
    $class_path = __DIR__ . "/../modules/hl7/classes/$server_class.php";
    break;
  case "http_proxy" :
    $server_type = "HTTP_proxy";
    $server_class = "CHTTPTunnel";
    $class_path = __DIR__ . "/../modules/system/classes/$server_class.php";
    break;
  default :
    echo "Incorrect server type specified!\n";
    exit(0);
}

require_once $class_path;

$tmp_dir = CSocketBasedServer::getTmpDir();

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
