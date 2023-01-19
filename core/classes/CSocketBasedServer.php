<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Socket\SocketClient;
use Ox\Core\Socket\SocketServer;

class CSocketBasedServer
{
    /**
     * The SocketClient instance
     *
     * @var SocketClient
     */
    static $client = null;
    /**
     * Root URL called when receiving data on the $port
     *
     * @var string
     */
    protected $call_url = null;
    /**
     * The controller who will receive the messages
     *
     * @var string
     */
    protected $controller = null;
    /**
     * The module
     *
     * @var string
     */
    protected $module = null;
    /**
     * token replace the username and the password for the authentication
     *
     * @var null
     */
    protected $token = null;
    /**
     * Port to listen on
     *
     * @var int
     */
    protected $port = null;
    /**
     * The SSL certificate path (PEM format)
     *
     * @var string
     */
    protected $certificate = null;
    /**
     * The SSL certificate authority file path (PEM format)
     *
     * @var string
     */
    protected $certificate_authority = null;
    /**
     * The SSL passphrase
     *
     * @var string
     */
    protected $passphrase = null;
    /**
     * Set blocking/non-blocking mode on a stream
     *
     * @var int
     */
    protected $blocking_mode = 1;
    /**
     * The SocketServer instance
     *
     * @var SocketServer
     */
    protected $server = null;
    /**
     * Request count
     *
     * @var integer
     */
    protected $request_count = 0;
    /**
     * The start date time
     *
     * @var string
     */
    protected $started_datetime = null;
    /**
     * The clients
     *
     * @var array
     */
    protected $clients = [];

    /**
     * Override the default socket accept timeout. Time should be given in seconds
     *
     * @var int
     */
    protected $timeout = 5;

