<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

/**
 * Tell that the related CFile must be encrypted.
 */
interface ConfidentialObjectInterface
{
    /**
     * Get the used key name for encryption.
     *
     * @return string
     */
    public function getKeyName(): string;
}
