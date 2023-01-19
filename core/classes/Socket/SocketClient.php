<?php

/**
 * @package Classes
 * @author  Juan M. Hidalgo <juan@sentidocomunsite.com.ar>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU GPLv3
 * @link    http://php-socket.googlecode.com/
 */

namespace Ox\Core\Socket;

use Exception;

/**
 * Socket client wrapper class
 */
class SocketClient
{
    /** @var resource Socket Handle */
    private $hnd;

    /** @var string[] List of Hosts */
    private $host;

    /** @var string[] List of ip to connect, if one fails try with others */
    private $ip;

    /** @var int Port */
    private $port;

    /** @var int Socket type.  (SOCK_DGRAM | SOCK_RAW | SOCK_RDM | SOCK_SEQPACKET | SOCK_STREAM) */
    private $type;

    /** @var int Socket Family (AF_INET|AF_INET6|AF_UNIX) */
    private $family;

    /** @var int Socket protocol (SOL_SOCKET | SOL_TCP | SOL_UDP) */
    private $protocol;

    /** @var bool Socket connection state */
    private $bConnected;

    /** @var string Socket buffer */
    private $sBuffer;

    /** @var int Read TimeOut (seconds) */
    private $iReadTimeOut = 2;

    /** @var int Write TimeOut (seconds) */
    private $iWriteTimeOut = 2;

    /** @var bool Determines if error must be shown */
    public $bShowErros = false;

    /** @var bool Determines if Exception must be thrown */
    public $bExceptions = true;

    /**
     * Show errors if $bShowErrors is enabled
     * If $bExceptions, throws an exception, otherwise prints a message.
     * If $msg is not empty, show $msg;
     *
     * @param string $msg Error message
     *
     * @throws Exception
     *
     * @return void
     */
    public function error($msg = null)
    {
        if (!$this->bShowErros && !$this->bExceptions) {
            return;
        }

        $errCode = socket_last_error($this->hnd);
        if ($errCode != 0) {
            //Connection reset by peer
            if ($errCode == 104) {
                $this->bConnected = false;
            }

            $errMsg = socket_strerror($errCode);
            if ($this->bExceptions) {
                throw new Exception("Socket error. Code: $errCode - Message: $errMsg\n");
            } else {
                trigger_error("Socket Error. Code: $errCode - Message: $errMsg");
            }
            socket_clear_error($this->hnd);
        } elseif (strlen($msg)) {
            if ($this->bExceptions) {
                throw new Exception("Socket error." . $msg);
            } else {
                trigger_error("$msg\n", E_USER_ERROR);
            }
        }
    }

    /**
     * Constructor
     *
     * @param int $family   (AF_INET|AF_INET6|AF_UNIX)
     * @param int $type     (SOCK_DGRAM | SOCK_RAW |    SOCK_RDM | SOCK_SEQPACKET | SOCK_STREAM)
     * @param int $protocol (SOL_SOCKET | SOL_TCP | SOL_UDP)
     */
    public function __construct($family = AF_INET, $type = SOCK_STREAM, $protocol = SOL_TCP)
    {
        $this->hnd = @socket_create($family, $type, $protocol);
        $this->error();

        $this->family   = $family;
        $this->type     = $type;
        $this->protocol = $protocol;

        $this->sBuffer = false;
        $this->port    = null;
        $this->ip      = null;
        $this->host    = null;
    }

    /**
     * Sets a host and tries to resolve IP address. If Ip is valid adds it to List of ip
     *
     * @param string $sHost Host to connect to
     *
     * @return void
     */
    public function setHost($sHost)
    {
        if (!strlen($sHost)) {
            return;
        }

        $this->host[] = $sHost;
        $ip           = @gethostbyname($sHost);
        if ($ip) {
            $this->ip[] = $ip;
        } else {
            $this->error("Hostname $sHost could not be resolved");
        }
    }

    /**
     * Sets Ip addres
     *
     * @param string $sIp (xxx.xxx.xxx.xxx)
     *
     * @return void
     */
    public function setIp($sIp)
    {
        if (!strlen($sIp)) {
            return;
        }

        if (!ip2long($sIp)) {
            $this->error("Invalid IP ADDRESS. IP $sIp");
        }

        $this->ip[]   = $sIp;
        $this->host[] = @gethostbyaddr($sIp);
    }

    /**
     * Set Host port
     *
     * @param int $iPort Port to connect to
     *
     * @return void
     */
    public function setPort($iPort)
    {
        $this->port = $iPort;
    }

