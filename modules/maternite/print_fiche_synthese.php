<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesse;
use Ox\Mediboard\Maternite\CEchoGraph;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkRead();
$grossesse_id = CView::get('grossesse_id', 'ref class|CGrossesse');
$offline      = CView::get("offline", "bool default|0");
CView::checkin();

$lines_tp = [];

$grossesse = CGrossesse::findOrFail($grossesse_id);

$grossesse->loadRefsSurvEchographies();
$dossier_perinatal = $grossesse->loadRefDossierPerinat();
$patient           = $grossesse->loadRefParturiente();
$pere              = $grossesse->loadRefPere();
$dossier_medical   = $patient->loadRefDossierMedical();
$dossier_medical->loadRefsTraitements();
$last_consult = $grossesse->loadLastConsult();
if ($last_consult) {
    $last_consult->loadRefPraticien();
}
$patient->loadRefsCorrespondants();
$last_constantes  = CConstantesMedicales::getLatestFor($patient, null, ["poids", "taille"], null, false);
$constantes_maman = $dossier_perinatal->loadRefConstantesAntecedentsMaternels();
$constantes_maman->getIMC();

$difference_poids = 0;

if ($constantes_maman->poids && $last_constantes[0]->poids) {
    $difference_poids = $last_constantes[0]->poids - $constantes_maman->poids;
}

$sejours = $grossesse->loadRefsSejours();
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

foreach ($sejours as $_sejour) {
    $_sejour->loadRefPraticien();
}

$prescription_lines = [];
$dossier_medical->loadRefPrescription();

if ($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_id) {
    foreach ($dossier_medical->_ref_prescription->_ref_prescription_lines as $_line) {
        if ($_line->fin && $_line->fin <= CMbDT::date()) {
            continue;
        }
        $_line->loadRefsPrises();
        $prescription_lines[$_line->_id] = $_line;
    }

    $dossier_medical->_ref_prescription->_ref_prescription_lines = $prescription_lines;
}

$counter_depisage = [
    "immuno"     => 0,
    "serologie"  => 0,
    "biochimie"  => 0,
    "urine"      => 0,
    "bactero"    => 0,
    "trimestre1" => 0,
    "trimestre2" => 0,
    "general"    => 0,
    "vaginal"    => 0,
    "custom"     => 0,
];

// Get the custom fields (create by the user)
$depistage_customs       = [];
$depistage_field_customs = [];
$depistages              = $grossesse->loadBackRefs("depistages", "date ASC");

foreach ($depistages as $depistage) {
    $depistage->getSA();
    $depistage_customs = $depistage->loadRefsDepistageGrossesseCustom();

    $counter_depisage["custom"] = count($depistage_customs);

    foreach ($depistage_customs as $_depistage_custom) {
        if (!isset($depistage_field_customs[$_depistage_custom->libelle])) {
            foreach ($depistages as $_depistage) {
                $depistage_field_customs[$_depistage_custom->libelle][$_depistage->_id] = null;
            }
        }
        $depistage_field_customs[$_depistage_custom->libelle][$depistage->_id] = $_depistage_custom->valeur;
    }
}

ksort($depistage_field_customs);

$last_depistage               = end($depistages);
$last_depistages["object"]    = $last_depistage ?: new CDepistageGrossesse();
$last_depistages["immuno"]    = [
    "groupe_sanguin",
    "rhesus",
    "rhesus_bb",
    "rai",
    "test_kleihauer",
    "val_kleihauer",
    "rques_immuno",
];
$last_depistages["serologie"] = [
    "syphilis",
    "TPHA",
    "toxoplasmose",
    "rubeole",
    "hepatite_b",
    "hepatite_c",
    "vih",
    "parvovirus",
    "cmvg",
    "cmvm",
    "varicelle",
    "htlv",
    "rques_serologie",
];
$last_depistages["biochimie"] = [
    "nfs_hb",
    "gr",
    "gb",
    "vgm",
    "ferritine",
    "glycemie",
    "electro_hemoglobine_a1",
    "electro_hemoglobine_a2",
    "electro_hemoglobine_s",
    "tp",
    "tca",
    "fg",
    "nfs_plaquettes",
    "depistage_diabete",
    "rques_biochimie",
];

$last_depistages["urine"]      = ["albuminerie", "glycosurie", "albuminerie_24", "cbu"];
$last_depistages["bactero"]    = [
    "acide_urique",
    "asat",
    "alat",
    "phosphatase",
    "brb",
    "sel_biliaire",
    "creatininemie",
    "rques_bacteriologie",
];
$last_depistages["trimestre1"] = ["marqueurs_seriques_t21", "dpni", "dpni_rques", "pappa", "hcg1", "rques_t1"];
$last_depistages["trimestre2"] = ["afp", "hcg2", "estriol", "rques_t2"];
$last_depistages["general"]    = ["amniocentese", "pvc", "rques_hemato"];
$last_depistages["vaginal"]    = ["strepto_b", "parasitobacteriologique", "rques_vaginal"];

if ($depistages && count($depistages) > 0) {
    foreach ($depistages as $_depistage) {
        foreach ($last_depistages as $key_chapter => $_fields) {
            foreach ($_fields as $_field) {
                if (!in_array($key_chapter, ["object", "immuno", "serologie"]) && $_depistage->$_field) {
                    $counter_depisage[$key_chapter]++;
                }
            }
        }
    }
}

