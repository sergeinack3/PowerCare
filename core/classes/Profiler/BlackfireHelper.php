<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Profiler;

abstract class BlackfireHelper
{
    public static function addMarker(string $label): void
    {
        if (class_exists('BlackfireProbe')) {
            \BlackfireProbe::addMarker($label);
        }
    }
}
