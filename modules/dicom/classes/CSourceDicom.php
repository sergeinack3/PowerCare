<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\Socket\SocketClient;
use Ox\Mediboard\System\CExchangeSource;

/**
 * The Dicom exchange source
 */
class CSourceDicom extends CExchangeSource
{
    // Source type
    public const TYPE = 'dicom';

    /**
     * Table Key
     *
     * @var integer
     */
    public $dicom_source_id = null;

    /**
     * The port
     *
     * @var integer
     */
    public $port = null;

    /**
     * The socket client to test the connection to the source
     *
     * @var SocketClient
     */
    protected $_socket_client = null;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "dicom_source";
        $spec->key   = "dicom_source_id";

        return $spec;
    }

    /**
     * Get the properties of our class as string
     *
     * @return array
     */
    function getProps()
    {
        $props         = parent::getProps();
        $props["port"] = "num notNull";

        return $props;
    }

    /**
     * Return a SocketClient
     *
     * @return SocketClient
     */
    function getSocketClient()
    {
        if (!$this->_socket_client) {
            $this->setSocketClient();
        }

        return $this->_socket_client;
    }

    /**
     * Create the socket client
     *
     * @return null
     */
    function setSocketClient()
    {
        $this->_socket_client = new SocketClient();
        $this->_socket_client->setHost($this->host);
        $this->_socket_client->setPort($this->port);
    }

    /**
     * Initiate a connection with the source and send an echo message
     *
     * @return null
     */
    function sendEcho()
    {
        return;
    }

    /**
     * Check if the source is reachable
     *
     * @return boolean
     * @todo Faire un vrai isReachable (envoi de CEcho)
     *
     */
    function isReachableSource()
    {
        /* Commented because the connction with the socket client can causes delays on the source monitoring probes */
        //    if (!$this->_socket_client) {
        //      $this->setSocketClient();
        //    }
        //    if ($this->_socket_client->connect() !== null) {
        //      $this->_reachable = 0;
        //      $this->_message   = CAppUI::tr("CSourceDicom-unreachable-source", $this->host);
        //      return false;
        //    }
        return true;
    }

    /**
     * Source is autheenticated
     *
     * @return boolean|void
     */
    function isAuthentificate()
    {
        return true;
    }
}
