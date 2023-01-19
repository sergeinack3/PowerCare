<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Bcb\CBcbProduit;
use Ox\Mediboard\Medicament\CMedicamentArticle;

class DatamatrixVaccinController extends CLegacyController
{
    public function showDatamatrixIns()
    {
        $this->checkPermEdit();

        $patient_ins = CView::get("patient_ins", "ref class|CPatientINSNIR");

        CView::checkin();

        $patient_ins = CPatientINSNIR::findOrFail($patient_ins);
        $data        = $patient_ins->createDataForDatamatrix();
        $patient_ins->createDatamatrix($data);
        $this->renderSmarty(
            'vw_datamatrix_ins.tpl',
            [
                'patient' => $patient_ins,
            ]
        );
    }

    public function openModalReadDatamatrixVaccin()
    {
        $this->checkPermEdit();

        $search = CView::get("search", "bool default|1");

        CView::checkin();

        $this->renderSmarty(
            'vaccination/vw_read_datamatrix_vaccin.tpl',
            [
                'search' => $search,
            ]
        );
    }

    public function searchCIP()
    {
        CCanDo::checkRead();
        $code13 = CView::get("code13", "str");
        CView::checkin();

        CApp::json(CMedicamentArticle::get(CMedicamentArticle::getCIP7FromCIP13($code13)));
        CApp::rip();
    }

    public function readDatamatrixVaccin()
    {
        $this->checkPermEdit();

        $datamatrix = CView::get("datamatrix", "str");

        CView::checkin();

        if ($datamatrix == "") {
            CAppUI::setMsg('Datamatrix vide', UI_MSG_ERROR);
            echo CAppUI::getMsg();

            return;
        }

        //CIP
        $data["cip13"] = substr($datamatrix, 3, 13);

        //Expiration date
        $data["exp"] = substr($datamatrix, 18, 6);

        //Lot
        $data["lot"] = substr($datamatrix, 26);

        //Get last 2 digits (day), if 00 -> last day of month
        $end = substr($data["exp"], 4, 2);
        if ($end == '00') {
            $data["exp"] = substr($data["exp"], 0, 4);
            $day         = 't';
            $format      = 'ym';
        } else {
            $day    = 'd';
            $format = 'ymd';
        }

        $data["exp"]         = CMbDT::getDateTimeFromFormat($format, $data["exp"]);
        $data["exp_display"] = $data["exp"]->format($day . '/m/Y');
        $data["exp_hidden"]  = $data["exp"]->format('Y-m-' . $day);

        CAppUI::callbackAjax("dataVacc.createVaccin", $data);

        return;
    }
}
