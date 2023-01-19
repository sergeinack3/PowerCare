<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle\Level3;

use DOMElement;
use DOMNode;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Levels\Level3\CCDAIJBComorbidite;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CAntecedentSnomed;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

class CCDAHandleIJBComorbidite extends CCDAHandleLevel3
{
    /** @var string TEMPLATE_ID for section */
    private const TEMPLATE_ID = '2.25.299518904337880959076241620201932965147.17.10';

    /**
     * @var string[] IJB types antécédent
     */
    private static $mapping_type_antecedent = [
        "2.25.299518904337880959076241620201932965147.17.10.4" => "alle",
        "2.25.299518904337880959076241620201932965147.17.10.1" => "med",
        "2.25.299518904337880959076241620201932965147.17.10.3" => "chir",
        "2.25.299518904337880959076241620201932965147.17.10.2" => "gyn",
        "2.25.299518904337880959076241620201932965147.17.10.5" => "fam",
    ];

    /**
     * @inheritDoc
     */
    protected function handlePatient(): void
    {
        $patientRole = $this->getCDADomDocument()->getPatientRole();

        // Patient
        if (!$patientPI = $this->getPatientIdentifiers($patientRole)) {
            throw CCDAException::patientIdentifierNotFound();
        }

        $IPP = CIdSante400::getMatch(
            "CPatient",
            $this->getCDADomDocument()->getSender()->_tag_patient,
            $patientPI
        );

        // Patient non retrouvé par son IPP
        if (!$IPP->_id) {
            throw CCDAException::patientNotFound();
        }

        $patient = new CPatient();
        $patient->load($IPP->object_id);

        $this->setPatient($patient);
    }

    /**
     * Get patient identifier
     *
     * @return string|null
     */
    private function getPatientIdentifiers(DOMElement $node): ?string
    {
        return $this->getCDADomDocument()->queryAttributeNodeValue(
            "id[@root='" . CCDAIJBComorbidite::OID . "']",
            $node,
            "extension"
        );
    }

    /**
     * @inheritDoc
     */
    protected function handleComponents(): void
    {
        $this->patient = $this->getPatient();

        // Récupération ou création du dossier médical
        if (!$dossier_id = CDossierMedical::dossierMedicalId($this->patient->_id, $this->patient->_class)) {
            throw CCDAException::invalidMedicalFolder();
        }

        $cda_dom_document = $this->getCDADomDocument();

        // Récupération des components
        foreach ($cda_dom_document->getStructuredBodyComponents() as $_component_node) {
            foreach ($cda_dom_document->queryNodes("section/component", $_component_node) as $_sub_component_node) {
                foreach ($cda_dom_document->queryNodes("section/entry", $_sub_component_node) as $_entry) {
                    $this->mappingAntecedent($_entry, $dossier_id, $cda_dom_document->getInteropSender());
                }
            }
        }
    }

    /**
     * Mapping antecedent
     *
     * @param DOMNode        $_entry     DOM entry
     * @param int            $dossier_id Dossier ID
     * @param CInteropSender $sender     Sender
     *
     * @return void
     * @throws Exception
     */
    public function mappingAntecedent(DOMElement $_entry, $dossier_id, $sender)
    {
        $cda_dom_document = $this->getCDADomDocument();
        $report           = $cda_dom_document->getReport();

        $antecedent                     = new CAntecedent();
        $antecedent->dossier_medical_id = $dossier_id;
        $rques                          = $cda_dom_document->queryTextNode("observation/text", $_entry, ".");

        if (!$rques) {
            return;
        }

        $idex = new CIdSante400();
        // On essaye de retrouver l'antécédent par son identifiant
        $value_id = $cda_dom_document->getValueAttributNode(
            $cda_dom_document->queryNode("observation/id", $_entry),
            "root"
        );

        if ($value_id) {
            $idex = CIdSante400::getMatch($antecedent->_class, $sender->_tag_hl7, $value_id, $antecedent->_id);
            if ($idex->_id) {
                $antecedent->load($idex->object_id);
            }
        }

        $antecedent->rques = $rques;
        $antecedent->escapeValues();

        // Si on a pas l'identifiant on fait un loadMatching sur le nom
        if (!$antecedent->_id) {
            $antecedent->loadMatchingObject();
        }

        $antecedent->rques = $rques;
        // Type of antecedent
        $type_antecedent = $this->getType($_entry);
        if ($type_antecedent) {
            $antecedent->type = $type_antecedent;
        }

        // Certainly
        $degree_certainty = $this->getCertainly($_entry);
        if ($degree_certainty) {
            // Si degrés de certitude vaut "inexact", on doit annuler l'antécédent
            if ($degree_certainty == "inexact") {
                $antecedent->annule = 1;
            } else {
                $antecedent->degree_certainty = $degree_certainty;
                $antecedent->annule           = 0;
            }
        }

        // Start date
        $start_date = $this->getStartDate($_entry);
        if ($start_date) {
            $antecedent->date = $start_date;
        }

        // End date
        $end_date = $this->getEndDate($_entry);
        if ($end_date) {
            $antecedent->date_fin = $end_date;
        }

        if ($msg = $antecedent->store()) {
            $report->addItemFailed($antecedent, $msg);

            return;
        }

        // Store de l'idex
        if ($value_id) {
            $idex->setObject($antecedent);
            $idex->store();
        }

        $report->addItemsStored($antecedent);

        $this->getCodeSNOMED($_entry, $antecedent);
    }

