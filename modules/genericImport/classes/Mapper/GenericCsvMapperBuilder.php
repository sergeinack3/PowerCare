<?php

/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Adapter\CSVAdapter;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Mapper\MapperInterface;
use Ox\Import\Framework\Mapper\MapperMetadata;
use Ox\Import\GenericImport\GenericImport;
use Ox\Mediboard\Cabinet\Import\OxPivotActeNGAP;
use Ox\Mediboard\Cabinet\Import\OxPivotConsultation;
use Ox\Mediboard\Cabinet\Import\OxPivotInjection;
use Ox\Mediboard\Files\Import\OxPivotFile;
use Ox\Mediboard\Hospi\Import\OxPivotAffectation;
use Ox\Mediboard\Mediusers\Import\OxPivotMediuser;
use Ox\Mediboard\Patients\Import\OxPivotAntecedent;
use Ox\Mediboard\Patients\Import\OxPivotConstante;
use Ox\Mediboard\Patients\Import\OxPivotCorrespondant;
use Ox\Mediboard\Patients\Import\OxPivotDossierMedical;
use Ox\Mediboard\Patients\Import\OxPivotEvenementPatient;
use Ox\Mediboard\Patients\Import\OxPivotMedecin;
use Ox\Mediboard\Patients\Import\OxPivotPatient;
use Ox\Mediboard\Patients\Import\OxPivotTraitement;
use Ox\Mediboard\PlanningOp\Import\OxPivotOperation;
use Ox\Mediboard\PlanningOp\Import\OxPivotSejour;
use Ox\Mediboard\SalleOp\Import\OxPivotActeCCAM;

/**
 * CSV Mapper builder for generic import
 */
class GenericCsvMapperBuilder implements MapperBuilderInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /**
     * @param string $name
     *
     * @return MapperInterface
     * @throws ImportException
     */
    public function build(string $name): MapperInterface
    {
        $adapter = new CSVAdapter();

        $conditions = [];
        $patient_id = $this->configuration['patient_id'];

        switch ($name) {
            case GenericImport::UTILISATEUR:
                $meta   = new MapperMetadata(
                    $this->configuration['user_file_path'],
                    OxPivotMediuser::FIELD_ID,
                    $this->configuration
                );
                $mapper = new UserMapper($meta, $adapter);
                break;

            case GenericImport::PATIENT:
                if ($patient_id) {
                    $conditions[OxPivotPatient::FIELD_ID] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::PATIENT . '_file_path'],
                    OxPivotPatient::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new PatientMapper($meta, $adapter);
                break;

            case GenericImport::MEDECIN:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::MEDECIN . '_file_path'],
                    OxPivotMedecin::FIELD_ID,
                    $this->configuration
                );
                $mapper = new MedecinMapper($meta, $adapter);
                break;

            case GenericImport::CORRESPONDANT_MEDICAL:
                if ($patient_id) {
                    $conditions[OxPivotCorrespondant::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration['correspondant_file_path'],
                    OxPivotCorrespondant::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new CorrespondantMedicalMapper($meta, $adapter);
                break;

            case GenericImport::ANTECEDENT:
                if ($patient_id) {
                    $conditions[OxPivotAntecedent::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::ANTECEDENT . '_file_path'],
                    OxPivotAntecedent::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new AntecedentMapper($meta, $adapter);
                break;

            case GenericImport::TRAITEMENT:
                if ($patient_id) {
                    $conditions[OxPivotTraitement::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::TRAITEMENT . '_file_path'],
                    OxPivotTraitement::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new TraitementMapper($meta, $adapter);
                break;

            case GenericImport::PLAGE_CONSULTATION:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::CONSULTATION . '_file_path'],
                    OxPivotConsultation::FIELD_ID,
                    $this->configuration
                );
                $mapper = new PlageConsultationMapper($meta, $adapter);
                break;

            case GenericImport::CONSULTATION:
                if ($patient_id) {
                    $conditions[OxPivotConsultation::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::CONSULTATION . '_file_path'],
                    OxPivotConsultation::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new ConsultationMapper($meta, $adapter);
                break;

            case GenericImport::EVENEMENT:
                if ($patient_id) {
                    $conditions[OxPivotEvenementPatient::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::EVENEMENT . '_file_path'],
                    OxPivotEvenementPatient::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new EvenementMapper($meta, $adapter);
                break;

            case GenericImport::INJECTION:
                if ($patient_id) {
                    $conditions[OxPivotInjection::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::INJECTION . '_file_path'],
                    OxPivotInjection::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new InjectionMapper($meta, $adapter);
                break;

            case GenericImport::FICHIER:
                if ($patient_id) {
                    $conditions[OxPivotFile::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::FICHIER . '_file_path'],
                    OxPivotFile::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new FileMapper($meta, $adapter);
                break;

            case GenericImport::ACTE_CCAM:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::ACTE_CCAM . '_file_path'],
                    OxPivotActeCCAM::FIELD_ID,
                    $this->configuration
                );
                $mapper = new ActeCCAMMapper($meta, $adapter);
                break;

            case GenericImport::ACTE_NGAP:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::ACTE_NGAP . '_file_path'],
                    OxPivotActeNGAP::FIELD_ID,
                    $this->configuration
                );
                $mapper = new ActeNGAPMapper($meta, $adapter);
                break;

            case GenericImport::CONSTANTE:
                if ($patient_id) {
                    $conditions[OxPivotConstante::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::CONSTANTE . '_file_path'],
                    OxPivotConstante::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new ConstanteMapper($meta, $adapter);
                break;

            case GenericImport::DOSSIER_MEDICAL:
                if ($patient_id) {
                    $conditions[OxPivotDossierMedical::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::DOSSIER_MEDICAL . '_file_path'],
                    OxPivotDossierMedical::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new DossierMedicalMapper($meta, $adapter);
                break;
            case GenericImport::SEJOUR:
                if ($patient_id) {
                    $conditions[OxPivotSejour::FIELD_PATIENT] = $patient_id;
                }

                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::SEJOUR . '_file_path'],
                    OxPivotSejour::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new SejourMapper($meta, $adapter);
                break;
            case GenericImport::AFFECTATION:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::AFFECTATION . '_file_path'],
                    OxPivotAffectation::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new AffectationMapper($meta, $adapter);
                break;
            case GenericImport::OPERATION:
                $meta   = new MapperMetadata(
                    $this->configuration[GenericImport::OPERATION . '_file_path'],
                    OxPivotOperation::FIELD_ID,
                    $this->configuration,
                    $conditions
                );
                $mapper = new OperationMapper($meta, $adapter);
                break;
            default:
                throw new ImportException('Mapper does not exists for ' . $name);
        }

        if ($this->configuration) {
            $adapter->setConfiguration($this->configuration);
        }

        if ($mapper instanceof ConfigurableInterface && $this->configuration) {
            $mapper->setConfiguration($this->configuration);
        }

        return $mapper;
    }
}
