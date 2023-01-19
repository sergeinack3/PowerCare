<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Handler;

use Monolog\Formatter\FormatterInterface;
use Ox\Core\CAppUI;
use Ox\Core\Logger\Formatter\ElasticObjectFormatter;
use Ox\Mediboard\System\Elastic\ApplicationLog;

class ApplicationElasticHandler extends AbstractElasticObjectHandler
{
    /**
     * @return FormatterInterface
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new ElasticObjectFormatter(new ApplicationLog());
    }

    protected function canHandle(): bool
    {
        if ($this->is_active === null) {
            $this->is_active = (bool)CAppUI::conf('application_log_using_nosql');
        }

        return $this->is_active;
    }
}
