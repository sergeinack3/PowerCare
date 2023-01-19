<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Searcher;

use Exception;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterToken;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterURI;
use Ox\Interop\Ihe\CPIXm;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class PatientPIXm implements DelegatedObjectSearcherInterface
{
    private int $total = 0;

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CPIXm::class];
    }

    /**
     * @inheritDoc
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourcePatient::RESOURCE_TYPE];
    }

    /**
     * @return array
     */
    public function registerSupportedParameters(SearchParameterList $parameter_list): void
    {
        $parameter_list->add(
            new SearchParameterToken('sourceIdentifier'),
            new SearchParameterURI('targetSystem'),
        );
    }

    /**
     * @inheritDoc
     * @throws ApiRequestException
     * @throws Exception
     */
    public function search(CFHIRResource $resource, string $limit): array
    {
        if (!$sourceIdentifier = $this->getParameterSearch('sourceIdentifier')) {
            throw new CFHIRException("Invalid number of source identifiers");
        }

        return [];

        $patient = new CPatient();
        $ds      = $patient->getDS();
        $where   = $this->getWhere($resource, $ds);

        $group_by    = 'patient_user.patient_id';
        $this->total = $patient->countList($where, $group_by);

        return $patient->loadList($where, 'patient_id', $limit, $group_by);
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param CFHIRResource  $resource
     * @param CSQLDataSource $ds
     *
     * @return array
     * @throws ApiRequestException
     * @throws Exception
     */
    protected function getWhere(CFHIRResource $resource, CSQLDataSource $ds): array
    {
        return [];
    }
}
