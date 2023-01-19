<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Noemie;

use Exception;
use Ox\Mediboard\Jfse\Exceptions\JfseException;

final class NoemieException extends JfseException
{
    /**
     * @return static
     */
    public static function invalidExportFile(): self
    {
        return new static('InvalidExportFile', 'NoemiePayment-error-invalid_export_file');
    }
}
