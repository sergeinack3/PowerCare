<?php

/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Controllers\Legacy;

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Forms\CExClassRefChecker;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Controller to check the references of ex_classes
 */
class ExClassRefCheckerController extends CLegacyController
{
    public function vwRefChecker(): void
    {
        $this->checkPermAdmin();

        $ex_class   = new CExClass();
        $ex_classes = $ex_class->loadList();

        $ex_class_check = (new CExClassRefChecker())->getKeys(CMbArray::pluck($ex_classes, 'ex_class_id'));

        CExObject::checkLocales();

        $this->renderSmarty(
            'vw_ref_checker',
            [
                'ex_classes'     => $ex_classes,
                'ex_class_check' => $ex_class_check,
                'prefix'         => CExClassRefChecker::PREFIX,
                'pre_tbl'        => CExClassRefChecker::PRE_TBL,
            ]
        );
    }

    public function cronCheckRefs(): void
    {
        $this->checkPermAdmin();

        $default_step = CView::get('step', 'num default|100');

        CView::checkin();

        $ref_checker = new CExClassRefChecker();
        ['ex_class_id' => $ex_class_id, 'start' => $start, 'step' => $step] = $ref_checker->getNextExClassIdToCheck();

        $ref_checker = new CExClassRefChecker();
        $ref_checker->check($ex_class_id, $start, $step ?: $default_step);
    }

    public function doCheckFormIntegrity(): void
    {
        $this->checkPermAdmin();

        $ex_class_id = CView::post('ex_class_id', 'ref class|CExClass notNull');
        $start       = CView::post('start', 'num default|0');
        $step        = CView::post('step', 'num default|100');

        CView::checkin();

        $ref_checker = new CExClassRefChecker();
        $ref_checker->check($ex_class_id, $start, $step);

        echo CAppUI::getMsg();
    }

    public function vwRefErrors(): void
    {
        $this->checkPermAdmin();

        $ex_class_id = CView::get('ex_class_id', 'ref class|CExClass notNull');
        $del         = CView::get('del', 'bool default|0');

        CView::checkin();

        if (!$ex_class_id) {
            CAppUI::commonError();
        }

        $cache = new Cache(CExClassRefChecker::PREFIX, CExClassRefChecker::PRE_TBL . $ex_class_id, Cache::DISTR);
        if (!$cache->exists()) {
            CAppUI::stepAjax("Vérification des références non commencée", UI_MSG_ERROR);
        }

        if ($del) {
            $cache->rem();
            CAppUI::stepAjax("Réinitialisation de la vérification", UI_MSG_OK);
            CApp::rip();
        }

        $data = $cache->get();
        if (!is_countable($data['errors']) || !count($data['errors'])) {
            CAppUI::stepAjax("Aucune erreur pour le fomrulaire", UI_MSG_ERROR);
        }

        $this->renderSmarty(
            'vw_ref_errors',
            [
                'data' => $data,
                'ex_class_id' => $ex_class_id,
            ]
        );
    }
    
    public function doRepairRefs(): void
    {
        $this->checkPermAdmin();

        $ex_class_id = CView::post('ex_class_id', 'ref class|CExClass notNull');
        $start       = CView::post('start', 'num default|0');
        $step        = CView::post('step', 'num default|100');
        $continue    = CView::post('continue', 'bool default|0');

        CView::checkin();

        if (!$ex_class_id) {
            CAppUI::commonError();
        }

        $cache = new Cache(CExClassRefChecker::PREFIX, CExClassRefChecker::PRE_TBL . $ex_class_id, Cache::DISTR);
        if (!$cache->exists()) {
            CAppUI::stepAjax("Vérification des références non commencée", UI_MSG_ERROR);
        }

        $data = $cache->get();

        $start = (count($data['errors']) < $start) ? count($data['errors']) : $start;
        $ids   = array_slice($data['errors'], $start, $step);

        $ex_object  = new CExObject($ex_class_id);
        $ex_objects = $ex_object->loadAll($ids);

        $failed_repair = 0;
        $ids_corrected = [];
        foreach ($ex_objects as $_ex_object) {
            $repaired = false;

            foreach (CExClassRefChecker::FIELDS as $_field_class => $_field_id) {
                $object_guid = $_ex_object->{$_field_class} . '-' . $_ex_object->{$_field_id};
                if (CExObject::repairReferences($ex_class_id, $_ex_object->_id, $object_guid)) {
                    $repaired = true;
                }
            }

            if ($repaired) {
                $ids_corrected[] = $_ex_object->_id;
                CAppUI::setMsg("CExObject-msg-repaired", UI_MSG_OK);
            } else {
                $failed_repair++;
                CAppUI::setMsg("CExObject-msg-Repair failed", UI_MSG_WARNING);
            }
        }

        $data['errors'] = array_diff($data['errors'], $ids_corrected);

        $cache->put($data);

        echo CAppUI::getMsg();

        $start     = $start + $failed_repair;
        $new_count = count($data['errors']) - count($ids) + $failed_repair;

        CAppUI::js("nextCorrection('$start', '$continue', '$new_count')");
    }
}
