<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use Exception;
use Ox\Components\Cache\LayeredCache;
use Ox\Mediboard\Jfse\Api\Error;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\VitalCardClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CPatientVitalCard;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

class VitalCardService extends AbstractService
{
    /** @var VitalCardClient */
    protected $client;

    /** @var VitalCardMapper */
    protected $mapper;

    public function __construct(VitalCardClient $client = null, VitalCardMapper $mapper = null)
    {
        parent::__construct($client ?? new VitalCardClient());

        $this->mapper = $mapper ?? new VitalCardMapper();
    }

    public function read(bool $invoice_context = false): VitalCard
    {
        if (!$invoice_context) {
            $this->client->setErrorsHandler(function (Response $response): void {
                $error_codes = $response->getErrorCodes();
                foreach ($error_codes as $error_code) {
                    if ($error_code !== 61441) {
                        $error = $response->getError($error_code);
                        $response->removeError($error_code);

                        if ($error_code !== Error::GENERAL_API_ERROR || count($error_codes) === 1) {
                            throw ApiException::apiError(
                                $error->getDescription(),
                                $error->getCode(),
                                $error->getSource(),
                                $error->getDetails()
                            );
                        }
                    }
                }
            });
        }

        if (ApCvService::isApCvAuthorized()) {
            $apcv_service = new ApCvService();
            $apcv_service->destroyApCvContext();
        }

        $response = $this->client->read();

        $vital_card = $this->mapper->arrayToVitalCard($response->getContent());

        /* When the CPS card is absent, we set a flag,
           because only the administrative date of the patients must be returned */
        if ($response->hasError(61441)) {
            $vital_card->setCpsAbsent(true);
        } else {
            $vital_card->setCpsAbsent(false);
        }

        if ($vital_card->hasBeneficiaries()) {
            self::setBeneficiariesInCache($vital_card);
        }

        return $vital_card;
    }

    /**
     * @throws Exception
     */
    public function getVitalCardInfos(string $nir, Patient $patient, string $quality): VitalCard
    {
        $response = $this->client->getFromDB($nir, $patient->getBirthDate(), $patient->getBirthRank(), $quality);
        $content  = $response->getContent();

        return $this->mapper->arrayToVitalCard(
            ["donneescv" => json_decode(utf8_encode($content["donneesCV"]), true)]
        );
    }

    /**
     * @return CPatientVitalCard[]
     */
    public function getPatientsFromNir(string $nir): array
    {
        $patients = [];
        $beneficiaries = VitalCardMapper::getBeneficiariesFromJson(self::getBeneficiariesFromCache($nir));
        foreach ($beneficiaries as $beneficiary) {
            $patients[] = CPatientVitalCard::getFromEntity($beneficiary);
        }

        return $patients;
    }

    public function storePatientFromBeneficiary(
        CPatient $mb_patient,
        VitalCard $card,
        Beneficiary $beneficiary,
        ?Patient $patient,
        bool $from_adri = false
    ): void {
        $builder = new CPatientBuilder($mb_patient, $from_adri);

        if ($patient) {
            $builder->updateIdentity($patient);
        }

        $builder->updateInsuredBeneficiary($card, $beneficiary);
        $error = $builder->getPatient()->store();

        if ($error) {
            throw new Exception($error);
        }
    }

    public function getPatientFromVitalCard(CPatient $patient, CMediusers $user): CPatient
    {
        Utils::setJfseUserIdFromMediuser();
        $cache = LayeredCache::getCache(LayeredCache::INNER_OUTER);
        $card_data = $cache->get("Jfse-VitalCardService-vitalReading-{$user->_id}");

        if ($card_data) {
            if ($card_data['apcv'] && ApCvService::isApCvAuthorized()) {
                $card = (new ApCvService())->getVitalCard();
            } else {
                $card         = $this->getVitalCardInfos(
                    $card_data['nir'],
                    Patient::hydrate($card_data),
                    $card_data['quality']
                );
            }

            if ($card) {
                $beneficiary = $card->getSelectedBeneficiary(
                    $card_data['birth_date'],
                    $card_data['birth_rank'],
                    $card_data['quality']
                );

                $builder = new CPatientBuilder($patient);
                $builder->updateIdentity($beneficiary->getPatient());
                $builder->updateInsuredBeneficiary($card, $beneficiary);
                $patient = $builder->getPatient();

                $cache->delete("Jfse-VitalCardService-vitalReading-{$user->_id}");
            }
        }

        return $patient;
    }

    public static function setBeneficiariesInCache(VitalCard $vital_card): void
    {
        $cache = LayeredCache::getCache(LayeredCache::INNER_OUTER)->withCompressor();
        $cache->set(
            "Jfse-VitalCard-NIR-" . $vital_card->getFullNir(),
            json_encode($vital_card->getBeneficiaries()),
            300
        );
    }

    protected static function getBeneficiariesFromCache(string $nir): ?string
    {
        return LayeredCache::getCache(LayeredCache::INNER_OUTER)->withCompressor()->get("Jfse-VitalCard-NIR-{$nir}");
    }
}