    /**
     * Open socket connection
     *
     * @param string $sHost Host
     * @param int    $iPort Port
     *
     * @return void
     */
    public function open($sHost = null, $iPort = null)
    {
        if (strlen($sHost)) {
            $this->setHost($sHost);
        }
        if (strlen($iPort)) {
            $this->setPort($iPort);
        }
        $i = 0;
        do {
            if (@socket_connect($this->hnd, $this->ip[$i], $this->port)) {
                $this->bConnected = true;
            }
        } while (!$this->bConnected && $i++ < count($this->ip));

        if (!$this->bConnected) {
            $this->error();
        }
    }

    /**
     * Connect with  host (open alias)
     *
     * @param string $sHost Host
     * @param int    $iPort Port
     *
     * @return void
     */
    public function connect($sHost = null, $iPort = null)
    {
        $this->open($sHost, $iPort);
    }

    /**
     * Close socket connection
     *
     * @return void
     */
    public function close()
    {
        if (!$this->bConnected) {
            return;
        }
        @socket_shutdown($this->hnd, 2);
        @socket_close($this->hnd);
    }

    /**
     * Close socket connection (close alias)
     *
     * @return void
     */
    public function disconnect()
    {
        $this->close();
    }

    /**
     * Send data
     * If $sBuf is not empty try to send $sBuf, else try with $this->sBuffer
     *
     * @param string $sBuf     Buffer to send
     * @param int    $iTimeOut Timeout
     *
     * @return void
     */
    public function send($sBuf, $iTimeOut = null)
    {
        if (!strlen($this->sBuffer) && !strlen($sBuf)) {
            return;
        }
        if (!$this->bConnected) {
            $this->error("Socket error. Cannot send data on a closed socket.");

            return;
        }

        $vWrite = [$this->hnd];

        $WriteTimeOut = strlen($iTimeOut) ? $iTimeOut : $this->iWriteTimeOut;
        $vRead        = null;
        $vExcept      = null;
        while (($rr = @socket_select($vRead, $vWrite, $vExcept, $WriteTimeOut)) === false) {
            ;
        }

        if ($rr == 0) {
            return;
        }

        $tmpBuf  = strlen($sBuf) ? $sBuf : $this->sBuffer;
        $iBufLen = strlen($tmpBuf);
        $res     = @socket_send($this->hnd, $tmpBuf, $iBufLen, 0);

        if ($res === false) {
            $this->error();
        } elseif ($res < $iBufLen) {
            $tmpBuf = substr($tmpBuf, $res);
            $this->send($tmpBuf);
        }
    }

    /**
     * Send alias
     *
     * @param string $sBuf     Buffer to send
     * @param int    $iTimeOut Timeout
     *
     * @return void
     */
    public function write($sBuf, $iTimeOut = null)
    {
        $this->send($sBuf, $iTimeOut);
    }

    /**
     * Read data from socket
     *
     * @param int    $iTimeOut    The timeout value
     * @param string $end_pattern The end pattern
     *
     * @return string|null
     */
    public function recv($iTimeOut = null, $end_pattern = null)
    {
        if (!$this->bConnected) {
            $this->error("Socket error. Cannot read any data on a closed socket.");

            return null;
        }

        $vSocket       = [$this->hnd];
        $this->sBuffer = null;
        $buf           = null;
        $iBufLen       = 4096;
        $ReadTimeOut   = strlen($iTimeOut) ? $iTimeOut : $this->iReadTimeOut;
        $vWrite = null;
        $vExcept = null;
        try {
            while (($rr = @socket_select($vSocket, $vWrite, $vExcept, $ReadTimeOut)) === false) {
                ;
            }

            if ($rr == 0) {
                return null;
            }

            $res = @socket_recv($this->hnd, $buf, $iBufLen, 0);
            while ($res) {
                $this->sBuffer .= $buf;
                if ($end_pattern && strpos($this->sBuffer, $end_pattern)) {
                    break;
                }
                $buf = null;

                $vWrite = null;
                $vExcept = null;
                while (($rr = @socket_select($vSocket, $vWrite, $vExcept, $ReadTimeOut)) === false) {
                    ;
                }
                if ($rr == 0) {
                    break;
                }

                $res = @socket_recv($this->hnd, $buf, $iBufLen, 0);
            }
        } catch (Exception $e) {
            $this->error();
        }

        return $this->sBuffer;
    }

    /**
     * Recv alias
     *
     * @param int $iTimeOut Timeout
     *
     * @return string
     */
    public function read($iTimeOut = null)
    {
        return $this->recv($iTimeOut);
    }

    /**
     * Send data and wait response
     *
     * @param string $sBuf Buffer
     *
     * @return string
     */
    public function sendAndReceive($sBuf)
    {
        $this->send($sBuf);

        return $this->recv();
    }
}


