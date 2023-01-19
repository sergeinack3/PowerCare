<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Noemie\Acknowledgement;
use Ox\Mediboard\Jfse\Domain\Noemie\AcknowledgementTypeEnum;
use Ox\Mediboard\Jfse\Domain\Noemie\InvoicePayment;
use Ox\Mediboard\Jfse\Domain\Noemie\InvoiceSet;
use Ox\Mediboard\Jfse\Domain\Noemie\InvoiceThirdPartyPayment;
use Ox\Mediboard\Jfse\Domain\Noemie\InvoiceThirdPartyPaymentStatusEnum;
use Ox\Mediboard\Jfse\Domain\Noemie\Payment;
use Ox\Mediboard\Jfse\Domain\Noemie\PaymentRejection;

/**
 * Map the data returned by Jfse with Noemie domain entities
 */
final class NoemieMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return InvoiceThirdPartyPayment[]
     */
    public static function getInvoicesThirdPartyPayments(Response $response): array
    {
        $invoices = [];

        $list = CMbArray::get($response->getContent(), 'lstFacturesTP', []);
        foreach ($list as $row) {
            $invoices[] = self::arrayToInvoiceThirdPartyPayment($row);
        }

        return $invoices;
    }

    /**
     * @param array $data
     *
     * @return InvoiceThirdPartyPayment
     */
    protected static function arrayToInvoiceThirdPartyPayment(array $data): InvoiceThirdPartyPayment
    {
        $payments_data = CMbArray::get($data, 'lstPaiements', []);
        $payments      = [];
        foreach ($payments_data as $payment) {
            $payments[] = self::paymentArrayToInvoicePayment($payment);
        }

        /* Sometimes, the lstPaiements field is empty, and the payment data are in the field lstPaiementsAutres */
        $other_payments_data = CMbArray::get($data, 'lstPaiementsAutres', []);
        foreach ($other_payments_data as $payment) {
            $payments[] = self::otherPaymentArrayToInvoicePayment($payment);
        }

        $rejections_data = CMbArray::get($data, 'lstRejets', []);
        $rejections      = [];
        foreach ($rejections_data as $rejection) {
            $rejections[] = self::arrayToPaymentRejection($rejection);
        }

        return InvoiceThirdPartyPayment::hydrate([
            'invoice_number'            => intval(CMbArray::get($data, 'noFacture')),
            'invoice_id'                => CMbArray::get($data, 'idFacture'),
            'type'                      => intval(CMbArray::get($data, 'type')),
            'date'                      => self::toDateTimeImmutableOrNull($data, 'date'),
            'beneficiary_last_name'     => CMbArray::get($data, 'nomBeneficiaire'),
            'beneficiary_first_name'    => CMbArray::get($data, 'prenomBeneficiaire'),
            'beneficiary_nir'           => CMbArray::get($data, 'nir'),
            'practitioner_last_name'    => CMbArray::get($data, 'nomPS'),
            'practitioner_first_name'   => CMbArray::get($data, 'prenomPS'),
            'invoicing_number'          => CMbArray::get($data, 'noFacturation'),
            'amount'                    => floatval(CMbArray::get($data, 'montant')),
            'forced_state'              => CMbArray::get($data, 'etatForce'),
            'amo_organism'              => CMbArray::get($data, 'organismeAMO'),
            'amc_organism'              => CMbArray::get($data, 'organismeAMC'),
            'amo_third_party_payment'   => boolval(CMbArray::get($data, 'tpAMO')),
            'amc_third_party_payment'   => boolval(CMbArray::get($data, 'tpAMC')),
            'expected_amount'           => CMbArray::get($data, 'montantAttendu'),
            'beneficiary_amount'        => CMbArray::get($data, 'rac'),
            'expected_amo_amount'       => CMbArray::get($data, 'montantAMO'),
            'expected_amc_amount'       => CMbArray::get($data, 'montantAMC'),
            'status'                    => self::getInvoiceThirdPartyPaymentStatus(CMbArray::get($data, 'etatFacture')),
            'status_amo'                => self::getInvoiceThirdPartyPaymentStatus(CMbArray::get($data, 'etatAMO')),
            'status_amc'                => self::getInvoiceThirdPartyPaymentStatus(CMbArray::get($data, 'etatAMC')),
            'paid_amount'               => floatval(CMbArray::get($data, 'montantPayement')),
            'unpaid_amount'             => floatval(CMbArray::get($data, 'montantRestantDu')),
            'payments'                  => $payments,
            'rejections'                => $rejections,
        ]);
    }

    /**
     * @param string|null $status
     *
     * @return InvoiceThirdPartyPaymentStatusEnum|null
     */
    protected static function getInvoiceThirdPartyPaymentStatus(?string $status): ?InvoiceThirdPartyPaymentStatusEnum
    {
        return InvoiceThirdPartyPaymentStatusEnum::isValid($status)
                ? InvoiceThirdPartyPaymentStatusEnum::from($status) : null;
    }

    /**
     * @param array $data
     *
     * @return PaymentRejection
     */
    protected static function arrayToPaymentRejection(array $data): PaymentRejection
    {
        return PaymentRejection::hydrate([
            'date'          => self::toDateTimeImmutableOrNull($data, 'date'),
            'organism_type' => CMbArray::get($data, 'part'),
            'code'          => CMbArray::get($data, 'code'),
            'label'         => CMbArray::get($data, 'libelle'),
            'level'         => CMbArray::get($data, 'niveau'),
        ]);
    }

    /**
     * @param Response $response
     *
     * @return Payment[]
     */
    public static function getPaymentsFromResponse(Response $response): array
    {
        $payments = [];

        $results = CMbArray::get($response->getContent(), 'lstVirements', []);
        foreach ($results as $data) {
            $payments[] = self::arrayToPayment($data);
        }

        return $payments;
    }

    /**
     * @param array $data
     *
     * @return Payment
     */
    public static function arrayToPayment(array $data): Payment
    {
        return Payment::hydrate([
            'id'              => CMbArray::get($data, 'id'),
            'date'            => self::toDateTimeImmutableOrNull($data, 'date'),
            'label'           => CMbArray::get($data, 'libelleVir1'),
            'secondary_label' => CMbArray::get($data, 'libelleVir2'),
            'organism'        => CMbArray::get($data, 'organismePayeur'),
            'amount'          => floatval(CMbArray::get($data, 'montant', 0)),
        ]);
    }

    /**
     * @param Response $response
     *
     * @return InvoicePayment[]
     */
    public static function getInvoicePaymentsFromResponse(Response $response): array
    {
        $payments = [];

        $results = CMbArray::get($response->getContent(), 'lstPaiements', []);
        foreach ($results as $data) {
            $payments[] = self::paymentArrayToInvoicePayment($data);
        }

        $results = CMbArray::get($response->getContent(), 'lstPaiementsAutres', []);
        foreach ($results as $data) {
            $payments[] = self::paymentArrayToInvoicePayment($data);
        }

        return $payments;
    }

    /**
     * @param array $data
     *
     * @return InvoicePayment
     */
    protected static function paymentArrayToInvoicePayment(array $data): InvoicePayment
    {
        return InvoicePayment::hydrate([
            'date'                   => self::toDateTimeImmutableOrNull($data, 'date'),
            'amo_part_paid'          => trim(CMbArray::get($data, 'etatAMO', '')) === 'P',
            'amc_part_paid'          => trim(CMbArray::get($data, 'etatAMC', '')) === 'P',
            'amount_amo_paid'        => floatval(CMbArray::get($data, 'partAMO', 0)),
            'amount_amc_paid'        => floatval(CMbArray::get($data, 'partAMC', 0)),
            'amount_amo_asked'       => floatval(CMbArray::get($data, 'partAMODemandee', 0)),
            'amount_amc_asked'       => floatval(CMbArray::get($data, 'partAMCDemandee', 0)),
            'amo_label'              => CMbArray::get($data, 'organismeAMO', '') != ''
                ? CMbArray::get($data, 'organismeAMO', '') : null,
            'amc_label'              => CMbArray::get($data, 'organismeAMC', '') != ''
                ? CMbArray::get($data, 'organismeAMC', '') : null,
            'invoice_number'         => CMbArray::get($data, 'noFacture') ?
                intval(CMbArray::get($data, 'noFacture')) : null,
            'beneficiary_last_name'  => CMbArray::get($data, 'nomBeneficiaire'),
            'label'                  => CMbArray::get($data, 'libelleVir1'),
            'secondary_label'        => CMbArray::get($data, 'libelleVir2'),
            'total_amount'           => floatval(CMbArray::get($data, 'montant', 0)),
        ]);
    }

    /**
     * @param array $data
     *
     * @return InvoicePayment
     */
    protected static function otherPaymentArrayToInvoicePayment(array $data): InvoicePayment
    {
        $payment = [
            'date'             => self::toDateTimeImmutableOrNull($data, 'date'),
            'amo_part_paid'    => '',
            'amc_part_paid'    => '',
            'amount_amo_paid'  => 0,
            'amount_amc_paid'  => 0,
            'amount_amo_asked' => 0,
            'amount_amc_asked' => 0,
            'amo_label'        => '',
            'amc_label'        => '',
            'label'            => CMbArray::get($data, 'organisme', ''),
            'secondary_label'  => '',
        ];

        $origin = CMbArray::get($data, 'origine', 'RO');
        if ($origin === 'RO') {
            $payment['amo_part_paid']    = 'P';
            $payment['amount_amo_paid']  = floatval(CMbArray::get($data, 'montant', 0));
            $payment['amount_amo_asked'] = $payment['amount_amo_paid'];
            $payment['amo_label']        = CMbArray::get($data, 'organisme', '');
        } else {
            $payment['amc_part_paid']    = 'P';
            $payment['amount_amc_paid']  = floatval(CMbArray::get($data, 'montant', 0));
            $payment['amount_amc_asked'] = $payment['amount_amo_paid'];
            $payment['amc_label']        = CMbArray::get($data, 'organisme', '');
        }

        return InvoicePayment::hydrate($payment);
    }

    /**
     * @param Response                $response
     * @param AcknowledgementTypeEnum $type
     *
     * @return Acknowledgement[]
     */
    public static function getAcknowledgementFromResponse(Response $response, AcknowledgementTypeEnum $type): array
    {
        $acknowledgements = [];

        $results = CMbArray::get($response->getContent(), 'lstARLs', []);
        foreach ($results as $data) {
            $acknowledgements[] = self::arrayToAcknowledgement($data, $type);
        }

        return $acknowledgements;
    }

    /**
     * @param array                   $data
     * @param AcknowledgementTypeEnum $type
     *
     * @return Acknowledgement
     */
    public static function arrayToAcknowledgement(array $data, AcknowledgementTypeEnum $type): Acknowledgement
    {
        switch ($type) {
            case AcknowledgementTypeEnum::POSITIVE():
                $field_number = 'idLot';
                break;
            case AcknowledgementTypeEnum::NEGATIVE():
            default:
                $field_number = 'noLotNoemie';
        }

        return Acknowledgement::hydrate([
            'type'                    => $type,
            'id'                      => CMbArray::get($data, 'idRetour'),
            'set_id'                  => CMbArray::get($data, 'idLot'),
            'set_number'              => CMbArray::get($data, $field_number),
            'set_date'                => self::toDateTimeImmutableOrNull($data, 'dateLot'),
            'ack_date'                => self::toDateTimeImmutableOrNull($data, 'dateARL'),
            'label'                   => CMbArray::get($data, 'libelleRetour'),
            'rejected_invoice_number' => CMbArray::get($data, 'noFactureRejetee'),
        ]);
    }

    /**
     * @param Response $response
     *
     * @return InvoiceSet[]
     */
    public static function getInvoicesSetFromResponse(Response $response): array
    {
        $invoices = [];

        $results = CMbArray::get($response->getContent(), 'lstFacturesLot', []);
        foreach ($results as $data) {
            $set_number = CMbArray::get($data, 'noLot', 0);

            if (!array_key_exists($set_number, $invoices)) {
                $invoices[$set_number] = [];
            }

            $invoices[$set_number][] = InvoiceSet::hydrate([
                'id'        => CMbArray::get($data, 'idFacture'),
                'number'    => (int)CMbArray::get($data, 'noFacture'),
                'date'      => self::toDateTimeImmutableOrNull($data, 'date'),
            ]);
        }

        return $invoices;
    }
}