    /**
     * Get type of antecedent from XML
     *
     * @param DOMElement $_entry entry node
     *
     * @return String
     */
    public function getType(DOMElement $_entry)
    {
        return CMbArray::get(
            self::$mapping_type_antecedent,
            $this->getCDADomDocument()->queryTextNode("observation/templateId/@root", $_entry)
        );
    }

    /**
     * Get certainly from XML
     *
     * @param DOMNode $_entry entry node
     *
     * @return String|null
     * @throws Exception
     */
    public function getCertainly(DOMElement $_entry)
    {
        $cda_dom_document = $this->getCDADomDocument();

        $node_code_certitude = $cda_dom_document->queryNode(
            "observation/entryRelationship/observation/code[@code='17.10.0.1']",
            $_entry
        );

        if (!$node_code_certitude) {
            return null;
        }

        return $cda_dom_document->getValueAttributNode(
            $this->getValue($node_code_certitude->parentNode),
            "code"
        );
    }

    public function getValue(DOMNode $node)
    {
        return $this->getCDADomDocument()->queryNode("value", $node);
    }

    /**
     * Get start date from XML
     *
     * @param DOMNode $_entry entry node
     *
     * @return String|null
     * @throws Exception
     */
    public function getStartDate(DOMElement $_entry)
    {
        $cda_dom_document = $this->getCDADomDocument();

        $node_code_start_date = $cda_dom_document->queryNode(
            "observation/entryRelationship/observation/code[@code='17.10.1.2']",
            $_entry
        );

        if (!$node_code_start_date) {
            return null;
        }

        $start_date = $cda_dom_document->getValueAttributNode(
            $this->getValue($node_code_start_date->parentNode),
            "value"
        );
        switch (strlen($start_date)) {
            case 4:
                $format_start_date = $start_date . "-00-00";
                break;
            case 6:
                $values_date       = str_split($start_date, 2);
                $format_start_date = CMbArray::get($values_date, 0) . CMbArray::get(
                        $values_date,
                        1
                    ) . "-" . CMbArray::get($values_date, 2) . "-00";
                break;
            case 8:
                $format_start_date = CMbDT::date($start_date);
                break;
            default:
                return null;
        }

        return $format_start_date;
    }

    /**
     * Get end date from XML
     *
     * @param DOMNode $_entry entry node
     *
     * @return String|null
     * @throws Exception
     */
    public function getEndDate(DOMElement $_entry)
    {
        $cda_dom_document = $this->getCDADomDocument();

        $node_code_end_date = $cda_dom_document->queryNode(
            "observation/entryRelationship/observation/code[@code='17.10.1.3']",
            $_entry
        );

        if (!$node_code_end_date) {
            return null;
        }

        $end_date = $cda_dom_document->getValueAttributNode(
            $this->getValue($node_code_end_date->parentNode),
            "value"
        );
        switch (strlen($end_date)) {
            case 4:
                $format_start_date = $end_date . "-00-00";
                break;
            case 6:
                $values_date       = str_split($end_date, 2);
                $format_start_date = CMbArray::get($values_date, 0) . CMbArray::get(
                        $values_date,
                        1
                    ) . "-" . CMbArray::get($values_date, 2) . "-00";
                break;
            case 8:
                $format_start_date = CMbDT::date($end_date);
                break;
            default:
                return null;
        }

        return $format_start_date;
    }

    /**
     * Get code snomed form XML and store it
     *
     * @param DOMNode     $_entry     entry node
     * @param CAntecedent $antecedent antecedent
     *
     * @return null
     */
    public function getCodeSNOMED(DOMElement $_entry, CAntecedent $antecedent)
    {
        $cda_dom_document = $this->getCDADomDocument();
        $report           = $cda_dom_document->getReport();

        switch ($antecedent->type) {
            case "alle":
                $node_code_snomed = $cda_dom_document->queryNode(
                    "observation/entryRelationship/observation/code[@code='17.10.4.1']",
                    $_entry
                );
                $code_snomed      = $cda_dom_document->getValueAttributNode(
                    $this->getValue($node_code_snomed->parentNode),
                    "code"
                );
                break;
            default:
                $code_snomed = $cda_dom_document->getValueAttributNode(
                    $cda_dom_document->queryNode("observation/code", $_entry),
                    "code"
                );
        }

        if (!$code_snomed) {
            return null;
        }

        $antecedent_snomed                = new CAntecedentSnomed();
        $antecedent_snomed->code          = $code_snomed;
        $antecedent_snomed->antecedent_id = $antecedent->_id;
        $antecedent_snomed->loadMatchingObject();
        if ($msg = $antecedent_snomed->store()) {
            $report->addItemFailed($antecedent_snomed, $msg);

            return null;
        }

        $report->addItemsStored($antecedent_snomed);

        return null;
    }
}
