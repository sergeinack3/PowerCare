<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;

CCanDo::checkAdmin();

$cache = Cache::getCache(Cache::OUTER);

$cache->delete("search_indexing_step");

echo '<em class="empty">Aucun</em>';
