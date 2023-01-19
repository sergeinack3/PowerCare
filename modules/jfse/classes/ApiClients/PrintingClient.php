<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingCerfaConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingSlipConf;
use Ox\Mediboard\Jfse\Mappers\PrintingMapper;

class PrintingClient extends AbstractApiClient
{
    /** @var PrintingMapper */
    private $mapper;

    public function __construct(Client $client = null, PrintingMapper $mapper = null)
    {
        parent::__construct($client ?? new Client());

        $this->mapper = $mapper ?? new PrintingMapper();
    }

    public function getTransmissionSlip(PrintingSlipConf $conf): Response
    {
        $data = $this->mapper->slipConfToArray($conf);

        return self::sendRequest(Request::forge('PRINT-imprimerBordereau', $data));
    }

    public function getCerfa(PrintingCerfaConf $printing_cerfa_conf): Response
    {
        $data = $this->mapper->cerfaConfToArray($printing_cerfa_conf);

        return self::sendRequest(Request::forge('PRINT-imprimerCerfa', $data));
    }

    public function getInvoiceInformation(?string $invoice_id, ?int $invoice_number): Response
    {
        $data = [];
        if ($invoice_id !== null) {
            $data["idFacture"] = $invoice_id;
        }
        if ($invoice_number !== null) {
            $data["noFacture"] = $invoice_number;
        }

        return self::sendRequest(Request::forge('PRINT-imprimerInfosFSE', ['imprimerInfosFSE' => $data]));
    }

    public function getInvoiceReceipt(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('PRINT-imprimerQuittance', [
            'imprimerQuittance' => ['idFacture' => $invoice_id]
        ]));
    }

    public function getDreCopy(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('PRINT-imprimerCopieDRE', [
            'imprimerCopieDRE' => ['idFacture' => $invoice_id]
        ]));
    }

    public function getCheckUpReceipt(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('PRINT-imprimerBonExamen', [
            'imprimerBonExamen' => ['idFacture' => $invoice_id]
        ]));
    }
}
