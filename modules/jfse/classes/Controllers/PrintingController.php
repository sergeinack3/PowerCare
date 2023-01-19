<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CWkhtmlToPDF;
use Ox\Mediboard\CompteRendu\CWkHtmlToPDFConverter;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingCerfaConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingService;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingSlipConf;
use Ox\Mediboard\Jfse\Responses\FileResponse;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Printing\CPrintingCerfaConf;
use Ox\Mediboard\Jfse\ViewModels\Printing\CPrintingSlipConf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PrintingController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "slip"      => [
            "method" => "slip",
        ],
        "slipView"  => [
            "method"  => "slipView",
            "request" => "emptyRequest",
        ],
        "cerfa"     => [
            "method" => "cerfa",
            'request' => 'cerfaRequest'
        ],
        "cerfaCopy"     => [
            "method" => "cerfa",
            'request' => 'cerfaCopyRequest'
        ],
        "invoice"   => [
            "method" => "invoice",
            'request' => 'printRequest'
        ],
        "receipt"   => [
            "method" => "receipt",
            'request' => 'printRequest'
        ],
        "dreCopy"   => [
            "method" => "dreCopy",
            'request' => 'printRequest'
        ],
        "checkUpReceipt"   => [
            "method" => "checkUpReceipt",
            'request' => 'printRequest'
        ],
    ];

    /** @var PrintingService */
    private $service;

    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->service = new PrintingService();
    }

    public static function getRoutePrefix(): string
    {
        return 'print';
    }

    public function slipRequest(): Request
    {
        CCanDo::checkRead();

        $data = [
            "mode"     => (int)CView::post("mode", "num notNull"),
            "degrade"  => (bool)CView::post("degrade", "bool notNull"),
            "date_min" => new DateTimeImmutable(CView::post("date_min", "date")),
            "date_max" => new DateTimeImmutable(CView::post("date_max", "date")),
            "batch"    => (array)CView::post("batches_ids", "str"),
            "files"    => (array)CView::post("files_ids", "str"),
        ];

        return new Request([], $data);
    }

    public function slip(Request $request): Response
    {
        $conf = new PrintingSlipConf($request->get('mode'), $request->get('degrade'));
        $conf->setDateMin($request->get('date_min'));
        $conf->setDateMax($request->get('date_max'));
        $conf->setBatch($request->get('batch'));
        $conf->setFiles($request->get('files'));

        $content = base64_decode($this->service->getTransmissionSlip($conf));

        return new Response($content, 200, ['Content-Type' => 'application/pdf']);
    }

    public function slipView(Request $r): SmartyResponse
    {
        return new SmartyResponse('printing/print_slip', ["print_conf" => new CPrintingSlipConf()]);
    }

    public function cerfaRequest(): Request
    {
        CCanDo::checkRead();

        $data = [
            "invoice_id"     => CView::post("invoice_id", "str"),
            "duplicate"      => false,
        ];

        return new Request([], $data);
    }

    public function cerfaCopyRequest(): Request
    {
        CCanDo::checkRead();

        $data = [
            "invoice_id"     => CView::post("invoice_id", "str"),
            "duplicate"      => true,
        ];

        return new Request([], $data);
    }

    public function cerfa(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $conf = new PrintingCerfaConf($request->get('duplicate'));
        $conf->setInvoiceId($request->get('invoice_id'));

        $content = base64_decode($this->service->getCerfa($conf));

        $filename = CAppUI::tr(
            'JfseInvoiceView-title-cerfa_file',
            $this->getInvoiceNumber($request->get('invoice_id'))
        );

        if ($conf->getDuplicate()) {
            $filename = CAppUI::tr('JfseInvoiceView-title-copy') . " $filename";
        }

        return new FileResponse("$filename.pdf", CWkHtmlToPDFConverter::addAutoPrint($content), 'application/pdf');
    }

    public function printRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([
            "invoice_id" => CView::post("invoice_id", "str")
        ]);
    }

    public function invoice(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $content = base64_decode($this->service->getInvoiceInformation($request->get('invoice_id')));

        $filename = CAppUI::tr(
            'JfseInvoiceView-title-invoice_file',
            $this->getInvoiceNumber($request->get('invoice_id'))
        );

        return new FileResponse("$filename.pdf", CWkHtmlToPDFConverter::addAutoPrint($content), 'application/pdf');
    }

    public function receipt(Request $request): FileResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $content = base64_decode($this->service->getInvoiceReceipt($request->get('invoice_id')));

        $filename = CAppUI::tr(
            'JfseInvoiceView-title-receipt_file',
            $this->getInvoiceNumber($request->get('invoice_id'))
        );

        return new FileResponse("$filename.pdf", CWkHtmlToPDFConverter::addAutoPrint($content), 'application/pdf');
    }

    public function dreCopy(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $content = base64_decode($this->service->getDreCopy($request->get('invoice_id')));

        $filename = CAppUI::tr(
            'JfseInvoiceView-title-dre_copy_file',
            $this->getInvoiceNumber($request->get('invoice_id'))
        );

        return new FileResponse("$filename.pdf", CWkHtmlToPDFConverter::addAutoPrint($content), 'application/pdf');
    }

    public function checkUpReceipt(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $content = base64_decode($this->service->getCheckUpReceipt($request->get('invoice_id')));

        $filename = CAppUI::tr(
            'JfseInvoiceView-title-check_up_receipt_file',
            $this->getInvoiceNumber($request->get('invoice_id'))
        );

        return new FileResponse("$filename.pdf", CWkHtmlToPDFConverter::addAutoPrint($content), 'application/pdf');
    }

    private function getInvoiceNumber(string $invoice_id): int
    {
        $invoice = CJfseInvoice::getFromJfseId($invoice_id);

        return $invoice->invoice_number;
    }
}
