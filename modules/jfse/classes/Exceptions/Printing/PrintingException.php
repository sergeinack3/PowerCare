<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Printing;

use Ox\Mediboard\Jfse\Exceptions\JfseException;

class PrintingException extends JfseException
{

    public static function missingBatch(): self
    {
        return new static('MissiginBatch', 'PrintingService-Missing batch');
    }

    public static function missingDates(): self
    {
        return new static('MissiginDates', 'PrintingService-Missing dates');
    }

    public static function missingFiles(): self
    {
        return new static('MissiginFiles', 'PrintingService-Missing files');
    }

    public static function unknownMode(): self
    {
        return new static('UnknownMode', 'PrintingService-Unknown mode');
    }

    public static function missingInvoice(): self
    {
        return new static('MissingInvoice', 'PrintingService-Missing invoice');
    }
}
