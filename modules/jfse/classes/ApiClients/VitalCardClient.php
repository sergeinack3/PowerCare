<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTimeImmutable;
use Exception;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

class VitalCardClient extends AbstractApiClient
{
    public function read(?int $cps_code = null): Response
    {
        $data = ["dateLecture" => (new DateTimeImmutable())->format('Ymd')];

        if ($cps_code) {
            $data['codePorteur'] = $cps_code;
        }

        return self::sendRequest(Request::forge("DVF-lire", $data), 60);
    }

    /**
     * @throws Exception
     */
    public function getFromDB(string $nir, string $birth_date, string $birth_rank, string $quality): Response
    {
        $data = [
            "getDonneesVitale" => [
                "immatriculation" => $nir,
                "dateNaissance"   => str_replace('-', '', $birth_date),
                "rangGemellaire"  => $birth_rank,
                "qualite"         => (int)$quality,
            ],
        ];

        return self::sendRequest(Request::forge('DVF-getDonneesVitale', $data), 60);
    }
}
