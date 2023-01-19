<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CCorrespondantCourrier;
use Ox\Mediboard\CompteRendu\CDestinataire;
use Ox\Mediboard\CompteRendu\CListeChoix;
use Ox\Mediboard\Files\CDestinataireItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\MailReceiverService;
use Ox\Mediboard\Patients\CPatient;

/**
 * CCompteRendu aed
 */
if (isset($_POST["_do_empty_pdf"])) {
    $compte_rendu_id = CValue::post("compte_rendu_id");
    $compte_rendu    = new CCompteRendu();
    $compte_rendu->load($compte_rendu_id);

    /** @var CFile $_file */
    $compte_rendu->loadRefsFiles();
    $files = $compte_rendu->_ref_files;
    if (count($files)) {
        foreach ($files as $_file) {
            $_file->fileEmpty();
        }
    }
    CApp::rip();
}

$do                 = new CDoObjectAddEdit("CCompteRendu");
$do->redirectDelete = "m=compteRendu&new=1";
$dest_to_store = [];

// Récupération des marges du modele en fast mode
if (isset($_POST["fast_edit"]) && $_POST["fast_edit"] == 1 && isset($_POST["object_id"]) && $_POST["object_id"] != '') {
    $compte_rendu = new CCompteRendu();
    $compte_rendu->load($_POST["modele_id"]);

    if ($compte_rendu->_id) {
        $do->request["margin_top"]    = $compte_rendu->margin_top;
        $do->request["margin_bottom"] = $compte_rendu->margin_bottom;
        $do->request["margin_left"]   = $compte_rendu->margin_left;
        $do->request["margin_right"]  = $compte_rendu->margin_right;
    }
}

// Archive le document principal qui est dupliqué
if (isset($_POST["annule"]) && isset($_POST["modele_id"]) && $_POST["annule"] == 1 && isset($_POST["object_id"]) && $_POST["object_id"] != '') {
    $compte_rendu = new CCompteRendu();
    $compte_rendu->load($_POST["modele_id"]);

    $compte_rendu->annule = $_POST["annule"];
    $compte_rendu->store();

    // Remise a 0 pour ne pas archiver le deuxième document
    $_POST["annule"] = 0;
}

if (isset($_POST["_source"])) {
    $_POST["_source"] = stripslashes($_POST["_source"]);
}

$check_to_empty_field = CAppUI::pref("check_to_empty_field");

// Remplacement des zones de texte libre
if (isset($_POST["_texte_libre"])) {
    $compte_rendu = new CCompteRendu();
    $fields       = [];
    $values       = [];

    // Remplacement des \n par des <br>
    foreach ($_POST["_texte_libre"] as $key => $_texte_libre) {
        $is_empty = false;
        if (($check_to_empty_field && isset($_POST["_empty_texte_libre"][$key])) ||
            (!$check_to_empty_field && !isset($_POST["_empty_texte_libre"][$key]))
        ) {
            $values[] = "";
            $is_empty = true;
        } else {
            if ($_POST["_texte_libre"][$key] === "") {
                continue;
            }
            $values[] = nl2br($_POST["_texte_libre"][$key]);
        }
        if ($is_empty) {
            $fields[] = "<span class=\"field\">[[Texte libre - " . $_POST["_texte_libre_md5"][$key] . "]]</span>";
        } else {
            $fields[] = "[[Texte libre - " . $_POST["_texte_libre_md5"][$key] . "]]";
        }
    }

    $_POST["_source"]      = str_ireplace($fields, $values, $_POST["_source"]);
    $_POST["_texte_libre"] = null;
}

$destinataires = [];
$ids_corres    = "";
$do_merge = CValue::post("do_merge", 0);
$_medecin_exercice_place = CValue::post('_medecin_exercice_place');

