<?php

/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Context\CContextualIntegrationLocation;
use Ox\Mediboard\Etablissement\CGroups;

$object_class = CView::get("object_class", "str notNull class");
$object_id    = CView::get("object_id", "ref notNull class|CMbObject meta|object_class");
$location     = CView::get("location", "str notNull");
$uid          = CView::get("uid", "str notNull");

CView::checkin();

/** @var CMbObject $object */
$object = new $object_class();
$object->load($object_id);

$integration = new CContextualIntegrationLocation();
$ds          = $integration->getDS();

$group_id = CGroups::loadCurrent()->_id;

$where = [
    "contextual_integration.active"   => "= '1'",
    "contextual_integration.group_id" => $ds->prepare("= ?", $group_id),
    "location"                        => $ds->prepare("= ?", $location),
];

$ljoin = [
    "contextual_integration" =>
        "contextual_integration.contextual_integration_id = contextual_integration_location.integration_id",
];

/** @var CContextualIntegrationLocation[] $locations */
$locations = $integration->loadList($where, null, null, null, $ljoin);

if (count($locations) == 0) {
    return;
}
$show_menu = (count($locations) > 1);
foreach ($locations as $_location) {
    $_location->loadRefIntegration()->makeURL($object);
}

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("locations", $locations);
$smarty->assign("show_menu", $show_menu);
$smarty->assign("uid", $uid);
$smarty->display("inc_integration.tpl");
