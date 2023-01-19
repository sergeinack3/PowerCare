<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkAdmin();

CMbObject::$useObjectCache = false;

$axe            = CView::get('axe', 'str', true);
$entree         = CView::get('entree', 'date', true);
$sortie         = CView::get('sortie', 'date', true);
$period         = CView::get('period', 'enum list|HOUR|DAY|WEEK|MONTH default|DAY', true);
$hide_cancelled = CView::get('hide_cancelled', 'bool default|1', true);
$days           = CView::get('days', 'str', true);
$holidays       = Cview::get('holidays', 'bool default|0', true);
$age_min        = CView::get('age_min', array('str', 'default' => array(0, 15, 75, 85)), true);
$age_max        = CView::get('age_max', array('str', 'default' => array(14, 74, 84)), true);
$service_id     = CView::get('service_id', 'ref class|CService', true);

CView::checkin();

CView::enforceSlave();

/**
 * @param $areas
 * @param $series
 * @param $where
 * @param $ljoin
 * @param $dates
 * @param $period
 * @param $sejour       CSejour
 * @param $total
 * @param $start_field
 * @param $end_field
 */
function computeAttente($areas, &$series, $where, $ljoin, $dates, $period, $sejour, &$total, $start_field, $end_field) {
  $only_duration = empty($areas);

  list($_start_class, $_start_field) = explode(".", $start_field);
  list($_end_class, $_end_field) = explode(".", $end_field);

  // never when ljoin on consult (form field)
  if (strpos($start_field, "._") === false) {
    $where[$start_field] = "IS NOT NULL";
  }

  if (strpos($end_field, "._") === false) {
    $where[$end_field] = "IS NOT NULL";
  }

  if (!$only_duration) {
    foreach ($areas as $key => $value) {

      // never when ljoin on consult (form field)
      if (isset($value[$start_field]) && strpos($start_field, "._") === false) {
        $where[$start_field] = $value[$start_field];
      }

      if (isset($value[$end_field]) && strpos($end_field, "._") === false) {
        $where[$end_field] = $value[$end_field];
      }

      $series[$key] = array('data' => array(), "label" => $value[0]);

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
  }

  // Time
  $areas = array_merge(array(null));
  foreach ($areas as $key => $value) {
    $key = count($series);

    $series[$key] = array(
      'data'       => array(),
      'yaxis'      => ($only_duration ? 1 : 2),
      'lines'      => array("show" => true),
      'points'     => array("show" => true),
      'mouse'      => array("track" => true, "trackFormatter" => "timeLabelFormatter"),
      'label'      => ($only_duration ? "" : "Temps"),
      'color'      => "red",
      "shadowSize" => 0,
    );

    foreach ($dates as $i => $_date) {
      $_date_next             = CMbDT::dateTime("+1 $period", $_date);
      $where['sejour.entree'] = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
      /** @var CSejour[] $_sejours */
      $_sejours = $sejour->loadList($where, null, null, null, $ljoin);

      if ($_start_class == "rpu" || $_end_class == "rpu" || $_end_class == "rpu_attente") {
        CStoredObject::massLoadBackRefs($_sejours, "rpu");
      }

      if ($_start_class == "consultation" || $_end_class == "consultation") {
        $consultations = CStoredObject::massLoadBackRefs($_sejours, "consultations");
        CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
      }
      $type_attente = null;
      $times        = array();
      foreach ($_sejours as $_sejour) {
        // load RPU
        if ($_start_class == "rpu" || $_end_class == "rpu" || $_end_class == "rpu_attente") {
          $_sejour->loadRefRPU();
          $_rpu = $_sejour->_ref_rpu;
          if ($_end_class == "rpu_attente") {
            $type_attente = str_replace(array("=", " ", "'"), "", $where["rpu_attente.type_attente"]);
            $_rpu->loadRefsLastAttentes(array($type_attente));
          }
        }

        // load consult
        if ($_start_class == "consultation" || $_end_class == "consultation") {
          $_sejour->loadRefsConsultations();
          $_consult = $_sejour->_ref_consult_atu;
          if (!$_consult || !$_consult->heure) {
            continue;
          }
          $_consult->loadRefPlageConsult();
          if (!$_consult->_date) {
            continue;
          }
        }

        switch ($_start_class) {
          case "sejour":
            $_start_object = $_sejour;
            break;
          case "rpu":
            $_start_object = $_rpu;
            break;
          case "consultation":
            $_start_object = $_consult;
            break;
          case "rpu_attente":
            $_start_object = $_rpu->_ref_last_attentes[$type_attente];
            break;
        }

        switch ($_end_class) {
          case "sejour":
            $_end_object = $_sejour;
            break;
          case "rpu":
            $_end_object = $_rpu;
            break;
          case "consultation":
            $_end_object = $_consult;
            break;
          case "rpu_attente":
            $_end_object = $_rpu->_ref_last_attentes[$type_attente];
            break;
        }

        $start = $_start_object->$_start_field;
        $end   = $_end_object->$_end_field;

        if ($start && $end) {
          $times[] = CMbDT::minutesRelative($start, $end);
        }
      }
      $count = array_sum($times);
      $mean  = count($times) ? $count / count($times) : 0;

      $variance = 0;
      foreach ($times as $time) {
        $variance += pow($time - $mean, 2);
      }
      if (count($times)) {
        $variance /= count($times);
      }
      $std_dev = sqrt($variance);

      $series[$key]['data'][$i] = array($i, $mean);

      // mean - std_dev
      if (!isset($series[$key + 1])) {
        $series[$key + 1]                       = $series[$key];
        $series[$key + 1]["color"]              = "#666";
        $series[$key + 1]["lines"]["lineWidth"] = 1;
        $series[$key + 1]["points"]["show"]     = false;
        $series[$key + 1]["label"]              = "Temps - écart type";
      }
      $series[$key + 1]['data'][$i] = array($i, $mean - $std_dev);

      // mean + std_dev
      if (!isset($series[$key + 2])) {
        $series[$key + 2]                       = $series[$key];
        $series[$key + 2]["color"]              = "#666";
        $series[$key + 2]["lines"]["lineWidth"] = 1;
        $series[$key + 2]["points"]["show"]     = false;
        $series[$key + 2]["label"]              = "Temps + écart type";
      }
      $series[$key + 2]['data'][$i] = array($i, $mean + $std_dev);
    }
  }

  // Echange du dernier et tu premier des lignes pour avoir celle du milieu en avant plan
  $c = count($series);
  list($series[$c - 3], $series[$c - 1]) = array($series[$c - 1], $series[$c - 3]);
}

switch ($period) {
  default:
    $period = "DAY";
  case "DAY":
    $format = CAppUI::conf("date");
    break;

  case "WEEK";
    $format = "%V";
    $entree = CMbDT::date("+1 day last sunday", $entree);
    break;

  case "MONTH";
    $format = "%m/%Y";
    $entree = CMbDT::date("first day of this month", $entree);
    break;

  case "HOUR":
    $format = "%Hh%M";
    break;
}

if ($entree > $sortie) {
  list($entree, $sortie) = array($sortie, $entree);
}

if ($period != "HOUR" && $entree == $sortie) {
  $sortie = CMbDT::date("+1 day", $entree);
}

// Dates
$dates  = array();
$date   = CMbDT::dateTime($entree . " 00:00:00");
$sortie = CMbDT::dateTime(($period == "HOUR" ? $entree : $sortie) . " 23:59:59");

/* Récupération de la liste des jours fériés */
if ($holidays) {
  $holidays = array_keys(CMbDT::getHolidays($entree));
  if (CMbDT::format($entree, '%Y') != CMbDT::format($sortie, '%Y')) {
    $holidays = array_merge($holidays, array_keys(CMbDT::getHolidays($sortie)));
  }
}
else {
  $holidays = array();
}

$n = ($period == "HOUR") ? 100 : 400;
while ($date < $sortie && $n-- > 0) {
  /* Si les jours fériés ou une liste de jours de la semaine est sélectionnée, seules les dates correspondantes sont conservées */
  if (((!$days || empty($days)) || in_array(intval(CMbDT::format($date, '%u')), $days))
    && (empty($holidays) || in_array(CMbDT::date($date), $holidays))
  ) {
    $dates[] = $date;
  }
  $date = CMbDT::dateTime("+1 $period", $date);
}

$group = CGroups::loadCurrent();

$criteres_passage_uhcd = CAppUI::gconf("dPurgences CRPU criteres_passage_uhcd");

$where = array(
  "sejour.entree"   => null, // Doit toujours etre redefini
  "sejour.group_id" => "= '$group->_id'",
  "rpu.rpu_id"      => "IS NOT NULL",
);

if (!in_array($axe, array("passages_uhcd", "mutations_count"))) {
  $where[] = "sejour.type " . CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence()) . " OR sejour.UHCD = '1'";
}