if (isset($_POST["_source"])) {
    // Ajout d'entête / pied de page à la volée
    if (CAppUI::gconf("dPcompteRendu CCompteRendu header_footer_fly") && $_POST["object_id"] && !isset($_POST["fast_edit"])) {
        $modele = new CCompteRendu();
        $modele->load($_POST["modele_id"]);

        $header_id = CValue::post("header_id");
        $footer_id = CValue::post("footer_id");

        // Depuis un modèle
        if (!$_POST["compte_rendu_id"]) {
            // Présence d'un header / footer
            if ($modele->header_id || $modele->footer_id) {
                if ($header_id != $modele->header_id) {
                    $_POST["_source"] = CCompteRendu::replaceComponent($_POST["_source"], $header_id);
                }
                if ($footer_id != $modele->footer_id) {
                    $_POST["_source"] = CCompteRendu::replaceComponent($_POST["_source"], $footer_id, "footer");
                }
            } else {
                $_POST["_source"] = $modele->generateDocFromModel($_POST["_source"], $header_id, $footer_id);
            }
        } else {
            // Document existant
            $cr = new CCompteRendu();
            $cr->load($_POST["compte_rendu_id"]);

            if (!$cr->header_id && !$cr->footer_id && !$header_id && !$footer_id) {
                $_POST["_source"] = $cr->generateDocFromModel($_POST["_source"], $header_id, $footer_id);
            } else {
                if ($header_id != $cr->header_id) {
                    $_POST["_source"] = CCompteRendu::replaceComponent($_POST["_source"], $header_id);
                }
                if ($footer_id != $cr->footer_id) {
                    $_POST["_source"] = CCompteRendu::replaceComponent($_POST["_source"], $footer_id, "footer");
                }
            }
        }
    }

    // Application des listes de choix
    $fields = [];
    $values = [];
    if (isset($_POST["_CListeChoix"])) {
        $listes = $_POST["_CListeChoix"];

        $actual_encoding = CApp::$encoding;
        CApp::$encoding  = "windows-1252";

        foreach ($listes as $list_id => $options) {
            $options = array_map(["Ox\Core\CMbString", "htmlEntities"], $options);
            $list    = new CListeChoix();
            $list->load($list_id);
            $is_empty = false;
            if (($check_to_empty_field && isset($_POST["_empty_list"][$list_id]))
                || (!$check_to_empty_field && !isset($_POST["_empty_list"][$list_id]))
            ) {
                $values[] = "";
                $is_empty = true;
            } else {
                if ($options === [0 => "undef"]) {
                    continue;
                }
                CMbArray::removeValue("undef", $options);
                $values[] = nl2br(implode(", ", $options));
            }
            $nom = CMbString::htmlEntities($list->nom, ENT_QUOTES);
            if ($is_empty) {
                $fields[] = "<span class=\"name\">[Liste - " . $nom . "]</span>";
            } else {
                $fields[] = "[Liste - " . $nom . "]";
            }
        }

        CApp::$encoding = $actual_encoding;
    }

    $_POST["_source"] = str_ireplace($fields, $values, $_POST["_source"]);

    // Si purge_field est valué, on effectue l'opération de nettoyage des lignes
    if (isset($_POST["purge_field"]) && $_POST["purge_field"] != "") {
        $purge_field      = $_POST["purge_field"];
        $purge_field      = str_replace("/", "\/", $purge_field);
        $purge_field      = str_replace("<", "\<", $purge_field);
        $purge_field      = str_replace(">", "\>", $purge_field);
        $purge_field      .= "\s*\<br\s*\/\>";
        $_POST["_source"] = preg_replace("/\n$purge_field/", "", $_POST["_source"]);
    }

    // Application des destinataires
    foreach ($_POST as $key => $value) {
        // Remplacement des destinataires
        if (preg_match("/_dest_([\w]+)_([\w\-0-9]+)/", $key, $dest)) {
            $destinataires[] = $dest;
        }
    }

    if (count($destinataires) && $do_merge) {
        $object = new $_POST["object_class"];
        /** @var $object CMbObject */
        $object->load($_POST["object_id"]);
        $allDest = (new MailReceiverService($object))->getReceivers();

        $patient = method_exists($object, "loadRelPatient") ? $object->loadRelPatient() : new CPatient();

        // Récupération des correspondants ajoutés par l'autocomplete
        $cr_dest = new CCompteRendu();
        $cr_dest->load($_POST["compte_rendu_id"]);
        $cr_dest->mergeCorrespondantsCourrier($allDest, $_medecin_exercice_place);

        $bodyTag = '<div id="body">';

        // On sort l'en-tête et le pied de page
        $posBody = strpos($_POST["_source"], $bodyTag);

        if ($posBody) {
            $headerfooter = substr($_POST["_source"], 0, $posBody);
            $index_div    = strrpos($_POST["_source"], "</div>") - ($posBody + strlen($bodyTag));
            $body         = substr($_POST["_source"], $posBody + strlen($bodyTag), $index_div);
        } else {
            $headerfooter = "";
            $body         = $_POST["_source"];
        }

        if (CAppUI::pref("multiple_doc_correspondants")) {
            $do->doBind();
        }

        $allSources  = [];
        $modele_base = clone $do->_obj;
        $source_base = $body;

        $courrier_section = CAppUI::tr("common-Mail");
        $formule_subItem  = CAppUI::tr("CSalutation");
        $copy_subItem     = CAppUI::tr("common-copy to");

        foreach ($destinataires as $curr_dest) {
            $fields = [
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - Début]"),
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - Fin]"),
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - " . CAppUI::tr('CSalutation-vous-te') . "]"),
                str_replace(
                    "'",
                    "&#039;",
                    CMbString::htmlEntities("[$courrier_section - $formule_subItem - " . CAppUI::tr("CSalutation-vous-t") . "]")
                ),
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - " . CAppUI::tr('CSalutation-votre-ton') . "]"),
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - " . CAppUI::tr('CSalutation-votre-ta') . "]"),
                CMbString::htmlEntities("[$courrier_section - $formule_subItem - " . CAppUI::tr('CSalutation-votre-accord genre patient') . "]"),
                CMbString::htmlEntities("[$courrier_section - " . CAppUI::tr("common-recipient name") . "]"),
                CMbString::htmlEntities("[$courrier_section - " . CAppUI::tr("common-recipient address") . "]"),
                CMbString::htmlEntities("[$courrier_section - " . CAppUI::tr("common-recipient cp city") . "]"),
                CMbString::htmlEntities("[$courrier_section - " . CAppUI::tr("common-brotherhood") . "]"),
                CMbString::htmlEntities("[$courrier_section - $copy_subItem - " . CAppUI::tr("common-simple") . "]"),
                CMbString::htmlEntities("[$courrier_section - $copy_subItem - " . CAppUI::tr('common-simple (multiline)') . "]"),
                CMbString::htmlEntities("[$courrier_section - $copy_subItem - " . CAppUI::tr('common-full') . "]"),
                CMbString::htmlEntities("[$courrier_section - $copy_subItem - " . CAppUI::tr('common-full (multiline)') . "]"),
            ];

            // Champ copie à : on reconstruit en omettant le destinataire.
            $confraternie       = "";
            $copyTo             = "";
            $copyToMulti        = "";
            $copyToComplet      = "";
            $copyToCompletMulti = "";

            foreach ($destinataires as $_dest) {
                if ($curr_dest[0] == $_dest[0]) {
                    continue;
                }
                $_destinataire                = clone $allDest[$_dest[1]][$_dest[2]];
                $_destinataire->nom           = CMbString::htmlEntities(preg_replace("/(.*)(\([^\)]+\))/", '$1', $_destinataire->nom));
                $_destinataire->confraternite = $_destinataire->confraternite ? $_destinataire->confraternite . "," : null;

                $copyTo        .= $_destinataire->nom . "; ";
                $copyToMulti   .= $_destinataire->nom . "<br />";
                $copyToComplet .= $_destinataire->nom . " - " .
                    preg_replace("/\n\r\t/", " ", CMbString::htmlEntities($_destinataire->adresse)) . " " .
                    $_destinataire->cpville;

                $copyToCompletMulti .= $_destinataire->nom . " - " . preg_replace(
                        "/\n\r\t/",
                        " ",
                        CMbString::htmlEntities($_destinataire->adresse)
                    ) . " " .
                    $_destinataire->cpville;
                if (end($destinataires) !== $_dest) {
                    $copyToComplet      .= " ; ";
                    $copyToCompletMulti .= "<br />";
                }
            }

            $tutoiement = $allDest[$curr_dest[1]][$curr_dest[2]]->tutoiement;

            $values = [
                CMbString::htmlEntities($allDest[$curr_dest[1]][$curr_dest[2]]->starting_formula),
                CMbString::htmlEntities($allDest[$curr_dest[1]][$curr_dest[2]]->closing_formula),
                CAppUI::tr("CSalutation-" . ($tutoiement ? "te" : "vous")),
                CAppUI::tr("CSalutation-" . ($tutoiement ? "t'" : "vous")),
                CAppUI::tr("CSalutation-" . ($tutoiement ? "ton" : "votre")),
                CAppUI::tr("CSalutation-" . ($tutoiement ? "ta" : "votre")),
                CAppUI::tr("CSalutation-" . ($tutoiement ? ($patient->sexe === "m" ? "ton" : "ta") : "votre")),
                CMbString::htmlEntities(
                    preg_replace("/(.*)(\([^\)]+\))/", '$1', $allDest[$curr_dest[1]][$curr_dest[2]]->nom)
                ),
                nl2br(CMbString::htmlEntities($allDest[$curr_dest[1]][$curr_dest[2]]->adresse)),
                $allDest[$curr_dest[1]][$curr_dest[2]]->cpville,
                $allDest[$curr_dest[1]][$curr_dest[2]]->confraternite,
                $copyTo,
                $copyToMulti,
                $copyToComplet,
                $copyToCompletMulti,
            ];

            if (!CAppUI::pref("multiple_doc_correspondants")) {
                $max = 1;
                if (isset($_POST["_count_" . $curr_dest[1] . "_" . $curr_dest[2]])) {
                    $max = $_POST["_count_" . $curr_dest[1] . "_" . $curr_dest[2]];
                }
                for ($i = 0; $i < $max; $i++) {
                    $allSources[] = str_ireplace($fields, $values, $body);
                }

                // On concatène les en-tête, pieds de page et body's
                if ($headerfooter) {
                    $_POST["_source"] = $headerfooter;
                    $_POST["_source"] .= "<div id=\"body\">";
                    $_POST["_source"] .= implode("<hr class=\"pageBreak\" />", $allSources);
                    $_POST["_source"] .= "</div>";
                } else {
                    $_POST["_source"] = implode("<hr class=\"pageBreak\" />", $allSources);
                }
            } else {
                // Création d'un document par correspondant
                $body = str_ireplace($fields, $values, $source_base);

                $content = $body;

                if ($headerfooter) {
                    $content = $headerfooter . "<div id=\"body\">" . $body . "</div>";
                }

                // Si le compte-rendu a déjà été enregistré et que l'on applique le premier destinataire,
                // on modifie le compte-rendu existant
                if ($do->_obj->_id && $curr_dest === reset($destinataires)) {
                    $compte_rendu = $do->_obj;
                } else {
                    // Sinon clone du modèle
                    $compte_rendu               = clone $modele_base;
                    $compte_rendu->_id          = null;
                    $compte_rendu->_ref_content = null;
                    $compte_rendu->user_id      = null;
                    $compte_rendu->function_id  = null;
                    $compte_rendu->group_id     = null;
                    $do->_obj                   = $compte_rendu;
                }

                $compte_rendu->_source   = $content;
                $compte_rendu->nom       .= " à {$allDest[$curr_dest[1]][$curr_dest[2]]->nom}";
                $compte_rendu->modele_id = $modele_base->_id;

                $do->doStore();
                $ids_corres .= "{$do->_obj->_id}-";
            }

            [$dest_class, $dest_id] = explode("-", $allDest[$curr_dest[1]][$curr_dest[2]]->object_guid);

            if ($dest_class !== 'CMediusers') {
                // On relie à un destinataire item pour un publipostage postérieur
                $destinataire_item                = new CDestinataireItem();
                $destinataire_item->docitem_class = $do->_obj->_class;
                $destinataire_item->docitem_id    = $do->_obj->_id;

                $destinataire_item->dest_class = $dest_class;
                $destinataire_item->dest_id    = $dest_id;
                $destinataire_item->tag        = $allDest[$curr_dest[1]][$curr_dest[2]]->tag;

                if ($dest_class === 'CMedecin' && isset($_medecin_exercice_place[$destinataire_item->dest_id])) {
                    $destinataire_item->medecin_exercice_place_id =
                        $_medecin_exercice_place[$destinataire_item->dest_id];
                }
                if (CAppUI::pref("multiple_doc_correspondants")) {
                    $msg = $destinataire_item->store();
                    if ($msg) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }
                } else {
                    $dest_to_store[] = $destinataire_item;
                }
            }
        }
    }
}

