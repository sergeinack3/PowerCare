<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkAdmin();
$clean = CView::get("clean", "bool default|0");
$page  = CView::get("page", "num default|0");
CView::checkin();

//Recherche des séances mères non closent (annulée ou validée) ayant tous leur enfant qui sont tous validés ou annulés
$ljoin                                       = [];
$ljoin[]                                     = "evenement_ssr AS evt_fille ON
(evt_fille.seance_collective_id = evenement_ssr.evenement_ssr_id AND evt_fille.annule = '0' AND evt_fille.realise = '0')";
$where                                       = [];
$where["evt_fille.evenement_ssr_id"]         = " IS NULL";
$where["evenement_ssr.type_seance"]          = " = 'collective'";
$where["evenement_ssr.seance_collective_id"] = " IS NULL";
$where["evenement_ssr.annule"]               = " = '0'";
$where["evenement_ssr.realise"]              = " = '0'";
$evenement                                   = new CEvenementSSR();
$list_evts                                   = $evenement->loadList(
    $where,
    "debut desc",
    $clean ? null : "$page, 50",
    "evenement_ssr.evenement_ssr_id",
    $ljoin
);

//Recherche des séances collective n'ayant pas d'enfant
$ljoin2                                       = [];
$ljoin2[]                                     = "evenement_ssr AS evt_fille ON (evt_fille.seance_collective_id = evenement_ssr.evenement_ssr_id)";
$where2                                       = [];
$where2["evt_fille.evenement_ssr_id"]         = " IS NULL";
$where2["evenement_ssr.type_seance"]          = " = 'collective'";
$where2["evenement_ssr.seance_collective_id"] = " IS NULL";
$list_evts_empty                              = $evenement->loadList(
    $where2,
    "debut desc",
    null,
    "evenement_ssr.evenement_ssr_id",
    $ljoin2
);

$list_evts    = array_merge($list_evts, $list_evts_empty);
$nb_corrected = 0;
if ($clean) {
    foreach ($list_evts as $_evt_collectif) {
        /* @var CEvenementSSR $_evt_collectif */
        $_evt_collectif->loadRefsEvenementsSeance();
        $count_annule  = 0;
        $count_realise = 0;
        foreach ($_evt_collectif->_ref_evenements_seance as $_evt_fille) {
            if ($_evt_fille->realise) {
                $count_realise++;
            } elseif ($_evt_fille->annule) {
                $count_annule++;
            }
        }
        $_evt_collectif->_traitement = true;
        if (count($_evt_collectif->_ref_evenements_seance)) {
            if ($count_annule == count($_evt_collectif->_ref_evenements_seance)) {
                $_evt_collectif->annule = 1;
            } else {
                $_evt_collectif->realise = 1;
            }
            $msg = $_evt_collectif->store();
        } else {
            $msg = $_evt_collectif->delete();
        }
        if ($msg) {
            CApp::log($msg);
        } else {
            $nb_corrected++;
        }
    }
    $list_evts       = $evenement->loadList(
        $where,
        "debut desc",
        "$page, 50",
        "evenement_ssr.evenement_ssr_id",
        $ljoin
    );
    $list_evts_empty = $evenement->loadList($where2, "debut desc", null, "evenement_ssr.evenement_ssr_id", $ljoin2);
    $list_evts       = array_merge($list_evts, $list_evts_empty);
}
foreach ($list_evts as $_evt_collectif) {
    $_evt_collectif->loadRefTherapeute();
}

$total_evts = $evenement->countList($where, null, $ljoin);
$total_evts += $evenement->countList($where2, null, $ljoin2);

$smarty = new CSmartyDP();

$smarty->assign("evenements", $list_evts);
$smarty->assign("nb_corrected", $nb_corrected);
$smarty->assign("clean", $clean);
$smarty->assign("page", $page);
$smarty->assign("total_evts", $total_evts);

$smarty->display("vw_list_evt_completed");
