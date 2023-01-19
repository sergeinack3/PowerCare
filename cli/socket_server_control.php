<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// CLI or die
PHP_SAPI === "cli" or die;

// ---- Read arguments
$argv = $_SERVER["argv"];
$argc = $_SERVER["argc"];

if (count($argv) < 3) {
  echo <<<EOT
Usage: {$argv[0]} <type> <command> [<port>] [<host> = localhost]
  <type>    The type of the server, (dicom or mllp)
  <command> The command to issue on the server
  <port>    The port of the server to control
  <host>    The host of the server to control (default localhost)

EOT;
  exit(0);
}

require __DIR__."/socket_server_utils.php";

@list($self, $type, $command, $port, $host) = $argv;
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
    echo $server_class::send($host, $port, "__".strtoupper($command)."__\n");
    break;
    
  case "test":
    if (!$port) {
      echo "No port specified\n";
      exit(0);
    }
    $n = 30;
    $secs = 5;
    for ($i = 0; $i < $n; $i++) {
      echo $server_class::send($host, $port, $server_class::sampleMessage())."\n";
      usleep($secs * 1000000);
    }
    break;
    
  case "list":
    if (!in_array($host, array("localhost", "127.0.0.1", "::1"))) {
      outln("Specified host is not local, localhost will be used instead");
    }
    
    $processes = $server_class::getPsStatus();
    
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
