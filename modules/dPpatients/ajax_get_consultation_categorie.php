<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Cabinet\CPlageConsultCategorie;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;

/**
 * Defines a script for `CConsultationCategorie` objects loading from `edit_plage_consultation` template, to
 * `inc_get_consultation_categorie` template.
 *
 * The purpose of this script is to provide these objects after selecting a `CExercicePlace` object in plage consult
 * creation of edition view, and thus make these check if we want to link them on plage consult.
 *
 * Input:
 *   - `mediuser_id`:           GET field, `CMediuser` object UID.
 *   - `plage_consultation_id`: GET field, `CPlageConsult` object UID.
 *   - `exercice_place_id`:     GET field, `CExercicePlace` object UID.
 *
 * Output:
 *   - `objects`: Loaded `CConsultationCategorie` objects to be displayed. These objects will be set
 *     following:
 *     [
 *         'linked'                 => (true|false),
 *         'consultation_categorie' => $object,
 *     ]
 *
 * If `exercice_place_id` **is not defined**, dot not load object because target object loading references this class.
 *
 * If `plage_consultation_id` **is defined**, meaning that we are in plage consult edition case, loading additional
 * `CPlageConsultCategorie` objects for pre-checking purpose. This case will create more complex output variable to
 * template, following:
 *
 * If `plage_consultation_id` **is not defined**, meaning that we are in plage consult creation case, loading only
 * `CConsultationCategorie` objects for check purpose.
 */

// Permission checking
CCanDo::checkEdit();

// Getting GET fields
$mediuser_id       = CView::getRefCheckRead('mediuser_id', 'ref class|CMediusers');
$plage_consult_id  = CView::getRefCheckRead('plage_consult_id', 'ref class|CPlageconsult');
$exercice_place_id = CView::getRefCheckRead('exercice_place_id', 'ref class|CExercicePlace');
CView::checkin();

// If no ExercicePlace UID is provided, do not load anything
if (!$exercice_place_id) {
    return;
}

$mediuser = new CMediusers();
$mediuser->load($mediuser_id);

$exercice_place = new CExercicePlace();
$exercice_place->load($exercice_place_id);

// Creating SQL request for loading ConsultationCategorie objects
$consultation_categorie = new CConsultationCategorie();
$datasource             = $consultation_categorie->getDS();

$request = new CRequest();
$request->addWhereClause('praticien_id', $datasource->prepare(' = ?', $mediuser->_id));
$request->addWhereClause('exercice_place_id', $datasource->prepare(' = ?', $exercice_place->_id));
$request->addWhereClause('sync_appfine', $datasource->prepare(' = ?', true));
$consultation_categories = $consultation_categorie->loadListByReq($request);

// Creating SQL request for loading ConsultationCategorie objects with function_id instead of praticien_id
// On enlève pour le moment les catégories sur les motifs parce qu'on n'exporte pas de catégorie sans exercice place
// donc il faut que la cat soit sur un praticien_id et non une fonction_id

/*$request = new CRequest();
$request->addWhereClause('function_id', $datasource->prepare(' = ?', $mediuser->function_id));
$request->addWhereClause('sync_appfine', $datasource->prepare(' = ?', true));
$consultation_categories = array_merge($consultation_categories, $consultation_categorie->loadListByReq($request));*/

$plage_consult_categories = [];

$all_synchro = true;
if ($plage_consult_id) {
    foreach ($consultation_categories as $_consultation_categorie) {
        $where = [];
        $where['praticien_id'] = $datasource->prepare(' = ?', $mediuser->_id);
        $where['plage_id'] = $datasource->prepare(' = ?', $plage_consult_id);
        $where['sync_appfine'] = $datasource->prepare(' = ?', 1);

        $plage_consult_categories = $_consultation_categorie->loadBackRefs('categorie_plage_consult_liaisons', null, null, null, null, null, null, $where);

        if ($plage_consult_categories) {
            $_consultation_categorie->_sync_appfine = true;
        }
        else {
            $all_synchro = false;
        }
    }
}

// Displaying template
$template = new CSmartyDP();
$template->assign('all_synchro', $all_synchro);
$template->assign('plage_id', $plage_consult_id);
$template->assign('consultation_categories', $consultation_categories);
$template->assign('plage_consult_categories', $plage_consult_categories);
$template->display('inc_get_consultation_categorie');
