<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Noemie\NoemieService;
use Ox\Mediboard\Jfse\Domain\Noemie\NoemieTaskService;
use Ox\Mediboard\Jfse\Exceptions\Noemie\NoemieException;
use Ox\Mediboard\Jfse\Responses\FileResponse;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Cron\CCronJobLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class NoemieController extends AbstractController
{
    /** @var string[][] */
    protected static $routes = [
        'index'                         => [
            'method' => 'index',
        ],
        'exportPayments'                => [
            'method' => 'exportPayments',
        ],
        'importNoemiePayments'          => [
            'method'  => 'importNoemiePayments',
            'request' => 'taskRequest',
        ],
        'importInvoiceAcknowledgements' => [
            'method'  => 'importInvoiceAcknowledgements',
            'request' => 'taskRequest',
        ],
    ];

    /** @var NoemieService */
    protected $service;

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'noemie';
    }

    /**
     * ConventionController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->service = new NoemieService();
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function index(Request $request): SmartyResponse
    {
        return new SmartyResponse('noemie/export', ['jfse_user_id' => $request->get('jfse_user_id')]);
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function indexRequest(): Request
    {
        CCanDo::checkRead();

        return new Request(['jfse_user_id' => CView::post('jfse_user_id', 'num')]);
    }

    /**
     * @param Request $request
     *
     * @return FileResponse
     * @throws Exception
     * @throws NoemieException
     */
    public function exportPayments(Request $request): FileResponse
    {
        $user_id = $request->get('jfse_user_id');
        if ($user_id) {
            $jfse_user = CJfseUser::getFromJfseId($user_id);
        } else {
            $jfse_user = CJfseUser::getFromMediuser(CMediusers::get());
        }

        Utils::setJfseUserId($jfse_user->jfse_id);

        $file = $this->service->getPaymentsCsvFile(
            $jfse_user->jfse_id,
            $request->get('date_min'),
            $request->get('date_max')
        );

        if (!$file) {
            throw NoemieException::invalidExportFile();
        }

        $jfse_user->loadMediuser();
        $file_name = 'Export_Virement_' . $jfse_user->_mediuser->_view;
        if ($request->get('date_min')) {
            $file_name .= '_' . $request->get('date_min')->format('d-m-y');
        }

        if ($request->get('date_max')) {
            $file_name .= '_' . $request->get('date_max')->format('d-m-y') . '.csv';
        } else {
            $file_name .= '_' . (new DateTimeImmutable())->format('d-m-y') . '.csv';
        }

        return new FileResponse($file_name, $file->getContent());
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function exportPaymentsRequest(): Request
    {
        CCanDo::checkRead();

        $date_min = CView::post('date_min', 'date');
        if ($date_min != "") {
            $date_min = new DateTimeImmutable($date_min);
        } else {
            $date_min = null;
        }

        $date_max = CView::post('date_max', 'date');
        if ($date_max != "") {
            $date_max = new DateTimeImmutable($date_max);
        } else {
            $date_max = null;
        }

        return new Request([
            'jfse_user_id' => CView::post('jfse_user_id', ['num', 'default' => null]),
            'date_min'     => $date_min,
            'date_max'     => $date_max,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function importNoemiePayments(Request $request): JsonResponse
    {
        $report = (new NoemieTaskService())->processNoemiePayments(
            $request->get('user_pace'),
            $request->get('start'),
            $request->get('end')
        );

        $data = [];
        if (CApp::isCron()) {
            if ($report->getErrorsCounter() && !$report->getSuccessCounter()) {
                CCronJobLog::logError($report->getFinalReport());
            } else {
                CCronJobLog::logInfo($report->getFinalReport());
            }
        } else {
            $data['message'] = utf8_encode($report->getFinalReport());
        }

        return new JsonResponse($data);
    }

    /**
     * @return JsonResponse
     */
    public function importInvoiceAcknowledgements(): JsonResponse
    {
        $report = (new NoemieTaskService())->processInvoiceAcknowledgements();

        $data = [];
        if (CCronJobLog::getCronJobLogId()) {
            if ($report->getErrorsCounter() && !$report->getSuccessCounter()) {
                CApp::log('NoemieController', $report->getFinalReport(), LoggerLevels::LEVEL_ERROR);
            } else {
                CApp::log('NoemieController', $report->getFinalReport());
            }
        } else {
            $data['message'] = utf8_encode($report->getFinalReport());
        }

        return new JsonResponse($data);
    }

    /**
     * @return Request
     */
    public function taskRequest(): Request
    {
        CCanDo::checkAdmin();

        $user_pace = CView::get('user_pace', 'num');
        $user_pace = $user_pace != '' ? (int)$user_pace : null;

        $start = CView::get('start', 'date');
        $start = $start != '' ? new DateTimeImmutable($start) : null;

        $end = CView::get('end', 'date');
        $end = $end != '' ? new DateTimeImmutable($end) : null;

        return new Request([
            'user_pace' => $user_pace,
            'start'     => $start,
            'end'       => $end,
        ]);
    }
}