    /**
     * The socket based server constructor
     *
     * @param string  $call_url              The Mediboard root URL to call
     * @param string  $token                 The Mediboard token use for the authentification
     * @param integer $port                  The port number to listen on
     * @param string  $certificate           The path to the SSL/TLS certificate
     * @param string  $passphrase            The SSL/TLS certificate passphrase
     * @param string  $certificate_authority The SSL/TLS certificate authority file
     * @param int     $blocking_mode         Set blocking/non-blocking mode on a stream
     * @param int     $timeout               Override the default socket accept timeout. Time should be given in seconds
     *
     * @throws Exception
     */
    function __construct(
        string $call_url,
        string $token,
        int $port,
        string $certificate = null,
        string $passphrase = null,
        string $certificate_authority = null,
        int $blocking_mode = 1,
        int $timeout = 5
    ) {
        $this->call_url = rtrim($call_url, " /");
        $this->token                 = $token;
        $this->port                  = $port;
        $this->certificate           = $certificate;
        $this->passphrase            = $passphrase;
        $this->certificate_authority = $certificate_authority;
        $this->blocking_mode         = $blocking_mode;
        $this->timeout               = $timeout;

        $this->server = new SocketServer(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    /**
     * Send a request
     *
     * @param string  $host    The client's IP to send the request to
     * @param integer $port    The client's port number
     * @param string  $message The message to send
     *
     * @throws Exception
     * @return string The client's response
     */
    static function send($host, $port, $message)
    {
        try {
            if (!self::$client) {
                self::$client = new SocketClient();
                self::$client->connect($host, $port);
            }

            return self::$client->sendAndReceive($message);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get a list of the current servers processes
     *
     * @return array A list of structures containing the processes information
     */
    static function getPsStatus()
    {
        $tmp_dir = self::getTmpDir();

        $pid_files = glob("$tmp_dir/pid.*");
        $processes = [];

        foreach ($pid_files as $_file) {
            $_pid             = substr($_file, strrpos($_file, ".") + 1);
            $launched         = CMbDT::strftime("%Y-%m-%d %H:%M:%S", filemtime($_file));
            $content          = file($_file);
            $processes[$_pid] = [
                "port"     => trim($content[0]),
                "launched" => $launched,
                "type"     => trim($content[1]),
            ];
        }

        if (PHP_OS == "WINNT") {
            exec("tasklist", $out);
            $out = array_slice($out, 2);

            foreach ($out as $_line) {
                $_pid = (int)substr($_line, 26, 8);
                if (!isset($processes[$_pid])) {
                    continue;
                }

                $_ps_name                    = trim(substr($_line, 0, 25));
                $processes[$_pid]["ps_name"] = $_ps_name;
            }
        } else {
            exec("ps -e", $out);
            $out = array_slice($out, 1);

            foreach ($out as $_line) {
                $result = preg_match(
                    '# *([0-9]{1,6}) +[?a-z0-9/.-_]+ +[0-9:]{8} ([a-zA-Z0-9-_:/. ]+)#',
                    $_line,
                    $matches
                );

                if ($result && array_key_exists(1, $matches) && array_key_exists(2, $matches) && array_key_exists(
                        $matches[1],
                        $processes
                    )
                ) {
                    $_pid     = $matches[1];
                    $_ps_name = $matches[2];

                    $processes[$_pid]['ps_name'] = $_ps_name;
                }
            }
        }

        return $processes;
    }

    /**
     * Returns the temp directory
     *
     * @return string The temp directory path
     */
    static function getTmpDir()
    {
        $root_dir = __DIR__;

        include_once "$root_dir/CMbPath.php";

        $tmp_dir = "$root_dir/../../tmp/socket_server";
        CMbPath::forceDir($tmp_dir);

        return $tmp_dir;
    }

    /**
     * Return the call url
     *
     * @return string
     */
    function getCallUrl()
    {
        return $this->call_url;
    }

    /**
     * Set the call url
     *
     * @param string $url The url
     *
     * @return void
     */
    function setCallUrl($url)
    {
        $this->call_url = $url;
    }

    /**
     * Return the port
     *
     * @return integer
     */
    function getPort()
    {
        return $this->port;
    }

    /**
     * Set the port
     *
     * @param integer $port The port
     *
     * @return void
     */
    function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Set the certificate
     *
     * @param string $certificate The certificate
     *
     * @return void
     */
    function setCertificate($certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Set the passphrase
     *
     * @param string $passphrase The passphrase
     *
     * @return void
     */
    function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;
    }

    /**
     * Set the certificate authority file
     *
     * @param string $certificate_authority The certificate authority file
     *
     * @return void
     */
    function setCertificateAuthority($certificate_authority)
    {
        $this->certificate_authority = $certificate_authority;
    }

    /**
     * Return the request count
     *
     * @return integer
     */
    function getRequestCount()
    {
        return $this->request_count;
    }

    /**
     * Return the startedDateTime
     *
     * @return integer
     */
    function getStartedDateTime()
    {
        return $this->started_datetime;
    }

    /**
     * Return the client
     *
     * @return SocketClient
     */
    function getClient()
    {
        return self::$client;
    }

    /**
     * Return the server
     *
     * @return SocketServer
     */
    function getServer()
    {
        return $this->server;
    }

    /**
     * Handle request callback
     *
     * @param string  $request The request to handle
     * @param integer $id      The client's ID
     *
     * @return string A hash of the handled request
     */
    function handle($request, $id)
    {
        $client = $this->clients[$id];
        $buffer = &$this->clients[$id]["buffer"];

        // Commands
        switch (trim($request)) {
            case "__STOP__":
                $buffer = "";

                return false;

            case "__RESTART__":
                if (function_exists("quit")) {
                    quit("restart");
                }
                break;

            case "__STATS__":
                return json_encode($this->getStats());

            default:
                // do nothing
        }

        // Verification qu'on ne recoit pas un en-tete de message en ayant deja des données en buffer
        if ($buffer && $this->isHeader($request)) {
            echo sprintf(" !!! Got a header, while having data in the buffer from %d\n", $id);
        }

        echo sprintf(" > Got %d bytes from %d\n", strlen($request), $id);

        $buffer .= $request;
        // Si on recoit le flag de fin de message, on effectue la requete web

        if ($this->isMessageFull($buffer)) {
            echo sprintf(" > Got a full message from %d\n", $id);
            $buffer = $this->encodeClientRequest($buffer);
            $post   = [
                "m"               => $this->module,
                "dosql"           => $this->controller,
                "port"            => $this->port,
                "message"         => $buffer,
                "client_addr"     => $client["addr"],
                "client_port"     => $client["port"],
                "suppressHeaders" => 1,
            ];

            $start = microtime(true);

            $this->displayMessage($buffer);

            // We must keep m=$module in the GET because of user permissions
            $url = "$this->call_url/index.php?token=$this->token&m=$this->module";
            $ack = $this->requestHttpPost($url, $post);
            $this->request_count++;
            $time = microtime(true) - $start;
            echo sprintf(" > Request done in %f s\n", $time);

            $buffer = "";

            return $this->decodeResponse($this->formatAck($ack));
        } else {
            echo "Mise en buffer du message!\n";
            // Mise en buffer du message
            $buffer = $this->appendRequest($buffer);
        }

        return "";
    }

    /**
     * Get the server's stats
     *
     * @return array An array of various stats
     */
    function getStats()
    {
        return [
            "request_count" => $this->request_count,
            "started"       => $this->started_datetime,
            "memory"        => memory_get_usage(true),
            "memory_peak"   => memory_get_peak_usage(true),
        ];
    }

    /**
     * Check if the request is a header message
     *
     * @param string $request The request
     *
     * @return boolean
     */
    function isHeader($request)
    {
        return false;
    }

    /**
     * Check if the message is complete
     *
     * @param string $message The message
     *
     * @return boolean
     */
    function isMessageFull($message)
    {
        return true;
    }

    /**
     * Encode the request and return it
     *
     * @param string $buffer The buffer
     *
     * @return string
     */
    function encodeClientRequest($buffer)
    {
        return $buffer;
    }

    /**
     * Displays the received message in the output
     *
     * @param string $message Message to display
     *
     * @return void
     */
    function displayMessage($message)
    {
    }

    /**
     * Execute an HTTP POST request
     *
     * @param string $url  The URL to call
     * @param array  $data The data to pass to $url via POST
     *
     * @return string HTTP Response
     */
    function requestHttpPost($url, $data)
    {
        $data_url = http_build_query($data, null, "&");
        $data_len = strlen($data_url);

        $scheme  = substr($url, 0, strpos($url, ":"));
        $options = [
            $scheme => [
                "method"  => "POST",
                "header"  => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "Content-Length: $data_len",
                ],
                "content" => $data_url,
            ],
        ];

        $ctx = stream_context_create($options);

        return file_get_contents($url, false, $ctx);
    }

    /**
     * Decode the response and return it
     *
     * @param string $ack The response
     *
     * @return string
     */
    function decodeResponse($ack)
    {
        return $ack;
    }

    /**
     * Format the acknowledgement
     *
     * @param string  $ack     The acknowledgement
     *
     * @param integer $conn_id The connection id
     *
     * @return string
     */
    function formatAck($ack, $conn_id = null)
    {
        return $ack;
    }

    /**
     * Format the buffer
     *
     * @param string $buffer The buffer
     *
     * @return string
     */
    function appendRequest($buffer)
    {
        return $buffer;
    }

    /**
     * The open connection callback
     *
     * @param integer $id   The client's ID
     * @param string  $addr The client's IP address
     * @param integer $port The client's port
     *
     * @return boolean true
     */
    function onOpen($id, $addr, $port = null)
    {
        if (!isset($this->clients[$id])) {
            $this->clients[$id] = [
                "buffer" => "",
                "addr"   => $addr,
                "port"   => $port,
            ];
        }

        echo sprintf(" > %s: New connection [%d] arrived from %s:%d\n", date("H:i:s"), $id, $addr, $port);

        return true;
    }

    /**
     * Connection cleanup callback
     *
     * @param integer $id The client's ID
     *
     * @return void
     */
    function onCleanup($id)
    {
        unset($this->clients[$id]);
        echo sprintf(" > Connection [%d] cleaned-up\n", $id);
    }

    /**
     * Connection close callback
     *
     * @param integer $id The client's ID
     *
     * @return void
     */
    function onClose($id)
    {
        echo sprintf(" > Connection [%d] closed\n", $id);
    }

    /**
     * Write error callback
     *
     * @param integer $id The client's ID
     *
     * @return void
     */
    function writeError($id)
    {
        echo sprintf(" !!! Write error to [%d]\n", $id);
    }

    /**
     * Run the server
     *
     * @return void
     */
    function run()
    {
        $time                   = CMbDT::strftime("%Y-%m-%d %H:%M:%S");
        $v                      = CApp::getVersion();
        $motd                   = <<<EOT
-------------------------------------------------------
|   Welcome to the Mediboard {$this->getServerType()} Server v.$v   |
|   Started at $time                    |
-------------------------------------------------------

EOT;
        $this->started_datetime = $time;

        $server = $this->server->bind(
            "0.0.0.0",
            $this->port,
            $this->certificate,
            $this->passphrase,
            $this->certificate_authority
        );

        $server->setRequestHandler([$this, "handle"]);
        $server->setOnOpenHandler([$this, "onOpen"]);
        $server->setOnCleanupHandler([$this, "onCleanup"]);
        $server->setOnCloseHandler([$this, "onClose"]);
        $server->setOnWriteErrorHandler([$this, "writeError"]);
        $server->run($this->blocking_mode, $this->timeout);
    }

    /**
     * Return the server type
     *
     * @return string
     */
    function getServerType()
    {
        $class_name = get_class($this);
        $length     = strpos($class_name, "Server") - 1;

        return substr($class_name, 1, $length);
    }
}
