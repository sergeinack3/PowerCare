<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\FileClientInterface;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hprimsante\Events\ADM\CHPrimSanteADM;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceFileSystem;

/**
 * Destinataire de messages Hprimsante
 * Class CReceiverHprimSante
 */
class CReceiverHprimSante extends CInteropReceiver
{
    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceFTP::TYPE,
        CSourceSFTP::TYPE,
        CSourceFileSystem::TYPE,
    ];

    // DB Table key
    public $receiver_hprimsante_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'receiver_hprimsante';
        $spec->key      = 'receiver_hprimsante_id';
        $spec->messages = [
            "ADM" => ["ADM"],
            "REG" => ["REG"],
            "ORU" => ["ORU"],
        ];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|destinataires_hprimsante";

        return $props;
    }

    /**
     * get format of the object handler
     *
     * @param CEAIObjectHandler $objectHandler object handler
     *
     * @return mixed|null
     */
    function getFormatObjectHandler(CEAIObjectHandler $objectHandler)
    {
        $hprim_object_handlers = CHPrimSante::getObjectHandlers();
        $object_handler_class  = CClassMap::getSN($objectHandler);

        if (array_key_exists($object_handler_class, $hprim_object_handlers)) {
            return $hprim_object_handlers[$object_handler_class];
        }

        return null;
    }

    /**
     * @inheritdoc
     *            
     * @param CHPrimSanteEvent $evenement
     */
    function sendEvent($evenement, $object, $data = [], $headers = [], $message_return = false, $soapVar = false)
    {
        if (!parent::sendEvent($evenement, $object, $data, $headers, $message_return, $soapVar)) {
            return null;
        }

        $evenement->_receiver = $this;
        $this->loadConfigValues();
        $evenement->build($object);

        if (!$msg = $evenement->flatten()) {
            return null;
        }

        /** @var CExchangeHprimSante $exchange */
        $exchange = $evenement->_exchange_hpr;

        if (!$exchange->message_valide) {
            return null;
        }

        if (!$this->synchronous) {
            return null;
        }
        $source = CExchangeSource::get("$this->_guid-$evenement->type");

        if (!$source->_id || !$source->active) {
            return null;
        }

        $source->setData($msg, null, $exchange);
        $exchange->send_datetime = CMbDT::dateTime();

        try {
            /** @var FileClientInterface $client */
            $client = $source->getClient();
            $client->send();
        } catch (Exception $e) {
            throw new CMbException("CExchangeSource-no-response %s", $this->nom);
        }

        $exchange->response_datetime = CMbDT::dateTime();

        $ack_data = $source->getACQ();

        if (!$ack_data) {
            $exchange->store();

            return null;
        }

        /** @var CHPrimSanteEvent $data_format */
        $data_format = CHPrimSante::getEvent($exchange);

        $ack = new CHPrimSanteAcknowledgment($data_format);
        $ack->handle($ack_data);
        $exchange->send_datetime       = CMbDT::dateTime();
        $exchange->statut_acquittement = $ack->getStatutAcknowledgment();
        $exchange->acquittement_valide = $ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
        $exchange->_acquittement       = $ack_data;
        $exchange->store();

        return $ack_data;
    }
}
