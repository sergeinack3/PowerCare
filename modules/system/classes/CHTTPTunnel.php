<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

// Don't use requireLib because used in CLI
require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use phpseclib3\File\X509;

/**
 * An HTTP tunnel
 */
class CHTTPTunnel implements IShortNameAutoloadable
{
    /** @var Resource */
    public $listen_socket;
    /** @var Resource */
    public $target_socket;
    /** @var Resource */
    public $context;
    public $target_host;
    public $timer;
    public $log             = [
        "start_date"    => "",
        "timer"         => 0,
        "memory"        => 0,
        "memory_peak"   => 0,
        "hits"          => 0,
        "data_sent"     => 0,
        "data_received" => 0,
        "clients"       => [],
    ];
    public $running         = true;
    public $restart         = false;
    public $header_continue = false;
    public $revocation;

    const DATA_LENGTH = 1500;

    /**
     * Construct
     *
     * @param String $target     Target address
     * @param string $listen     Listen address
     * @param String $path_cert  Local certificate path
     * @param String $passphrase Local certificate passphrase
     * @param String $ca         Authority certificate
     */
    function __construct($target, $listen = 'tcp://0.0.0.0:8080', $path_cert = null, $passphrase = null, $ca = null)
    {
        $this->printLn("Construct HTTP Tunnel");

        //Initialize a socket server for listen the client request
        $context = $this->createContext($path_cert, $passphrase, $ca, true);
        $socket  = stream_socket_server($listen, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
        if (!$socket) {
            $this->printLn("Error : $errstr ($errno)");
            $this->restartScript();
        }

        stream_set_blocking($socket, false);

        $this->listen_socket     = $socket;
        $this->target_host       = $target;
        $this->log["start_date"] = date("d-m-Y H:i:s");
    }

    /**
     * Open a connection to the target address
     *
     * @return Bool
     */
    function openTarget()
    {
        $old_timer = $this->timer;
        if ($old_timer && ((time() - $old_timer) < 3600)) {
            return true;
        }
        $this->printLn("Create the Target");

        //Add the context of the connection
        $context = null;
        if ($this->context) {
            $context = $this->context;
        }

        if ($this->target_socket) {
            stream_socket_shutdown($this->target_socket, STREAM_SHUT_RDWR);
        }

        //Create the client for request the target address
        $target = stream_socket_client($this->target_host, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
        if (!$target) {
            $this->printLn("Error : $errstr ($errno)");
            $this->restartScript();
        }

        //Active the blocking
        stream_set_blocking($target, true);
        //Active the crypto SSL/TLS for the connection
        $crypto = stream_socket_enable_crypto($target, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
        if (!$crypto) {
            return false;
        }

        $this->target_socket = $target;
        if ($this->revocation) {
            if (!$this->checkCertificate()) {
                stream_socket_shutdown($this->target_socket, STREAM_SHUT_RDWR);

                return false;
            }
        }
        $this->timer = time();

        return true;
    }

    /**
     * Run the Tunnel
     *
     * @return void
     */
    function run()
    {
        $this->printLn("Running the HTTP Tunnel\n");
        $client_socks = [];
        $master_sock  = $this->listen_socket;

        while ($this->running) {
            //we place the client for read the socket
            $read_socks = $client_socks;
            //we place our server for read the socket
            $read_socks[] = $master_sock;
            //We check the evolution of master socket
            if (!@stream_select($read_socks, $write, $except, null) && $this->running) {
                $this->printLn("Error : $except");
                $this->restartScript();
            }

            if (in_array($master_sock, $read_socks)) {
                //We continue the program when a client to connect
                $new_client = stream_socket_accept($master_sock, 15, $peer_name);
                if ($new_client) {
                    $this->printLn("Connection accepted from $peer_name");
                    //We place the new client to the list
                    $client_socks[] = $new_client;
                }
                //We delete the master sock for the suite
                unset($read_socks[array_search($master_sock, $read_socks)]);
            }

            foreach ($read_socks as $sock) {
                $this->printLn("Receive request client");
                //Active the crypto SSL/TLS for the connection for a new connection
                if (!$this->header_continue) {
                    stream_set_blocking($sock, true);
                    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_SSLv23_SERVER)) {
                        $this->restartScript();
                    }
                }

                $request   = $this->read($sock);
                $peer_name = $this->getAddresse(stream_socket_get_name($sock, true));

                $t = microtime(true);
                if (strpos($request, "CMD") !== false) {
                    $this->printLn("Command system");
                    $result   = $this->executeCommand($request);
                    $response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n$result";
                } else {
                    if ($this->openTarget($sock)) {
                        $response = $this->serve($request);
                    } else {
                        $response = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>
  <soap:Fault>
    <faultcode>soap:Server</faultcode>
    <faultstring>Impossible d\'initialiser la connexion</faultstring>
    <faultactor>openTarget</faultactor>
    <detail>
    </detail>
  </soap:Fault>
</soap:Body></soap:Envelope>';
                        $header   = "HTTP/1.1 200 OK\nContent-Type: text/xml;charset=UTF-8\nContent-Length: " . strlen(
                                $response
                            );
                        $response = $header . "\n\n" . $response;
                    }
                    $this->setLogClient($peer_name, "hits", 1);
                    $this->setLogClient($peer_name, "data_received", strlen($response));
                    $this->setLogClient($peer_name, "data_sent", strlen($request));
                }

                $this->printLn("Request done in " . ((microtime(true) - $t) * 1000) . " ms !");

                //return the response to the client
                $this->printLn("Send response for client");
                fwrite($sock, $response);
                $this->header_continue = true;
                if (strpos($response, "100 Continue") === false) {
                    $this->header_continue = false;
                    //We delete the client and close her connection
                    unset($client_socks[array_search($sock, $client_socks)]);
                    stream_socket_shutdown($sock, STREAM_SHUT_RDWR);
                    $this->printLn("Connection closed from $peer_name\n");
                }
            }
        }
        if ($this->restart) {
            $this->restartScript();
        }
        $this->quit();
    }

    /**
     * Send the request and return the response of the target
     *
     * @param String $data Request to send
     *
     * @return string
     */
    function serve($data)
    {
        $this->timer = time();
        $this->printLn("Send request client");
        fwrite($this->target_socket, $data);
        $this->printLn("Receive response for client");
        $response = $this->read($this->target_socket);

        return $response;
    }

    /**
     * Read the data on a connection
     *
     * @param Resource $target Connection to read
     *
     * @return string
     */
    function read($target)
    {
        $content = "";
        $length  = self::DATA_LENGTH;
        while ($data = fread($target, $length)) {
            $content .= $data;
            $meta    = stream_get_meta_data($target);
            $length  = min(self::DATA_LENGTH, $meta["unread_bytes"]);

            if ($meta["unread_bytes"] === 0) {
                stream_set_blocking($target, false);
                if ($test = fread($target, 1)) {
                    $content .= $test;
                    $meta    = stream_get_meta_data($target);
                    $length  = min(self::DATA_LENGTH, $meta["unread_bytes"] - 1);
                } else {
                    stream_set_blocking($target, true);
                    break;
                }
                stream_set_blocking($target, true);
            }
        }

        return $content;
    }

    /**
     * Set the authentification by certificate
     *
     * @param String $path_cert  Path certificate
     * @param String $passphrase Passphrase of the certificate
     * @param String $path_ca    Path ca certificate
     * @param String $revocation Path revocation certificat
     *
     * @return void
     */
    function setAuthentificationCertificate($path_cert, $passphrase, $path_ca, $revocation = null)
    {
        $this->printLn("Configurate the HTTP Tunnel");
        $this->revocation = $revocation;
        $this->context    = $this->createContext($path_cert, $passphrase, $path_ca);
    }

    /**
     * Create a SSL context
     *
     * @param String $path_cert   Local certificate path
     * @param String $passphrase  Local certificate passphrase
     * @param String $path_ca     Authority certificate path
     * @param Bool   $self_signed Allow the local certificate self signed
     *
     * @return resource
     */
    private function createContext($path_cert = null, $passphrase = null, $path_ca = null, $self_signed = false)
    {
        $context = stream_context_create();

        if ($path_ca) {
            stream_context_set_option($context, "ssl", "cafile", $path_ca);
            stream_context_set_option($context, "ssl", "verify_peer", true);
        }

        if ($self_signed) {
            stream_context_set_option($context, 'ssl', 'allow_self_signed', $self_signed);
            stream_context_set_option($context, "ssl", "verify_peer", false);
        }

        if ($path_cert) {
            stream_context_set_option($context, "ssl", "local_cert", $path_cert);
            stream_context_set_option($context, "ssl", "passphrase", $passphrase);
        }

        stream_context_set_option($context, "ssl", "capture_peer_cert", true);

        return $context;
    }

    /**
     * Add the value into the general information
     *
     * @param String $type  Type log
     * @param String $value value log
     *
     * @return void
     */
    function setLog($type, $value)
    {
        $this->log[$type] += $value;
    }

    /**
     * Add the value into the client information
     *
     * @param String $entity Client
     * @param String $type   Type log
     * @param String $value  Value log
     *
     * @return void
     */
    function setLogClient($entity, $type, $value)
    {
        $this->setLog($type, $value);
        if (!array_key_exists($entity, $this->log["clients"])) {
            $this->log["clients"][$entity] = [];
        }
        if (array_key_exists($type, $this->log["clients"][$entity])) {
            $this->log["clients"][$entity][$type] += $value;
        } else {
            $this->log["clients"][$entity][$type] = $value;
        }
    }

    /**
     * Return the address without the port
     *
     * @param String $addresse_port address
     *
     * @return String
     */
    function getAddresse($addresse_port)
    {
        $addresse = explode(":", $addresse_port);

        return $addresse[0];
    }

    /**
     * Execute the command system for the tunnel
     *
     * @param String $request command system to execute
     *
     * @return string
     */
    function executeCommand($request)
    {
        $command = explode(" ", $request);
        switch ($command[1]) {
            case "RESTART":
                $this->running = false;
                $this->restart = true;
                break;
            case "STOP":
                $this->running = false;
                break;
            case "STAT":
                $this->log["timer"]       = $this->timer ? time() - $this->timer : "NI";
                $this->log["memory"]      = memory_get_usage(true);
                $this->log["memory_peak"] = memory_get_peak_usage(true);

                return json_encode($this->log);
                break;
        }

        return "";
    }

    /**
     * Function to execute on shutdown
     *
     * @return void
     */
    function onShutdown()
    {
        global $pid_file;
        unlink($pid_file);
    }

    /**
     * SIG number manager
     *
     * @param integer $signo The signal number to handle
     *
     * @return void
     */
    function sigHandler($signo)
    {
        switch ($signo) {
            case SIGTERM:
            case SIGINT:
                $this->quit();
                break;
            case SIGHUP:
                $this->quit("restart");
                break;
        }
    }

    /**
     * Restarts the current server
     * Only works on Linux (not MacOS and Windows)
     *
     * @return void
     */
    function restartScript()
    {
        if (!function_exists("pcntl_exec")) {
            return;
        }
        $this->quit("restart");
        $this->printLn("Restart the HTTP Tunnel");
        pcntl_exec($_SERVER["_"], $_SERVER["argv"]);
    }

    /**
     * Exit the script, with a status
     *
     * @param string $status Exit status : "ok" or "restart"
     *
     * @return void
     */
    function quit($status = "ok")
    {
        $this->printLn("Stop the HTTP Tunnel");

        $this->running = false;
        if ($this->target_socket) {
            stream_socket_shutdown($this->target_socket, STREAM_SHUT_RDWR);
        }
        stream_socket_shutdown($this->listen_socket, STREAM_SHUT_RDWR);

        if ($status !== "restart") {
            exit(0);
        }
    }

    /**
     * Verify the revocation of the certificate and the name
     *
     * @return bool
     */
    function checkCertificate()
    {
        $this->printLn("Verify the certificate");
        $path_revocation = $this->revocation;
        $certificate     = "";
        $option          = stream_context_get_options($this->target_socket);

        if ($option["ssl"]["peer_certificate"]) {
            $peer_certificate = $option["ssl"]["peer_certificate"];
            openssl_x509_export($peer_certificate, $certificate);
            $x509 = new X509();
            $cert = $x509->loadX509($certificate);
            $dn   = $x509->getSubjectDN();
            $dn   = array_pop($dn["rdnSequence"]);
            $host = explode(":", $this->target_host);
            if ($dn[0]["value"]["printableString"] !== $host[0]) {
                $this->printLn("Error : the server name does not match that of the certificate");

                return false;
            }
            $serial     = strtoupper($cert['tbsCertificate']['serialNumber']->toHex());
            $revocation = file($path_revocation);
            if (in_array("$serial\n", $revocation, true)) {
                $this->printLn("Error : revoked certificate");

                return false;
            }

            return true;
        }

        $this->printLn("Error : untransmitted certificate");

        return false;
    }

    /**
     * Print the text
     *
     * @param String $text Text
     *
     * @return void;
     */
    function printLn($text)
    {
        echo CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " $text\n";
    }
}
