<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Searcher;

use Exception;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Utilities\SearchParameters\ParameterToken;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterDate;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterToken;
use Ox\Interop\Ihe\CPDQm;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class PatientPDQm implements DelegatedObjectSearcherInterface
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
            new SearchParameterToken('identifier'),
            new SearchParameterString('family'),
            new SearchParameterString('given'),
            new SearchParameterDate('birthdate'),
            new SearchParameterToken('email'),
            new SearchParameterString('address'),
            new SearchParameterString('address-city'),
            new SearchParameterString('address-postalcode'),
            new SearchParameterToken('gender'),
        );
    }

    /**
     * @inheritDoc
     * @throws ApiRequestException
     * @throws Exception
     */
    public function search(CFHIRResource $resource, string $limit): array
    {
        $patient = new CPatient();
        $ds      = $patient->getDS();
        $ljoin   = [];
        $where   = $this->getWhere($resource, $ds, $ljoin);

        $group_by    = empty($ljoin) ? null : 'patients.patient_id';

        $this->total = $group_by ? $patient->countListGroupBy($where, null, $group_by, $ljoin)
            : $patient->countList($where, null, $ljoin);

        return $patient->loadList($where, 'patient_id', $limit, $group_by, $ljoin);
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
     * @param array          &$ljoin
     *
     * @return array
     * @throws ApiRequestException
     */
    protected function getWhere(CFHIRResource $resource, CSQLDataSource $ds, array &$ljoin): array
    {
        $where = [];
        if (CAppUI::isCabinet()) {
            $function_id          = CMediusers::get()->function_id;
            $where["function_id"] = "= '$function_id'";
        }

        if ($family_param = $resource->getParameterSearch('family')) {
            $whereOr   = [];
            $whereOr[] = $family_param->getSql('nom', $ds);
            $whereOr[] = $family_param->getSql('nom_jeune_fille', $ds);

            $where[] = '(' . implode(" OR ", $whereOr) . ")";
        }

        /** @var SearchParameter[] $prenoms_params */
        if ($prenoms_params = $resource->getSearchParameters('given')) {
            /** @var SearchParameter $prenom_params */
            $prenom_params = CMbArray::extract($prenoms_params, 0);
            $where[]       = $prenom_params->getSql('prenom', $ds);

            foreach ($prenoms_params as $search_parameter) {
                $where[] = $search_parameter->getSql('prenoms', $ds);
            }
        }

        // Birthdate
        if ($parameter_birthday = $resource->getParameterSearch('birthdate')) {
            $where[] = $parameter_birthday->getSql('naissance', $ds);
        }

        // Gender
        if ($gender_parameter = $resource->getParameterSearch('gender')) {
            $genger  = $gender_parameter->getValue()->getCode() === "female" ? 'f' : 'm';
            $where[] = $gender_parameter->getSql('sexe', $ds, $genger);
        }

        // City
        if ($city_parameter = $resource->getParameterSearch('address-city')) {
            $where[] = $city_parameter->getSql('ville', $ds);
        }

        // Postal code
        if ($postal_parameter = $resource->getParameterSearch('address-postalcode')) {
            $where[] = $postal_parameter->getSql('cp', $ds);
        }

        // Address
        if ($address_parameter = $resource->getParameterSearch('address')) {
            $where[] = $address_parameter->getSql('adresse', $ds);
        }

        // identifier
        if ($identifier_params = $resource->getSearchParameters('identifier')) {
            foreach ($identifier_params as $key => $identifier_param) {
                /** @var ParameterToken $token_value */
                $token_value = $identifier_param->getValue();
                $system      = $token_value->getSystem();
                $code        = $token_value->getCode();
                if (!$system && !$code) {
                    continue;
                }
                $ljoin[] = "id_sante400 AS id$key ON id$key.object_id = patients.patient_id"
                    . "  AND (id$key.object_class = 'CPatient')";

                if ($system) {
                    $domain = new CDomain();
                    $domain->tag = $system->getValue();
                    if (!$domain->loadMatchingObjectEsc()) {
                        continue;
                    }

                    $where[] = $ds->prepare("id$key.tag = ?", $domain->tag);
                }

                if ($code) {
                    $where[] = $ds->prepare("id$key.id400 = ?", $code);
                }

                // todo il faut qu'on puisse inclure les id400 dans la ressource mapper (peut importe le mapper).
                // todo il faut agir à postériori du mapping de ressource
            }
        }

        return $where;
    }
}
