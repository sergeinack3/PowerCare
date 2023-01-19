<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$date_min           = CView::get('_date_min', ['date', 'default' => CMbDT::date()]);
$date_max           = CView::get('_date_max', ['date', 'default' => CMbDT::date()]);
$user_id            = CView::getRefCheckRead('chir', 'ref class|CMediusers');
$free_consultations = CView::get('cs', 'bool default|1');
$patient_payments   = CView::get('_etat_reglement_patient', 'enum list|all|reglee|non_reglee default|all');
$tier_payments      = CView::get('_etat_reglement_tiers', 'enum list|all|reglee|non_reglee default|all');
$work_accident      = CView::get('_etat_accident_travail', 'enum list|all|yes|no default|all');
$all_group          = CView::get('_all_group_money', 'bool default|1');
$tarif_id           = CView::get("tarif", "str");
$lieu_id            = CView::get("lieu", "ref class|CLieuConsult");


CView::checkin();

$where    = [
    'ouverture'                  => "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'",
    'cloture'                    => 'IS NOT NULL',
    'facture_cabinet.patient_id' => 'IS NOT NULL',
];
$ljoin    = [];
$order    = 'ouverture, praticien_id';
$group_by = 'facture_id';

$user = new CMediusers();
if ($user_id) {
    $user  = CMediusers::get($user_id);
    $users = CConsultation::loadPraticiensCompta($user_id);

    $where['praticien_id'] = CSQLDataSource::prepareIn(array_keys($users));
}

if (!$free_consultations) {
    $where[] = 'du_tiers + du_patient > 0';
}

if ($patient_payments == 'reglee') {
    $where['patient_date_reglement'] = 'IS NOT NULL';
} elseif ($patient_payments == 'non_reglee') {
    $where['patient_date_reglement'] = 'IS NULL';
    $where['du_patient']             = '> 0';
}

if ($tier_payments == 'reglee') {
    $where['tiers_date_reglement'] = 'IS NOT NULL';
} elseif ($tier_payments == 'non_reglee') {
    $where['tiers_date_reglement'] = 'IS NULL';
    $where['du_tiers']             = '> 0';
}

if ($work_accident != 'all' && $work_accident != '') {
    $ljoin['facture_liaison'] = 'facture_liaison.facture_id = facture_cabinet.facture_id';
    $ljoin['consultation']    = 'facture_liaison.object_id = consultation.consultation_id';

    $where['facture_liaison.facture_class'] = " = 'CFactureCabinet'";
    $where['facture_liaison.object_class']  = " = 'CConsultation'";

    if ($work_accident == 'yes') {
        $where['consultation.date_at'] = 'IS NOT NULL';
    } elseif ($work_accident == 'no') {
        $where['consultation.date_at'] = 'IS NULL';
    }
}

// Tarifs
if ($tarif_id) {
    $tarif = new CTarif();
    $tarif->load($tarif_id);
    $ljoin["facture_liaison"] = "facture_liaison.facture_id = facture_cabinet.facture_id";
    $ljoin["consultation"]    = "facture_liaison.object_id = consultation.consultation_id";

    $where["consultation.tarif"] = " = '$tarif->description'";
}

$bill = new CFactureCabinet();
/** @var CFactureCabinet[] $bills */
$bills = [];
if ($all_group) {
    $bills = $bill->loadList($where, $order, null, $group_by, $ljoin);
} else {
    $bills = $bill->loadGroupList($where, $order, null, $group_by, $ljoin);
}

CMbObject::massLoadFwdRef($bills, 'praticien_id');
CMbObject::massLoadFwdRef($bills, 'patient_id');
$links         = CMbObject::massLoadBackRefs($bills, 'facture_liaison');
$consultations = CMbObject::massLoadFwdRef($links, 'object_id', 'CConsultation');
CMbObject::massLoadFwdRef($links, 'object_id');
CMbObject::massLoadBackRefs($bills, 'reglements');


$file = new CCSVFile();
$file->writeLine(
    [
        'N° Facture',
        'Nom du patient',
        'N° Sécurité sociale',
        'Date consultation',
        'Nom du tarif',
        'Montant de base',
        'Montant DH',
        'Montant HT',
        'Montant TVA',
        'Total facturé',
        'Dû patient',
        'Montant réglé par le patient',
        'Dû tiers',
        'Dû AMO',
        'Dû AMC',
        'Montant réglé par les tiers',
        'Date d\'acquittement',
    ]
);

foreach ($bills as $bill) {
    $user    = $bill->loadRefPraticien();
    $patient = $bill->loadRefPatient();
    $bill->loadRefsReglements();
    $bill->loadRefsConsultation();
    foreach ($bill->_ref_consults as $consult) {
        $consult->loadRefFacture()->loadRefsReglements();

        if ($lieu_id && $lieu_id != $consult->loadRefPlageConsult()->loadRefAgendaPraticien()->lieuconsult_id) {
            continue;
        }
        $acknowledgement_date = '';
        if ($bill->du_patient && $bill->patient_date_reglement && $bill->tiers_date_reglement && $bill->du_tiers) {
            $acknowledgement_date = $bill->patient_date_reglement >= $bill->tiers_date_reglement
                ? $bill->patient_date_reglement : $bill->tiers_date_reglement;
        } elseif ($bill->du_patient && $bill->patient_date_reglement && !$bill->du_tiers) {
            $acknowledgement_date = $bill->patient_date_reglement;
        } elseif ($bill->du_tiers && $bill->tiers_date_reglement && !$bill->du_patient) {
            $acknowledgement_date = $bill->tiers_date_reglement;
        }

        if ($acknowledgement_date) {
            $acknowledgement_date = CMbDT::format($acknowledgement_date, CAppUI::conf("date"));
        }

        $file->writeLine(
            [
                $bill->_id,
                "$patient->nom $patient->prenom",
                $patient->matricule,
                CMbDT::format($consult->_date, CAppUI::conf("date")),
                $consult->tarif,
                $consult->secteur1,
                $consult->secteur2,
                $consult->secteur3,
                $consult->du_tva,
                $consult->_somme,
                $consult->du_patient,
                $consult->_ref_facture->_reglements_total_patient,
                $consult->du_tiers,
                $consult->total_amo,
                $consult->total_amc,
                $consult->_ref_facture->_reglements_total_tiers,
                $acknowledgement_date,
            ]
        );
    }
}

$file_name = "Recette_{$user->_user_last_name}_{$user->_user_first_name}_du_";

if ($date_min != $date_max) {
    $file_name .= CMbDT::format($date_min, '%d-%m-%Y') . '_au_' . CMbDT::format($date_max, '%d-%m-%Y');
} else {
    $file_name .= CMbDT::format($date_min, '%d-%m-%Y');
}

$file->stream($file_name);
CApp::rip();
