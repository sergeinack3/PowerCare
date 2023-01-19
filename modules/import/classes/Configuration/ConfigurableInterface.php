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
interface ConfigurableInterface
{
    /**
     * @param Configuration $configuration
     *
     * @return void
     */
    public function setConfiguration(Configuration $configuration): void;

    /**
     * @return Configuration|null
     */
    public function getConfiguration(): ?Configuration;
}
