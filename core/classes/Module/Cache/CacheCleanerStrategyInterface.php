<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Cache;

use Ox\Core\CSmartyDP;

/**
 * Cache cleaner strategy interface implemented by all cache cleaning strategies.
 */
interface CacheCleanerStrategyInterface
{
    /**
     * Execution of specific strategy
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Return execution outputs in HTML format
     *
     * @param CSmartyDP|null $smarty
     *
     * @return string
     */
    public function getHtmlResult(CSmartyDP $smarty = null): string;
}
