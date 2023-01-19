<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathService;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathMappingException;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CarePath\CCarePath;
use Ox\Mediboard\Jfse\ViewModels\CarePath\CCarePathDoctor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CarePathController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        'edit'  => [
            'method'  => 'edit',
            'request' => 'editRequest',
        ],
        'store' => [
            'method'  => 'store',
            'request' => 'storeRequest',
        ],
    ];

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'carePath';
    }

    public function editRequest(): Request
    {
        return new Request();
    }

    public function edit(Request $request): SmartyResponse
    {
        $care_path             = new CCarePath();

        $vars = [
            "care_path"                => $care_path,
            "care_path_doctor"         => new CCarePathDoctor(),
            'referring_physician'      => false,
            'corresponding_physicians' => []
        ];

        return new SmartyResponse('care_path/edit', $vars);
    }

    public function storeRequest(): Request
    {
        $care_path_indicators = array_values(CarePathEnum::toArray());

        $install_date      = CView::post("install_date", "date");
        $poor_md_zone_date = CView::post("poor_md_zone_install_date", "date");
        $indicator         = CView::post("indicator", "enum list|" . implode('|', $care_path_indicators) . " notNull");
        $first_name        = CView::post("first_name", "str");
        $last_name         = CView::post("last_name", "str");
        $invoicing_id      = CView::post("invoicing_id", "str length|9");

        $indicator_key = CarePathEnum::search($indicator);

        $data = [
            "invoice_id"                => CView::post("invoice_id", "str notNull"),
            "indicator"                 => $indicator ? CarePathEnum::$indicator_key() : null,
            "install_date"              => ($install_date) ? new DateTimeImmutable($install_date) : null,
            "poor_md_zone_install_date" => ($poor_md_zone_date) ? new DateTimeImmutable($poor_md_zone_date) : null,
            "declaration"               => (bool)CView::post("declaration", "bool"),
            "first_name"                => CMbString::removeAccents(substr($first_name, 0, 15)),
            "last_name"                 => CMbString::removeAccents(substr($last_name, 0, 25)),
            "invoicing_id"              => $invoicing_id,
        ];

        if (
            in_array($indicator, [CarePathEnum::ORIENTED_BY_NRP(), CarePathEnum::ORIENTED_BY_RP()])
            && (!$first_name || !$last_name)
        ) {
            throw CarePathMappingException::missingDoctor();
        } elseif ($indicator == CarePathEnum::RECENTLY_INSTALLED_RP() && !$install_date) {
            throw CarePathMappingException::missingInstallDate();
        } elseif ($indicator == CarePathEnum::POOR_MEDICALIZED_ZONE() && !$poor_md_zone_date) {
            throw CarePathMappingException::missingPoorMdZoneInstallDate();
        }

        return new Request([], $data);
    }

    public function store(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $service = new CarePathService();
        $service->saveCarePath($request->request->all());

        return new JsonResponse(['success' => true, 'message' => 'CCarePath-Saved']);
    }
}
