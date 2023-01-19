<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Patients\CCourbeReference;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id   = CView::get('patient_id', "ref class|CPatient", true);
$graph_name   = CView::get('graph_name', "str", true);
$constantName = CView::get('constantName', "str");
$graph_print  = CView::get('graph_print', "num default|0");
CView::checkin();

if (!$constantName) {
    $xTickFormat = 0;
    $tab         = [];
    $tab_xaxis   = [];
    $i           = 0;

    $patients = new CPatient();
    $patient  = $patients->load($patient_id);
    $patient->evalAgeMois();
    $constantes = $patient->loadRefsConstantesMedicales("datetime ASC");

    $data_courbe = new CCourbeReference();

    $age_min = 3;
    $age_max = 18;
    $day_year_min = 0.0328767; // 1 Day = 0,00273973 year
    $day_year_max = 0.00273973;

    $sexe        = $patient->sexe;
    $age         = ((!in_array($graph_name, ["_imc", "perimetre_cranien"]) && $patient->_mois < 36) || (($graph_name == "perimetre_cranien") && $patient->_mois < 60)) ? $age_min : $age_max;
    $number_aged = ((!in_array($graph_name, ["_imc", "perimetre_cranien"]) && $patient->_mois < 36) || (($graph_name == "perimetre_cranien") && $patient->_mois < 60)) ? $day_year_min : $day_year_max;

    $SA                  = null;
    $date_time_naissance = "$patient->naissance 00:00:00";

    if (CModule::getActive('maternite')) {
        $patient->loadRefNaissance()->loadRefGrossesse();
        $semaine_grossesse = $patient->_ref_naissance->_ref_grossesse->_semaine_grossesse;
        $date_time_naissance = $patient->_ref_naissance->date_time;

        if ($semaine_grossesse) {
            if ($semaine_grossesse >= 35) {
                $SA = "+35";
            } else {
                $SA = "-35";
            }
        }
    }

    $datas = $data_courbe::getReferencePointsXMLtoArray($graph_name, $sexe, $age, $SA);

    if ($graph_name == "taille" || $graph_name == "poids") {
        $courbe_reference = $datas[$sexe][$age];
    } elseif ($graph_name == "bilirubine_transcutanee") {
        $courbe_reference = $datas;
    } elseif ($graph_name == "bilirubine_totale_sanguine") {
        $courbe_reference = $datas[$SA];
    } else {
        $courbe_reference = $datas[$sexe];
    }

    if ($graph_name == "bilirubine_transcutanee") {
        foreach ($constantes as $_constante) {
            $calculHour[] = CMbDT::hoursRelative($date_time_naissance, $_constante->datetime);
            if ($_constante->_bilirubine_transcutanee_front && $_constante->_bilirubine_transcutanee_sternum && $calculHour[$i] <= 144) {
                $tab[] = [
                    $calculHour[$i],
                    $_constante->_bilirubine_transcutanee_front,
                    $_constante->_bilirubine_transcutanee_sternum,
                ];
            }
            $i++;
        }
    } elseif ($graph_name == "bilirubine_totale_sanguine") {
        foreach ($constantes as $_constante) {
            $calculHour[] = CMbDT::hoursRelative($date_time_naissance, $_constante->datetime);
            if ($_constante->$graph_name && (($SA == "+35" && $calculHour[$i] <= 168) || ($SA == "-35" &&
                        $calculHour[$i] <= 250))) {
                $tab[] = [$calculHour[$i], $_constante->$graph_name];
            }
            $i++;
        }
    } else {
        foreach ($constantes as $_constante) {
            // calcul a partir de la date de naissance,le nombre d"années par rapport a l'ajout d'une constante
            $calculMonth[] = CMbDT::daysRelative($patient->naissance, $_constante->datetime) * $number_aged;

            if ($_constante->$graph_name) {
                $tab[] = [$calculMonth[$i], $_constante->$graph_name];
            }
            $i++;
        }
    }

    // recupere le min et max de l'axe y
    $yaxis_min = $courbe_reference["ymin"];
    $yaxis_max = $courbe_reference["ymax"];

    // recupere le pas de l'ordonnée y et l'abcisse x
    $yTickStep = $courbe_reference["yTickSize"];
    $xTickStep = $courbe_reference["xTickSize"];

    // recupere le nom de la courbe de croissance
    $growthCurveName = $courbe_reference["name"];

    // recupere l'unit de mesure
    $unit = CCourbeReference::$graph_datas[$graph_name]["unit"];

    // recupere le type d'age (mois ou ans)
    $type_age = $courbe_reference["age"]["type_age"];

    // Recupere l'axe x
    $xaxis_min = $courbe_reference["age"]["age_min"];
    $xaxis_max = $courbe_reference["age"]["age_max"];

    $total = count($courbe_reference["reference"]);
    for ($j = 0; $j <= $total; $j++) {
        $tab_xaxis[] = [$j];
    }

    if ($graph_name == "bilirubine_transcutanee") {
        $Data[] = [
            'label'     => CAppUI::tr("courbe_patient"),
            'id'        => "{$patient_id}",
            'data'      => $tab,
            'bandwidth' => ['show' => true, 'lineWidth' => '6px'],
            'color'     => 'rgb(169,32,32)',
            'hoverable' => true,
        ];
    } else {
        $Data[] = [
            'label'     => CAppUI::tr("courbe_patient"),
            'id'        => "{$patient_id}",
            'data'      => $tab,
            'lines'     => ['show' => true, 'lineWidth' => 3],
            'points'    => ['show' => true, 'symbol' => "square"],
            'color'     => 'rgb(169,32,32)',
            'hoverable' => true,
        ];
    }
}

$smarty = new CSmartyDP();

$smarty->assign('graph_name', $graph_name);

if ($constantName) {
    $smarty->assign('constantName', $constantName);
    $smarty->assign('patient_id', $patient_id);

    $smarty->display('inc_select_courbe_reference_graph');
} else {
    $smarty->assign('graph_axes', CCourbeReference::formatGraphDataset($graph_name, $sexe, $age, $SA));
    $smarty->assign('growthCurveName', $growthCurveName);
    $smarty->assign('Data', $Data);
    $smarty->assign('yMin', $yaxis_min);
    $smarty->assign('yMax', $yaxis_max);
    $smarty->assign('xMin', $xaxis_min);
    $smarty->assign('xMax', $xaxis_max);
    $smarty->assign('unit', $unit);
    $smarty->assign('tab_xaxis', $tab_xaxis);
    $smarty->assign('yTickStep', $yTickStep);
    $smarty->assign('xTickStep', $xTickStep);
    $smarty->assign('xTickFormat', $xTickFormat);
    $smarty->assign('type_age', $type_age);
    $smarty->assign('graph_print', $graph_print);

    $smarty->display('inc_courbe_reference_graph');
}