if (!count($destinataires) || !$do_merge || !CAppUI::pref("multiple_doc_correspondants")) {
    $do->doBind();
    if (intval(CValue::post("del"))) {
        $do->doDelete();
    } else {
        $do->doStore();
        foreach ($dest_to_store as $_dest_to_store) {
            $_dest_to_store->docitem_id = $do->_obj->_id;

            $msg = $_dest_to_store->store();

            if ($msg) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
        // Pour le fast mode en impression navigateur, on envoie la source du document complet.
        $margins               = [
            $do->_obj->margin_top,
            $do->_obj->margin_right,
            $do->_obj->margin_bottom,
            $do->_obj->margin_left,
        ];
        $do->_obj->_entire_doc = (new CCompteRendu())->loadHTMLcontent(
            $do->_obj->_source,
            "",
            $margins,
            CCompteRendu::$fonts[$do->_obj->font],
            $do->_obj->size
        );
    }
}

// On supprime les correspondants
$correspondants_courrier = $do->_obj->loadRefsCorrespondantsCourrier();
/** @var $_corres CCorrespondantCourrier */
foreach ($correspondants_courrier as $_corres) {
    if ($msg = $_corres->delete()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
}

// Gestion des CCorrespondantCourrier
if (!$do_merge && !intval(CValue::post("del")) && strpos($do->_obj->_source, "[Courrier -") && isset($_POST["object_class"])) {
    // On stocke les correspondants cochés
    $object = new $_POST["object_class"];
    $object->load($_POST["object_id"]);

    $allDest = (new MailReceiverService($object))->getReceivers();

    foreach ($allDest as $class => $_dest_by_class) {
        foreach ($_dest_by_class as $i => $_dest) {
            if (!isset($_POST["_dest_{$class}_$i"])) {
                continue;
            }
            [$object_class, $object_id] = explode("-", $_dest->object_guid);
            $corres                  = new CCorrespondantCourrier();
            $corres->compte_rendu_id = $do->_obj->_id;
            $corres->tag             = $_dest->tag;
            $corres->object_id       = $object_id;
            $corres->object_class    = $object_class;

            if ($object_class === 'CMedecin' && isset($_medecin_exercice_place[$object_id])) {
                $corres->medecin_exercice_place_id = $_medecin_exercice_place[$object_id];
            }
            $corres->quantite        = $_POST["_count_{$class}_$i"];

            if ($msg = $corres->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
            unset($_POST["_dest_{$class}_$i"]);
        }
    }

    // Correspondants courrier ajoutés par autocomplete
    foreach ($_POST as $key => $value) {
        if (preg_match("/_dest_([a-zA-Z]*)_([0-9]+)/", $key, $matches)) {
            $corres                  = new CCorrespondantCourrier();
            $corres->compte_rendu_id = $do->_obj->_id;
            $corres->tag             = "correspondant";
            $corres->object_id       = $matches[2];
            $corres->object_class    = $matches[1];

            if ($object_class === 'CMedecin' && isset($_medecin_exercice_place[$corres->object_id])) {
                $corres->medecin_exercice_place_id = $_medecin_exercice_place[$corres->object_id];
            }

            if ($msg = $corres->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
    }
}

if (strlen($ids_corres)) {
    $do->_obj->_ids_corres = $ids_corres;
}

if ($do->ajax) {
    $do->doCallback();
} else {
    // Si c'est un compte rendu
    if ($do->_obj->object_id && !intval(CValue::post("del"))) {
        $do->redirect = "m=compteRendu&a=edit&dialog=1&compte_rendu_id=" . $do->_obj->_id . "&unique_id=" . $_POST["unique_id"];
    } else {
        if (intval(CValue::post("del") && isset($_POST["_tab"]))) {
            $do->redirect = "m=compteRendu&a=vw_modeles";
        } else {
            // Si c'est un modèle de compte rendu
            $do->redirect = "m=compteRendu&a=addedit_modeles&dialog=1&compte_rendu_id=" . $do->_obj->_id;
        }
    }
    $do->doRedirect();
}
