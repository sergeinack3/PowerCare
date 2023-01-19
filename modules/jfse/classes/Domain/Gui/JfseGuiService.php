<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Gui;

use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\ApiClients\CpsClient;
use Ox\Mediboard\Jfse\ApiClients\InvoicingClient;
use Ox\Mediboard\Jfse\ApiClients\JfseGuiClient;
use Ox\Mediboard\Jfse\ApiClients\VitalCardClient;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Exceptions\GuiException;
use Ox\Mediboard\Jfse\Mappers\CpsMapper;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Jfse\Tests\Unit\Mappers\CpsMapperTest;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

final class JfseGuiService extends AbstractService
{
    /** @var JfseGuiClient */
    protected $client;

    public function __construct(JfseGuiClient $client = null)
    {
        parent::__construct($client ?? new JfseGuiClient());
    }

    public function manageUsers(): array
    {
        $response = $this->client->manageUsers();

        return $response->getContent();
    }

    public function manageEstablishments(): array
    {
        $response = $this->client->manageEstablishments();

        return $response->getContent();
    }

    public function settings(): array
    {
        $response = $this->client->settings();

        return $response->getContent();
    }

    public function manageFormula(): array
    {
        $response = $this->client->manageFormula();

        return $response->getContent();
    }

    public function userSettings(int $jfse_id = null): array
    {
        if (!$jfse_id) {
            $jfse_user = CJfseUser::getFromMediuser(CMediusers::get());
            $jfse_id = $jfse_user->jfse_id;
        }

        $user = (new UserManagementService())->getUser($jfse_id);

        $display_substitute = true;
        if ($user->getSubstitutionSession()) {
            $display_substitute = false;
        }
        $response = $this->client->userSettings($display_substitute);

        return $response->getContent();
    }

    public function viewInvoice(string $invoice_id): array
    {
        $response = $this->client->viewInvoice($invoice_id);

        return $response->getContent();
    }

    public function validateInvoice(string $invoice_id): Invoice
    {
        $invoicing_client = new InvoicingClient();
        $invoice = InvoicingMapper::getInvoiceFromResponse($invoicing_client->getDonneesFacture($invoice_id));
        $invoice->validate();

        (new InvoicingService())->updateConsultationAfterInvoiceValidation($invoice);

        return $invoice;
    }

    public function invoiceDashboard(): array
    {
        $response = $this->client->invoiceDashboard();

        return $response->getContent();
    }

    public function scorDashboard(): array
    {
        $response = $this->client->scorDashboard();

        return $response->getContent();
    }

    public function globalTeletransmission(): array
    {
        $response = $this->client->globalTeletransmission();

        return $response->getContent();
    }

    public function manageNoemieReturns(): array
    {
        $response = $this->client->manageNoemieReturns();

        return $response->getContent();
    }

    public function manageTLA(): array
    {
        $response = $this->client->manageTLA();

        return $response->getContent();
    }

    public function moduleVersion(): array
    {
        $response = $this->client->moduleVersion();

        return $response->getContent();
    }

    public function apiVersion(): array
    {
        $response = $this->client->apiVersion();

        return $response->getContent();
    }

    public function readCps(): array
    {
        $client = new CpsClient();
        $response = $client->read();

        $card = CpsMapper::getCardFromReadResponse($response);

        $data = ['success' => true];
        if ($card->countSituations() > 1) {
            $response = $this->client->selectCpsSituation($response->getContent());

            $data = array_merge($data, $response->getContent());
        } else {
            $data['message'] = 'CPS ' . $card->getFirstName() . ' ' . $card->getLastName() . ' lue';
        }

        return $data;
    }

    public function readVitalCard(CConsultation $consultation): array
    {
        $client = new VitalCardClient();
        $response = $client->read();
        $vital = (new VitalCardMapper())->arrayToVitalCard($response->getContent());

        $data = ['success' => true];
        if ($vital->countBeneficiaries() > 1) {
            $response = $this->client->selectVitalBeneficiary($response->getContent());

            $data = array_merge($data, $response->getContent());
        } else {
            $beneficiary = $vital->getFirstBeneficiary();

            if ($this->linkBeneficiaryToPatient($consultation->loadRefPatient(), $beneficiary, $vital->getFullNir())) {
                $data['message'] = 'Carte Vitale ' . $beneficiary->getPatient()->getFirstName()
                . ' ' . $beneficiary->getPatient()->getLastName() . ' lue';
            } else {
                $data['success'] = false;
                    $data['error'] = 'Le bénéficiaire est déjà associé à un autre patient!';
            }
        }

        return $data;
    }

    public function handleVitalReading(CConsultation $consultation, array $data): array
    {
        if (!isset($data['method']['parameters']['cv']) || !isset($data['method']['output']['selection'])) {
            throw GuiException::invalidVitalData();
        }

        $vital = (new VitalCardMapper())->arrayToVitalCard($data['method']['parameters']['cv']);

        $beneficiary = $vital->selectBeneficiary($data['method']['output']['selection']);

        $data = ['success' => true];
        if ($this->linkBeneficiaryToPatient($consultation->loadRefPatient(), $beneficiary, $vital->getFullNir())) {
            $data['message'] = 'Carte Vitale ' . $beneficiary->getPatient()->getFirstName()
                . ' ' . $beneficiary->getPatient()->getLastName() . ' lue';
        } else {
            $data['success'] = false;
            $data['error'] = 'Le bénéficiaire est déjà associé à un autre patient!';
        }

        return $data;
    }

    private function linkBeneficiaryToPatient(CPatient $patient, Beneficiary $beneficiary, string $nir): bool
    {
        $result = false;
        $patient_link = CJfsePatient::getFromPatient($patient);
        if (
            $patient_link && $patient_link->nir == $nir
            && $patient_link->birth_date == $beneficiary->getPatient()->getBirthDate()
            && $patient_link->birth_rank == $beneficiary->getPatient()->getBirthRank()
            && $patient_link->quality == $beneficiary->getQuality()
        ) {
                $result = true;
        } elseif (!$patient_link) {
            $patient_link = new CJfsePatient();
            $patient_link->patient_id = $patient->_id;
            $patient_link->nir = $nir;
            $patient_link->birth_date = $beneficiary->getPatient()->getBirthDate();
            $patient_link->birth_rank = $beneficiary->getPatient()->getBirthRank();
            $patient_link->quality = $beneficiary->getQuality();

            if (!$patient_link->store()) {
                $result = true;
            }
        }

        return $result;
    }
}
