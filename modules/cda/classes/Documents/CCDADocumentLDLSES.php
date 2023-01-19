<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Documents;

use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Transversaux\CDAEntryFRSimpleObservation;
use Ox\Interop\Cda\Components\Meta\CDAMetaPatientRole;
use Ox\Interop\Cda\Components\Sections\ANS\Medications\CDASectionFRTraitementsSortie;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRResultatsEvenementsLDLSES;
use Ox\Interop\Cda\Components\Sections\ANS\ReasonsForCare\CDASectionFRRaisonRecommandationNonCode;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component3;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RecordTarget;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_StructuredBody;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDADocumentLDLSES extends CCDADocumentCDA
{
    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_RecordTarget
     */
    protected function setRecordTarget(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $record = new CCDAPOCD_MT000040_RecordTarget();
        $patient_role = (new CDAMetaPatientRole($this->factory, $this->factory->patient))->build();
        $patient_role->setClassCode();
        $record->setPatientRole($patient_role);

        $document->appendRecordTarget($record);
    }

    /**
     * @return CCDAPOCD_MT000040_StructuredBody
     */
    protected function buildComponentsLevel3(): CCDAPOCD_MT000040_StructuredBody
    {
        $structured_body = parent::buildComponentsLevel3();

        // Section FR - Status du document LDL - SES
        $this->setStatusDocument($structured_body);

        // Section FR - Raison de la recommandation non code
        $this->setRaisonRecommandationNonCode($structured_body);

        // Section FR - Resultats evenements LDL SES
        $this->setResultatsEvenements($structured_body);

        // Section FR - Traitements administres
        $this->setTraitementsAdministres($structured_body);

        // Section FR - Traitements a la sorite
        $this->setTraitementsSortie($structured_body);

        // Section FR - Resultats examens non code
        $this->setResultatsExamensNonCode($structured_body);

        // Section FR - Plan de soins
        $this->setPlanDeSoins($structured_body);

        // Section FR - Dispositif medicaux
        $this->setDispositifMedicaux($structured_body);

        // Section FR - Allergies et intolerances
        $this->setAllergiesEtIntolerances($structured_body);

        return $structured_body;
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     *
     * @throws CCDAException
     */
    protected function setStatusDocument(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        $section = (new CDASection($this->factory))->build();
        $factory = $this->factory;
        if ($factory->targetObject instanceof CSejour) {
            $stateDoc = $factory->targetObject->_etat == 'cloture' ? 'Validé' : 'En cours';
        }
        elseif ($factory->targetObject instanceof CConsultation) {
            $stateDoc = $factory->targetObject->chrono == 64 ? 'Validé' : 'En cours';
        }
        else {
            $stateDoc = 'Validé';
        }

        // Template Ids
        CCDADocTools::addTemplatesId($section, ['1.2.250.1.213.1.1.2.35', '1.2.250.1.213.1.1.2.35.1']);

        // Code
        CCDADocTools::setCodeLoinc($section, '33557-0');

        // Title
        CCDADocTools::setTitle($section, 'Statut du document');

        // Text
        $text = '<table><tbody><tr><td>Statut du document</td><td><content ID="statutDoc">'.$stateDoc.'</content></td></tr></tbody></table>';
        CCDADocTools::setText($section, $text);

        $options = [];
        // Code cd
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'GEN-065',
            '1.2.250.1.213.1.1.4.322',
            'Statut du document',
            CCDAFactory::TA_ASIP
        );

        // Status Code
        $options['status_code'] = 'completed';

        // Code value
        $options['code_value'] = CCDADocTools::prepareCodeCD(
            $stateDoc == 'En cours' ? 'GEN-066' : 'GEN-068',
            '1.2.250.1.213.1.1.4.322',
            $stateDoc,
            CCDAFactory::TA_ASIP
        );

        // Entry FR status doc
        $simple_observation = (new CDAEntryFRSimpleObservation($this->factory, $options));
        // conformity [FR-Status-Document, FR-Statut-document-LDL-SES]
        $simple_observation->addTemplateIds(['1.2.250.1.213.1.1.3.48.16', '1.2.250.1.213.1.1.3.48.16.1']);

        // Text
        $simple_observation->addText("#statutDoc", true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::date());

        // build Entry
        $entry = $simple_observation->buildEntry();
        $section->appendEntry($entry);

        $component = new CCDAPOCD_MT000040_Component3();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setRaisonRecommandationNonCode(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // build content
        $motif = '';
        $object = $this->factory->targetObject;
        if ($object instanceof CSejour) {
            $motif = $object->libelle;
        }
        elseif ($object instanceof CConsultation) {
            $motif = $object->motif;
        }
        $content = '<table><tbody><tr><td>'.$motif.'</td></tr></tbody></table>';

        $section = (new CDASectionFRRaisonRecommandationNonCode($this->factory, $content))->build();
        $component = new CCDAPOCD_MT000040_Component3();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setTraitementsAdministres(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setResultatsEvenements(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        $section = (new CDASectionFRResultatsEvenementsLDLSES($this->factory))->build();

        $component = new CCDAPOCD_MT000040_Component3();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     *
     * @throws CCDAException
     */
    protected function setTraitementsSortie(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        $section = (new CDASectionFRTraitementsSortie($this->factory))->build();
        $component = new CCDAPOCD_MT000040_Component3();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setResultatsExamensNonCode(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setPlanDeSoins(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setDispositifMedicaux(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    protected function setAllergiesEtIntolerances(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
       // not implemented
    }
}
