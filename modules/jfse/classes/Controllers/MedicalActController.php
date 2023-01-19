<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\Formula\FormulaService;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\MedicalAct\InsuranceAmountForcing;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalActService;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormula;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CJfseActView;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MedicalActController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "unlink" => [
            "method" => "unlinkAct",
        ],
        "link" => [
            "method" => "linkAct",
        ],
        'edit' => [
            'method' => 'editAct'
        ],
        'store' => [
            'method' => 'storeAct'
        ]
    ];

    public static function getRoutePrefix(): string
    {
        return 'medicalActs';
    }

    public function linkAct(Request $request): Response
    {
        /** @var CActe $act */
        $act = CActe::loadFromGuid($request->get('act_class') . '-' . $request->get('act_id'));

        $invoice_data_model = CJfseInvoice::getFromJfseId($request->get('invoice_id'));

        /* We set the user Jfse id in the cache for the authorisation token */
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new MedicalActService();
        try {
            $result = $service->setMedicalAct($invoice_data_model->jfse_id, $act);
        } catch (JfseException $e) {
            $result = false;
        }

        return new JsonResponse([
            'success'           => $result,
            'consultation_id'   => $invoice_data_model->consultation_id,
            'invoice_id'        => $invoice_data_model->jfse_id,
        ]);
    }

    public function linkActRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id' => CView::post('invoice_id', 'str'),
            'act_class'  => CView::post('act_class', 'str'),
            'act_id'     => CView::post('act_id', 'ref meta|act_class')
        ]);
    }

    public function unlinkAct(Request $request): Response
    {
        $act_data_model = CJfseAct::find($request->get('act_id'));
        $invoice_data_model = $act_data_model->loadInvoice();

        /* We set the user Jfse id in the cache for the authorisation token */
        Utils::setJfseUserIdFromInvoiceId($invoice_data_model->jfse_id);

        $service = new MedicalActService();
        try {
            $result = $service->deleteMedicalAct($invoice_data_model, $act_data_model->jfse_id);
        } catch (JfseException $e) {
            $result = false;
        }

        return new JsonResponse([
            'success'           => $result,
            'consultation_id'   => $invoice_data_model->consultation_id,
            'invoice_id'        => $invoice_data_model->jfse_id,
        ]);
    }

    public function unlinkActRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ['act_id' => CView::post('act_id', 'ref class|CJfseAct')]);
    }

    public function editAct(Request $request): SmartyResponse
    {
        /** @var CActe $act */
        $act = CActe::loadFromGuid($request->get('act_guid'));
        Utils::setJfseUserIdFromMediuser($act->loadRefExecutant());

        switch ($act->_class) {
            case 'CActeCCAM':
                $response = $this->editActCcam($act);
                break;
            case 'CActeNGAP':
                $response = $this->editActNgap($act);
                break;
            case 'CActeLPP':
                $response = $this->editActLpp($act);
                break;
            default:
                $response = new SmartyResponse();
        }

        return $response;
    }

    public function editActRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['act_guid' => CView::post('act_guid', 'str notNull')]);
    }

    public function editActCcam(CActeCCAM $act): SmartyResponse
    {
        /** @var CJfseAct $data_model */
        $data_model = $act->loadUniqueBackRef('jfse_act_link');

        $act->getTarif();
        $act->loadRefExecutant();

        $execution_date_min = CMbDT::date('-27 months', $act->_ref_object->_date);
        if ($act->_ref_object->_ref_patient->naissance > $execution_date_min) {
            $execution_date_min = $act->_ref_object->_ref_patient->naissance;
        }

        $code     = $act->_ref_code_ccam;
        $activity = $code->activites[$act->code_activite];
        $phase    = $activity->phases[$act->code_phase];

        $modifiers = $act->modificateurs;
        if (property_exists($phase, '_modificateurs')) {
            foreach ($phase->_modificateurs as $modificateur) {
                $position = strpos($modifiers, $modificateur->code);
                if ($position !== false) {
                    if ($modificateur->_double == "1") {
                        $modificateur->_checked = $modificateur->code;
                    } elseif ($modificateur->_double == "2") {
                        $modificateur->_checked = $modificateur->code . $modificateur->_double;
                    } else {
                        $modificateur->_checked = null;
                    }
                } else {
                    $modificateur->_checked = null;
                }
            }
        }

        CCodageCCAM::precodeModifiers($phase->_modificateurs, $act, $act->loadRefObject());
        $act->getMontantModificateurs($phase->_modificateurs);

        $teeth = CDentCCAM::loadList();
        $teeth_list = reset($teeth);

        $data_model->loadInvoice();
        $invoice = (new InvoicingService())->getInvoice($data_model->_invoice->jfse_id);

        $medical_act = $invoice->getMedicalAct($data_model->jfse_id);
        if ($medical_act instanceof MedicalAct) {
            $medical_act = CJfseActView::getFromEntity($medical_act);
        }

        $formulas = [];
        $third_party_amc = $invoice->getComplementaryHealthInsurance()->getThirdPartyAmc();
        if ($third_party_amc) {
            $formulas        = CFormula::getListFromEntities(
                $invoice->getComplementaryHealthInsurance()->getAssistant()->getFormulas()
            );
        }

        return new SmartyResponse('medical_act/act_ccam_edit', [
            'act'                => $act,
            'phase'              => $phase,
            'activity'           => $activity,
            'teeth_list'         => $teeth_list,
            'medical_act'        => $medical_act,
            'formulas'           => $formulas,
            'execution_date_min' => $execution_date_min,
            'third_party_amc'    => $third_party_amc
        ]);
    }

    public function editActNgap(CActeNGAP $act): SmartyResponse
    {
        $act->loadRefExecutant();
        $act->getLibelle();
        $act->getForbiddenComplements();
        $act->loadTargetObject();
        $act->_ref_object->loadRefPatient();

        $execution_date_min = CMbDT::date('-27 months', $act->_ref_object->_date);
        if ($act->_ref_object->_ref_patient->naissance > $execution_date_min) {
            $execution_date_min = $act->_ref_object->_ref_patient->naissance;
        }

        /** @var CJfseAct $data_model */
        $data_model = $act->loadUniqueBackRef('jfse_act_link');
        $data_model->loadInvoice();
        $invoice = (new InvoicingService())->getInvoice($data_model->_invoice->jfse_id);
        $medical_act = CJfseActView::getFromEntity($invoice->getMedicalAct($data_model->jfse_id));

        $formulas = [];
        $third_party_amc = $invoice->getComplementaryHealthInsurance()->getThirdPartyAmc();
        if ($third_party_amc) {
            $formulas        = CFormula::getListFromEntities(
                $invoice->getComplementaryHealthInsurance()->getAssistant()->getFormulas()
            );
        }

        return new SmartyResponse('medical_act/act_ngap_edit', [
            'act'                => $act,
            'medical_act'        => $medical_act,
            'formulas'           => $formulas,
            'execution_date_min' => $execution_date_min,
            'third_party_amc'    => $third_party_amc
        ]);
    }

    public function editActLpp(CActeLPP $act): SmartyResponse
    {
        $act->loadRefExecutant();
        $act->_ref_object->loadRefPatient();

        $execution_date_min = CMbDT::date('-27 months', $act->_ref_object->_date);
        if ($act->_ref_object->_ref_patient->naissance > $execution_date_min) {
            $execution_date_min = $act->_ref_object->_ref_patient->naissance;
        }

        /** @var CJfseAct $data_model */
        $data_model = $act->loadUniqueBackRef('jfse_act_link');
        $data_model->loadInvoice();
        $invoice = (new InvoicingService())->getInvoice($data_model->_invoice->jfse_id);
        $medical_act = CJfseActView::getFromEntity($invoice->getMedicalAct($data_model->jfse_id));

        $formulas = [];
        $third_party_amc = $invoice->getComplementaryHealthInsurance()->getThirdPartyAmc();
        if ($third_party_amc) {
            $formulas        = CFormula::getListFromEntities(
                $invoice->getComplementaryHealthInsurance()->getAssistant()->getFormulas()
            );
        }

        return new SmartyResponse('medical_act/act_lpp_edit', [
            'act'                => $act,
            'medical_act'        => $medical_act,
            'formulas'           => $formulas,
            'execution_date_min' => $execution_date_min,
            'third_party_amc'    => $third_party_amc
        ]);
    }

    public function storeAct(Request $request): JsonResponse
    {
        $act = CActe::loadFromGuid($request->get('act_guid'));
        /** @var CJfseAct $data_model */
        $data_model = $act->loadUniqueBackRef('jfse_act_link');
        $data_model->loadInvoice();
        $invoice = (new InvoicingService())->getInvoice($data_model->_invoice->jfse_id);
        $old_medical_act = $invoice->getMedicalAct($data_model->jfse_id);

        Utils::setJfseUserIdFromInvoiceId($data_model->_invoice->jfse_id);

        $formula = null;
        if (
            $request->get('formula_number') && (!$old_medical_act->getFormula()
                || $old_medical_act->getFormula()->getFormulaNumber() != $request->get('formula_number'))
        ) {
            $formula = $invoice->getComplementaryHealthInsurance()->getAssistant()
                ->getFormulaFromApplicableFormulas($request->get('formula_number'));

            $formula->setParametersFromArray($request->get('formula_parameters'));
        }

        $amo_forcing = null;
        if (
            $request->get('amo_forcing_choice') !== null
            && $old_medical_act->getAmoAmountForcing()->getChoice() != $request->get('amo_forcing_choice')
        ) {
            $amo_forcing_data = [
                'type'                    => 'AMO',
                'choice'                  => $request->get('amo_forcing_choice'),
                'computed_insurance_part' => $request->get('amo_forcing_computed_amount')
            ];

            if ($request->get('amo_forcing_choice') != 0) {
                $amo_forcing_data['modified_insurance_part'] = $request->get('amo_forcing_modified_amount');
            }

            $amo_forcing = InsuranceAmountForcing::hydrate($amo_forcing_data);
        }

        $amc_forcing = null;
        if (
            $request->get('amc_forcing_choice') !== null
            && $old_medical_act->getAmcAmountForcing()->getChoice() != $request->get('amc_forcing_choice')
        ) {
            $amc_forcing_data = [
                'type'                    => 'AMC',
                'choice'                  => $request->get('amc_forcing_choice'),
                'computed_insurance_part' => $request->get('amc_forcing_computed_amount')
            ];

            if ($request->get('amc_forcing_choice') != 0) {
                $amc_forcing_data['modified_insurance_part'] = $request->get('amc_forcing_modified_amount');
            }

            $amc_forcing = InsuranceAmountForcing::hydrate($amc_forcing_data);
        }

        (new MedicalActService())->setMedicalAct($invoice->getId(), $act, $formula, $amo_forcing, $amc_forcing);

        return new JsonResponse(['success' => true]);
    }

    public function storeActRequest(): Request
    {
        CCanDo::checkEdit();

        $formula_parameters = [];
        foreach (CView::post('formula_parameters', ['str', 'default' => []]) as $parameter) {
            $formula_parameters[] = json_decode(stripslashes($parameter), true);
        }

        return new Request([
            'act_guid'                    => CView::post('act_guid', 'str notNull'),
            'amo_forcing_choice'          => CView::post('amo_forcing_choice', ['num', 'default' => null]),
            'amo_forcing_computed_amount' => CView::post('amo_forcing_computed_amount', ['float', 'default' => null]),
            'amo_forcing_modified_amount' => CView::post('amo_forcing_modified_amount', ['float', 'default' => null]),
            'amc_forcing_choice'          => CView::post('amc_forcing_choice', ['num', 'default' => null]),
            'amc_forcing_computed_amount' => CView::post('amc_forcing_computed_amount', ['float', 'default' => null]),
            'amc_forcing_modified_amount' => CView::post('amc_forcing_modified_amount', ['float', 'default' => null]),
            'formula_number'              => CView::post('formula_number', ['num', 'default' => null]),
            'formula_parameters'          => $formula_parameters,
        ]);
    }
}
