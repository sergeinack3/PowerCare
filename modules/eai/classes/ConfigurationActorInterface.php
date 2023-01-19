<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

/**
 * Description
 */
interface ConfigurationActorInterface
{
    /** @var string Replace %s by your suffix */
    public const REGEX_MATCH_SUFFIX = '[\w\-]+-(?:%s)$';

    /**
     * List configuration for actor and format
     *
     * @return array [key_name => [config_acotors[]]
     */
    public function getConfigurationsActor(): array;

    /**
     * Get suffix section for the configuration name for actor configurations
     *
     * Each configuration for actor class should end with the suffix declared here
     *
     * @return array
     */
    public function getSuffixesSectionActor(): array;
}