if ($service_id) {
  $ds                         = $group->getDS();
  $where["sejour.service_id"] = $ds->prepare("= ?", $service_id);
}

if ($hide_cancelled) {
  $where["sejour.annule"] = "= '0'";
}

$ljoin = array(
  'rpu' => 'sejour.sejour_id = rpu.sejour_id',
);

$rpu    = new CRPU();
$sejour = new CSejour();
$total  = 0;

$data = array();

switch ($axe) {
  default:
    $axe = "age";

  // Sur le patient
  case "age":
    $data[$axe] = array(
      'options' => array(
        'title' => 'Par tranche d\'âge'
      ),
      'series'  => array()
    );

    $ljoin['patients'] = 'patients.patient_id = sejour.patient_id';

    $series = &$data[$axe]['series'];
    foreach ($age_min as $index => $age) {
      if ($age == null || $age == '') {
        continue;
      }

      $limits = array($age, CValue::read($age_max, $index));

      // La borne max (en années) si définie doit être strictement inférieure à la borne minimale suivante
      // donc on incrémente d'une unité
      if ($limits[1]) {
        $limits[1]++;
      }

      $label = $limits[1] ? ("$limits[0] - " . ($limits[1])) : ">= $limits[0]";

      $min = intval($limits[0]) * 365.25;
      if ($limits[1] != '') {
        $max = intval($limits[1]) * 365.25;
      }

      $where[100] = "TO_DAYS(sejour.entree) - TO_DAYS(patients.naissance) >= $min";

      if ($limits[1] != null) {
        $where[101] = "TO_DAYS(sejour.entree) - TO_DAYS(patients.naissance) < $max";
      }
      else {
        unset($where[101]);
      }

      $series[$index] = array('data' => array(), 'label' => "$label ans");

      foreach ($dates as $i => $_date) {
        $_date_next                 = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']     = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                      = $sejour->countList($where, null, $ljoin);
        $total                      += $count;
        $series[$index]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Sur le patient
  case "sexe":
    $data[$axe] = array(
      "options" => array(
        "title" => 'Par sexe'
      ),
      "series"  => array()
    );

    $ljoin['patients'] = 'patients.patient_id = sejour.patient_id';

    $series = &$data[$axe]['series'];
    $areas  = array("m", "f");
    foreach ($areas as $key => $value) {
      $label                  = CAppUI::tr("CPatient.$axe.$value");
      $where["patients.$axe"] = "= '$value'";
      $series[$key]           = array('data' => array(), 'label' => $label);

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Sur le RPU
  case "ccmu":
  case "orientation":
    $data[$axe] = array(
      "options" => array(
        "title" => CAppUI::tr("CRPU-$axe")
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    $areas  = array_merge(array(null), array_values($rpu->_specs[$axe]->_list));
    foreach ($areas as $key => $value) {
      $label             = CAppUI::tr("CRPU.$axe.$value");
      $where["rpu.$axe"] = (is_null($value) ? "IS NULL" : "= '$value'");
      $series[$key]      = array('data' => array(), 'label' => $label);

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Sur le séjour
  case "provenance":
  case "destination":
  case "transport":
  case "mode_entree":
  case "mode_sortie":
    $data[$axe] = array(
      "options" => array(
        "title" => CAppUI::tr("CSejour-$axe")
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    $areas  = array_merge(array(null), array_values($sejour->_specs[$axe]->_list));
    foreach ($areas as $key => $value) {
      $label                = CAppUI::tr("CSejour.$axe.$value");
      $where["sejour.$axe"] = (is_null($value) ? "IS NULL" : "= '$value'");
      $series[$key]         = array('data' => array(), 'label' => $label);

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Séjour sans RPU
  case "without_rpu":
    $data[$axe] = array(
      "options" => array(
        "title" => "Séjours d'urgence sans RPU"
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    $areas  = array_merge(array(null));
    foreach ($areas as $key => $value) {
      $where["rpu.rpu_id"] = "IS NULL";
      $series[$key]        = array('data' => array());

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Nombre de transferts
  case "transfers_count":
    $data[$axe] = array(
      "options" => array(
        "title" => "Nombre de transferts"
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];

    $sejour = new CSejour;
    $end    = end($dates);
    $start  = reset($dates);

    $query = new CRequest;
    $query->addSelect("sejour.etablissement_sortie_id");
    $query->addTable("sejour");
    $query->addGroup("sejour.etablissement_sortie_id");
    $query->addWhere(
      array(
        "DATE(sejour.entree) BETWEEN '" . CMbDT::date($start) . "' AND '" . CMbDT::date($end) . "'",
        "sejour.etablissement_sortie_id" => "IS NOT NULL",
      )
    );
    $etab_externe_ids = $sejour->_spec->ds->loadColumn($query->makeSelect());

    $etab_externe         = new CEtabExterne;
    $etabs                = $etab_externe->loadList(
      array($etab_externe->_spec->key => $etab_externe->_spec->ds->prepareIn($etab_externe_ids))
    );
    $etabs["none"]        = $etab_externe;
    $etabs["none"]->_view = "Non renseigné";

    $where["sejour.mode_sortie"] = "= 'transfert'";

    $key = 0;
    foreach ($etabs as $_id => $_etab) {
      $series[$key] = array('data' => array(), 'label' => $_etab->_view);

      $sub_total = 0;
      foreach ($dates as $i => $_date) {
        $_date_next                              = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']                  = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $where['sejour.etablissement_sortie_id'] = ($_id === "none" ? "IS NULL" : "= '$_id'");
        $count                                   = $sejour->countList($where, null, $ljoin);
        $total                                   += $count;
        $sub_total                               += $count;
        $series[$key]['data'][$i]                = array($i, intval($count));
      }
      $series[$key]['subtotal'] = $sub_total;
      $key++;
    }

    // suppression des series vides
    foreach ($series as $_key => $_serie) {
      if ($_serie['subtotal'] == 0) {
        unset($series[$_key]);
      }
    }
    $series = array_values($series);
    break;
  // Nombre de mutations
  case "mutations_count":
    $data[$axe] = array(
      "options" => array(
        "title" => "Nombre de mutations"
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];

    $service            = new CService();
    $service->group_id  = CGroups::loadCurrent()->_id;
    $service->cancelled = 0;
    $services           = $service->loadMatchingList("nom");

    $services["none"]        = new CService();
    $services["none"]->_view = "Non renseigné";

    $where["sejour.mode_sortie"] = "= 'mutation'";

    $key = 0;
    foreach ($services as $_id => $_service) {
      $series[$key] = array('data' => array(), 'label' => $_service->_view);

      $sub_total = 0;
      foreach ($dates as $i => $_date) {
        $_date_next                        = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']            = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $where['sejour.service_sortie_id'] = ($_id === "none" ? "IS NULL" : "= '$_id'");
        $count                             = $sejour->countList($where, null, $ljoin);
        $total                             += $count;
        $sub_total                         += $count;
        $series[$key]['data'][$i]          = array($i, intval($count));
      }
      $series[$key]['subtotal'] = $sub_total;
      $key++;
    }

    // suppression des series vides
    foreach ($series as $_key => $_serie) {
      if ($_serie['subtotal'] == 0) {
        unset($series[$_key]);
      }
    }
    $series = array_values($series);
    break;
  case "accident_travail_count":
    $where["rpu.date_at"] = "IS NOT NULL";

    $data[$axe] = array(
      "options" => array(
        "title" => "Nombre d'accidents de travail renseignés"
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    $areas  = array_merge(array(null));
    foreach ($areas as $key => $value) {
      $series[$key] = array('data' => array());

      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree']   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
    }
    break;
  // Radio
  case "radio":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Attente radio",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series                                = &$data[$axe]['series'];
    $areas                                 = array(
      array(
        "Sans radio",
        "rpu_attente.depart" => "IS NULL",
        "rpu_attente.retour" => "IS NULL",
      ),
      array(
        "Attente radio sans retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NULL", // FIXME: prendre en charge les attentes de moins d'une minute
      ),
      array(
        "Attente radio avec retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NOT NULL",
      ),
    );
    $ljoinSpec                             = $ljoin;
    $ljoinSpec["rpu_attente"]              = "rpu_attente.rpu_id = rpu.rpu_id";
    $whereSpec                             = $where;
    $whereSpec["rpu_attente.type_attente"] = " = 'radio'";
    $start_field                           = "rpu_attente.depart";
    $end_field                             = "rpu_attente.retour";
    computeAttente($areas, $series, $whereSpec, $ljoinSpec, $dates, $period, $sejour, $total, $start_field, $end_field);
    break;
  // Biolo
  case "bio":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Attente biologie",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    $areas  = array(
      array(
        "Sans biologie",
        "rpu_attente.depart" => "IS NULL",
        "rpu_attente.retour" => "IS NULL",
      ),
      array(
        "Attente biologie sans retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NULL", // FIXME: prendre en charge les attentes de moins d'une minute
      ),
      array(
        "Attente biologie avec retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NOT NULL",
      ),
    );

    $ljoinSpec                             = $ljoin;
    $ljoinSpec["rpu_attente"]              = "rpu_attente.rpu_id = rpu.rpu_id";
    $whereSpec                             = $where;
    $whereSpec["rpu_attente.type_attente"] = " = 'bio'";
    $start_field                           = "rpu_attente.depart";
    $end_field                             = "rpu_attente.retour";
    computeAttente($areas, $series, $whereSpec, $ljoinSpec, $dates, $period, $sejour, $total, $start_field, $end_field);
    break;
  // Spé
  case "spe":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Attente spécialiste",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series                                = &$data[$axe]['series'];
    $areas                                 = array(
      array(
        "Sans spécialiste",
        "rpu_attente.depart" => "IS NULL",
        "rpu_attente.retour" => "IS NULL",
      ),
      array(
        "Attente spécialiste sans retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NULL", // FIXME: prendre en charge les attentes de moins d'une minute
      ),
      array(
        "Attente spécialiste avec retour",
        "rpu_attente.depart" => "IS NOT NULL",
        "rpu_attente.retour" => "IS NOT NULL",
      ),
    );
    $ljoinSpec                             = $ljoin;
    $ljoinSpec["rpu_attente"]              = "rpu_attente.rpu_id = rpu.rpu_id";
    $whereSpec                             = $where;
    $whereSpec["rpu_attente.type_attente"] = " = 'specialiste'";
    $start_field                           = "rpu_attente.depart";
    $end_field                             = "rpu_attente.retour";
    computeAttente($areas, $series, $whereSpec, $ljoinSpec, $dates, $period, $sejour, $total, $start_field, $end_field);
    break;
  case "duree_sejour":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Durée de séjour",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    computeAttente(
      array(array("Nombre de passages")), $series, $where, $ljoin, $dates, $period, $sejour,
      $total, "sejour.entree", "sejour.sortie"
    );
    break;
  case "duree_pec":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Durée de prise en charge",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    computeAttente(
      array(array("Nombre de passages")), $series, $where, $ljoin, $dates, $period,
      $sejour, $total, "consultation._datetime", "sejour.sortie"
    );
    break;
  case "duree_attente":
    $data[$axe] = array(
      "options" => array(
        "title"  => "Durée d'attente",
        "yaxis"  => array("title" => "Nombre de passages"),
        "y2axis" => array("title" => "Temps (min.)"),
      ),
      "series"  => array()
    );

    $series = &$data[$axe]['series'];
    computeAttente(
      array(array("Nombre de passages")), $series, $where, $ljoin, $dates,
      $period, $sejour, $total, "sejour.entree", "consultation._datetime"
    );
    break;
  case "diag_infirmier":
    $data[$axe] = array(
      'options' => array(
        'title' => 'Par diagnostique infirmier'
      ),
      'series'  => array()
    );
    $ds         = CSQLDataSource::get("std");
    $rpu        = new CRPU;
    $ljoin      = array(
      'sejour' => 'rpu.sejour_id = sejour.sejour_id',
    );

    $where["diag_infirmier"] = "IS NOT NULL";

    $group                    = CGroups::loadCurrent();
    $group_id                 = $group->_id;
    $where['sejour.group_id'] = " = '$group_id'";

    $where['sejour.entree'] = "BETWEEN '" . reset($dates) . "' AND '" . end($dates) . "'";
    $nb_rpus                = $rpu->countList($where, null, $ljoin);

    $percent = CValue::get("_percent") * $nb_rpus;

    $percent = $percent / 100;

    $sql = "SELECT TRIM(TRAILING '\r\n' from substring_index( `diag_infirmier` , '\\n', 1 )) AS categorie, COUNT(*) as nb_diag
    FROM `rpu`
    LEFT JOIN `sejour` ON `rpu`.sejour_id = `sejour`.sejour_id
    WHERE `diag_infirmier` IS NOT NULL
    AND `group_id` = '$group_id'
    AND `entree` BETWEEN '" . reset($dates) . "' AND '" . end($dates) . "'
    GROUP BY categorie
    HAVING nb_diag > $percent";

    $result = $ds->exec($sql);
    $areas  = array();
    while ($row = $ds->fetchArray($result)) {
      $areas[] = $row["categorie"];
    }

    $areas[] = "AUTRES DIAGNOSTICS";

    foreach ($areas as &$_area) {
      $_area = rtrim($_area);
      $_area = CMbString::removeDiacritics($_area);
      $_area = strtoupper($_area);
    }

    $areas = array_unique($areas);

    $data[$axe]["options"]["title"] .= " (" . count($areas) . " catégories)";

    $series = &$data[$axe]['series'];

    $totaux = array();

    // On compte le nombre total de rpu par date
    foreach ($dates as $i => $_date) {
      $_date_next             = CMbDT::dateTime("+1 $period", $_date);
      $where['sejour.entree'] = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
      $totaux[$i]             = $rpu->countList($where, null, $ljoin);
    }

    $j = 0;

    foreach ($areas as $value) {
      unset($where[10]);
      $label     = $value;
      $value     = addslashes(preg_quote($value));
      $where[10] = "(rpu.diag_infirmier RLIKE '^{$value}[[:space:]]*\n' OR rpu.diag_infirmier RLIKE '^{$value}[[:space:]]*')";

      $series[$j] = array('data' => array(), 'label' => $label);

      foreach ($dates as $i => $_date) {
        // Si la catégorie est autre, on lui affecte le total soustrait aux valeurs des autres catégories
        if ($value == "AUTRES DIAGNOSTICS") {
          $series[$j]['data'][$i] = array($i, $totaux[$i]);
          $total                  += $totaux[$i];
          continue;
        }
        $_date_next             = CMbDT::dateTime("+1 $period", $_date);
        $where['sejour.entree'] = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                  = $rpu->countList($where, null, $ljoin);
        $totaux[$i]             -= $count;

        $total                  += $count;
        $series[$j]['data'][$i] = array($i, intval($count));
      }
      $j++;
    }
    break;
  case 'motif_sfmu':
    $data[$axe] = array(
      'options' => array(
        'title' => CAppUI::tr('CRPU-motif_sfmu')
      ),
      'series'  => array()
    );

    $series = &$data[$axe]['series'];
    $motifs = array(0 => 'Indéterminé');
    $ds     = $sejour->getDS();
    foreach ($dates as $i => $_date) {
      $_date_next              = CMbDT::dateTime("+1 $period", $_date);
      $where['sejour.entree']  = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
      $where['rpu.motif_sfmu'] = ' IS NULL';
      if (!array_key_exists(0, $series)) {
        $series[0] = array('data' => array(), 'label' => 'Indéterminé');
      }
      $count                 = intval($sejour->countList($where, null, $ljoin));
      $total                 += $count;
      $series[0]['data'][$i] = array($i, $count);

      $where['rpu.motif_sfmu'] = ' IS NOT NULL';
      $query                   = new CRequest();
      $query->addTable('sejour');
      $query->addColumn('COUNT(sejour.sejour_id)', 'count');
      $query->addColumn('rpu.motif_sfmu', 'motif_sfmu_id');
      $query->addColumn('motif_sfmu.libelle', 'motif_sfmu_title');
      $query->addWhere($where);
      $query->addLJoin($ljoin);
      $query->addLJoinClause('motif_sfmu', "rpu.motif_sfmu = motif_sfmu.motif_sfmu_id");
      $query->addGroup('rpu.motif_sfmu');
      $results = $ds->loadList($query->makeSelect());
      $s       = $query->makeSelect();
      if ($results) {
        foreach ($results as $result) {
          if (!in_array($result['motif_sfmu_id'], $motifs)) {
            $motifs[]             = $result['motif_sfmu_id'];
            $motif_index          = array_search($result['motif_sfmu_id'], $motifs);
            $series[$motif_index] = array('data' => array(), 'label' => $result['motif_sfmu_title']);
          }
          else {
            $motif_index = array_search($result['motif_sfmu_id'], $motifs);
          }
          $total += intval($result['count']);

          $series[$motif_index]['data'][$i] = array($i, intval($result['count']));
        }
      }
    }

    /* Add empty results for each motif */
    foreach ($motifs as $index => $motif_id) {
      foreach ($dates as $i => $_date) {
        if (!array_key_exists($i, $series[$index]['data'])) {
          $series[$index]['data'][$i] = array($i, 0);
          ksort($series[$index]['data']);
        }
      }

      ksort($series[$motif_index]['data']);
    }
    break;
  case 'DP':
    $data[$axe] = array(
      'options' => array(
        'title' => CAppUI::tr('CSejour-DP')
      ),
      'series'  => array()
    );

    $series = &$data[$axe]['series'];
    $codes  = array(0 => 'Indéterminé');
    $ds     = $sejour->getDS();
    foreach ($dates as $i => $_date) {
      $_date_next             = CMbDT::dateTime("+1 $period", $_date);
      $where['sejour.entree'] = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
      $where['sejour.DP']     = ' IS NULL';
      if (!array_key_exists(0, $series)) {
        $series[0] = array('data' => array(), 'label' => 'Indéterminé');
      }
      $count                 = intval($sejour->countList($where, null, $ljoin));
      $total                 += $count;
      $series[0]['data'][$i] = array($i, $count);

      $where['sejour.DP'] = ' IS NOT NULL';
      $query              = new CRequest();
      $query->addTable('sejour');
      $query->addColumn('COUNT(sejour.sejour_id)', 'count');
      $query->addColumn('sejour.DP', 'DP');
      $query->addWhere($where);
      $query->addLJoin($ljoin);
      $query->addGroup('sejour.DP');
      $results = $ds->loadList($query->makeSelect());
      $s       = $query->makeSelect();
      if ($results) {
        foreach ($results as $result) {
          if (!in_array($result['DP'], $codes)) {
            $codes[]    = $result['DP'];
            $code_index = array_search($result['DP'], $codes);
            $code       = CCodeCIM10::get($result['DP']);
            $label      = $result['DP'];

            if ($code->exist) {
              $label .= " - " . $code->libelle;
            }
            $series[$code_index] = array('data' => array(), 'label' => $label);
          }
          else {
            $code_index = array_search($result['DP'], $codes);
          }
          $total += intval($result['count']);

          $series[$code_index]['data'][$i] = array($i, intval($result['count']));
        }
      }
    }

    /* Add empty results for each motif */
    foreach ($codes as $index => $code) {
      foreach ($dates as $i => $_date) {
        if (!array_key_exists($i, $series[$index]['data'])) {
          $series[$index]['data'][$i] = array($i, 0);
        }
      }

      ksort($series[$index]['data']);
    }
    break;
  case "passages_uhcd":
    $series   = array();
    $criteres = array();

    if ($criteres_passage_uhcd) {
      foreach (CRPU::$criteres_uhcd as $_critere) {
        $criteres["rpu.$_critere"] = CAppUI::tr("CRPU-$_critere");
      }
    }
    else {
      $criteres["sejour.UHCD"] = CAppUI::tr("CSejour-UHCD");
    }

    $key = 0;
    foreach ($criteres as $_critere => $_critere_view) {
      $series[$key] = array("data" => array(), 'label' => $_critere_view);

      $where[100] = "$_critere = '1'";

      $sub_total = 0;
      foreach ($dates as $i => $_date) {
        $_date_next               = CMbDT::dateTime("+1 $period", $_date);
        $where["sejour.entree"]   = "BETWEEN '$_date' AND '" . CMbDT::dateTime("-1 second", $_date_next) . "'";
        $count                    = $sejour->countList($where, null, $ljoin);
        $total                    += $count;
        $sub_total                += $count;
        $series[$key]['data'][$i] = array($i, intval($count));
      }
      $series[$key]['subtotal'] = $sub_total;
      $key++;
    }

    $data[$axe] = array(
      "options" => array(
        "title" => CAppUI::tr("CRPU-Passages UHCD")
      ),
      "series"  => $series
    );
    break;
  case "pec_ioa":
    // $series[axe1]["data"][x=1] = (f(x) = y)
    // Query
    $query = new CRequest(false);

    $query->addTable('rpu r');

    $query->addColumn("DATE(s.entree)", "entree");
    $query->addColumn("r.pec_ioa", "pec_ioa");
    $query->addColumn("AVG(UNIX_TIMESTAMP(r.pec_ioa) - UNIX_TIMESTAMP(s.entree))", "avg_pec");

    if ($entree) {
      $query->addWhere("s.entree between '$entree' and '$sortie'");
      $query->addWhere("r.pec_ioa is not null");
    }

    $query->addLJoin("sejour s on r.sejour_id = s.sejour_id");
    $query->addGroup("DATE(s.entree)");

    $ds      = (new CRPU())->getDS();
    $results = $ds->loadList($query->makeSelect());

    // Fetch and create data
    $series = [0 => ["label" => "Temps en minutes"]];
    foreach ($results as $_result) {
      $index = array_search($_result["entree"]." 00:00:00", $dates);
      $interval_dt = (new DateTime())->setTimestamp($_result["avg_pec"]);

      $avg_pec_dt = new DateTime();
      $avg_pec_dt->setTimestamp(0);
      $avg_pec = $avg_pec_dt->diff($interval_dt);

      $series[0]["data"][$index] = [$index, $avg_pec->h * 60 + $avg_pec->i];
    }

    // Add empty values for those which are missing
    foreach ($dates as $index => $_date) {
      if (!$series[0]["data"][$index]) {
        $series[0]["data"][$index] = [$index, 0];
      }
    }
    sort($series[0]["data"]);

    $data[$axe] = array(
      "options" => array(
        "title" => CAppUI::tr("CRPU-stats_pec_ioa")
      ),
      "series"  => $series
    );
}

// Ticks
$ticks = array();
foreach ($dates as $i => $_date) {
  $ticks[$i] = array($i, CMbDT::format($_date, $format));
}

$group_view = $group->_view;
if ($period == "HOUR") {
  $group_view .= " - " . CMbDT::format($entree, CAppUI::conf("date"));
}
foreach ($data as &$_data) {
  $totals = array();
  foreach ($_data["series"] as &$_series) {
    if (isset($_series["lines"]["show"]) && $_series["lines"]["show"]) {
      $_series["bars"]["show"] = false;
      continue;
    }

    foreach ($_series["data"] as $key => $value) {
      if (!isset($totals[$key][0])) {
        $totals[$key][0] = $key;
        $totals[$key][1] = 0;
      }
      $totals[$key][1] += $value[1];
    }
  }

  $yaxis_max = ($totals) ? max(CMbArray::array_flatten(CMbArray::pluck($totals, 1))) : null;

  $_data["options"]             = CFlotrGraph::merge("bars", $_data["options"]);
  $_data["options"]             = CFlotrGraph::merge(
    $_data["options"],
    array(
      'colors' => array(
        /*"#1650A8", */
        "#2075F5", "#A89F16", "#F5C320",
        "#027894", "#784DFF", "#BC772A", "#FF9B34",
        "#00A080", "#8407E1", "#D04F3E", "#FF7348",
        "#A89FC6", "#15C320", "#027804",
      ),
      'xaxis'  => array('ticks' => $ticks, 'labelsAngle' => 45),
      'yaxis'  => array('max' => ($yaxis_max) ? ceil($yaxis_max + ($yaxis_max / 10)) + 1 : null),
      'bars'   => array('stacked' => true),
    )
  );
  $_data["options"]["subtitle"] = "$group_view - Total: $total";

  $_data["series"][] = array(
    "data"    => $totals,
    "label"   => "Total",
    "bars"    => array("show" => false),
    "lines"   => array("show" => false),
    "markers" => array("show" => true),
  );
}

CApp::json($data, "text/javascript");
