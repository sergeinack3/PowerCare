<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Profiles;

/**
 * Description
 */
interface ProfileResource
{
    /**
     * List all canonical resources for this profile
     *
     * @return string[]
     */
    public static function listResourceCanonicals(): array;

    /**
     * Give the canonical of profile
     *
     * @return string
     */
    public static function getCanonical(): string;

    /**
     * Get the name for this profile
     *
     * @return string
     */
    public static function getProfileName(): string;
}
