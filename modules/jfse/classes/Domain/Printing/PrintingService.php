<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Printing;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\ApiClients\PrintingClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\Printing\PrintingException;

class PrintingService extends AbstractService
{
    /** @var PrintingClient */
    protected $client;

    public function __construct(PrintingClient $client = null)
    {
        parent::__construct($client ?? new PrintingClient());
    }

    public function getTransmissionSlip(PrintingSlipConf $conf): string
    {
        switch ($conf->getMode()) {
            case PrintSlipModeEnum::MODE_ONE_PRINT()->getValue():
            case PrintSlipModeEnum::MODE_MULTIPLE_PRINT()->getValue():
                if (!$conf->getBatch()) {
                    throw PrintingException::missingBatch();
                }
                break;
            case PrintSlipModeEnum::MODE_PRINT_DATE_BOUNDS()->getValue():
                if (!$conf->getDateMin() || !$conf->getDateMax()) {
                    throw PrintingException::missingDates();
                }
                break;
            case PrintSlipModeEnum::MODE_ONE_OR_SEVERAL_FILES()->getValue():
                if (!$conf->getFiles()) {
                    throw PrintingException::missingFiles();
                }
                break;
            default:
                throw PrintingException::unknownMode();
        }

        $content = $this->client->getTransmissionSlip($conf)->getContent();

        return CMbArray::get($content, 'bordereau');
    }

    public function getCerfa(PrintingCerfaConf $printing_cerfa_conf): string
    {
        if (!$printing_cerfa_conf->getInvoiceId() && !$printing_cerfa_conf->getInvoiceNumber()) {
            throw PrintingException::missingInvoice();
        }

        $content = $this->client->getCerfa($printing_cerfa_conf)->getContent();

        return CMbArray::get($content, 'cerfa');
    }

    public function getInvoiceInformation(?string $invoice_id, ?int $invoice_number = null): string
    {
        if ($invoice_id === null && $invoice_number === null) {
            throw PrintingException::missingInvoice();
        }

        $content = $this->client->getInvoiceInformation($invoice_id, $invoice_number)->getContent();

        return CMbArray::get($content, 'infosFSE');
    }

    public function getInvoiceReceipt(string $invoice_id): string
    {
        $response = $this->client->getInvoiceReceipt($invoice_id);

        return CMbArray::get($response->getContent(), 'quittance');
    }

    public function getDreCopy(string $invoice_id): string
    {
        $response = $this->client->getDreCopy($invoice_id);

        return CMbArray::get($response->getContent(), 'copieDRE');
    }

    public function getCheckUpReceipt(string $invoice_id): string
    {
        $response = $this->client->getCheckUpReceipt($invoice_id);

        return CMbArray::get($response->getContent(), 'bonExamen');
    }
}
