<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Invoice;

use Exception;
use Ox\Mediboard\Jfse\Exceptions\JfseException;

final class InvoiceException extends JfseException
{
    /**
     * @return static
     */
    public static function invalidInvoice(): self
    {
        return new static('InvalidFactureId', 'InvoiceClient-Invalid invoice id');
    }

    /**
     * @param string         $invoice_id The invoice's guid
     * @param Exception|null $previous   The previously thrown exception
     *
     * @return static
     */
    public static function invoiceNotFound(string $invoice_id, Exception $previous = null): self
    {
        return new static(
            'InvoiceNotFound',
            'JfseInvoiceException-error-invoice_not_found',
            [$invoice_id],
            0,
            $previous
        );
    }

    /**
     * @param int            $consultation_id The consultation's guid
     * @param Exception|null $previous        The previously thrown exception
     *
     * @return static
     */
    public static function consultationNotFound(int $consultation_id, Exception $previous = null): self
    {
        return new static(
            'ConsultationNotFound',
            'JfseInvoiceException-error-consultation_not_found',
            [$consultation_id],
            0,
            $previous
        );
    }

    /**
     * @param int            $user_id The invoice's guid
     * @param Exception|null $previous   The previously thrown exception
     *
     * @return static
     */
    public static function userNotFound(int $user_id, Exception $previous = null): self
    {
        return new static(
            'UserNotFound',
            'JfseInvoiceException-error-invoice_not_found',
            [$user_id],
            0,
            $previous
        );
    }

    /**
     * @param int            $patient_id The patient's guid
     * @param Exception|null $previous   The previously thrown exception
     *
     * @return static
     */
    public static function patientNotFound(int $patient_id, Exception $previous = null): self
    {
        return new static(
            'PatientNotFound',
            'JfseInvoiceException-error-patient_not_found',
            [$patient_id],
            0,
            $previous
        );
    }

    /**
     * @param string         $message
     * @param Exception|null $previous
     *
     * @return static
     */
    public static function persistenceError(string $message, Exception $previous = null): self
    {
        return new static('PersistenceError', $message, [], 0, $previous);
    }
}
