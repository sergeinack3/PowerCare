<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Services;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Kernel\Exception\RouteException;
use Ox\Core\Kernel\Routing\RouteManager;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tamm\Cabinet\CAppelSIH;

/**
 * Description
 */
class EvenementPatientDHEService
{

    /** @var RouteManager  */
    private $manager;
    /** @var CSejour  */
    private $sejour;
    /** @var COperation  */
    private $operation;
    /** @var CService  */
    private $service;
    /** @var CPatient  */
    private $patient;
    /** @var CMediusers  */
    private $praticien;
    /** @var CMediusers  */
    private $mediuser;
    /** @var CEvenementPatient  */
    private $evenement_patient;
    /** @var CAppelSIH  */
    private $appel_sih;
    /** @var CEvenementPatient */
    private $evenement_intervention;
    /** @var CEvenementPatient */
    private $evenement_sejour;

    /**
     * @param int $evenement_id
     *
     * @throws RouteException
     * @throws Exception
     */
    public function __construct(int $evenement_id)
    {
        $this->manager           = new RouteManager();
        $this->sejour            = new CSejour();
        $this->operation         = new COperation();
        $this->patient           = new CPatient();
        $this->praticien         = new CMediusers();
        $this->mediuser          = new CMediusers();
        $this->evenement_patient = new CEvenementPatient();
        $this->appel_sih         = new CAppelSIH();
        $this->service           = new CService();

        $this->evenement_patient->load($evenement_id);
        $this->evenement_patient->loadRefsId400SIH();
        $this->manager->loadAllRoutes();
    }

    /**
     * @param string $dhe_class
     * @param int    $dhe_id
     *
     * @return string
     * @throws RouteException
     */
    public function constructRoute(string $dhe_class, int $dhe_id): string
    {
        if ($dhe_class === 'CSejour') {
            $route_name = 'planning_sejour';
            $resources  = '?relations=praticien,patient,service';
            $fieldsets  = '&fieldsets=default,admission,sortie,annulation,urgences,placement,repas,cotation';
        } else {
            $route_name = 'planning_dhe';
            $resources  = '?relations=praticien,patient,anesth';
            $fieldsets  = '&fieldsets=default,examen,timing,tarif,extra';
        }

        $route = $this->manager->getRouteByName($route_name);

        return str_replace(
            ['{sejour_id}', '{operation_id}'],
            $dhe_id,
            $route->getPath()
        ) . $resources . $fieldsets;
    }

    /**
     * @param int    $sih_id
     * @param string $sih_type
     * @param bool   $load_intervention
     *
     * @return mixed
     * @throws RouteException|HttpException
     */
    public function requestDHE(int $sih_id, string $sih_type, bool $load_intervention = false)
    {
        $type = $load_intervention ? 'evenement_intervention' : 'evenement_sejour';
        [$dhe_class, $dhe_id] = explode('-', $this->$type->_ref_context_id400->id400);
        $route = $this->constructRoute($dhe_class, $dhe_id);
        $res   = $this->appel_sih->subCall($route, $sih_id, $sih_type);

        $error = CMbArray::getRecursive($res->getBody('ISO-8859-1'), 'errors message');
        if ($error) {
            throw new HttpException($res->getStatusCode(), $error);
        }

        return json_decode($res->getBody());
    }

    /**
     * @param int $sih_id
     *
     * @return string
     * @throws CMbException
     */
    public function getSIHType(int $sih_id): string
    {
        $token     = $this->appel_sih->getAccessCabinet($this->evenement_patient->loadRefPraticien()->rpps);
        return $token['list_sih'][$sih_id]['type'];
    }

    /**
     * @return int|null
     */
    public function getSIHId(): ?int
    {
        return $this->evenement_patient->_ref_sih_id400->id400;
    }

    /**
     * @return string|null
     */
    public function getDHEGuid(): ?string
    {
        return $this->evenement_patient->_ref_context_id400->id400;
    }

