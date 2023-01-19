<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Documents;

use Ox\Interop\Cda\Components\Meta\CDAMetaPatientRole;
use Ox\Interop\Cda\Components\Sections\ANS\Medications\CDASectionFRTraitementsAdmission;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAllergiesEtIntolerances;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsChirurgicaux;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsMedicaux;
use Ox\Interop\Cda\Components\Sections\ANS\ReasonsForCare\CDASectionFRRaisonRecommandationNonCode;
use Ox\Interop\Cda\Components\Sections\ANS\SousSections\CDASectionFRCommentaireNonCode;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component3;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RecordTarget;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_StructuredBody;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDADocumentLDLEES extends CCDADocumentCDA
{
    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_RecordTarget
     */
    protected function setRecordTarget(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $record       = new CCDAPOCD_MT000040_RecordTarget();
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

        // Section FR - Raison de la recommandation non code
        $this->setRaisonRecommandation($structured_body);

        // Section FR - Traitements à l'admission
        $component = new CCDAPOCD_MT000040_Component3();
        $section   = (new CDASectionFRTraitementsAdmission($this->factory))->build();
        $component->setSection($section);
        $structured_body->appendComponent($component);

        // Section FR - Allergies et intolerances
        $component = new CCDAPOCD_MT000040_Component3();
        $section   = (new CDASectionFRAllergiesEtIntolerances($this->factory))->build();
        $component->setSection($section);
        $structured_body->appendComponent($component);

        // Section FR - Antecedents Medicaux
        if (CDASectionFRAntecedentsMedicaux::check($this->factory)) {
            $component = new CCDAPOCD_MT000040_Component3();
            $section   = (new CDASectionFRAntecedentsMedicaux($this->factory))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }

        // Section FR - Antecedents Chirurgicaux
        if (CDASectionFRAntecedentsChirurgicaux::check($this->factory)) {
            $component = new CCDAPOCD_MT000040_Component3();
            $section   = (new CDASectionFRAntecedentsChirurgicaux($this->factory))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }

        // Section FR - Commentaire non code
        $this->setComment($structured_body);

        return $structured_body;
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    public function setComment(CCDAPOCD_MT000040_StructuredBody $structured_body): void
    {
        // build comment
        $comment = $this->factory->patient->rques ?: 'Aucune autre information utile';
        $content    = '<table><thead><tr><th colspan="3">Commentaires</th></tr></thead><tbody><tr><td><content ID="commentUtile">'.$comment.'</content></td></tr></tbody></table>';

        // Set comment
        $component = new CCDAPOCD_MT000040_Component3();
        $section   = (new CDASectionFRCommentaireNonCode($this->factory, $content))->build();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_StructuredBody $structured_body
     */
    public function setRaisonRecommandation(CCDAPOCD_MT000040_StructuredBody $structured_body): void
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

        // Set comment
        $component = new CCDAPOCD_MT000040_Component3();
        $section   = (new CDASectionFRRaisonRecommandationNonCode($this->factory, $content))->build();
        $component->setSection($section);
        $structured_body->appendComponent($component);
    }
}
