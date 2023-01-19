<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Jfse\ApiClients\NoemieClient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Jfse\Exceptions\DataModelException;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Mappers\NoemieMapper;

/**
 * A service class that handle the features related to the Noemie and ARLs returns
 */
final class NoemieService extends AbstractService
{
    /** @var NoemieClient The API Client */
    protected $client;

    /**
     * NoemieService constructor.
     *
     * @param NoemieClient|null $client
     */
    public function __construct(NoemieClient $client = null)
    {
        parent::__construct($client ?? new NoemieClient());
    }

    /**
     * Get the list of Noemie payments from Jfse and returns it converted into a CSV file
     *
     * @param string        $jfse_user_id
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     *
     * @return CCSVFile|null
     */
    public function getPaymentsCsvFile(
        string $jfse_user_id,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null
    ): ?CCSVFile {
        $response = $this->client->getPayments($jfse_user_id, $date_min, $date_max);

        $payments = NoemieMapper::getPaymentsFromResponse($response);

        $file = null;
        if (count($payments)) {
            $file = new CCSVFile();
            $file->writeLine([
                CAppUI::tr('NoemiePayments-date'),
                CAppUI::tr('NoemiePayments-label'),
                CAppUI::tr('NoemiePayments-id'),
                CAppUI::tr('NoemiePayments-organism'),
                CAppUI::tr('NoemiePayments-amount'),
            ]);

            foreach ($payments as $payment) {
                $file->writeLine([
                    $payment->getDate()->format('d/m/Y'),
                    $payment->getLabel(),
                    $payment->getSecondaryLabel(),
                    $payment->getOrganism(),
                    $payment->getAmount(),
                ]);
            }
        }

        return $file;
    }

    /**
     * @param CJfseUser              $user
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     *
     * @return array
     * @throws Exception
     */
    public function processPaymentsForUser(
        CJfseUser $user,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null
    ): array {
        $invoices = NoemieMapper::getInvoicesThirdPartyPayments(
            $this->client->getInvoicesThirdPartyPayments($user->jfse_id, $date_min, $date_max)
        );

        $results = [
            'payments' => [],
            'rejected' => [],
            'errors'   => [],
        ];

        foreach ($invoices as $invoice) {
            /* If there is a CJfseInvoice that matches the id of the InvoiceThirdPartyPayment */
            if ($invoice->loadDataModel()) {
                $invoice->updateDataModel();
                switch ($invoice->getStatus()) {
                    case InvoiceThirdPartyPaymentStatusEnum::PAID():
                    /* In case of an anomaly (differences in the AMC/AMO amounts between the payment and the invoice),
                     * there is still a payment, and the invoice can be fully reimbursed */
                    case InvoiceThirdPartyPaymentStatusEnum::ANOMALY():
                    /* Even if an Invoice is still pending, it can have payments
                     * (mostly in case of AMO and AMC third party payment) */
                    case InvoiceThirdPartyPaymentStatusEnum::PENDING():
                        $result = $this->processInvoicePayments($invoice);

                        if (count($result['success'])) {
                            $results['payments'][$invoice->getInvoiceId()] = $result['success'];
                        }

                        if (count($result['errors'])) {
                            $results['errors'][$invoice->getInvoiceId()] = $result['errors'];
                        }
                        break;
                    case InvoiceThirdPartyPaymentStatusEnum::REJECTED():
                        try {
                            $invoice->setDataModelRejectReason();
                            $results['rejected'][$invoice->getInvoiceId()] = [
                                'reason' => $invoice->getDataModel()->reject_reason,
                            ];
                        } catch (InvoiceException $e) {
                            $results['errors'][$invoice->getInvoiceId()] = [
                                'invoice_id' => $invoice->getInvoiceId(),
                                'message'    => $e->getMessage(),
                            ];
                        }
                        break;
                    default:
                }
            }
        }

        return $results;
    }

