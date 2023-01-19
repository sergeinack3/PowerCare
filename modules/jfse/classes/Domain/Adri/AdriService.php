<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Adri;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\AdriClient;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCardService;
use Ox\Mediboard\Jfse\Exceptions\AdriServiceException;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\ApiMessageException;
use Ox\Mediboard\Jfse\Mappers\AdriMapper;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Patients\CPatient;

class AdriService extends AbstractService
{
    /** @var AdriClient */
    protected $client;

    /** @var VitalCardService */
    private $vital_service;

    /** @var VitalCardMapper */
    private $mapper;

    public function __construct(
        AdriClient $client = null,
        VitalCardService $vital_service = null,
        VitalCardMapper $mapper = null
    ) {
        parent::__construct($client ?? new AdriClient());

        $this->vital_service = $vital_service ?? new VitalCardService();
        $this->mapper        = $mapper ?? new VitalCardMapper();
    }

    public function getInfosInvoiceAdri(string $invoice_id): array
    {
        $this->client->setMessagesHandler([$this, 'handleAdriMessages']);
        $response = $this->client->getInfosInvoiceAdri($invoice_id);

        return $response->getContent(); // TODO return invoice object
    }

    public function getFromCPatient(CPatient $mb_patient): ?Beneficiary
    {
        $jfse_patient = null;
        if (CJfsePatient::isPatientLinked($mb_patient)) {
            $jfse_patient = CJfsePatient::getFromPatient($mb_patient);
            $beneficiary = $this->mapper->getBeneficiaryFromPatientDataModel(
                $jfse_patient,
                true
            );
        } else {
            if (
                !$mb_patient->code_regime || !$mb_patient->matricule
                || !$mb_patient->naissance || !$mb_patient->rang_naissance
            ) {
                $fields = [];
                if (!$mb_patient->code_regime) {
                    $fields[] = 'Régime';
                }
                if (!$mb_patient->matricule) {
                    $fields[] = 'NIR';
                }
                if (!$mb_patient->naissance) {
                    $fields[] = 'Date de naissance';
                }
                if (!$mb_patient->rang_naissance) {
                    $fields[] = 'Rang de naissance';
                }

                throw AdriServiceException::missingMandatoryFields($fields);
            }
            $beneficiary  = $this->mapper->getBeneficiaryFromPatient($mb_patient, true);
        }

        $infos = $this->getInfosAdri($beneficiary);

        return $infos->getVitalCard()->getFirstBeneficiary();
    }

    public function getInfosAdri(Beneficiary $beneficiary): Adri
    {
        $content = $this->client->getInfos($beneficiary)->getContent();

        // Exceptions from the ADRi service
        if (isset($content["exception"])) {
            $error = $content["exception"];
            throw ApiException::apiError(
                "Type " . $error["type"] . ". " . $error["libelle"],
                (int)$error["global"],
                null,
                $error["detail"]
            );
        }

        return (new AdriMapper())->arrayToAdri($content);
    }

    public function handleAdriMessages(Response $response): void
    {
        if ($response->hasMessages()) {
            $messages_to_handle = [];

            foreach ($response->getMessages() as $message) {
                if ($message->getSourceLibrary() === 'TELESERVICE ADRI') {
                    $messages_to_handle[] = $message;
                }
            }

            if (count($messages_to_handle)) {
                throw new ApiMessageException($messages_to_handle);
            }
        }
    }
}
