<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

/**
 * PHP version prerequisite
 */
class MySQLVersion extends Prerequisite
{
    const VERSION_REQUIRE = '5.5';

    /** @var string $current_version */
    public $current_version;

    /**
     * Compare PHP version
     *
     * @param bool $strict Check also warnings
     *
     * @return bool
     * @see parent::check
     *
     */
    public function check($strict = true): bool
    {
        return substr($this->current_version, 0, 2) >= $this->name;
    }

    /**
     * @return string
     */
    public function getVersionInstalled(): string
    {
        return $this->current_version;
    }

    /**
     * Return all instances of self
     *
     * @return self
     */
    public function getAll(): self
    {
        return $this;
    }
}