    /**
     * Creates the data models for the payments, updates the invoice data model,
     * and creates the CReglement for the CFacture
     *
     * @param InvoiceThirdPartyPayment $invoice
     *
     * @return array
     */
    protected function processInvoicePayments(InvoiceThirdPartyPayment $invoice): array
    {
        $results = [
            'success' => [
                'invoice_id' => $invoice->getInvoiceId(),
                'payment_amount' => 0,
                'payment_count'  => 0,
            ],
            'errors' => []
        ];

        foreach ($invoice->getPayments() as $payment) {
            try {
                $invoice_data_model = $invoice->getDataModel();

                $payment->createDataModel($invoice_data_model);

                $invoice_data_model->loadConsultation();
                /** @var CFactureCabinet $facture */
                $facture = $invoice_data_model->_consultation->loadRefFacture();
                $facture->loadRefsReglements();

                if ($facture->_du_restant_tiers) {
                    $reglement = $this->createCReglement($payment, $facture);
                    $results['success']['payment_amount'] += $reglement->montant;
                    $results['success']['payment_count']++;
                }
            } catch (DataModelException | Exception $e) {
                $results['errors'] = [
                    'invoice_id' => $invoice->getInvoiceId(),
                    'message'    => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * @param InvoicePayment  $payment
     * @param CFactureCabinet $facture
     *
     * @return CReglement
     * @throws DataModelException
     */
    protected function createCReglement(InvoicePayment $payment, CFactureCabinet $facture): CReglement
    {
        $reglement = new CReglement();
        $reglement->object_class = $facture->_class;
        $reglement->object_id = $facture->_id;
        $reglement->emetteur = CReglement::EMETTEUR_TIERS;

        $amount = $payment->getAmountAmoPaid() + $payment->getAmountAmcPaid();

        $reglement->montant = $facture->_du_restant_tiers >= $amount ? $amount : (float)$facture->_du_restant_tiers;
        $reglement->mode = CReglement::MODE_VIREMENT;
        $reglement->date = $payment->getDate() ? $payment->getDate()->format('Y-m-d H:i:s') : CMbDT::date();
        $reglement->reference = $payment->getAmoLabel() ?: $payment->getAmcLabel();

        if ($msg = $reglement->store()) {
            throw DataModelException::persistenceError($msg);
        }

        if ($facture->_du_restant_tiers === $amount || $facture->_du_restant_tiers < $amount) {
            $facture->tiers_date_reglement = CMbDT::date($reglement->date);

            if ($msg = $facture->store()) {
                throw DataModelException::persistenceError($msg);
            }
        }

        return $reglement;
    }

    /**
     * Get the acknowledgement for the given user, and update the status of the invoices
     *
     * @param CJfseUser              $user
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     *
     * @return int[]
     */
    public function processInvoiceAcknowledgements(
        CJfseUser $user,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null
    ): array {
        $results = [
            'success' => 0,
            'errors'  => 0,
        ];

        $acknowledgements = NoemieMapper::getAcknowledgementFromResponse(
            $this->client->getPositiveAcknowledgements($user->jfse_id, $date_min, $date_max),
            AcknowledgementTypeEnum::POSITIVE()
        );

        /** @var Acknowledgement[] $acknowledgements */
        $acknowledgements = array_merge($acknowledgements, NoemieMapper::getAcknowledgementFromResponse(
            $this->client->getNegativeAcknowledgements($user->jfse_id),
            AcknowledgementTypeEnum::NEGATIVE()
        ));

        // Recuperation des factures par lots, a partir des id et n° de lots

        $set_numbers = [];
        foreach ($acknowledgements as $acknowledgement) {
            if (!in_array($acknowledgement->getSetNumber(), $set_numbers)) {
                $set_numbers[] = $acknowledgement->getSetNumber();
            }
        }

        $invoices = NoemieMapper::getInvoicesSetFromResponse(
            $this->client->getInvoicesBySets($set_numbers, $user->jfse_id)
        );

        foreach ($acknowledgements as $acknowledgement) {
            $result = $this->processAcknowledgement($acknowledgement, $invoices);

            $results['success'] += $result['success'];
            $results['errors'] += $result['errors'];
        }

        return $results;
    }

    /**
     * Update the status of all the invoices linked to the acknowledgement
     *
     * @param Acknowledgement $acknowledgement
     * @param array           $invoices
     *
     * @return array
     */
    protected function processAcknowledgement(Acknowledgement $acknowledgement, array $invoices): array
    {
        $results = [
            'success' => 0,
            'errors'  => 0,
        ];

        if ($acknowledgement->getSetNumber() && array_key_exists($acknowledgement->getSetNumber(), $invoices)) {
            /** @var InvoiceSet $invoice */
            foreach ($invoices[$acknowledgement->getSetNumber()] as $invoice) {
                if ($acknowledgement->getType()->getValue() === AcknowledgementTypeEnum::POSITIVE()->getValue()) {
                    $status = InvoiceStatusEnum::ACCEPTED();
                    $reject_reason = null;
                } else {
                    $status = InvoiceStatusEnum::REJECTED();
                    $reject_reason = $acknowledgement->getLabel();
                }

                try {
                    if ($invoice->updateDataModelStatus($status, $reject_reason)) {
                        $results['success']++;
                    } else {
                        $results['errors']++;
                    }
                } catch (DataModelException $e) {
                    $results['errors']++;
                }
            }
        }

        return $results;
    }
}