    /**
     * @param mixed $data
     */
    public function prepareResources($data): void
    {
        $tr_false =  CAppUI::tr('bool.0');
        $tr_true  = CAppUI::tr('bool.1');
        $object = $data->data->type === 'intervention' ? 'operation' : 'sejour';

        foreach ($data->data->attributes as $prop => $value) {
            if (property_exists($this->$object, $prop)) {
                if (is_bool($value)) {
                    $value = $value ? $tr_true : $tr_false;
                }
                $this->$object->$prop = $value;
            }
        }

        foreach ($data->included as $relation) {
            foreach ($relation->attributes as $prop => $value) {
                if (property_exists($this->{$relation->type}, $prop)) {
                    $this->{$relation->type}->$prop = $value;
                }
            }
        }

        $this->patient->updateFormFields();
        $this->praticien->_view = $this->praticien->_user_last_name . " " . $this->praticien->_user_first_name;
        $this->mediuser->_view  = $this->mediuser->_user_last_name . " " . $this->mediuser->_user_first_name;
    }

    /**
     * @param string $dhe_class
     * @param bool   $sejour_only
     *
     * @return string
     * @throws Exception
     */
    private function getTemplateContent(string $dhe_class, bool $sejour_only): string
    {
        $css_path         = CAppUI::conf('base_url') . '/style/mediboard_ext/standard.css';
        $smarty           = new CSmartyDP();
        $smarty->assign('anesth', $this->mediuser);
        $smarty->assign('patient', $this->patient);
        $smarty->assign('praticien', $this->praticien);
        $smarty->assign('sejour', $this->sejour);
        $smarty->assign('operation', $this->operation);
        $smarty->assign('service', $this->service);
        $smarty->assign('dhe_class', $dhe_class);
        $smarty->assign('css_path', $css_path);
        $smarty->assign('sejour_only', $sejour_only);
        return $smarty->fetch('print_DHE_resume');
    }

    /**
     * @param string $dhe_class
     *
     * @throws Exception
     */
    public function printDHE(string $dhe_class): void
    {
        $file      = new CFile();
        $htmltopdf = new CHtmlToPDF();

        $sejour_only = true;
        if ($this->evenement_intervention && $this->evenement_intervention->_id) {
            $sejour_only          = false;
            $sih_id               = $this->getSIHId();
            $intervention_content = $this->requestDHE($sih_id, $this->getSIHType($sih_id), true);
            $this->prepareResources($intervention_content);
        }

        $content = $this->getTemplateContent($dhe_class, $sejour_only);

        $file->object_class = $this->evenement_patient->_class;
        $file->object_id    = $this->evenement_patient->_id;
        $file->file_name    = 'DHE-' . $this->evenement_patient->type . '-' . CMbDT::date() . '.pdf';
        $file->file_type    = 'application/pdf';
        $file->fillFields();
        $file->updateFormFields();
        $cr               = new CCompteRendu();
        $cr->_page_format = 'A4';
        $cr->_orientation = 'portrait';


        $htmltopdf->generatePDF($content, true, $cr, $file);

        if ($msg = $file->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        }
    }

    /**
     * @param string $dhe_class
     *
     * @throws Exception
     */
    public function prepareContext(string $dhe_class): void
    {
        $child = $this->evenement_patient->loadRefChild();
        $parent = $this->evenement_patient->loadRefParent();

        if ($parent->_id) {
            $parent->loadRefsId400SIH();
            $this->evenement_intervention = $this->evenement_patient;
            $this->evenement_sejour = $parent;
        }
        if ($child->_id) {
            $child->loadRefsId400SIH();
            $this->evenement_sejour = $this->evenement_patient;
            $this->evenement_intervention = $child;
            $this->evenement_patient->loadRefsId400SIH();
        }
        if (!$parent->_id && !$child->_id) {
            if ($dhe_class === 'CSejour') {
                $this->evenement_sejour = $this->evenement_patient;
            } else {
                $this->evenement_intervention = $this->evenement_patient;
            }
        }
    }
}
