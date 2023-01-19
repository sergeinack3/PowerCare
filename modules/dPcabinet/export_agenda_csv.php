<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$prat_id    = CValue::get("prat_id", CMediusers::get()->_id);
$date_min   = CValue::get("date_min", CMbDT::date());
$only_empty = CValue::get("only_empty");

$praticien = new CMediusers();
$praticien->load($prat_id)->needsEdit();

CStoredObject::$useObjectCache = false;

$plage = new CPlageconsult();

$where = array(
  "chir_id" => "=  '$praticien->_id'",
  "date"    => ">= '$date_min'"
);
$order = array(
  "date",
  "debut",
);

$request = new CRequest();
$request->addWhere($where);
$request->addOrder($order);

$query = $request->makeSelect($plage);

$ds = $plage->getDS();
$result = $ds->exec($query);

ob_end_clean();
header("Content-Type: text/plain;charset=".CApp::$encoding);
header("Content-Disposition: attachment;filename=\"Agenda $praticien->_view.csv\"");

$fp = fopen("php://output", "w");
$csv = new CCSVFile($fp);

$titles = array(
  "plage ID",
  "plage date",
  "plage debut",
  "plage fin",
  "plage frequence",
  "plage libelle",
  "plage couleur",

  "rdv ID",
  "rdv debut",
  "rdv creneaux",
  "rdv motif",

  "patient ID",
  "patient nom",
  "patient prenom",
  "patient prenom 2",
  "patient prenom 3",
  "patient nom jf",
  "patient naissance",
  "patient sexe",
  "patient civilite",
  "patient tel",
  "patient mob",
  "patient email",
  "patient numero ss",
  "patient adresse",
  "patient cp",
  "patient ville",
  "patient pays",
);

$csv->writeLine($titles);

/** @var CPlageconsult $_plage */
while ($_plage = $ds->fetchObject($result, "CPlageconsult")) {
  $_consults = $_plage->loadRefsConsultations(false, false);

  if (count($_consults) == 0) {
    $row = array(
      $_plage->_id,
      $_plage->date,
      $_plage->debut,
      $_plage->fin,
      $_plage->freq,
      $_plage->libelle,
      $_plage->color,

      null,
      null,
      null,
      null,

      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
    );

    $csv->writeLine($row);
    continue;
  }

  if ($only_empty) {
    continue;
  }

  CStoredObject::massLoadFwdRef($_consults, "patient_id");

  foreach ($_consults as $_consult) {
    $_patient = $_consult->loadRefPatient();

    $row = array(
      $_plage->_id,
      $_plage->date,
      $_plage->debut,
      $_plage->fin,
      $_plage->freq,
      $_plage->libelle,
      $_plage->color,

      $_consult->_id,
      $_consult->heure,
      $_consult->duree,
      $_consult->motif,

      $_patient->_id,
      $_patient->nom,
      $_patient->prenom,
      $_patient->_prenom_2,
      $_patient->_prenom_3,
      $_patient->nom_jeune_fille,
      $_patient->naissance,
      $_patient->sexe,
      $_patient->civilite,
      $_patient->tel,
      $_patient->tel2,
      $_patient->email,
      $_patient->matricule,
      $_patient->adresse,
      $_patient->cp,
      $_patient->ville,
      $_patient->pays,
    );

    $csv->writeLine($row);
  }
}

CApp::rip();
