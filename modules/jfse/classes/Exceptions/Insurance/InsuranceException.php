<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Insurance;

use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Throwable;

/**
 * Class InsuranceException
 */
class InsuranceException extends JfseException
{
    final public function __construct(
        string $name,
        string $locale,
        array $locale_args = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($name, $locale, $locale_args, $code, $previous);
    }


    /**
     * @return static
     */
    public static function invoiceNotInitialized(): self
    {
        return new static('Invoice not initialized', 'CInvoice-Not initialized');
    }
}
