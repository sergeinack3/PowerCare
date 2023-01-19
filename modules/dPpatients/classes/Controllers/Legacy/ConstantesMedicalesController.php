<?php

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\ObservationResult\CObservationValueToConstant;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

class ConstantesMedicalesController extends CLegacyController
{
    /**
     * Stores CConstantesMedicales from the CObservationResults linked to the source
     *
     * @return mixed
     * @throws Exception
     */
    public function importObservationResultsDataToConstants()
    {
        $this->checkPermEdit();

        $source_name  = CView::get("source_name", "str");
        $current_date = CView::get("date", "date");

        CView::checkin();

        if (!$source_name) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CExchangeSource-error-noSourceName");
            return;
        }

        $source  = CMbObject::loadFromGuid($source_name);

        if (!$source->_id) {
            CAppUI::stepMessage(UI_MSG_ERROR, CAppUI::tr("CExchangeSource-no-source", $source->name));
            return;
        }

        $counter = 0;

        if (!$current_date) {
            $current_date = CMbDT::date();
        }

        $frequency_integration_constants = CAppUI::gconf(
            "monitoringPatient CConstantesMedicales frequency_integration_constants"
        );

        $values      = [];
        $ds          = CSQLDataSource::get('std');
        $conversions = CObservationValueToConstant::loadForGroup();

        if (empty($conversions)) {
            CAppUI::setMsg('CObservationValueToConstant-error-conversion-not_configured', UI_MSG_ERROR);

            return;
        }

        /* @var CObservationValueToConstant[] $_conversions */
        foreach ($conversions as $_conversions) {
            /* @var CObservationValueToConstant $conversion */
            foreach ($_conversions as $conversion) {
                $query = new CRequest();
                $query->addColumn('rv.value', 'value');
                $query->addColumn('s.datetime', 'datetime');
                $query->addColumn('s.context_id', 'sejour_id');
                $query->addTable('observation_result AS r');
                $query->addLJoin('observation_result_value AS rv ON rv.observation_result_id = r.observation_result_id');
                $query->addLJoin(
                    'observation_result_set AS s ON r.observation_result_set_id = s.observation_result_set_id'
                );
                $query->addLJoin('observation_value_type AS t ON rv.value_type_id = t.observation_value_type_id');
                $query->addLJoin('observation_value_unit AS u ON rv.unit_id = u.observation_value_unit_id');
                $query->addLJoin('sejour ON (sejour.sejour_id = s.context_id AND s.context_class = "CSejour")');
                $query->addWhere("s.sender_class = '{$source->_class}'");
                $query->addWhere("s.sender_id = {$source->_id}");
                $query->addWhere(
                    "s.datetime <= DATE_ADD(s.datetime, INTERVAL {$frequency_integration_constants} MINUTE)"
                );
                $query->addWhere("rv.value_type_id = {$conversion->value_type_id}");
                $query->addWhere("rv.unit_id = {$conversion->value_unit_id}");
                $query->addWhere("DATE(sejour.entree) = '{$current_date}'");
                $query->addOrder('s.datetime DESC');
                $query->setLimit('0, 1');

                $results = $ds->loadList($query->makeSelect());

                foreach ($results as $_result) {
                    if ($_result['value']) {
                        $sejour = CSejour::find($_result['sejour_id']);

                        $values[$sejour->_id][$conversion->constant_name]["value"]    = $_result['value'];
                        $values[$sejour->_id][$conversion->constant_name]["datetime"] = $_result['datetime'];
                    }
                }
            }
        }

        if (count($values)) {
            foreach ($values as $_sejour_id => $constants) {
                $sejour = CSejour::find($_sejour_id);

                foreach ($constants as $_constant_name => $_constant) {
                    $where                  = [];
                    $where["datetime"]      = "= '{$_constant["datetime"]}'";
                    $where["patient_id"]    = "= '$sejour->patient_id'";
                    $where["context_class"] = "= '$sejour->_class'";
                    $where["context_id"]    = "= '$sejour->_id'";

                    $constant = new CConstantesMedicales();

                    if (!$constant->loadObject($where)) {
                        $constant->patient_id     = $sejour->patient_id;
                        $constant->context_class  = $sejour->_class;
                        $constant->context_id     = $sejour->_id;
                        $constant->datetime       = $_constant["datetime"];
                        $constant->origin         = 'Mindray';
                        $constant->_convert_value = false;

                        $constant->$_constant_name = $_constant["value"];
                        $constant->store();

                        if ($constant->_id) {
                            $counter++;
                        }
                    }
                }
            }
        }

        CAppUI::stepMessage(UI_MSG_OK, CAppUI::tr("CConstantesMedicales-msg-added") . " x $counter");
    }
}
