<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\Client\Legacy\CSOAPLegacy;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CExchangeSource;
use SoapFault;

class SoapLegacyController extends CLegacyController
{
    protected CSourceSOAP         $exchange_source;
    protected SOAPClientInterface $soap_client;

    public function ajaxConnexionSOAP(): void
    {
        try {
            CCanDo::checkAdmin();

            // Check params
            if (null == $exchange_source_name = CValue::get("exchange_source_name")) {
                CAppUI::stepMessage(UI_MSG_ERROR, "Aucun nom de source d'�change sp�cifi�");
            }

            $this->exchange_source = CExchangeSource::get($exchange_source_name, CSourceSOAP::TYPE, true, null, false);

            if (!$this->exchange_source) {
                CAppUI::stepMessage(
                    UI_MSG_ERROR,
                    "Aucune source d'�change disponible pour ce nom : '$exchange_source_name'"
                );
            }

            if (!$this->exchange_source->host) {
                CAppUI::stepMessage(UI_MSG_ERROR, "Aucun h�te pour la source d'�change : '$exchange_source_name'");
            }

            $options = [
                "encoding" => $this->exchange_source->encoding,
                "loggable" => false,
            ];

            $this->soap_client = $this->exchange_source->getClient();
            $this->soap_client->hasError();


            if ($this->soap_client->hasError() !== false) {
                CAppUI::stepMessage(
                    UI_MSG_ERROR,
                    "Impossible de joindre la source de donn�e : '$exchange_source_name'"
                );
            } else {
                CAppUI::stepMessage(UI_MSG_OK, "Connect� � la source '$exchange_source_name'");
            }


            $this->soap_client->checkServiceAvailability();
        } catch (CMbException $e) {
            CAppUI::stepMessage(
                UI_MSG_ERROR,
                $e->getMessage()
            );
        }catch (SoapFault $soap_fault) {
            CAppUI::stepMessage(
                UI_MSG_ERROR,
                $soap_fault->getMessage()
            );
        }
    }

    public function ajaxGetFunctionsSOAP(): void
    {
        $this->ajaxConnexionSOAP();

        CAppUI::stepMessage(UI_MSG_OK, "Liste des fonctions SOAP publi�es");

        // Cr�ation du template
        $smarty = new CSmartyDP();

        $smarty->assign("exchange_source", $this->exchange_source);
        $smarty->assign("functions", $this->soap_client->getFunctions());
        $smarty->assign("types", $this->soap_client->getTypes());
        $smarty->assign("form_name", CValue::get("form_name"));

        $smarty->display("inc_soap_functions.tpl");
    }
}
