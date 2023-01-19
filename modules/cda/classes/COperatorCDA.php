<?php
/**
 * @package Mediboard\cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;

/**
 * Class COperatorCDA
 * Operator CDA
 */
class COperatorCDA extends CEAIOperator
{
    /**
     * Event
     *
     * @param CExchangeDataFormat $data_format data format
     *
     * @return string|null
     */
    function event(CExchangeDataFormat $data_format)
    {
        $msg = $data_format->_message;
        $data_format->loadRefsInteropActor();
        $sender = $data_format->_ref_sender;

        /** @var CCDAEvent $evt */
        $evt               = $data_format->_event_message;
        $evt->_data_format = $data_format;

        $sender->loadBackRefConfigCDA();
       // if ($sender->_ref_config_cda->encoding === "UTF-8") {
       //     //$msg = utf8_decode($msg);
       // }

        try {
            // Création de l'échange
            $exchange_cda = new CExchangeCDA();
            $exchange_cda->load($data_format->_exchange_id);

            /** @var CCDADomDocument $dom_evt */
            $dom_evt = $evt->getCDAEvent($msg);
            if (!$dom_evt) {
                CCDAException::eventCDANotFound();

                return null;
            }

            // Gestion des notifications ?
            if (!$exchange_cda->_id) {
                $exchange_cda->populateEchange($data_format, $evt);
                $exchange_cda->message_valide = 1;
            }

            $exchange_cda->loadRefsInteropActor();
            $exchange_cda->date_production = CMbDT::dateTime();
            $exchange_cda->store();

            // Pas de traitement du message
            if (!$data_format->_to_treatment) {
                return null;
            }

            $dom_evt->_ref_exchange_cda = $exchange_cda;
            $dom_evt->_ref_sender       = $sender;

            return $dom_evt->handle();
        } catch (Exception $e) {
            CCDAException::invalidDocument();
        }

        return null;
    }
}

