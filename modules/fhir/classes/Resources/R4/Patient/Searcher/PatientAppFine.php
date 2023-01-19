<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Searcher;

use Exception;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterDate;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;
use Ox\Interop\Ihe\CPDQm;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class PatientAppFine implements DelegatedObjectSearcherInterface
{
    private int $total = 0;

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CPDQm::class];
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
            new SearchParameterString('family'),
            new SearchParameterString('given'),
            new SearchParameterDate('birthdate'),
            new SearchParameterString('email'),
        );
    }

    /**
     * @param CFHIRResource $resource
     * @param string        $limit
     *
     * @return array
     * @throws ApiRequestException
     * @throws Exception
     */
    public function search(CFHIRResource $resource, string $limit): array
    {
        $patient = new CPatient();
        $ds      = $patient->getDS();
        $where   = $this->getWhere($resource, $ds);

        $group_by = 'patient_user.patient_id';
        $ljoin    = [
            'patient_user' => 'patient_user.patient_id = patients.patient_id',
            'users'        => 'patient_user.user_id = users.user_id'
        ];

        $this->total = $patient->countListGroupBy($where, null, $group_by, $ljoin);

        return $patient->loadList($where, 'patient_id', $limit, $group_by, $ljoin);
    }

    /**
     * @param CFHIRResource  $resource
     * @param CSQLDataSource $ds
     *
     * @return array
     * @throws ApiRequestException
     */
    private function getWhere(CFHIRResource $resource, CSQLDataSource $ds): array
    {
        $where = [];

        // Last name
        if ($family_param = $resource->getParameterSearch('family')) {
            $where[] = $family_param->getSql('nom', $ds);
        }

        // First name
        if ($given_param = $resource->getParameterSearch('given')) {
            $where[] = $given_param->getSql('prenom', $ds);
        }

        // Birthdate
        if ($parameter_birthday = $resource->getParameterSearch('birthdate')) {
            $where[] = $parameter_birthday->getSql('naissance', $ds);
        }

        // email ==> username
        if ($email_param = $resource->getParameterSearch('email')) {
            $email_param->setOperator(RequestFilter::FILTER_EQUAL);
            $where[] = $email_param->getSql('users.user_username', $ds);
        }

        return $where;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
