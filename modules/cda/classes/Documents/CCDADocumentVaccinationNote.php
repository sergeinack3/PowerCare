<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Documents;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\ANS\Medications\CDASectionFRTraitements;
use Ox\Interop\Cda\Components\Sections\ANS\Medications\CDASectionFRVaccinations;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAllergiesEtIntolerances;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsMedicaux;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRProblemesActifs;
use Ox\Interop\Cda\Components\Sections\ANS\PlansOfCare\CDASectionFRDirectivesAnticipees;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetAddressLine;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_family;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_given;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Levels\Level3\ANS\CCDAVaccinationNote;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssociatedEntity;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component2;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component3;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Participant1;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Person;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_StructuredBody;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDADocumentVaccinationNote extends CCDADocumentCDA
{
    /** @var CInjection */
    private $injection;

    /**
     * CCDADocumentVSM constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAVaccinationNote $factory)
    {
        parent::__construct($factory);

        /** @var CStoredObject $target */
        $target = $this->factory->targetObject;

        /** @var CInjection $injection */
        $this->injection = $injection = $target->getFromStore(CInjection::class);
        if (!$injection || !$injection instanceof CInjection || !$injection->_id) {
            throw CCDAException::missingParameter('injection');
        }
    }

    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_ClinicalDocument
     * @throws Exception
     */
    public function generateMetadata(CCDAPOCD_MT000040_ClinicalDocument $document): CCDAPOCD_MT000040_ClinicalDocument
    {
        $clinical_document = parent::generateMetadata($document);

        // Ajout du participant (médecin traitant) obligatoire pour le VSM
        $document->appendParticipant($this->getParticipant());

        return $clinical_document;
    }

    /**
     * @return CCDAPOCD_MT000040_Component2
     * @throws Exception
     */
    protected function buildComponentsLevel3(): CCDAPOCD_MT000040_StructuredBody
    {
        $structured_body = parent::buildComponentsLevel3();

        // section Antécédents médicaux [FR-Antecedents-medicaux]
        // not implemented DMP
        /*if (CDASectionFRAntecedentsMedicaux::check($this->factory)) {
            $component = new CCDAPOCD_MT000040_Component3();
            $section   = (new CDASectionFRAntecedentsMedicaux($this->factory))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }*/

        // section Problèmes actifs [FR-Problemes-actifs]
        // not implemented DMP
        /*if (CDASectionFRProblemesActifs::check($this->factory)) {
            $component = new CCDAPOCD_MT000040_Component3();
            $section   = (new CDASectionFRProblemesActifs($this->factory))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }*/

        // section Traitements [FR-Traitements]
        // not implemented DMP
        /*$component = new CCDAPOCD_MT000040_Component3();
        $section   = (new CDASectionFRTraitements($this->factory))->build();
        $component->setSection($section);
        $structured_body->appendComponent($component);*/

        // section Signes vitaux [FR-Signes-vitaux]
        // todo signes vitaux de la journée de la vaccination ?

        // section Allergies et intolérances [FR-Allergies-et-intolerances]
        // not implemented DMP
        /*if (CDASectionFRAllergiesEtIntolerances::check($this->factory)) {
            $component = new CCDAPOCD_MT000040_Component3();
            $section   = (new CDASectionFRAllergiesEtIntolerances($this->factory))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }*/

        // section Historique des grossesses [FR-Historique-des-grossesses]
        // todo voir avec df les infos la dessus

        // section Habitus, Mode de vie [FR-Habitus-mode-de-vie]
        // todo voir avec df pour les habitus

        $component = new CCDAPOCD_MT000040_Component3();
        if ($this->injection->isVaccinated()) {
            // section Vaccinations effectuées [FR-Vaccinations]
            $section   = (new CDASectionFRVaccinations($this->factory, [$this->injection]))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        } else {
            // Refus de vaccinations [FR-Directives-anticipees]
            $section = (new CDASectionFRDirectivesAnticipees($this->factory, $this->injection))->build();
            $component->setSection($section);
            $structured_body->appendComponent($component);
        }

        // section Vaccins recommandés [FR-Vaccins-recommandes]
        // not implemented

        return $structured_body;
    }

    /**
     * @param $factory
     *
     * @return CCDAPOCD_MT000040_Participant1|null
     * @throws \Exception
     */
    public function getParticipant(): ?CCDAPOCD_MT000040_Participant1
    {
        $factory = $this->factory;
        $participant = new CCDAPOCD_MT000040_Participant1();
        $participant->setTypeCode('INF');

        $ccdace = new CCDACE();
        $ccdace->setCode('PCP');
        $ccdace->setCodeSystem('2.16.840.1.113883.5.88');
        $ccdace->setDisplayName('Médecin Traitant');
        $participant->setFunctionCode($ccdace);

        // Ajout du time
        $ivlTs = new CCDAIVL_TS();
        $ivxbL = new CCDAIVXB_TS();
        $ivxbL->setValue(CMbDT::dateTime());
        $ivlTs->setLow($ivxbL);
        $participant->setTime($ivlTs);

        $target        = $factory->targetObject;
        $patient       = null;
        $etablissement = null;

        if ($target instanceof CSejour) {
            $etablissement = $target->loadRefEtablissement();
            $patient = $target->loadRefPatient();
        } elseif ($target instanceof CConsultation) {
            $etablissement = $target->loadRefGroup();
            $patient = $target->loadRefPatient();
        } elseif ($target instanceof CPatient) {
            $etablissement = CGroups::loadCurrent();
            $patient = $target;
        }

        if (!$etablissement) {
            $factory->report->addData(
                CAppUI::tr('CReport-msg-Impossible to retrieve group from target'),
                CItemReport::SEVERITY_ERROR
            );
            return null;
        }

        if (!$patient || !$patient->loadRefMedecinTraitant() || !$patient->loadRefMedecinTraitant()->_id) {
            $factory->report->addData(
                CAppUI::tr(
                    'CReport-msg-Impossible to retrieve patient from target or patient doesnt have treated doctor'
                ),
                CItemReport::SEVERITY_ERROR
            );
            return null;
        }

        $mediUser = $patient->_ref_medecin_traitant;
        if (!$mediUser->rpps) {
            $factory->report->addData(
                CAppUI::tr('CReport-msg-Treated doctor doesnt have rpps'),
                CItemReport::SEVERITY_ERROR
            );
            return null;
        }

        // Ajout associatedEntity
        $associated = new CCDAPOCD_MT000040_AssociatedEntity();
        $associated->setClassCode("PROV");
        $id_root = new CCDAII();
        $id_root->setRoot('1.2.250.1.71.4.2.1');
        $id_root->setExtension($mediUser->rpps);
        $id_root->setAssigningAuthorityName('ASIP Santé');
        $associated->appendId($id_root);

        // Ajout entity
        $ad = new CCDAAD();
        $street = new CCDA_adxp_streetAddressLine();
        $street->setData($etablissement->adresse);
        $street2 = new CCDA_adxp_streetAddressLine();
        $street2->setData($etablissement->cp . " " . $etablissement->ville);

        $ad->append("streetAddressLine", $street);
        $ad->append("streetAddressLine", $street2);

        // Ajout person
        $person = new CCDAPOCD_MT000040_Person();
        $pn = new CCDAPN();

        $enxp = new CCDA_en_family();
        $enxp->setData($mediUser->_p_last_name);
        $pn->append("family", $enxp);

        $enxp = new CCDA_en_given();
        $enxp->setData($mediUser->_p_first_name);
        $pn->append("given", $enxp);

        $person->appendName($pn);
        $associated->setAssociatedPerson($person);

        $associated->appendAddr($ad);

        $participant->setAssociatedEntity($associated);

        return $participant;
    }

}
