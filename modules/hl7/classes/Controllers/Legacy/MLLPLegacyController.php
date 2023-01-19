<?php


/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CSourceMLLP;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Core\CLegacyController;

class MLLPLegacyController extends CLegacyController
{
    protected CSourceMLLP $exchange_source;

    public function ajaxConnexionMLLP(): void
    {
        CCanDo::checkAdmin();

        // Check params
        if (null == $exchange_source_name = CValue::get("exchange_source_name")) {
            CAppUI::stepMessage(UI_MSG_ERROR, "Aucun nom de source d'échange spécifié");
        }

        $this->exchange_source = CExchangeSource::get($exchange_source_name, "mllp", true, null, false);

        if (!$this->exchange_source) {
            CAppUI::stepMessage(
                UI_MSG_ERROR,
                "Aucune source d'échange disponible pour ce nom : '$exchange_source_name'"
            );
        }

        if (!$this->exchange_source->host) {
            CAppUI::stepMessage(UI_MSG_ERROR, "Aucun hôte pour la source d'échange : '$exchange_source_name'",);
        }

        try {
            $this->exchange_source->getClient()->getSocketClient();
            CAppUI::stepMessage(UI_MSG_OK, "Connexion au serveur MLLP réussi");
            if ($ack = $this->exchange_source->getClient()->getData()) {
                echo "<pre>$ack</pre>";
            }
        } catch (Exception $e) {
            CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
            CApp::rip();
        }
    }

    public function ajaxSendMLLP(): void
    {
        $this->ajaxConnexionMLLP();

        try {
            $this->exchange_source->setData("Hello world !\n");

            /** @var MLLPClientInterface $client */
            $client = $this->exchange_source->getClient();
            $client->send();
            CAppUI::stepMessage(UI_MSG_OK, "Données transmises au serveur MLLP");
            if ($ack = $client->getData()) {
                echo "<pre>$ack</pre>";
            }
        } catch (Exception $e) {
            CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
        }
    }
}
