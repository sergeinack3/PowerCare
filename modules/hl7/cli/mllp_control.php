<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Interop\Hl7\CMLLPServer;

require __DIR__."/mllp_utils.php";

// ---- Read arguments
$argv = $_SERVER["argv"];
$argc = $_SERVER["argc"];

if (count($argv) < 2) {
  echo <<<EOT
Usage: {$argv[0]} <command> [<port>] [<host> = localhost]
  <command> The command to issue on the MLLP server
  <port>    The port of the MLLP server to control
  <host>    The host of the MLLP server to control (default localhost)

EOT;
  exit(0);
}

@list($self, $command, $port, $host) = $argv;
if (!$host) {
  $host = "localhost";
}
// ---- End read arguments

$msg_ok    = "OK    ";
$msg_error = "ERROR ";

if (PHP_OS == "Linux") {
  $msg_ok    = "\033[1;32m$msg_ok\033[0m";
  $msg_error = "\033[1;31m$msg_error\033[0m";
}

switch ($command) {
  case "stop":
  case "restart": 
    if (!$port) {
      echo "No port specified\n";
      exit(0);
    }
    echo CMLLPServer::send($host, $port, "__".strtoupper($command)."__\n");
    break;
    
  case "test":
    if (!$port) {
      echo "No port specified\n";
      exit(0);
    }
    $n = 30;
    $secs = 60;
    for ($i = 0; $i < $n; $i++) {
      echo CMLLPServer::send($host, $port, "\x0B".CMLLPServer::sampleMessage()."\x1C\x0D");
      usleep($secs * 1000000);
    }
    break;
    
  case "list":
    if (!in_array($host, array("localhost", "127.0.0.1", "::1"))) {
      outln("Specified host is not local, localhost will be used instead");
    }
    
    $processes = CMLLPServer::getPsStatus();
    
    echo "--------------------------------------\n";
    echo "   PID |  PORT | STATUS | PS NAME     \n";
    echo "--------------------------------------\n";
    
    foreach ($processes as $_pid => $_status) {
      $_ok = isset($_status["ps_name"]) && stripos($_status["ps_name"], "php") !== false;
      $_msg = ($_ok ? $msg_ok : $msg_error);
      
      printf(" %5.d | %5.d | %s | %s \n", $_pid, $_status["port"], $_msg, @$_status["ps_name"]);
    }
    
    break;
    
  default:
    echo "Unknown command '$command'\n";
    exit(1);
}
