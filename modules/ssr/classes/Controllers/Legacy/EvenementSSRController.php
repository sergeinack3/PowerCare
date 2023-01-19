<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CActeCdARR;
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CActePrestationSSR;

class EvenementSSRController extends CLegacyController
{

    public function validateSSREvents(): void
    {
        $this->checkPermRead();

        $token_evts    = CView::post("token_evts", "str");
        $evenement_ids = explode("|", $token_evts);
        // Recuperation des codes cdarrs a ajouter et a supprimer aux evenements
        $add_codes   = CView::post("added_codes", "str");
        $added_codes = explode("|", $add_codes);
        $rem_codes   = CView::post("remed_codes", "str");
        $remed_codes = explode("|", $rem_codes);
        $other_codes = CView::post("_codes", "str");

        CView::checkin();

        $codes = [];
        if ($added_codes) {
            $codes["add"] = $added_codes;
        }
        if ($remed_codes) {
            $codes["rem"] = $remed_codes;
        }

        // Ajout des codes rajoutés depuis l'autocomplete
        if ($other_codes && is_array($other_codes) && count($other_codes)) {
            foreach ($other_codes as $_other) {
                $codes["add"][] = $_other;
            }
        }

        global $can;
        $modifiy_evt_everybody = CAppUI::gconf("ssr general modifiy_evt_everybody");
        $presta                = CAppUI::gconf("ssr general use_acte_presta") == 'presta';

        foreach ($evenement_ids as $_evenement_id) {
            $evenement = CEvenementSSR:: findOrNew($_evenement_id);

            if (!$modifiy_evt_everybody) {
                // Autres rééducateurs
                $therapeute_id = $evenement->therapeute_id;
                if ($evenement->seance_collective_id) {
                    $therapeute_id = $evenement->loadRefSeanceCollective()->therapeute_id;
                }
                if ($therapeute_id && !in_array(
                        CAppUI::$instance->user_id,
                        $evenement->getTherapeutes()
                    ) && !$can->admin) {
                    CAppUI::displayMsg(
                        CAppui::tr("CEvenementSSR-no_modify_evt_other_reeduc"),
                        "CEvenementSSR-msg-modify"
                    );
                    continue;
                }
            }

            $line_elt        = $evenement->loadRefPrescriptionLineElement();
            $csarr_codes_elt = $line_elt->_ref_element_prescription->loadRefsCsarrs();

            $csarr_activites = [];
            foreach ($csarr_codes_elt as $_code_elt) {
                $csarr_activites[$_code_elt->code] = $_code_elt;
            }

            // Actes par code pour chaque événement
            $actes_by_code = [];
            foreach ($evenement->loadRefsActes() as $type => $_actes) {
                foreach ($_actes as $_acte) {
                    $actes_by_code[$_acte->_class][$_acte->code][$_acte->_id] = $_acte;
                }
            }

            foreach ($codes as $action => $_codes) {
                foreach ($_codes as $_code) {
                    if (!$_code) {
                        continue;
                    }
                    if ($presta) {
                        [$_code, $_quantite, $_type_presta] = explode('-', $_code);
                        $classe_acte = "CActePrestationSSR";
                    } else {
                        $classe_acte = strlen($_code) == 7 ? "CActeCsARR" : "CActeCdARR";
                    }
                    // Ajout de l'acte a tous les évènements
                    if ($action == "add") {
                        if (!isset($actes_by_code[$classe_acte][$_code])) {
                            $acte                   = new $classe_acte();
                            $acte->evenement_ssr_id = $_evenement_id;
                            $acte->code             = $_code;
                            $acte->commentaire      = isset($csarr_activites[$_code]) ? $csarr_activites[$_code]->commentaire : null;
                            $acte->_modulateurs     = isset($csarr_activites[$_code]) && $csarr_activites[$_code]->modulateurs ? explode(
                                "|",
                                $csarr_activites[$_code]->modulateurs
                            ) : null;
                            $acte->extension        = isset($csarr_activites[$_code]) ? $csarr_activites[$_code]->code_ext_documentaire : null;
                            if ($presta) {
                                $acte->quantite = $_quantite;
                                $acte->type     = $_type_presta;
                            }
                            $msg = $acte->store();
                            CAppUI::displayMsg($msg, "$acte->_class-msg-create");
                        } elseif ($presta) {
                            foreach ($actes_by_code[$classe_acte][$_code] as $_acte) {
                                if ($_acte->quantite == $_quantite) {
                                    continue;
                                }
                                $_acte->quantite = $_quantite;
                                $msg             = $_acte->store();
                                CAppUI::displayMsg($msg, "$_acte->_class-msg-create");
                            }
                        }
                    } elseif ($action == "rem") {
                        // Suppression de l'acte pour tous les évènements
                        if (isset($actes_by_code[$classe_acte][$_code])) {
                            foreach ($actes_by_code[$classe_acte][$_code] as $_acte) {
                                $msg = $_acte->delete();
                                CAppUI::displayMsg($msg, "$_acte->_class-msg-delete");
                            }
                        }
                    }
                }
            }
        }

        echo CAppUI::getMsg();
        $this->rip();
    }
}
