<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Mappers\HealthInsuranceMapper;

/**
 * Class HealthInsuranceClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class HealthInsuranceClient extends AbstractApiClient
{

    public const NO_FORM_ERROR = 2001;

    /**
     *
     * @param int    $type
     * @param int    $search_mode
     * @param string $name
     * @param array  $ids
     * @param int    $etablissement_id
     *
     * @return Response
     */
    public function search(
        int $type,
        int $search_mode,
        ?string $name,
        ?array $ids,
        ?int $etablissement_id
    ): Response {
        $data = [
            "getListeMutuelles" => [
                "typeOrganisme" => $type,
                "mode"          => $search_mode,
            ],
        ];
        if ($ids !== null) {
            $data["getListeMutuelles"]["lstIdJfse"] = $ids;
        }
        if ($etablissement_id !== null) {
            $data["getListeMutuelles"]["idEtablissement"] = $etablissement_id;
        }
        if ($name !== null) {
            $data["getListeMutuelles"]["nom"] = $name;
        }
        $request = Request::forge('MUT-getListeMutuelles', $data);

        return self::sendRequest($request);
    }

    /**
     * Fonction permettant d ajouter ou modifier une mutuelle
     *
     * @param string $code
     * @param string $name
     * @param array  $ids
     * @param int    $etablissement_id
     *
     * @return Response
     */
    public function save(
        string $code = null,
        string $name = null,
        array $ids = [],
        int $etablissement_id = 0
    ): Response {
        $mapper = new HealthInsuranceMapper();

        $request = Request::forge(
            'MUT-updateMutuelle',
            [$mapper->getArrayFromData($code, $name, $ids, $etablissement_id)]
        );

        return self::sendRequest($request);
    }

    /**
     * Fonction permettant de supprimer une mutuelle
     *
     * @param int    $etablissement_id
     * @param string $code
     * @param array  $ids
     *
     * @return Response
     */
    public function delete(
        string $code = ""
    ): Response {
        $request = Request::forge('MUT-deleteMutuelle', ["deleteMutuelle" => ["code" => $code]]);

        return self::sendRequest($request);
    }
}
