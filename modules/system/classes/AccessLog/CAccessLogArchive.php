<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\AccessLog;

/**
 * Access Log Archive
 */
class CAccessLogArchive extends CAccessLog
{
    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec          = parent::getSpec();
        $spec->table   = 'access_log_archive';
        $spec->archive = true;

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["module_action_id"] .= " back|access_log_archives";

        return $props;
    }
}
