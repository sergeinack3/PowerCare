<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Convention\Correspondence;
use Ox\Mediboard\Jfse\Domain\Convention\Grouping;
use Ox\Mediboard\Jfse\Mappers\ConventionMapper;

/**
 * Class ConventionClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class ConventionClient extends AbstractApiClient
{
    /** @var ConventionMapper */
    private $mapper;

    public function __construct(?Client $client = null, ?ConventionMapper $mapper = null)
    {
        parent::__construct($client);

        $this->mapper = $mapper ?? new ConventionMapper();
    }

    public function updateConvention(Convention $convention): Response
    {
        $data = $this->mapper->makeStoreArrayFromConvention($convention);

        $request = Request::forge('CNV-updateConvention', $data);

        return self::sendRequest($request);
    }

    public function listConventions(): Response
    {
        $request = Request::forge('CNV-getListeConventions');

        return self::sendRequest($request);
    }

    public function listRegroupements(): Response
    {
        return self::sendRequest(Request::forge('CNV-getListeRegroupements'));
    }

    public function listCorrespondances(): Response
    {
        return self::sendRequest(Request::forge('CNV-getListeCorrespondances'));
    }

    public function listTypesConvention(): Response
    {
        return self::sendRequest(Request::forge('CNV-getListeTypesConvention'));
    }

    public function updateRegroupement(Grouping $grouping): Response
    {
        $data    = $this->mapper->makeStoreArrayFromGrouping($grouping);
        $request = Request::forge('CNV-updateRegroupement', $data);

        return self::sendRequest($request);
    }

    public function updateCorrespondance(Correspondence $correspondence): Response
    {
        $data    = $this->mapper->makeStoreArrayFromCorrespondence($correspondence);
        $request = Request::forge('CNV-updateCorrespondance', $data);

        return self::sendRequest($request);
    }

    public function deleteConvention(int $id): Response
    {
        $data    = [
            "deleteConvention" => [
                "id" => $id,
            ],
        ];
        $request = Request::forge('CNV-deleteConvention', $data);

        return self::sendRequest($request);
    }

    public function deleteRegroupement(int $id): Response
    {
        $data    = [
            "deleteRegroupement" => [
                "id" => $id,
            ],
        ];
        $request = Request::forge('CNV-deleteRegroupement', $data);

        return self::sendRequest($request);
    }

    public function deleteCorrespondance(int $id): Response
    {
        $data    = [
            "deleteCorrespondance" => [
                "id" => $id,
            ],
        ];
        $request = Request::forge('CNV-deleteCorrespondance', $data);

        return self::sendRequest($request);
    }

    public function importConventionsRegroupementsByPS(int $mode, ?int $dest_jfse_id, ?int $dest_group_id): Response
    {
        $data = [
            "importConventionsRegroupementsByPS" => [
                "mode" => $mode,
            ],
        ];

        if ($dest_jfse_id !== null) {
            $data["importConventionsRegroupementsByPS"]["idJfseDest"] = $dest_jfse_id;
        } else {
            $data["importConventionsRegroupementsByPS"]["idEtablissement"] = $dest_group_id;
        }

        $request = Request::forge('CNV-importConventionsRegroupementsByPS', $data);

        return self::sendRequest($request);
    }

    public function importFichierBin(string $file_binary): Response
    {
        $data    = [
            "importFichierBin" => [
                "binaire" => $file_binary,
            ],
        ];
        $request = Request::forge('CNV-importFichierBin', $data);

        return self::sendRequest($request);
    }

    public function importFichiersZip(array $file_binaries, ?int $jfse_id): Response
    {
        $data  = [
            "importFichiersZip" => [
                "lstFichiers" => [],
            ],
        ];
        $files = [];

        foreach ($file_binaries as $key => $file_binary) {
            $file = [
                "binaire" => $file_binary,
            ];
            if ($jfse_id !== null) {
                $file["idJfse"] = $jfse_id;
            }
            $files[] = $file;
        }
        $data["importFichiersZip"]["lstFichiers"] = $files;

        $request = Request::forge('CNV-importFichiersZip', $data);
        $request->setForceObject(false);

        return self::sendRequest($request);
    }

    public function uploadFichiersCsv(string $file_binary, ?int $jfse_id): Response
    {
        $data = [
            "uploadFichierConventionCSV" => [
                "binaire" => $file_binary,
            ],
        ];

        if ($jfse_id !== null) {
            $data["uploadFichierConventionCSV"]["idJfse"] = $jfse_id;
        }

        $request = Request::forge('CNV-uploadFichierConventionCSV', $data);

        return self::sendRequest($request);
    }

    public function listConventionsToInstall(string $file_name, int $jfse_id): Response
    {
        $data    = [
            "getListeConventionsAInstaller" => [
                "nomFichier" => $file_name,
                "idJfse"     => $jfse_id,
            ],
        ];
        $request = Request::forge('CNV-getListeConventionsAInstaller', $data);

        return self::sendRequest($request);
    }

    public function updateConventionsViaCsv(string $file_name, int $jfse_id): Response
    {
        $data    = [
            "updateConventionsViaCSV" => [
                "nomFichier" => $file_name,
                "idJfse"     => $jfse_id,
            ],
        ];
        $request = Request::forge('CNV-updateConventionsViaCSV', $data);

        return self::sendRequest($request);
    }

    public function deleteFichierConventions(string $file_name, int $jfse_id): Response
    {
        $data    = [
            "deleteFichierConventions" => [
                "nomFichier" => $file_name,
                "idJfse"     => $jfse_id,
            ],
        ];
        $request = Request::forge('CNV-deleteFichierConventions', $data);

        return self::sendRequest($request);
    }
}