$immuno_serology = [];

// Get the last value for immuno and serology
foreach ($depistages as $_depistage) {
    foreach ($last_depistages as $key_chapter => $_fields) {
        foreach ($_fields as $_field) {
            if (in_array($key_chapter, ["immuno", "serologie"])) {
                if (!isset($immuno_serology[$key_chapter][$_field])) {
                    $immuno_serology[$key_chapter][$_field] = html_entity_decode('&mdash;', ENT_COMPAT);
                }

                if ($_depistage->$_field) {
                    $date  = CMbDT::format($_depistage->date, CAppUI::conf('date'));
                    $value = CAppUI::tr("CDepistageGrossesse.$_field." . $_depistage->$_field);

                    if ($_field == "val_kleihauer" || $_field == "rques_immuno" || $_field == "rques_serologie") {
                        $value = $_depistage->$_field . $_field == "val_kleihauer" ? " /ml" : "";
                    }

                    $style = "";

                    if (($_field == "rhesus" && $_depistage->$_field == "neg") ||
                        ($_field == "rai" && $_depistage->$_field == "pos")) {
                        $style = "font-weight: bold; color: red;";
                    }

                    $immuno_serology[$key_chapter][$_field] = "<span style='{$style}'>$value</span>" . " ($date)";

                    $counter_depisage[$key_chapter]++;
                }
            }
        }
    }
}

// Father antecedent
$father            = $grossesse->loadRefPere();
$father_constantes = $dossier_perinatal->loadRefConstantesAntecedentsPaternels();
$father->loadRefDossierMedical();

$father_atcd["counter"]     = 0;
$father_atcd["antecedents"] = [
    "ant_fam_pere_gemellite",
    "ant_fam_pere_malformations",
    "ant_fam_pere_maladie_genique",
    "ant_fam_pere_maladie_chrom",
    "ant_fam_pere_diabete",
    "ant_fam_pere_hta",
    "ant_fam_pere_phlebite",
    "ant_fam_pere_autre",
];

foreach ($father_atcd["antecedents"] as $_field) {
    if ($dossier_perinatal->$_field) {
        $father_atcd["counter"]++;
    }
}

// Echograhy
$list_children = [];
$survEchoList  = [];
$echographies  = $grossesse->loadBackRefs("echographies", "date ASC");

/** @var CSurvEchoGrossesse $_echographie */
foreach ($echographies as $_echographie) {
    $_echographie->getSA();

    if ($grossesse->multiple) {
        $list_children[$_echographie->num_enfant][$_echographie->_id] = $_echographie;
    } else {
        $list_children["1"][$_echographie->_id] = $_echographie;
    }
}

// Graphs
$graphs       = "lcc|cn|bip|pc|pa|lf|poids_foetal";
$list_graphs  = explode("|", $graphs);
$survEchoData = [];
$all_graphs   = [];

foreach ($list_graphs as $_graph) {
    foreach ($echographies as $_echographie) {
        $age_gest = $grossesse->getAgeGestationnel($_echographie->date);
        $x        = $age_gest['SA'];

        if ($_graph === 'cn') {
            $x = $age_gest['SA'] * 7 + $age_gest['JA'];
        }

        if ($grossesse->multiple && $_echographie->num_enfant) {
            $survEchoList[$_graph][$_echographie->num_enfant][] = [
                $x,
                $_echographie->$_graph,
                'id' => $_echographie->_id,
            ];

            $survEchoData[$_graph][$_echographie->num_enfant] = [
                'id'     => "{$_graph}_{$grossesse_id}",
                'label'  => "Enfant n°" . $_echographie->num_enfant,
                'data'   => $survEchoList[$_graph][$_echographie->num_enfant],
                'lines'  => ['show' => true],
                'points' => ['show' => true],
            ];
        } else {
            $survEchoList[$_graph]["1"][] = [$x, $_echographie->$_graph, 'id' => $_echographie->_id];

            $survEchoData[$_graph]["1"] = [
                'id'     => "{$_graph}_{$grossesse_id}",
                'label'  => 'Enfant',
                'data'   => $survEchoList[$_graph]["1"],
                'lines'  => ['show' => true],
                'points' => ['show' => true],
                'color'  => 'rgb(0,0,0)',
            ];
        }

        $all_graphs[$_graph][$_echographie->num_enfant ?: "1"]["survEchoData"] = [$survEchoData[$_graph][$_echographie->num_enfant ?: "1"]];
        $all_graphs[$_graph][$_echographie->num_enfant ?: "1"]["graph_axes"]   = CEchoGraph::formatGraphDataset(
            $_graph
        );
    }
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("depistages", $depistages);
$smarty->assign("last_constantes", $last_constantes);
$smarty->assign("difference_poids", $difference_poids);
$smarty->assign("last_depistages", $last_depistages);
$smarty->assign("immuno_serology", $immuno_serology);
$smarty->assign("depistage_field_customs", $depistage_field_customs);
$smarty->assign("list_children", $list_children);
$smarty->assign("graphs", $graphs);
$smarty->assign("list_graphs", $list_graphs);
$smarty->assign("counter_depisage", $counter_depisage);
$smarty->assign("father_atcd", $father_atcd);
$smarty->assign("offline", $offline);
$smarty->assign("all_graphs", $all_graphs);
$smarty->display("print_fiche_synthese");
