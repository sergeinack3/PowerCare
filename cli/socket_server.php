<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// For sig_handler
use Ox\Core\CSocketBasedServer;
use Ox\Interop\Dicom\CDicomServer;
use Ox\Interop\Hl7\CMLLPServer;
use Ox\Mediboard\System\CHTTPTunnel;

declare(ticks=1);

// CLI or die
PHP_SAPI === "cli" or die;

// Ignores user logout
ignore_user_abort(true);
set_time_limit(0);

global $exit_status, $pid_file, $handler, $service;
$service     = false;
$exit_status = "error";

/**
 * Test and returns the most appropriate service command
 *
 * @param string $service_name name of the service to restart
 *
 * @return string
 */
function getServiceRestartCommand($service_name)
{
    //Testing systemctl and service commands to figure out which one will be used
    $ret_code = -1;
    $d        = [];
    exec('which systemctl', $d, $ret_code);

    $service_name = preg_replace('[^\w_]', '', $service_name);

    if ($ret_code === 0) {
        return 'systemctl restart ' . $service_name;
    }

    $ret_code = -1;
    exec('which service', $d, $ret_code);

    if ($ret_code === 0) {
        return 'service ' . $service_name . ' restart';
    }

    return '/etc/init.d/' . $service_name . ' restart';
}

/**
 * Restarts the current server
 * Only works on Linux (not MacOS and Windows)
 *
 * @return void
 */
function restart()
{
    global $service;

    if ($service) {
        $restart_command = getServiceRestartCommand($service);

        exec($restart_command);
    } else {
        if (!function_exists("pcntl_exec")) {
            return;
        }

        global $handler;

        fclose($handler->getServer()->__socket);

        pcntl_exec($_SERVER["_"], $_SERVER["argv"]);
    }
}

/**
 * Script shutdown callback
 *
 * @return void
 */
function on_shutdown()
{
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
function quit($new_exit_status = "ok")
{
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
    function sig_handler($signo)
    {
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
    pcntl_signal(SIGINT, "sig_handler"); // Sent when hitting ctrl+c in the cli
    pcntl_signal(SIGHUP, "sig_handler"); // Restart
}

// ---- Read arguments
$argv = $_SERVER["argv"];
$argc = $_SERVER["argc"];

if (count($argv) < 5) {
    echo <<<EOT
Usage: {$argv[0]} <type> <root_url> <token> [--port port]
  <type>              The type of the server, (dicom or mllp)
  <root_url>          The root url for mediboard, ie https://localhost/mediboard
  <token> token use for the authentification
  [--port <port>]     The port to listen on (default: 7001)
  [--cert <cert>]     The SSL certificate file if the connection is secured, with both public and pricate key (PEM) (default: none)
  [--passphrase <passphrase>] The SSL passphrase (default: none)
  [--cafile <cafile>]  The SSL certificate authority file (PEM) (default: none)
  [--service]          Name of the Linux service (if false, the server won't use the services) (default: false)
  [--debug]            Debug mode (default: false)
  [--non_blocking_mode] Set non-blocking mode on a stream (default: true)
  [--timeout]          Override the default socket accept timeout. Time should be given in seconds (default: 5)

EOT;
    exit(0);
}

require __DIR__ . "/socket_server_utils.php";

$options = [
    "url"           => $argv[2],
    "token"         => $argv[3],
    "debug"         => false,
    "port"          => 7001,
    "cert"          => null,
    "passphrase"    => null,
    "cafile"        => null,
    "blocking_mode" => 1,
    "timeout"       => 5,
];

for ($i = 3; $i < $argc; $i++) {
    switch ($argv[$i]) {
        case "--debug":
            $options["debug"] = true;
            break;

        case "--non_blocking_mode":
            $options["blocking_mode"] = 0;
            break;

        case "--service":
            global $service;
            $service = $argv[++$i];
            break;

        case "--port":
        case "--cert":
        case "--passphrase":
        case "--cafile":
        case "--timeout":
            $options[substr($argv[$i], 2)] = $argv[++$i];
            break;
    }
}

// ---- End read arguments
if ($options["cert"] && !is_readable($options["cert"])) {
    outln("SSL certificate not readable: '{$options['cert']}', exiting.");
    die;
}

if ($options["cafile"] && !is_readable($options["cafile"])) {
    outln("SSL CAfile not readable: '{$options['cafile']}', exiting.");
    die;
}

// Do not use CApp::registerShutdown (CLI)
register_shutdown_function('on_shutdown');

// Write a flag file with the PID and the port
$pid_file = "$tmp_dir/pid." . getmypid();
file_put_contents($pid_file, $options["port"] . "\n" . $server_type);

try {
    outln("Starting $server_type Server on port " . $options["port"] . " with token '" . $options["token"] . "'");

    if ($options["cert"]) {
        outln("SSL certificate: '{$options['cert']}'");
    }

    if ($options["cafile"]) {
        outln("SSL certificate authority: '{$options['cafile']}'");
    }

    switch ($server_class) {
        case "CDicomServer":
            $classname = CDicomServer::class;
            break;
        case "CMLLPServer":
            $classname = CMLLPServer::class;
            break;
        case "CHTTPTunnel":
            $classname = CHTTPTunnel::class;
            break;
        default:
    }

    /** @var CSocketBasedServer $handler */
    $handler = new $classname(
        $options["url"],
        $options["token"],
        $options["port"],
        $options["cert"],
        $options["passphrase"],
        $options["cafile"],
        $options["blocking_mode"],
        $options["timeout"],
    );

    $handler->run();

    quit();
} catch (Exception $e) {
    $message = $e->getMessage();

    if ($message == "Address already in use") {
        outln($message);
        quit();
    }

    $stderr = fopen("php://stderr", "w");
    fwrite($stderr, $message . PHP_EOL);
}

quit();
