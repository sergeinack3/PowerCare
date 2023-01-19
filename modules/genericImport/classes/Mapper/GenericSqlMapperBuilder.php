<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Core\CSQLDataSource;
use Ox\Import\Framework\Adapter\MySqlAdapter;
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
 * SQL Mapper builder for generic import
 */
class GenericSqlMapperBuilder implements MapperBuilderInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** Specificc table names in sql database */
    protected const EVENEMENT    = 'evenement_patient';
    protected const INTERVENTION = 'intervention';

    /** @var CSQLDataSource */
    private $ds;

    /**
     * @throws ImportException
     */
    public function build(string $name): MapperInterface
    {
        $adapter = new MySqlAdapter($this->getDatasource());

        switch ($name) {
            case GenericImport::ACTE_CCAM:
                $mapper = new ActeCCAMMapper(
                    $this->getMapperMeta(GenericImport::ACTE_CCAM, OxPivotActeCCAM::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::ACTE_NGAP:
                $mapper = new ActeNGAPMapper(
                    $this->getMapperMeta(GenericImport::ACTE_NGAP, OxPivotActeNGAP::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::AFFECTATION:
                $mapper = new AffectationMapper(
                    $this->getMapperMeta(GenericImport::AFFECTATION, OxPivotAffectation::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::ANTECEDENT:
                $mapper = new AntecedentMapper(
                    $this->getMapperMeta(
                        GenericImport::ANTECEDENT,
                        OxPivotAntecedent::FIELD_ID,
                        OxPivotAntecedent::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::CONSTANTE:
                $mapper = new ConstanteMapper(
                    $this->getMapperMeta(
                        GenericImport::CONSTANTE,
                        OxPivotConstante::FIELD_ID,
                        OxPivotConstante::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::PLAGE_CONSULTATION:
                $mapper = new PlageConsultationMapper(
                    $this->getMapperMeta(
                        GenericImport::CONSULTATION,
                        OxPivotConsultation::FIELD_ID,
                        OxPivotConsultation::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::CONSULTATION:
                $mapper = new ConsultationMapper(
                    $this->getMapperMeta(
                        GenericImport::CONSULTATION,
                        OxPivotConsultation::FIELD_ID,
                        OxPivotConsultation::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::CORRESPONDANT_MEDICAL:
                $mapper = new CorrespondantMedicalMapper(
                    $this->getMapperMeta(
                        GenericImport::CORRESPONDANT_MEDICAL,
                        OxPivotCorrespondant::FIELD_ID,
                        OxPivotCorrespondant::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::DOSSIER_MEDICAL:
                $mapper = new DossierMedicalMapper(
                    $this->getMapperMeta(
                        GenericImport::DOSSIER_MEDICAL,
                        OxPivotDossierMedical::FIELD_ID,
                        OxPivotDossierMedical::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::EVENEMENT:
                $mapper = new EvenementMapper(
                    $this->getMapperMeta(
                        self::EVENEMENT,
                        OxPivotEvenementPatient::FIELD_ID,
                        OxPivotEvenementPatient::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::INJECTION:
                $mapper = new InjectionMapper(
                    $this->getMapperMeta(
                        GenericImport::INJECTION,
                        OxPivotInjection::FIELD_ID
                    ),
                    $adapter
                );
                break;
            case GenericImport::FICHIER:
                $mapper = new FileMapper(
                    $this->getMapperMeta(GenericImport::FICHIER, OxPivotFile::FIELD_ID, OxPivotFile::FIELD_PATIENT),
                    $adapter
                );
                break;
            case GenericImport::OPERATION:
                $mapper = new OperationMapper(
                    $this->getMapperMeta(self::INTERVENTION, OxPivotOperation::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::MEDECIN:
                $mapper = new MedecinMapper(
                    $this->getMapperMeta(GenericImport::MEDECIN, OxPivotMedecin::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::PATIENT:
                $mapper = new PatientMapper(
                    $this->getMapperMeta(GenericImport::PATIENT, OxPivotPatient::FIELD_ID, OxPivotPatient::FIELD_ID),
                    $adapter
                );
                break;
            case GenericImport::SEJOUR:
                $mapper = new SejourMapper(
                    $this->getMapperMeta(GenericImport::SEJOUR, OxPivotSejour::FIELD_ID, OxPivotSejour::FIELD_PATIENT),
                    $adapter
                );
                break;
            case GenericImport::TRAITEMENT:
                $mapper = new TraitementMapper(
                    $this->getMapperMeta(
                        GenericImport::TRAITEMENT,
                        OxPivotTraitement::FIELD_ID,
                        OxPivotTraitement::FIELD_PATIENT
                    ),
                    $adapter
                );
                break;
            case GenericImport::UTILISATEUR:
                $mapper = new UserMapper(
                    $this->getMapperMeta(GenericImport::UTILISATEUR, OxPivotMediuser::FIELD_ID),
                    $adapter
                );
                break;
            default:
                throw new ImportException('Mapper does not exists for ' . $name);
        }

        if ($this->configuration) {
            $adapter->setConfiguration($this->configuration);

            if ($mapper instanceof ConfigurableInterface) {
                $mapper->setConfiguration($this->configuration);
            }
        }

        return $mapper;
    }

    protected function getMapperMeta(
        string $collection_name,
        string $primary,
        ?string $patient_field = null
    ): MapperMetadata {
        $conditions = [];
        if ($patient_id = $this->configuration['patient_id']) {
            $conditions[$patient_field] = $this->ds->prepare('= ?', $patient_id);
        }

        return new MapperMetadata(
            $collection_name,
            $primary,
            $this->configuration,
            $conditions
        );
    }

    protected function getDatasource(): CSQLDataSource
    {
        return $this->ds = CSQLDataSource::get($this->configuration['dsn']);
    }
}
