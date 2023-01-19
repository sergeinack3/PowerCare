<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CService;

CCanDo::check();

$service_id   = CView::get("service_id", 'ref class|CService');
$day_relative = CView::get('day_relative', 'num');
$g            = CView::get('g', 'ref class|CGroups');
CView::checkin();

if (!$g) {
    $g = CService::findOrFail($service_id)->group_id;
}

// Redirection vers le bilan par service
// avec toutes les catégories cochées
// (entête caché)
$url = "m=hospi&a=vw_bilan_service&token_cat=all&service_id=$service_id&g=$g&offline=1&dialog=1";
if (!is_null($day_relative) && $day_relative >= 0) {
    $url .= "&day_relative=$day_relative";
}

CAppUI::redirect($url);
