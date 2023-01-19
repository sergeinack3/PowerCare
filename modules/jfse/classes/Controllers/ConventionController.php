<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Convention\ConventionService;
use Ox\Mediboard\Jfse\Domain\Convention\Correspondence;
use Ox\Mediboard\Jfse\Domain\Convention\Grouping;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Convention\CConvention;
use Ox\Mediboard\Jfse\ViewModels\Convention\CConventionType;
use Ox\Mediboard\Jfse\ViewModels\Convention\CCorrespondence;
use Ox\Mediboard\Jfse\ViewModels\Convention\CGrouping;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConventionController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class ConventionController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        'editConvention'                     => [
            'method'  => 'editConvention',
            'request' => 'emptyRequest',
        ],
        'updateConvention'                   => [
            'method' => 'updateConvention',
        ],
        'deleteConvention'                   => [
            'method' => 'deleteConvention',
        ],
        'listConventions'                    => [
            'method'  => 'listConventions',
            'request' => 'emptyRequest',
        ],
        'editGrouping'                       => [
            'method'  => 'editGrouping',
            'request' => 'emptyRequest',
        ],
        'updateGrouping'                     => [
            'method' => 'updateGrouping',
        ],
        'deleteGrouping'                     => [
            'method' => 'deleteGrouping',
        ],
        'listGroupings'                      => [
            'method'  => 'listGroupings',
            'request' => 'emptyRequest',
        ],
        'editCorrespondence'                 => [
            'method'  => 'editCorrespondence',
            'request' => 'emptyRequest',
        ],
        'updateCorrespondence'               => [
            'method' => 'updateCorrespondence',
        ],
        'deleteCorrespondence'               => [
            'method' => 'deleteCorrespondence',
        ],
        'listCorrespondences'                => [
            'method'  => 'listCorrespondences',
            'request' => 'emptyRequest',
        ],
        'listTypesConvention'                => [
            'method'  => 'listTypesConvention',
            'request' => 'emptyRequest',
        ],
        'importConventionsRegroupementsByPS' => [
            'method' => 'importConventionsRegroupementsByPS',
        ],
        'importModal'                        => [
            'method'  => 'importModal',
            'request' => 'emptyRequest',
        ],
        'importBinFile'                      => [
            'method' => 'importBinFile',
        ],
        'importZipFile'                      => [
            'method' => 'importZipFile',
        ],
        'uploadCsvFile'                      => [
            'method' => 'uploadCsvFile',
        ],
        'getListeConventionsToInstall'       => [
            'method' => 'getListeConventionsToInstall',
        ],
        'updateConventionsViaCsv'            => [
            'method' => 'updateConventionsViaCsv',
        ],
        'deleteFichierConventions'           => [
            'method' => 'deleteFichierConventions',
        ],
    ];

    /** @var ConventionService */
    private $convention_service;

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return "convention";
    }

    /**
     * ConventionController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->convention_service = new ConventionService();
    }

    public function editConvention(): SmartyResponse
    {
        $convention = new CConvention();

        return new SmartyResponse('convention/convention/edit', ['convention' => $convention]);
    }

    public function editGrouping(): SmartyResponse
    {
        $grouping = new CGrouping();

        return new SmartyResponse('convention/grouping/edit', ['grouping' => $grouping]);
    }

    public function editCorrespondence(): SmartyResponse
    {
        $correspondence = new CCorrespondence();

        return new SmartyResponse('convention/correspondence/edit', ['correspondence' => $correspondence]);
    }

    public function listConventions(): SmartyResponse
    {
        $conventions = [];

        $data = $this->convention_service->listConventions();
        foreach ($data as $convention) {
            $conventions[] = CConvention::getFromEntity($convention);
        }

        return new SmartyResponse('convention/convention/list', ['conventions' => $conventions]);
    }

    public function listGroupings(): SmartyResponse
    {
        $groupings = array_map(
            function (Grouping $grouping): CGrouping {
                return CGrouping::getFromEntity($grouping);
            },
            $this->convention_service->listRegroupements()
        );

        return new SmartyResponse('convention/grouping/list', ['groupings' => $groupings]);
    }

    public function listCorrespondences(): SmartyResponse
    {
        $correspondences = [];

        $data = $this->convention_service->listCorrespondences();

        foreach ($data as $correspondence) {
            $correspondences[] = CCorrespondence::getFromEntity($correspondence);
        }

        return new SmartyResponse('convention/correspondence/list', ['correspondences' => $correspondences]);
    }

    public function updateConvention(Request $request): SmartyResponse
    {
        $is_saved = $this->convention_service->updateConvention(Convention::hydrate($request->request->all()));

        if (!$is_saved) {
            return SmartyResponse::message('CConvention-not-updated', SmartyResponse::MESSAGE_WARNING);
        }

        return SmartyResponse::message('CConvention-updated', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function updateConventionRequest(): Request
    {
        CCanDo::checkEdit();
        $data = [
            "convention_id"               => Utils::toIntOrNull(CView::post('convention_id', 'num')),
            "signer_organization_number"  => CView::post('signer_organization_number', 'str'),
            "convention_type"             => CView::post('convention_type', 'str'),
            "secondary_criteria"          => CView::post('secondary_criteria', 'str'),
            "agreement_type"              => CView::post('agreement_type', 'str'),
            "signer_organization_label"   => CView::post('signer_organization_label', 'str'),
            "amc_number"                  => CView::post('amc_number', 'str'),
            "amc_label"                   => CView::post('amc_label', 'str'),
            "statutory_operator"          => CView::post('statutory_operator', 'str'),
            "routing_code"                => CView::post('routing_code', 'str'),
            "host_id"                     => CView::post('host_id', 'str'),
            "domain_name"                 => CView::post('domain_name', 'str'),
            "sts_referral_code"           => CView::post('sts_referral_code', 'str'),
            "group_convention_flag"       => boolval(CView::post('group_convention_flag', 'bool default|0')),
            "certificate_use_flag"        => boolval(CView::post('certificate_use_flag', 'num default|0')),
            "sts_disabled_flag"           => boolval(CView::post('sts_disabled_flag', 'num default|0')),
            "cancel_management"           => boolval(CView::post('cancel_management', 'num default|0')),
            "rectification_management"    => boolval(CView::post('rectification_management', 'num default|0')),
            "convention_application"      => boolval(CView::post('convention_application', 'num default|0')),
            "systematic_application"      => boolval(CView::post('systematic_application', 'num default|0')),
            "convention_application_date" => CView::post('convention_application_date', 'str'),
            "group_id"                    => CView::post('group_id', 'num'),
            "jfse_id"                     => CView::post('jfse_id', 'num'),
        ];

        return new Request([], $data);
    }

    public function updateGrouping(Request $request): SmartyResponse
    {
        $is_saved = $this->convention_service->updateRegroupement(Grouping::hydrate($request->request->all()));
        if (!$is_saved) {
            return SmartyResponse::message('CGrouping-not-updated', SmartyResponse::MESSAGE_WARNING);
        }

        return SmartyResponse::message('CGrouping-updated', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function updateGroupingRequest(): Request
    {
        CCanDo::checkEdit();
        $data = [
            "grouping_id"                => intval(CView::post('grouping_id', 'num')),
            "amc_number"                 => CView::post('amc_number', 'str'),
            "amc_label"                  => CView::post('amc_label', 'str'),
            "convention_type"            => CView::post('convention_type', 'str'),
            "convention_type_label"      => CView::post('convention_type_label', 'str'),
            "secondary_criteria"         => CView::post('secondary_criteria', 'str'),
            "signer_organization_number" => CView::post('signer_organization_number', 'str'),
            "group_id"                   => intval(CView::post('group_id', 'num')),
            "jfse_id"                    => intval(CView::post('jfse_id', 'num')),
        ];

        return new Request([], $data);
    }

    public function updateCorrespondence(Request $request): SmartyResponse
    {
        $is_saved = $this->convention_service->updateCorrespondance(Correspondence::hydrate($request->request->all()));

        if (!$is_saved) {
            return SmartyResponse::message('CCorrespondence-not-updated', SmartyResponse::MESSAGE_WARNING);
        }

        return SmartyResponse::message('CCorrespondence-updated', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function updateCorrespondenceRequest(): Request
    {
        CCanDo::checkEdit();
        $data = [
            "correspondence_id"       => intval(CView::post('correspondence_id', 'num')),
            "health_insurance_number" => CView::post('health_insurance_number', 'str'),
            "regime_code"             => CView::post('regime_code', 'str'),
            "amc_number"              => CView::post('amc_number', 'str'),
            "amc_label"               => CView::post('amc_label', 'str'),
            "group_id"                => intval(CView::post('group_id', 'num')),
        ];

        return new Request([], $data);
    }

    public function deleteConvention(Request $request): SmartyResponse
    {
        $this->convention_service->deleteConvention($request->get('id'));

        return SmartyResponse::message('CConvention-deleted', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function deleteGrouping(Request $request): SmartyResponse
    {
        $this->convention_service->deleteRegroupement($request->get('id'));

        return SmartyResponse::message('CGrouping-deleted', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function deleteCorrespondence(Request $request): SmartyResponse
    {
        $this->convention_service->deleteCorrespondance($request->get('id'));

        return SmartyResponse::message('CCorrespondence-deleted', SmartyResponse::MESSAGE_SUCCESS);
    }

    public function deleteConventionRequest(): Request
    {
        CCanDo::checkEdit();

        $id = CView::post('id', 'num notNull');

        return new Request([], ['id' => $id]);
    }

    public function deleteGroupingRequest(): Request
    {
        CCanDo::checkEdit();

        $id = CView::post('id', 'num notNull');

        return new Request([], ['id' => $id]);
    }

    public function deleteCorrespondenceRequest(): Request
    {
        CCanDo::checkEdit();

        $id = CView::post('id', 'num notNull');

        return new Request([], ['id' => $id]);
    }

    public function listTypesConvention(): SmartyResponse
    {
        $types_convention = [];

        $data = $this->convention_service->listTypesConvention();

        foreach ($data as $type_convention) {
            $types_convention[] = CConventionType::getFromEntity($type_convention);
        }

        return new SmartyResponse('convention/convention/list_types', ['types_convention' => $types_convention]);
    }

    public function importConventionsRegroupementsByPS(Request $request): SmartyResponse
    {
        $is_imported = $this->convention_service->importConventionsRegroupementsByPS(
            $request->get('mode'),
            $request->get('jfse_id'),
            $request->get('group_id')
        );
        if (!$is_imported) {
            return SmartyResponse::message(
                'CConvention-import conventions regroupements by PS error',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CConvention-import conventions regroupements by PS success',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    public function importConventionsRegroupementsByPSRequest(): Request
    {
        CCanDo::checkEdit();

        $data = [
            "mode"     => CView::post('mode', 'num notNull'),
            "jfse_id"  => Utils::toIntOrNull(CView::post('jfse_id', 'num')),
            "group_id" => Utils::toIntOrNull(CView::post('group_id', 'num')),
        ];

        return new Request([], $data);
    }

    public function importModal(): SmartyResponse
    {
        return new SmartyResponse('convention/import/import_files', []);
    }

    public function importBinFile(Request $request): SmartyResponse
    {
        $is_imported = $this->convention_service->importFichierBin(
            $request->get('file_name')
        );
        if (!$is_imported) {
            return SmartyResponse::message(
                'CConvention-binary file import error',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CConvention-binary file import success',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    public function importBinFileRequest(): Request
    {
        $formfile = CValue::files('formfile');

        $data = [
            "file_name" => $formfile['tmp_name'][0],
        ];

        return new Request([], $data);
    }

    public function importZipFile(Request $request): SmartyResponse
    {
        $is_imported = $this->convention_service->importFichiersZip(
            $request->get('file_name'),
            $request->get('jfse_id')
        );

        if (!$is_imported) {
            return SmartyResponse::message(
                'CConvention-zip file import error',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CConvention-zip file import success',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    public function importZipFileRequest(): Request
    {
        $formfile = CValue::files('formfile');

        $data = [
            "file_name" => $formfile['tmp_name'][0],
            "jfse_id"   => CView::post('jfse_id', 'num notNull'),
        ];

        return new Request([], $data);
    }

    public function uploadCsvFile(Request $request): SmartyResponse
    {
        $file_name = $this->convention_service->uploadFichiersCsv(
            $request->get('file_name'),
            $request->get('jfse_id')
        );

        return new SmartyResponse('convention/import/import_results', ['import_results' => $file_name]);
    }

    public function uploadCsvFileRequest(): Request
    {
        $formfile = CValue::files('formfile');

        $data = [
            "file_name" => $formfile['tmp_name'][0],
            "jfse_id"   => CView::post('jfse_id', 'num notNull'),
        ];

        return new Request([], $data);
    }

    public function getListeConventionsToInstall(Request $request): SmartyResponse
    {
        $elements_to_install = $this->convention_service->listConventionsToInstall(
            $request->get("file_name"),
            $request->get("jfse_id")
        );

        return new SmartyResponse(
            'convention/import/conventions_to_install',
            [
                'conventions_to_install' => $elements_to_install["conventions_to_install"],
                'groupings_to_install'   => $elements_to_install["groupings_to_install"],
            ]
        );
    }

    public function getListeConventionsToInstallRequest(): Request
    {
        $data = [
            "file_name" => CView::post('file_name', 'str notNull'),
            "jfse_id"   => CView::post('jfse_id', 'num notNull'),
        ];

        return new Request([], $data);
    }

    public function updateConventionsViaCsv(Request $request): SmartyResponse
    {
        $is_updated = $this->convention_service->updateConventionsViaCsv(
            $request->get("file_name"),
            $request->get("jfse_id")
        );

        if (!$is_updated) {
            return SmartyResponse::message(
                'CConvention-update from csv file error',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CConvention-update from csv file success',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    public function updateConventionsViaCsvRequest(): Request
    {
        $data = [
            "file_name" => CView::post('file_name', 'str notNull'),
            "jfse_id"   => CView::post('jfse_id', 'num notNull'),
        ];

        return new Request([], $data);
    }

    public function deleteFichierConventions(Request $request): SmartyResponse
    {
        $is_deleted = $this->convention_service->deleteFichierConventions(
            $request->get("file_name"),
            $request->get("jfse_id")
        );

        if (!$is_deleted) {
            return SmartyResponse::message(
                'CConvention-delete file error',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CConvention-delete file success',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    public function deleteFichierConventionsRequest(): Request
    {
        $data = [
            "file_name" => CView::post('file_name', 'str notNull'),
            "jfse_id"   => CView::post('jfse_id', 'num notNull'),
        ];

        return new Request([], $data);
    }
}
