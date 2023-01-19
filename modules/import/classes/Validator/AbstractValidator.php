<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Validator;

use Ox\Core\Specification\SpecificationInterface;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Entity\ActeCCAM;
use Ox\Import\Framework\Entity\ActeNGAP;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Antecedent;
use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\ConsultationAnesth;
use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\DossierMedical;
use Ox\Import\Framework\Entity\EvenementPatient;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Injection;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\Traitement;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Entity\ValidationAwareInterface;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationAbnormalFlag;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationExam;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationFile;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationIdentifier;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationPatient;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResponsible;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResult;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultSet;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultValue;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationValueUnit;

/**
 * Description
 */
abstract class AbstractValidator implements ValidatorVisitorInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /**
     * @param SpecificationInterface|null $spec
     * @param ValidationAwareInterface    $object
     *
     * @return SpecificationViolation|null
     */
    protected function validateObject(
        ?SpecificationInterface $spec,
        ValidationAwareInterface $object
    ): ?SpecificationViolation {
        if ($spec === null) {
            return null;
        }

        $remains = $spec->remainderUnsatisfiedBy($object);

        if ($remains instanceof SpecificationInterface) {
            return $remains->toViolation($object);
        }

        return null;
    }

    /**
     * Get the specification for external user
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalUserSpec(): ?SpecificationInterface;

    /**
     * Return the specifications for external patient
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalPatientSpec(): ?SpecificationInterface;

    /**
     * Return the specifications for external medecin
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalMedecinSpec(): ?SpecificationInterface;

    /**
     * Return the specifications for external plageconsult
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalPlageConsultSpec(): ?SpecificationInterface;

    /**
     * Return the specification for external consultation
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalConsultationSpec(): ?SpecificationInterface;

    /**
     * Return the specification for external consultation anesth
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getConsultationAnesthSpec(): ?SpecificationInterface;

    /**
     * Return the specification for external sejour
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalSejourSpec(): ?SpecificationInterface;

    /**
     * Return the specifications for external file
     *
     * @return SpecificationInterface|null
     */
    abstract protected function getExternalFileSpec(): ?SpecificationInterface;

    abstract protected function getExternalAntecedentSpec(): ?SpecificationInterface;

    abstract protected function getExternalTraitementSpec(): ?SpecificationInterface;

    abstract protected function getExternalCorrespondantSpec(): ?SpecificationInterface;

    abstract protected function getExternalEvenementPatientSpec(): ?SpecificationInterface;

    abstract protected function getExternalInjectionSpec(): ?SpecificationInterface;

    abstract protected function getExternalActeCCAMSpec(): ?SpecificationInterface;

    abstract protected function getExternalActeNGAPSpec(): ?SpecificationInterface;

    abstract protected function getExternalConstanteSpec(): ?SpecificationInterface;

    abstract protected function getExternalDossierMedicalSpec(): ?SpecificationInterface;

    abstract protected function getExternalAffectationSpec(): ?SpecificationInterface;

    abstract protected function getExternalOperationSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationResultSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationIdentifierSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationResultValueSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationResultSetSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationAbnormalFlagSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationValueUnitSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationFileSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationResponsibleSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationExamSpec(): ?SpecificationInterface;

    abstract protected function getExternalObservationPatientSpec(): ?SpecificationInterface;

    /**
     * @inheritDoc
     */
    public function validateUser(User $user): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalUserSpec(), $user);
    }

    /**
     * @inheritDoc
     */
    public function validatePatient(Patient $patient): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalPatientSpec(), $patient);
    }

    /**
     * @inheritDoc
     */
    public function validateMedecin(Medecin $medecin): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalMedecinSpec(), $medecin);
    }

    /**
     * @inheritDoc
     */
    public function validatePlageConsult(PlageConsult $plage_consult): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalPlageConsultSpec(), $plage_consult);
    }

    /**
     * @inheritDoc
     */
    public function validateConsultation(Consultation $consultation): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalConsultationSpec(), $consultation);
    }

    /**
     * @inheritDoc
     */
    public function validateConsultationAnesth(ConsultationAnesth $consultation): ?SpecificationViolation
    {
        return $this->validateObject($this->getConsultationAnesthSpec(), $consultation);
    }

    /**
     * @inheritDoc
     */
    public function validateSejour(Sejour $sejour): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalSejourSpec(), $sejour);
    }

    /**
     * @inheritDoc
     */
    public function validateFile(File $file): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalFileSpec(), $file);
    }

    public function validateAntecedent(Antecedent $antecedent): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalAntecedentSpec(), $antecedent);
    }

    public function validateTraitement(Traitement $traitement): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalTraitementSpec(), $traitement);
    }

    public function validateCorrespondant(Correspondant $correspondant): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalCorrespondantSpec(), $correspondant);
    }

    public function validateEvenementPatient(EvenementPatient $evenement_patient): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalEvenementPatientSpec(), $evenement_patient);
    }

    public function validateInjection(Injection $injection): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalInjectionSpec(), $injection);
    }

    public function validateActeCCAM(ActeCCAM $acte_ccam): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalActeCCAMSpec(), $acte_ccam);
    }

    public function validateActeNGAP(ActeNGAP $acte_ngap): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalActeNGAPSpec(), $acte_ngap);
    }

    public function validateConstante(Constante $constante): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalConstanteSpec(), $constante);
    }

    public function validateDossierMedical(DossierMedical $dossier_medical): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalDossierMedicalSpec(), $dossier_medical);
    }

    public function validateAffectation(Affectation $affectation): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalAffectationSpec(), $affectation);
    }

    public function validateOperation(Operation $operation): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalOperationSpec(), $operation);
    }

    public function validateObservationResult(ObservationResult $observation_result): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationResultSpec(), $observation_result);
    }

    public function validateObservationIdentifier(ObservationIdentifier $observation_identifier): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationIdentifierSpec(), $observation_identifier);
    }

    public function validateObservationResultValue(ObservationResultValue $observation_result_value): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationResultValueSpec(), $observation_result_value);
    }

    public function validateObservationResultSet(ObservationResultSet $observation_result_set): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationResultSetSpec(), $observation_result_set);
    }

    public function validateObservationAbnormalFlag(ObservationAbnormalFlag $observation_flag): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationAbnormalFlagSpec(), $observation_flag);
    }

    public function validateObservationValueUnit(ObservationValueUnit $observation_value_unit): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationValueUnitSpec(), $observation_value_unit);
    }

    public function validateObservationFile(ObservationFile $observation_file): ?SpecificationViolation
    {
        return $this->validateObject($this->getExternalObservationFileSpec(), $observation_file);
    }

    public function validateObservationResponsible(
        ObservationResponsible $observation_responsible
    ): ?SpecificationViolation {
        return $this->validateObject($this->getExternalObservationResponsibleSpec(), $observation_responsible);
    }

    public function validateObservationExam(
        ObservationExam $observation_exam
    ): ?SpecificationViolation {
        return $this->validateObject($this->getExternalObservationExamSpec(), $observation_exam);
    }

    public function validateObservationPatient(
        ObservationPatient $observation_patient
    ): ?SpecificationViolation {
        return $this->validateObject($this->getExternalObservationPatientSpec(), $observation_patient);
    }
}
