<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\InsuranceType\AbstractInsurance;
use Ox\Mediboard\Jfse\Mappers\InsuranceTypeMapper;

/**
 * Class InsuranceTypeClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class InsuranceTypeClient extends AbstractApiClient
{
    public const NO_INVOICE_ERROR = 2001;

    /**
     * @return Response
     */
    public function getAllTypes(): Response
    {
        $request = Request::forge('NAT-getListeNatureAssurance', []);

        return self::sendRequest($request);
    }

    /**
     * @param AbstractInsurance $insurance_type
     * @param string            $invoice_id
     *
     * @return Response
     */
    public function save(AbstractInsurance $insurance_type, string $invoice_id): Response
    {
        $mapper        = new InsuranceTypeMapper();
        $prepared_data = $mapper->getArrayFromInsuranceType($insurance_type, $invoice_id);

        $request = Request::forge('FDS-setNatureAssurance', $prepared_data);

        return self::sendRequest($request);
    }
}
