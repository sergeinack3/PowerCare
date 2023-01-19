<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CCCAMImport;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodeNGAP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\PlanningOp\CSejour;

class NgapLegacyController extends CLegacyController
{
    public function ngapCodeAutocomplete(): void
    {
        $this->checkPermRead();

        $object_class = CView::get("object_class", 'str');
        $object_id    = CView::get("object_id", "ref class|$object_class");
        $code         = CView::post("code", 'str');
        $executant_id = CView::post("executant_id", 'ref class|CMediusers');
        $date         = CView::post('date', ['date', 'default' => CMbDT::date()]);
        $speciality   = CView::post('speciality', 'str');
        $urgences     = CView::request('urgences', 'bool default|0');

        CView::checkin();
        CView::enableSlave();

        /** @var CCodable $object */
        $object = new $object_class();
        $object->load($object_id);
        $object->countActes();
        $object->loadRefsActes();

        $praticien = CMediusers::get($executant_id);
        if (!$executant_id && $speciality) {
            $praticien               = new CMediusers();
            $praticien->spec_cpam_id = $speciality;
        }
        $spe_undefined = $praticien->spec_cpam_id ? false : true;

        $codes = CCodeNGAP::search(
            $code,
            $praticien,
            $date,
            $object->_count_actes ? false : true,
            true,
            (bool)$urgences
        );

        foreach ($codes as $_key => $_code) {
            $_code->getTarifFor($praticien, $date);

            if (!$_code->lettre_cle && !empty($_code->associations)) {
                $authorized_asso = false;
                if (in_array('NGAP', $_code->associations) && count($object->_ref_actes_ngap)) {
                    $authorized_asso = true;
                } elseif (in_array('CCAM', $_code->associations) && count($object->_ref_actes_ccam)) {
                    $authorized_asso = true;
                } else {
                    foreach ($object->_ref_actes_ngap as $_acte) {
                        if (in_array($_acte->code, $_code->associations)) {
                            $authorized_asso = true;
                            break;
                        }
                    }
                }

                if (!$authorized_asso) {
                    unset($codes[$_key]);
                }
            }
        }

        $this->renderSmarty('code_ngap_autocomplete.tpl', [
            'code'          => $code,
            'codes'         => $codes,
            'spe_undefined' => $spe_undefined,
        ]);
    }

    public function ngapIndex(): void
    {
        $this->checkPermRead();
        CView::checkin();

        $specialites = CSpecCPAM::getList();
        $this->renderSmarty('vw_ngap.tpl', [
            'specs' => $specialites,
            'spec'  => reset($specialites),
            'date'  => CMbDT::date(),
        ]);
    }

    public function ngapCodeSearch(): void
    {
        $this->checkPermRead();

        $spec_id = CView::get('spec', 'num default|1');
        $date    = CView::get('date', ['date', 'default' => CMbDT::date()]);
        $zone    = CView::get('zone', 'enum list|metro|antilles|mayotte|guyane-reunion default|metro');
        $urgence = CView::post('urgence', 'bool default|0');

        CView::checkin();
        CView::enableSlave();

        $spec = CSpecCPAM::get($spec_id);

        $codes = CCodeNGAP::getForSpeciality($spec, $date, $zone, (bool)$urgence);

        $this->renderSmarty('inc_list_codes_ngap.tpl', [
            'spec'  => $spec,
            'date'  => $date,
            'codes' => $codes,
        ]);
    }

    public function viewDuplicateNgap(): void
    {
        $this->checkPermEdit();

        $codable_guid = CView::get('codable_guid', 'guid class|CCodable');
        $acte_guid    = CView::get('acte_guid', 'guid class|CActeNGAP');

        CView::checkin();

        /** @var CCodable $codable */
        $codable = CMbObject::loadFromGuid($codable_guid);

        if ($codable->_id) {
            $acte = CActeNGAP::loadFromGuid($acte_guid);

            $this->renderSmarty('inc_duplicate_ngap.tpl', [
                'codable' => $codable,
                'acte'    => $acte,
            ]);
        }
    }

    public function duplicateNgap(): void
    {
        $this->checkPermEdit();

        $codable_guid = CView::post('codable_guid', 'str');
        $actes        = explode('|', CView::post('actes', 'str'));
        $date_multiple = CView::post("multiple_date", "str");
        $type_of_date   = CView::post("type_of_date", "str default|one_date");

        /** @var CSejour $codable */
        $codable = CMbObject::loadFromGuid($codable_guid);
        $date = CView::post('date', ['date', 'default' => $codable->sortie]);

        CView::checkin();

        if ($codable->_id) {
            $codable->loadRefsActesNGAP();

            foreach ($actes as $_acte_id) {
                if (array_key_exists($_acte_id, $codable->_ref_actes_ngap)) {
                    $_acte = $codable->_ref_actes_ngap[$_acte_id];

                    if ($type_of_date != "one_date") {
                        $date_multiple = explode(",", $date_multiple);
                        $days = count($date_multiple);
                    } else {
                        $days = CMbDT::daysRelative($_acte->execution, CMbDT::format($date, '%Y-%m-%d 00:00:00')) + 1;
                        $_date = CMbDT::dateTime(null, $_acte->execution);
                    }
                    $time = CMbDT::time(null, $_acte->execution);
                    for ($i = 1; $i <= $days; $i++) {
                        if ($type_of_date != "one_date") {
                            $_acte->execution = $date_multiple[$i - 1] . " " . $time;
                        } else {
                            $_acte->execution = CMbDT::date("+$i DAYS", $_date) . " $time";
                        }

                        $_acte->_id = null;
                        $_acte->store();
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importNgapDatabase(): void
    {
        $this->checkPermAdmin();
        CView::enableSlave();
        CApp::setTimeLimit(360);
        ini_set("memory_limit", "128M");

        $import = new CCCAMImport();
        $import->importDatabase(['ngap']);

        foreach ($import->getMessages() as $message) {
            CAppUI::stepAjax(...$message);
        }
    }
}
