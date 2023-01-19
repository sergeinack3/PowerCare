<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CItemLiaison;

$liaisons_j         = CValue::post("liaisons_j");
$liaisons_p         = CValue::post("liaisons_p");
$liaisons_p_forfait = CValue::post("liaisons_p_forfait");
$sejour_id          = CValue::post("sejour_id");

if (is_array($liaisons_p)) {
    foreach ($liaisons_p as $liaison_id => $quantite) {
        $item_liaison = new CItemLiaison();
        $item_liaison->load($liaison_id);

        // Enregistrement si la quantité est valide
        if ($quantite) {
            if ($quantite != $item_liaison->quantite) {
                $item_liaison->quantite = $quantite;
                $msg                    = $item_liaison->store();
                CAppUI::displayMsg($msg, 'CPrestationPonctuelle-msg-create');
            }
        } else {
            // Suppression sinon
            $msg = $item_liaison->delete();
            CAppUI::displayMsg($msg, 'CPrestationPonctuelle-msg-delete');
        }
    }
}

if (is_array($liaisons_p_forfait)) {
    foreach ($liaisons_p_forfait as $item_id => $_liaison_id) {
        if ($_liaison_id == "empty") {
            continue;
        }
        $item_liaison = new CItemLiaison();
        $item_liaison->load($_liaison_id);

        if ($item_liaison->_id) {
            continue;
        }

        $item_liaison->sejour_id       = $sejour_id;
        $item_liaison->item_souhait_id = $item_id;

        if ($item_liaison->loadMatchingObject()) {
            $msg = $item_liaison->delete();
            CAppUI::displayMsg($msg, 'CPrestationPonctuelle-msg-delete');
        } else {
            $msg = $item_liaison->store();
            CAppUI::displayMsg($msg, 'CPrestationPonctuelle-msg-create');
        }
    }
}

if (is_array($liaisons_j)) {
    foreach ($liaisons_j as $prestation_id => $by_date) {
        foreach ($by_date as $date => $liaison) {
            $souhait_id   = null;
            $realise_id   = null;
            $sous_item_id = null;

            $item_liaison = new CItemLiaison();

            // Liaison utilisée pour l'affichage
            // Pas de store
            if (
                (@isset($liaison["souhait"]["temp"]) && !@isset($liaison["realise"]["new"])) ||
                (@isset($liaison["realise"]["temp"]) && !@isset($liaison["souhait"]["new"]))
            ) {
                continue;
            }

            if (isset($liaison["souhait"])) {
                if (isset($liaison["souhait"]["new"])) {
                    $souhait_id   = $liaison["souhait"]["new"]["item_souhait_id"];
                    $sous_item_id = @$liaison["souhait"]["new"]["sous_item_id"];
                } else {
                    $souhait_id = reset($liaison["souhait"]);
                    if ($souhait_id) {
                        $souhait_id = @$souhait_id["item_souhait_id"];
                    }
                    $sous_item_id = @reset($liaison["souhait"]);
                    if ($sous_item_id) {
                        $sous_item_id = @$sous_item_id["sous_item_id"];
                    }
                    if ($sous_item_id === null) {
                        $sous_item_id = "";
                    }
                    $liaison_souhait = array_keys($liaison["souhait"]);
                    $item_liaison->load(reset($liaison_souhait));
                }
            }

            if (isset($liaison["realise"])) {
                if (isset($liaison["realise"]["new"])) {
                    $realise_id = $liaison["realise"]["new"]["item_realise_id"];
                } else {
                    $liaison_real = $liaison["realise"];
                    $realise_id   = reset($liaison_real);
                    if ($realise_id) {
                        $realise_id = @$realise_id["item_realise_id"];
                    }
                    if (!$item_liaison->_id) {
                        $keys_liaison_real = array_keys($liaison["realise"]);
                        $item_liaison->load(reset($keys_liaison_real));
                    }
                }
            }

            if (!$item_liaison->_id) {
                $item_liaison->date      = $date;
                $item_liaison->sejour_id = $sejour_id;
            }

            // On ne store que si c'est nouvelle liaison
            // ou un changement de niveau
            if (
                !$item_liaison->_id ||
                $item_liaison->item_souhait_id != $souhait_id ||
                $item_liaison->item_realise_id != $realise_id ||
                $item_liaison->sous_item_id != $sous_item_id
            ) {
                $create = !$item_liaison->_id;

                $item_liaison->item_souhait_id = $souhait_id;
                $item_liaison->item_realise_id = $realise_id;
                $item_liaison->sous_item_id    = $sous_item_id;
                $item_liaison->prestation_id   = $prestation_id;
                $msg                           = $item_liaison->store();

                CAppUI::displayMsg(
                    $msg,
                    $create ? 'CPrestationJournaliere-msg-create' : 'CPrestationJournaliere-msg-modify'
                );
            }
        }
    }
}

echo CAppUI::getMsg();
CApp::rip();
