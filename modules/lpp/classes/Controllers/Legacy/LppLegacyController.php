<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Mediboard\Lpp\Repository\LppPricingRepository;
use Ox\Mediboard\Mediusers\CMediusers;

class LppLegacyController extends CLegacyController
{
    /**
     * Display the index view for searching LppCodes
     *
     * @throws \Exception
     */
    public function viewSearch(): void
    {
        $this->checkPermRead();

        CView::checkin();

        try {
            $chapters = LppChapterRepository::getInstance()->loadChaptersFromParent('0');
        } catch (LppDatabaseException $e) {
            $e->stepAjax(UI_MSG_ERROR);
            CApp::rip();

            return;
        }

        $this->renderSmarty('vw_search', [
            'chapters' => $chapters,
            'codes'    => [],
            'start'    => 0,
            'total'    => 0,
        ]);
    }

    /**
     * Returns the list of chapters that are descendant of the given chapter
     *
     * @throws \Exception
     */
    public function getDescendantChapters(): void
    {
        $this->checkPermRead();

        $parent_id = CView::get('parent_id', 'str');

        CView::checkin();

        try {
            $chapters = LppChapterRepository::getInstance()->loadChaptersFromParent($parent_id);
        } catch (LppDatabaseException $e) {
            $this->renderJson(['msg' => $e->getMessage()]);

            return;
        }

        $data = ['level' => strlen($parent_id), 'chapters' => []];

        foreach ($chapters as $_chapter) {
            $data['chapters'][] = [
                'id'   => $_chapter->id,
                'view' => "$_chapter->rank - $_chapter->name",
            ];
        }

        $this->renderJson($data);
    }

    /**
     * Search the Lpp codes matching the given parameters
     *
     * @throws \Exception
     */
    public function searchLppCodes(): void
    {
        $this->checkPermRead();

        $code       = CView::get('code', 'str');
        $text       = CView::get('text', 'str');
        $chapter_id = CView::get('chapter_id', 'str');
        $start      = CView::get('start', 'num default|0');

        CView::checkin();

        try {
            $repository = LppCodeRepository::getInstance();
            $codes      = $repository->search($code, $text, $chapter_id, null, $start, 100);
            $total      = LppCodeRepository::getInstance()->count($code, $text, $chapter_id);
        } catch (LppDatabaseException $e) {
            $e->stepAjax(UI_MSG_ERROR);
            CApp::rip();

            return;
        }

        $this->renderSmarty('inc_search_results', [
            'codes' => $codes,
            'start' => $start,
            'total' => $total,
        ]);
    }

    /**
     * Display the view for the given Lpp code
     *
     * @throws \Exception
     */
    public function viewCode(): void
    {
        $this->checkPermRead();

        $code = CView::get('code', 'str notNull');

        CView::checkin();

        try {
            $code = LppCodeRepository::getInstance()->load($code);
            $code->loadLastPricing();
            $code->loadPricings();
            $code->loadParent();
            $code->loadCompatibilities();
            $code->loadIncompatibilities();
        } catch (LppDatabaseException $e) {
            $e->stepAjax(UI_MSG_ERROR);
            CApp::rip();

            return;
        }

        $this->renderSmarty('inc_code', ['code' => $code]);
    }

    /**
     * Display the view of the Lpp pricing for the given code at the given date
     *
     * @throws \Exception
     */
    public function viewCodePricing(): void
    {
        $this->checkPermRead();

        $code = CView::get('code', 'str notNull');
        $date = CView::get('date', 'date notNull');

        CView::checkin();

        try {
            $pricing = LppPricingRepository::getInstance()->loadFromDate($code, $date);
        } catch (LppDatabaseException $e) {
            $e->stepAjax(UI_MSG_ERROR);
            CApp::rip();

            return;
        }

        $this->renderSmarty('inc_pricing', ['pricing' => $pricing]);
    }

    /**
     * Autocomplete view for Lpp codes
     *
     * @throws \Exception
     */
    public function codeAutocomplete(): void
    {
        $this->checkPermRead();

        $text         = CView::post('code', 'str');
        $executant_id = CView::post('executant_id', 'ref class|CMediusers');
        $date         = CView::post('date', 'date');

        CView::checkin();

        try {
            $repository = LppCodeRepository::getInstance();
            $codes      = $repository->search($text, $text, null, $date, 0, 100);

            if ($executant_id) {
                $user             = CMediusers::get($executant_id);
                $prestation_codes = [];
                if ($user->spec_cpam_id) {
                    $prestation_codes = $repository->getAllowedPrestationCodesForSpeciality($user->spec_cpam_id);
                }
            }

            foreach ($codes as $_key => $_code) {
                $_code->loadLastPricing($date);
                $_code->getQualificatifsDepense();

                if (!$_code->_last_pricing->code) {
                    unset($codes[$_key]);
                }

                if ($executant_id && !in_array($_code->_last_pricing->prestation_code, $prestation_codes)) {
                    unset($codes[$_key]);
                }
            }
        } catch (LppDatabaseException $e) {
            $e->stepAjax(UI_MSG_ERROR);
            CApp::rip();

            return;
        }

        $this->renderSmarty('inc_code_autocomplete', ['codes' => $codes]);
    }

    /**
     * Displays the codage lpp view
     *
     * @throws \Exception
     */
    public function codageLpp(): void
    {
        $this->checkPermRead();

        $object_class = CView::get('object_class', 'str');
        $object_id    = CView::get('object_id', 'ref meta|object_class');

        CView::checkin();

        /** @var CCodable $codable */
        $codable = CMbObject::loadFromGuid("$object_class-$object_id");

        $codable->loadRefsActesLPP();
        foreach ($codable->_ref_actes_lpp as $_acte) {
            $_acte->loadRefExecutant();
            $_acte->_ref_executant->loadRefFunction();
        }

        $acte_lpp = CActeLPP::createFor($codable);

        $this->renderSmarty('inc_codage_lpp', [
            'codable'  => $codable,
            'acte_lpp' => $acte_lpp,
        ]);
    }

    /**
     * Display the module configuration view
     */
    public function configure(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('configure');
    }
}
