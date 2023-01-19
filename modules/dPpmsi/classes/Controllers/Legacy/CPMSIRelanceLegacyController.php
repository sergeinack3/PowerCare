<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Mediboard\Pmsi\Services\PMSIRelanceService;

/**
 * Legacy Controller PMSI
 */
class CPMSIRelanceLegacyController extends CLegacyController
{
    /**
     * Displays a patient's stay
     *
     * @return void
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function searchRelances(): void
    {
        $this->checkPermEdit();

        $status           = CView::get("status", "str", true);
        $urgence          = CView::get("urgence", "str", true);
        $type_doc         = CView::get("type_doc", "str", true);
        $commentaire_med  = CView::get("commentaire_med", "str", true);
        $chir_id          = CView::get("chir_id", "ref class|CMediusers", true);
        $date_min_relance = CView::get("date_min_relance", "date", true);
        $date_max_relance = CView::get("date_max_relance", "date", true);
        $date_min_sejour  = CView::get("date_min_sejour", "date", true);
        $date_max_sejour  = CView::get("date_max_sejour", "date", true);
        $NDA              = CView::get("NDA", "str");
        $export           = CView::get('export', 'bool default|0');
        $type_sejour      = CView::get('type', "enum list|" . implode("|", CSejour::$types), true);
        $order_col        = CView::get('order_col', 'str default|nom');
        $order_way        = CView::get('order_way', 'enum list|ASC|DESC default|DESC');

        CView::checkin();

        $pmsi_relance_service = new PMSIRelanceService();
        $relance              = new CRelancePMSI();

        $order = $pmsi_relance_service->computeOrder($order_col, $order_way);

        $relances     = [];
        $sejour_exist = 0;

        if ($NDA) {
            $sejour = $pmsi_relance_service->getSejourFromNDA($NDA);
            if ($sejour->_id) {
                $relance->sejour_id = $sejour->_id;
                $relance->loadMatchingObject();

                if ($relance->_id) {
                    $relances[] = $relance;
                }

                $sejour_exist = 1;
            }
        } else {
            $pmsi_relance_service->joinSejour();
            $pmsi_relance_service->addGroupFilter();
            if ($date_min_sejour && $date_max_sejour) {
                $pmsi_relance_service->addDatesFilter($date_min_sejour, $date_max_sejour);
            }

            if ($status) {
                $pmsi_relance_service->addStatusFilter($status);
            }

            if ($urgence) {
                $pmsi_relance_service->addUrgenceFilter($urgence);
            }

            if ($type_doc) {
                $pmsi_relance_service->addDocTypeFilter($type_doc);
            }

            if ($commentaire_med != "") {
                $pmsi_relance_service->addCommentFilter($commentaire_med);
            }

            if ($chir_id) {
                $pmsi_relance_service->addChirFilter($chir_id);
            }

            if ($type_sejour) {
                $pmsi_relance_service->addTypeSejourFilter($type_sejour);
            }

            $pmsi_relance_service->addRelanceDatesFilter($date_min_relance, $date_max_relance);

            $where    = $pmsi_relance_service->getWhere();
            $ljoin    = $pmsi_relance_service->getLJoin();
            $relance  = new CRelancePMSI();
            $relances = $relance->loadList($where, $order, null, null, $ljoin);
        }

        CStoredObject::massLoadFwdRef($relances, "patient_id");
        CStoredObject::massLoadFwdRef($relances, "chir_id");

        /** @var array $sejours */
        $sejours = CStoredObject::massLoadFwdRef($relances, "sejour_id");

        CSejour::massLoadNDA($sejours);

        $readFiles          = new CReadFile();
        $readFiles->user_id = CMediusers::get()->_id;
        $readFiles          = $readFiles->loadMatchingList();

        $notReadFiles = [];

        foreach ($relances as $_relance) {
            $_relance->loadRefSejour();
            $_relance->loadRefPatient();
            $_relance->loadRefChir();
            $_relance->_ref_sejour->loadRefsDocs();

            $notReadFiles[$_relance->_ref_sejour->_id] = [];
            foreach ($_relance->_ref_sejour->_ref_documents as $document) {
                $flag_read = false;
                foreach ($readFiles as $readFile) {
                    if ($readFile->object_id == $document->_id && $readFile->object_class == $document->_class) {
                        $flag_read = true;
                        break;
                    }
                }
                if (!$flag_read) {
                    $notReadFiles[$_relance->_ref_sejour->_id][] = $document;
                }
            }
        }

        // Relances par praticien (pour l'impression)
        $relances_by_prat = $pmsi_relance_service->getRelancesByPrat($relances);
        $prats            = $pmsi_relance_service->getPrats($relances);

        $chir = new CMediusers();
        if ($chir_id) {
            $chir = CMediusers::get($chir_id);
        }

        if (!$export) {
            $tpl_vars = [
                'relances'         => $relances,
                'relances_by_prat' => $relances_by_prat,
                'prats'            => $prats,
                'date_min_relance' => $date_min_relance,
                'date_max_relance' => $date_max_relance,
                'status'           => $status,
                'urgence'          => $urgence,
                'type_doc'         => $type_doc,
                'commentaire_med'  => $commentaire_med,
                'chir'             => $chir,
                'sejour_exist'     => $sejour_exist,
                'order_way'        => $order_way,
                'order_col'        => $order_col,
                'notReadFiles'     => $notReadFiles,
            ];
            if ($sejour_exist) {
                $tpl_vars['sejour'] = $sejour;
            }

            $this->renderSmarty('inc_search_relances', $tpl_vars);
        } else {
            $pmsi_relance_service->export($relances, $date_min_relance, $date_max_relance);
        }
    }
}
