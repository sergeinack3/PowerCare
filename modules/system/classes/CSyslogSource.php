<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Socket\SocketClient;
use Ox\Interop\Eai\CExchangeDataFormat;

class CSyslogSource extends CExchangeSource
{
    // Source type
    public const TYPE = 'syslog';

    /** @var integer Primary key */
    public $syslog_source_id;

    /** @var string Syslog source protocol to use */
    public $protocol;

    /** @var integer Syslog source timeout connection */
    public $timeout;

    public $port;
    public $ssl_certificate;
    public $ssl_passphrase;
    public $iv_passphrase;
    public $_socket_client;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "syslog_source";
        $spec->key   = "syslog_source_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                    = parent::getProps();
        $props["port"]            = "num default|514 notNull";
        $props["protocol"]        = "enum list|TCP|UDP|TLS default|TCP notNull";
        $props["timeout"]         = "num default|5";
        $props["ssl_certificate"] = "str";
        $props["ssl_passphrase"]  = "password show|0 loggable|0";
        $props["iv_passphrase"]   = "str show|0 loggable|0";

        return $props;
    }

    function updateEncryptedFields()
    {
        if ($this->ssl_passphrase === "") {
            $this->ssl_passphrase = null;
        } else {
            if (!empty($this->ssl_passphrase)) {
                $this->ssl_passphrase = $this->encryptString($this->ssl_passphrase, "iv_passphrase");
            }
        }
    }

    /**
     * @inheritdoc
     */
    function isSecured()
    {
        return (($this->protocol == 'TLS') && $this->ssl_certificate && is_readable($this->ssl_certificate));
    }

    /**
     * @inheritdoc
     */
    function getProtocol()
    {
        return strtolower($this->protocol);
    }

    /**
     * Sends a SYSLOG test message
     */
    function sendTestMessage()
    {
        $msg = '<107>1 ' . CMbDT::format(null, '%Y-%m-%dT%H:%M:%SZ') . ' MEDIBOARD This is a Syslog message sample';
        $this->sendMessage($msg);
    }

    /**
     * Sends a SYSLOG message
     *
     * @param string $msg Message to send
     */
    function sendMessage($msg)
    {
        if (!$this->_socket_client) {
            $this->connect();
        }

        try {
            $this->setData($msg);
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
        }
    }

    function connect()
    {
        if ($this->_socket_client) {
            return $this->_socket_client;
        }

        return $this->getSocketClient();
    }

    function setData($data, $argsList = false, CExchangeDataFormat $exchange = null)
    {
        $this->_data = strlen($data) . " " . $data;
        fwrite($this->getSocketClient(), $this->_data);
    }

    /**
     * @inheritdoc
     */
    function isReachableSource()
    {
        // UDP
        if ($this->protocol == 'UDP') {
            try {
                $this->testUDPConnection();
                $this->_reachable = 2;
            } catch (CMbException $e) {
                $this->_reachable = 0;
                $this->_message   = $e->getMessage();

                return false;
            }
        } // TCP, TLS
        else {
            try {
                $this->connect();
                $this->_reachable = 2;
            } catch (CMbException $e) {
                $this->_reachable = 0;
                $this->_message   = $e->getMessage();

                return false;
            }
        }

        return true;
    }

    /**
     * Write some data in order to check if UDP port is open (Not really reliable)
     *
     * @return bool
     * @throws CMbException
     */
    function testUDPConnection()
    {
        $handle = fsockopen("udp://$this->host", $this->port, $errno, $errstr, 2);

        if (!$handle) {
            throw new CMbException("$errno : $errstr");
        }

        socket_set_timeout($handle, $this->timeout);
        $write = fwrite($handle, "x00");

        if (!$write) {
            throw new CMbException("common-error-Unable to write to port.");
        }

        $start_time = time();
        $header     = fread($handle, 1);
        $endTime    = time();
        $time_diff  = $endTime - $start_time;

        fclose($handle);
        if ($time_diff < $this->timeout) {
            throw new CMbException("common-error-Unreachable source", $this->name);
        }
    }

    /**
     * Get socket client
     *
     * @return SocketClient
     * @throws CMbException
     */
    function getSocketClient()
    {
        if ($this->_socket_client) {
            return $this->_socket_client;
        }

        $address = $this->getProtocol() . "://$this->host:$this->port";
        $context = stream_context_create();

        if ($this->isSecured()) {
            stream_context_set_option($context, 'ssl', 'local_cert', $this->ssl_certificate);

            if ($this->ssl_passphrase) {
                $ssl_passphrase = $this->getPassword($this->ssl_passphrase, "iv_passphrase");
                stream_context_set_option($context, 'ssl', 'passphrase', $ssl_passphrase);
            }
        }

        $this->startCallTrace();
        $this->_socket_client = @stream_socket_client(
            $address,
            $errno,
            $errstr,
            ($this->timeout) ? $this->timeout : 5,
            STREAM_CLIENT_CONNECT,
            $context
        );
        $this->stopCallTrace();
        if (!$this->_socket_client) {
            throw new CMbException("common-error-Unreachable source", $this->name);
        }

        $this->startCallTrace();
        stream_set_blocking($this->_socket_client, 1);
        $this->stopCallTrace();

        return $this->_socket_client;
    }
}
