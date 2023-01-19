<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\PlanningOp\CProtocole;

CCanDo::checkRead();
$uf_id           = CView::get("uf_id", "ref class|CUniteFonctionnelle", true);
$order_col       = CView::get("order_col", "enum list|libelle|chir_id|function_id", true);
$order_way       = CView::get("order_way", "enum list|ASC|DESC default|DESC", true);
$onlyRefreshList = CView::get("onlyRefreshList", "bool default|0");

CView::checkin();
$uf = new CUniteFonctionnelle();
$uf->load($uf_id);

$type_uf = $uf->type;

//todo: revoir la façon de parcourir les types d'affectations
if ($type_uf == "hebergement") {
    $type_affectations = [
        "CLit"       => [],
        "CChambre"   => [],
        "CService"   => [],
        "CProtocole" => [],
    ];
} elseif ($type_uf == "soins") {
    $type_affectations = ["CService" => [], "CProtocole" => []];
} elseif ($type_uf == "medicale") {
    $type_affectations = ["CMediusers" => [], "CFunctions" => [], "CProtocole" => []];
}

/** @var CAffectationUniteFonctionnelle[]|CAffectationUfSecondaire[]|CUniteFonctionnelle[] $items */
foreach ($type_affectations as $type => $tab_type) {
    $items = [];
    if ($type == "CProtocole") {
        $protocole                             = new CProtocole();
        $protocole->{"uf_" . $type_uf . "_id"} = $uf_id;
        /** @var CProtocole[] $protocoles */
        $protocoles = $protocole->loadMatchingList();
        foreach ($protocoles as $_protocole_id => $_protocole) {
            $items[] = $_protocole;
            $_protocole->loadRefChir();
            $_protocole->_ref_chir->loadRefUser();
            $_protocole->loadRefFunction();
        }

        //Sort des protocoles
        if ($order_col == "chir_id") {
            uasort(
                $items,
                function ($a, $b) use ($order_way) {
                    return ($order_way == "DESC") ? $a->_ref_chir->_view <=> $b->_ref_chir->_view : -($a->_ref_chir->_view <=> $b->_ref_chir->_view);
                }
            );
        } elseif ($order_col == "function_id") {
            uasort(
                $items,
                function ($a, $b) use ($order_way) {
                    return ($order_way == "DESC") ? $a->_ref_function->_view <=> $b->_ref_function->_view : -($a->_ref_function->_view <=> $b->_ref_function->_view);
                }
            );

        } else {
            uasort(
                $items,
                function ($a, $b) use ($order_way) {
                    return ($order_way == "DESC") ? strtolower($a->_view) <=> strtolower($b->_view) : -(strtolower($a->_view) <=> strtolower($b->_view));
                }
            );
        }
    } else {
        $affect               = new CAffectationUniteFonctionnelle();
        $affect->uf_id        = $uf_id;
        $affect->object_class = $type;
        $items                = $affect->loadMatchingList();

        $affect_secondary               = new CAffectationUfSecondaire();
        $affect_secondary->uf_id        = $uf_id;
        $affect_secondary->object_class = $type;
        $items                          = array_merge($items, $affect_secondary->loadMatchingList());

        foreach ($items as $_affect) {
            /* @var CAffectationUniteFonctionnelle|CAffectationUfSecondaire $_affect */
            $_affect->loadRefContexte();
        }

        uasort(
            $items,
            function ($a, $b) use ($order_way) {
                return ($order_way == "DESC") ? strtolower($a->_view) <=> strtolower($b->_view) : -(strtolower($a->_view) <=> strtolower($b->_view));
            }
        );    }

    $type_affectations[$type] = $items;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("uf", $uf);
$smarty->assign("type_affectations", $type_affectations);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

if ($onlyRefreshList) {
    $smarty->display("vw_list_stats_uf");
} else {
    $smarty->display("vw_stats_uf");
}
