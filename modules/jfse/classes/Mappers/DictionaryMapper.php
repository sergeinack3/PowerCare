<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;

class DictionaryMapper extends AbstractMapper
{
    public function getRegimesFromResponse(Response $response): array
    {
        $regimes = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $data) {
            $regimes[] = [
                'code'  => CMbArray::get($data, 'code', ''),
                'label' => CMbArray::get($data, 'libelle', ''),
            ];
        }

        return $regimes;
    }

    public function getOrganismsFromResponse(Response $response): array
    {
        $organisms = [];
        foreach (CMbArray::get($response->getContent(), 'lstOrganismes', []) as $data) {
            $organisms[] = [
                'regime_code' => CMbArray::get($data, 'codeRegime', ''),
                'fund_code'   => CMbArray::get($data, 'codeCaisse', ''),
                'center_code' => CMbArray::get($data, 'codeCentre', ''),
                'label'       => CMbArray::get($data, 'nomCaisse', ''),
                'regime_label' => CMbArray::get($data, 'nomRegime', ''),
            ];
        }

        return $organisms;
    }

    public function getManagingCodesFromResponse(Response $response): array
    {
        $codes = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $data) {
            $codes[] = [
                'code'  => CMbArray::get($data, 'code', ''),
                'label' => CMbArray::get($data, 'libelle', ''),
                'id'    => CMbArray::get($data, 'id', ''),
            ];
        }

        return $codes;
    }
}
