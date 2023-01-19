<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// For sig_handler
use Ox\Core\CApp;
use Ox\Interop\Hl7\CMLLPServer;

declare(ticks = 1);

require __DIR__."/mllp_utils.php";

// Ignores user logout
ignore_user_abort(true);
CApp::setTimeLimit(0);

global $exit_status, $pid_file, $handler;
$exit_status = "error";

/**
 * Restarts the current server
 * Only works on Linux (not MacOS and Windows)
 * 
 * @return void
 */
function restart(){
  if (!function_exists("pcntl_exec")) {
    return;
  }
  
  global $handler;

  socket_close($handler->getServer()->__socket);
  
  pcntl_exec($_SERVER["_"], $_SERVER["argv"]);
}

/**
 * Script shutdown callback
 * 
 * @return void
 */
function on_shutdown() {
  global $exit_status, $pid_file;
  
  switch ($exit_status) {
    case "error":
      outln("Server stopped unexpectedly, trying to restart.");
      restart();
      break;
    
    case "restart":
      outln("Restarting ...");
      @unlink($pid_file); 
      outln("Server stopped.");
      restart();
      break;
    
    default:
      outln("Server stopped.");
      @unlink($pid_file);
      break;
  }
}

/**
 * Exit the script, with a status
 * 
 * @param string $new_exit_status Exit status : "ok" or "error"
 * 
 * @return void
 */
function quit($new_exit_status = "ok"){
  global $exit_status;
  $exit_status = $new_exit_status;
  exit ($exit_status == "error" ? 1 : 0);
}

if (function_exists("pcntl_signal")) {
  /**
   * SIG number manager
   * 
   * @param integer $signo The signal number to handle
   * 
   * @return void
   */
  function sig_handler($signo) {
    switch ($signo) {
      case SIGTERM:
      case SIGINT:
        quit();
        break;
        
      case SIGHUP:
        quit("restart");
        break;
    }
  }
  
  pcntl_signal(SIGTERM, "sig_handler");
  pcntl_signal(SIGINT , "sig_handler"); // Sent when hitting ctrl+c in the cli
  pcntl_signal(SIGHUP , "sig_handler"); // Restart
}

// ---- Read arguments
$argv = $_SERVER["argv"];
$argc = $_SERVER["argc"];

if (count($argv) < 4) {
  echo <<<EOT
Usage: {$argv[0]} <root_url> <username> <password> [--port port]
  <root_url>      The root url for mediboard, ie https://localhost/mediboard
  <username>      The name of the user requesting, ie cron
  <password>      The password of the user requesting, ie ****
  [--port <port>] The port to listen on (default: 7001)
  [--cert <cert>] The SSL certificate if the connection is secured (default: none)
  [--passphrase <passphrase>] The SSL passphrase (default: none)

EOT;
  exit(0);
}

$options = array(
  "url"        => $argv[1],
  "username"   => $argv[2],
  "password"   => $argv[3],
  "debug"      => false,
  "port"       => 7001,
  "cert"       => null,
  "passphrase" => null,
);

for ($i = 3; $i < $argc; $i++) {
  switch ($argv[$i]) {
    case "--debug":
      $options["debug"] = true;
      break;
    
    case "--port":
    case "--cert":
    case "--passphrase":
      $options[substr($argv[$i], 2)] = $argv[++$i];
      break;
  }
}
// ---- End read arguments

if ($options["cert"] && !is_readable($options["cert"])) {
  outln("SSL certificate not readable: '{$options['cert']}', exiting.");
  die;
}

// Do not use CApp::registerShutdown (CLI)
register_shutdown_function('on_shutdown');

// Write a flag file with the PID and the port
$pid_file = "$tmp_dir/pid.".getmypid();
file_put_contents($pid_file, $options["port"]);

try {
  outln("Starting MLLP Server on port ".$options["port"]." with user '".$options["username"]."'");
  
  if ($options["cert"]) {
    outln("SSL certificate: '{$options['cert']}'");
  }
  
  $handler = new CMLLPServer(
    $options["url"], 
    $options["username"], 
    $options["password"], 
    $options["port"], 
    $options["cert"], 
    $options["passphrase"]
  );
  
  $handler->run();
  
  quit();
}
catch(Exception $e) {
  $message = $e->getMessage();
  
  if ($message == "Address already in use") {
    outln($message);
    quit();
  }
  
  $stderr = fopen("php://stderr", "w");
  fwrite($stderr, $message.PHP_EOL);
}

quit();
