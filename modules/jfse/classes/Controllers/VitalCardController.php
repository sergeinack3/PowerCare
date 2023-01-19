<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Exception;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CAppUI;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\Comparator;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvAcquisitionModeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvContext;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvService;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\PatientBeneficiaryComparator;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCardService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CPatientVitalCard;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VitalCardController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "beneficiaries"                   => [
            "method" => "beneficiaries",
        ],
        "beneficiaries/show"              => [
            "method" => "showBeneficiaries",
        ],
        "beneficiaries/select"            => [
            "method"  => "saveSelectedBeneficiary",
            "request" => "beneficiarySelectRequest",
        ],
        "beneficiaries/store/confirm"     => [
            "method"  => "comparePatientFieldsStore",
            "request" => "beneficiarySelectRequest",
        ],
        "beneficiaries/store/silent"      => [
            "method" => "silentUpdateFromVitalCard",
        ],
        "beneficiaries/infos"             => [
            "method" => "getBeneficiaryInfo",
        ],
        "unlink"                          => [
            "method" => "unlinkPatientVitalCard",
        ],
        "beneficiaries/identity/store"    => [
            "method"  => "storeIdentity",
            "request" => "beneficiarySelectRequest",
        ],
        "beneficiaries/identity/confirm"  => [
            "method"  => "confirmStoreIdentity",
            "request" => "beneficiarySelectRequest",
        ],
        'beneficiary/get/json'            => [
            'method' => 'getBeneficiaryData',
        ],
        'apCv/get'                        => [
            'method' => 'getBeneficiariesFromApCv',
        ],
        'apCv/getFromCache'               => [
            'method'  => 'getBeneficiariesFromApCvInCache',
            'request' => 'getBeneficiariesFromApCvInCacheRequest',
        ],
        'apCv/renewApCvContextForInvoice' => [
            'method' => 'renewApCvContextForInvoice',
        ],
        'apCv/emptyCache'                 => [
            'method'  => 'emptyApCVCache',
            'request' => 'emptyRequest',
        ],
    ];

    /** @var VitalCardService */
    private $service;

    /** @var ApCvService */
    private $apcv_service;

    /** @var VitalCard */
    private $vital_card;

    public function __construct(string $route, VitalCardService $service = null, ApCvService $apcv_service = null)
    {
        parent::__construct($route);

        $this->service      = $service ?? new VitalCardService();
        $this->apcv_service = $apcv_service ?? new ApCvService();
    }


    public static function getRoutePrefix(): string
    {
        return 'vitalCard';
    }

    /**
     * @throws Exception
     */
    public function beneficiariesRequest(): Request
    {
        return new Request(['invoice_context' => (bool)CView::post('invoice_context', 'bool default|0')]);
    }

    /**
     * @route vitalCard/beneficiaries
     */
    public function beneficiaries(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();
        $vital_card = $this->service->read($request->get('invoice_context'));

        $data = [
            "beneficiaries" => $vital_card->getBeneficiaries(),
            "nir"           => $vital_card->getFullNir(),
            'cps_absent'    => (int)$vital_card->getCpsAbsent(),
        ];

        return new JsonResponse($data);
    }

    /**
     * @throws Exception
     */
    public function showBeneficiariesRequest(): Request
    {
        $data = [
            'cps_absent'      => (bool)CView::post('cps_absent', 'bool default|0'),
            "nir"             => CView::post("nir", "str notNull"),
            "action"          => CView::post("action", "str notNull"),
            "patient_id"      => CView::post("patient_id", "ref class|CPatient"),
            'apcv'            => intval(CView::post('apcv', 'bool default|0')),
            'consultation_id' => intval(CView::post('consultation_id', 'num')),
        ];

        return new Request([], $data);
    }

    /**
     * @route vitalCard/beneficiaries/show
     */
    public function showBeneficiaries(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromMediuser();

        if ($request->get('apcv')) {
            $patients = $this->apcv_service->getPatients();
        } else {
            $patients = $this->service->getPatientsFromNir($request->get('nir'));
        }

        $data = [
            "patients"        => $patients,
            "mb_patient_id"   => $request->get('patient_id'),
            "nir"             => $request->get('nir'),
            "action"          => $request->get('action'),
            'cps_absent'      => $request->get('cps_absent'),
            'apcv'            => $request->get('apcv'),
            'consultation_id' => $request->get('consultation_id'),
        ];

        return new SmartyResponse('vital_card/select_beneficiary', $data);
    }

    /**
     * @route vitalCard/beneficiaries/select
     */
    public function saveSelectedBeneficiary(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();
        $data = [
            "quality"    => $request->get('quality'),
            "birth_rank" => $request->get('birth_rank'),
            "birth_date" => $request->get('birth_date'),
            "nir"        => $request->get('nir'),
            'apcv'       => $request->get('apcv'),
        ];

        $patient = CPatient::findOrNew($request->get('patient_id'));
        /* If the patient is linked to a CJfsePatient with a different NIR */
        if (
            $patient->_id && !CJfsePatient::patientIsLinkedToNir($patient, $data['nir'])
            && CJfsePatient::isPatientLinked($patient) && $patient->_annees < 18
        ) {
            if ($data['apcv']) {
                $card = $this->apcv_service->getVitalCard();
            } else {
                $card = $this->getVitalCardFromRequest($request);
            }

            $beneficiary = $this->getBeneficiaryFromVitalCard($request, $card);

            $this->service->storePatientFromBeneficiary($patient, $card, $beneficiary, $beneficiary->getPatient());
        }

        LayeredCache::getCache(LayeredCache::INNER_OUTER)->set(
            'Jfse-VitalCardService-vitalReading-' . CMediusers::get()->_id,
            $data
        );

        return new JsonResponse('', 204);
    }

    /**
     * @throws Exception
     */
    public function beneficiarySelectRequest(): Request
    {
        $data = [
            "nir"             => CView::post("nir", "str notNull"),
            "birth_date"      => CView::post("birth_date", "str notNull"),
            "birth_rank"      => CView::post("birth_rank", "str notNull"),
            "quality"         => CView::post("quality", "str notNull"),
            "patient_id"      => CView::post("patient_id", "ref class|CPatient"),
            "action"          => CView::post("action", "str"),
            /* Ensure that in case of the creation of a FSE, the consultation's id is passed along */
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation'),
            'apcv'            => (int)CView::post('apcv', 'bool default|0'),
        ];

        return new Request([], $data);
    }

    /**
     * @route vitalCard/beneficiaries/store/confirm
     * @throws Exception
     */
    public function comparePatientFieldsStore(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromMediuser();

        if ($request->get('apcv')) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->getVitalCardFromRequest($request);
        }

        $beneficiary = $this->getBeneficiaryFromVitalCard($request, $card);
        $patient     = $beneficiary->getPatient();

        $data_cache = [
            "quality"    => $beneficiary->getQuality(),
            "birth_rank" => $patient->getBirthRank(),
            "birth_date" => $patient->getBirthDate(),
            "nir"        => $card->getFullNir(),
        ];

        LayeredCache::getCache(LayeredCache::INNER_OUTER)->set(
            'Jfse-VitalCardService-vitalReading-' . CMediusers::get()->_id,
            $data_cache
        );

        $data = [
            "compare"         => [
                "before"      => [
                    "first_name" => '',
                    "last_name"  => '',
                    "birth_date" => '',
                    "nir"        => '',
                ],
                "after"       => [
                    "first_name" => $patient->getFirstName(),
                    "last_name"  => $patient->getLastName(),
                    "birth_date" => $patient->getBirthDate(),
                    "nir"        => $beneficiary->getFullCertifiedNir() ?: $card->getFullNir(),
                ],
                'differences' => [
                    'first_name' => true,
                    'last_name'  => true,
                    'birth_date' => true,
                    'nir'        => true,
                ],
            ],
            "beneficiary"     => CPatientVitalCard::getFromEntity($beneficiary),
            "beneficiary_nir" => $card->getFullNir(),
            "action"          => $request->get('action'),
            'consultation_id' => $request->get('consultation_id'),
            'apcv'            => $request->get('apcv'),
        ];

        try {
            $mb_patient                              = CPatient::findOrFail($request->get('patient_id'));
            $data['compare']['before']['first_name'] = $mb_patient->prenom;
            $data['compare']['before']['last_name']  = $mb_patient->nom;
            $data['compare']['before']['birth_date'] = $mb_patient->naissance;
            $data['compare']['before']['nir']        = $mb_patient->matricule;
            $data['patient_id']                      = $mb_patient->_id;

            $data['compare']['differences']['first_name'] =
                strtolower($mb_patient->prenom) !== strtolower($patient->getFirstName());
            $data['compare']['differences']['last_name']  =
                strtolower($mb_patient->nom) !== strtolower($patient->getLastName());
            $data['compare']['differences']['birth_date'] = $mb_patient->naissance !== $patient->getBirthDate();

            if (
                (!$beneficiary->getFullCertifiedNir() && $mb_patient->matricule)
                || (!$mb_patient->matricule && $beneficiary->getFullCertifiedNir())
                || ($mb_patient->matricule && $beneficiary->getFullCertifiedNir()
                    && $mb_patient->matricule !== $beneficiary->getFullCertifiedNir())
            ) {
                $data['compare']['differences']['nir'] = true;
            } else {
                $data['compare']['differences']['nir'] = false;
            }
        } catch (CMbModelNotFoundException $e) {
            $data['patient_id'] = null;
        }

        return new SmartyResponse('vital_card/compare_patient', $data);
    }

    private function getVitalCardFromRequest(Request $request): VitalCard
    {
        // Avoid fetching to many times
        if ($this->vital_card) {
            return $this->vital_card;
        }

        $patient = $this->makePatientFromRequest($request);

        $nir              = $request->get('nir');
        $quality          = $request->get('quality');
        $this->vital_card = $this->service->getVitalCardInfos($nir, $patient, $quality);

        return $this->vital_card;
    }

    private function makePatientFromRequest(Request $request): Patient
    {
        return Patient::hydrate(
            [
                "birth_date" => $request->get('birth_date'),
                "birth_rank" => $request->get('birth_rank'),
            ]
        );
    }

    private function getBeneficiaryFromRequest(Request $request): Beneficiary
    {
        $quality = $request->get('quality');
        $patient = $this->makePatientFromRequest($request);
        $card    = $this->getVitalCardFromRequest($request);

        return $card->getSelectedBeneficiary($patient->getBirthDate(), $patient->getBirthRank(), $quality);
    }

    private function getBeneficiaryFromVitalCard(Request $request, VitalCard $card): Beneficiary
    {
        $quality = $request->get('quality');
        $patient = $this->makePatientFromRequest($request);

        return $card->getSelectedBeneficiary($patient->getBirthDate(), $patient->getBirthRank(), $quality);
    }

    public function silentUpdateFromVitalCardRequest(): Request
    {
        $data = [
            "patient_id" => CView::post("patient_id", "ref class|CPatient notNull"),
            'apcv'       => (int)CView::post('apcv', 'bool default|0'),
        ];

        return new Request([], $data);
    }

    /**
     * @route beneficiaries/store/silent
     * @throws Exception
     */
    public function silentUpdateFromVitalCard(Request $request): JsonResponse
    {
        $mb_patient = CPatient::findOrFail($request->get('patient_id'));

        $quality = $mb_patient->qual_beneficiaire;
        $patient = Patient::hydrate(
            [
                "birth_date" => $mb_patient->naissance,
                "birth_rank" => $mb_patient->rang_naissance,
            ]
        );

        if ($request->get('apcv')) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->service->getVitalCardInfos($mb_patient->matricule, $patient, $quality);
        }

        $beneficiary = $card->getSelectedBeneficiary($patient->getBirthDate(), $patient->getBirthRank(), $quality);

        $this->service->storePatientFromBeneficiary($mb_patient, $card, $beneficiary, null);

        return new JsonResponse('', 204);
    }

    /**
     * @throws Exception
     */
    public function unlinkPatientVitalCardRequest(): Request
    {
        $data = [
            "link_id" => CView::post("link_id", "ref class|CJfsePatient"),
        ];

        return new Request([], $data);
    }

    /**
     * @route vitalCard/unlink
     * @throws Exception
     */
    public function unlinkPatientVitalCard(Request $request): SmartyResponse
    {
        $link  = CJfsePatient::findOrFail($request->get('link_id'));
        $error = $link->delete();

        if ($error) {
            return SmartyResponse::message(
                "VitalCardController-Error when deleting the link",
                SmartyResponse::MESSAGE_ERROR
            );
        }

        return SmartyResponse::message("VitalCardController-Link deleted", SmartyResponse::MESSAGE_SUCCESS);
    }

    public function getBeneficiaryInfoRequest(): Request
    {
        $data = [
            "patient_id" => CView::post("patient_id", "ref class|CPatient notNull"),
        ];

        return new Request([], $data);
    }

    /**
     * @route vitalCard/beneficiaries/infos
     * @throws Exception
     */
    public function getBeneficiaryInfo(Request $request): JsonResponse
    {
        $link             = new CJfsePatient();
        $link->patient_id = $request->get('patient_id');
        $link->loadMatchingObjectEsc();

        if (!$link->_id) {
            return new JsonResponse();
        }

        $patient = Patient::hydrate(["birth_date" => $link->birth_date, "birth_rank" => $link->birth_rank]);

        if ($request->get('apcv')) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->service->getVitalCardInfos($link->nir, $patient, $link->quality);
        }

        $beneficiary = $card->getSelectedBeneficiary($link->birth_date, $link->birth_rank, $link->quality);

        $data = [
            "first_name"      => $beneficiary->getPatient()->getFirstName(),
            "last_name"       => $beneficiary->getPatient()->getLastName(),
            "nir"             => $card->getFullNir(),
            "acs"             => $beneficiary->getAcs(),
            "regime"          => $card->getInsured()->getRegimeLabel(),
            "open_amo_rights" => $beneficiary->hasOpenAmoRights(),
            "link_id"         => $link->_id,
        ];

        return new JsonResponse($data);
    }

    /**
     * @route beneficiaries/identity/confirm
     */
    public function confirmStoreIdentity(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $mb_patient = CPatient::findOrFail($request->get('patient_id'));

        if ($request->get('apcv') && ApCvService::isApCvAuthorized()) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->getVitalCardFromRequest($request);
        }

        $beneficiary = $this->getBeneficiaryFromVitalCard($request, $card);

        $identical_beneficiary = Comparator::compare(new PatientBeneficiaryComparator(), $beneficiary, $mb_patient);
        $identical             = (bool)($card->getFullNir() === $mb_patient->matricule && $identical_beneficiary);

        return new JsonResponse(['must_confirm' => !$identical]);
    }

    /**
     * @route beneficiaries/identity/store
     */
    public function storeIdentity(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $mb_patient = CPatient::findOrFail($request->get('patient_id'));

        if ($request->get('apcv')) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->getVitalCardFromRequest($request);
        }

        $beneficiary = $this->getBeneficiaryFromVitalCard($request, $card);

        $this->service->storePatientFromBeneficiary($mb_patient, $card, $beneficiary, $beneficiary->getPatient());

        return new JsonResponse('Updated', 200);
    }

    /**
     * @route beneficiary/get/json
     */
    public function getBeneficiaryData(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $patient = Patient::hydrate([
                                        'birth_date' => $request->get('birth_date'),
                                        'birth_rank' => $request->get('birth_rank'),
                                    ]);

        if ($request->get('apcv')) {
            $card = $this->apcv_service->getVitalCard();
        } else {
            $card = $this->service->getVitalCardInfos($request->get('nir'), $patient, $request->get('quality'));
        }

        $beneficiary = $card->getSelectedBeneficiary(
            $patient->getBirthDate(),
            $patient->getBirthRank(),
            $request->get('quality')
        );

        if ($beneficiary) {
            $beneficiary->setInsured($card->getInsured());
            $data = $beneficiary;
        } else {
            $data = [
                'error' => 'CBeneficiary-error-not_found',
            ];
        }

        return new JsonResponse($data);
    }

    public function getBeneficiaryDataRequest(): Request
    {
        $data = [
            "nir"        => CView::post("nir", "str notNull"),
            "birth_date" => CView::post("birth_date", "str notNull"),
            "birth_rank" => CView::post("birth_rank", "str notNull"),
            "quality"    => CView::post("quality", "str notNull"),
            'apcv'       => (int)CView::post('apcv', 'bool default|0'),
        ];

        return new Request([], $data);
    }

    /**
     * Get a new ApCVContext from the service, from a NFC reader or the QRCode content
     *
     * @route vitalCard/apCv/get
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBeneficiariesFromApCv(Request $request): JsonResponse
    {
        if ($request->get('user_id')) {
            Utils::setJfseUserIdFromMediuser(CMediusers::get($request->get('user_id')));
        } elseif ($request->get('consultation_id')) {
            Utils::setJfseUserIdFromConsultation(CConsultation::findOrNew($request->get('consultation_id')));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        $mode = ApCvAcquisitionModeEnum::from($request->get('mode'));

        $vital_card = $this->apcv_service->generateApCvContext($mode, $request->get('context'));

        $data = [
            "beneficiaries" => $vital_card->getBeneficiaries(),
            "nir"           => $vital_card->getFullNir(),
            'cps_absent'    => (int)$vital_card->getCpsAbsent(),
            'apcv'          => true,
        ];

        return new JsonResponse($data);
    }

    public function getBeneficiariesFromApCvRequest(): Request
    {
        return new Request([
                               'mode'            => (int)CView::post(
                                   'mode',
                                   ApCvAcquisitionModeEnum::getProp() . ' notNull'
                               ),
                               'context'         => CView::post('context', 'str'),
                               'user_id'         => CView::post('user_id', 'ref class|CMediusers'),
                               'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation'),
                           ]);
    }

    /**
     * Get the ApCVContext data stored in cache, if any
     *
     * @route vitalcard/apCv/getFromCache
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBeneficiariesFromApCvInCache(Request $request): JsonResponse
    {
        if ($request->get('user_id')) {
            Utils::setJfseUserIdFromMediuser(CMediusers::get($request->get('user_id')));
        } elseif ($request->get('consultation_id')) {
            Utils::setJfseUserIdFromConsultation(CConsultation::findOrNew($request->get('consultation_id')));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        $vital_card = $this->apcv_service->getVitalCard();

        if ($vital_card) {
            $data = [
                "beneficiaries" => $vital_card->getBeneficiaries(),
                "nir"           => $vital_card->getFullNir(),
                'cps_absent'    => (int)$vital_card->getCpsAbsent(),
                'apcv'          => true,
            ];
        } else {
            $data = ['error' => CAppUI::tr('ApCVProfile-error-no_apcv_context_in_cache')];
        }

        return new JsonResponse($data);
    }

    public function getBeneficiariesFromApCvInCacheRequest(): Request
    {
        return new Request([
                               'user_id'         => CView::post('user_id', 'ref class|CMediusers'),
                               'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation'),
                           ]);
    }

    public function renewApCvContextForInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $mode = ApCvAcquisitionModeEnum::from($request->get('mode'));

        $context = $this->apcv_service->renewApCvContextForInvoice(
            $request->get('invoice_id'),
            $mode,
            $request->get('context')
        );

        $result = false;
        if ($context instanceof ApCvContext) {
            $result = true;
        }

        return new JsonResponse(['result' => $result]);
    }

    public function renewApCvContextForInvoiceRequest(): Request
    {
        return new Request([
                               'invoice_id' => CView::post('invoice_id', 'str'),
                               'mode'       => (int)CView::post(
                                   'mode',
                                   ApCvAcquisitionModeEnum::getProp() . ' notNull'
                               ),
                               'context'    => CView::post('context', 'str'),
                           ]);
    }

    /**
     * @return SmartyResponse
     * @todo Remove the method after the Certification
     *
     */
    public function emptyApCVCache(): SmartyResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $this->apcv_service->deleteDataInCache();
        $this->apcv_service->deleteContextInCache();

        return new SmartyResponse('inc_message', [
            'type'    => 'info',
            'message' => 'Contexte ApCV en cache supprimé',
        ]);
    }
}
