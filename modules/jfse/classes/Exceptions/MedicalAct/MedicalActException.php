<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\MedicalAct;

use Exception;
use Ox\Mediboard\Jfse\Exceptions\JfseException;

class MedicalActException extends JfseException
{
    /**
     * @param string         $act_guid         The act's guid
     * @param Exception|null $previous The previously thrown exception
     *
     * @return static
     */
    public static function actNotFound(string $act_guid, Exception $previous = null): self
    {
        return new static(
            'ActNotFound',
            'JfseInvoiceException-error-act_not_found',
            [$act_guid],
            0,
            $previous
        );
    }
    /**
     * @param string $act_class The act's class
     *
     * @return static
     */
    public static function invalidActType(string $act_class): self
    {
        return new static(
            'ActNotFound',
            'JfseInvoiceException-error-invalid_act_type',
            [$act_class],
            0
        );
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
