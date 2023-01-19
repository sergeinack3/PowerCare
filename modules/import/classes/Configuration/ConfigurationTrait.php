<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Configuration;

/**
 * Description
 */
trait ConfigurationTrait
{
    /** @var Configuration */
    protected $configuration;

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }
}
