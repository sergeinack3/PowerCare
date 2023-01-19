<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Repository;

use Exception;
use Generator;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Antecedent;
use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\DossierMedical;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\EvenementPatient;
use Ox\Import\Framework\Entity\ExternalReference;
use Ox\Import\Framework\Entity\Injection;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Mapper\MapperInterface;
use Ox\Import\GenericImport\GenericImport;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationExam;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationIdentifier;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationPatient;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResponsible;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResult;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultSet;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationValueUnit;

/**
 * Generic class to handle the mapper and retrieve data
 */
class GenericRepository implements RepositoryInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var MapperBuilderInterface */
    private $builder;

    /** @var MapperInterface */
    protected $mapper;

    /** @var MapperInterface[] */
    protected $mapper_pool;

    /** @var string Import resource name */
    private $resource_name;

    /**
     * GenericExternalObjectRepository constructor.
     *
     * @param MapperBuilderInterface $builder
     * @param string                 $resource_name
     *
     * @throws Exception
     */
    public function __construct(MapperBuilderInterface $builder, string $resource_name)
    {
        $this->builder       = $builder;
        $this->resource_name = $resource_name;
        $this->mapper        = $this->buildMapper();
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;

        if ($this->builder instanceof ConfigurableInterface) {
            $this->builder->setConfiguration($configuration);
        }

        if ($this->mapper instanceof ConfigurableInterface) {
            $this->mapper->setConfiguration($configuration);

            // Need to rebuild mapper on configuration change because of complex object creation
            $this->mapper = $this->buildMapper();
        }
    }

    /**
     * @return MapperInterface
     * @throws Exception
     */
    private function buildMapper(): MapperInterface
    {
        return $this->builder->build($this->resource_name);
    }

    /**
     * @inheritDoc
     */
    public function findById($id): ?EntityInterface
    {
        return $this->mapper->retrieve($id);
    }

    /**
     * Todo: Implement eager loading (mass load)
     * Todo: Implement sorting
     *
     * @inheritDoc
     */
    public function get(int $count = 1, int $offset = 0, $id = null): ?Generator
    {
        foreach ($this->mapper->get($count, $offset, $id) as $_object) {
            yield $_object;
        }
    }

    /**
     * @inheritDoc
     */
    public function findInPoolById($name, $id): ?EntityInterface
    {
        $mapper = ($this->mapper_pool[$name]) ?? $this->mapper_pool[$name] = $this->builder->build($name);

        return $mapper->retrieve($id);
    }

    public function findCollectionInPool(string $name): ?Generator
    {
        $mapper = ($this->mapper_pool[$name]) ?? $this->mapper_pool[$name] = $this->builder->build($name);

        foreach ($mapper->get(500) as $_mapper) {
            yield $_mapper;
        }
    }

    public static function getExternalClassFromType(string $type): ?string
    {
        switch ($type) {
            case GenericImport::UTILISATEUR:
                return User::EXTERNAL_CLASS;
            case GenericImport::MEDECIN:
            case ExternalReference::MEDECIN_USER:
                return Medecin::EXTERNAL_CLASS;
            case ExternalReference::PLAGE_CONSULTATION:
            case ExternalReference::PLAGE_CONSULTATION_AUTRE:
                return PlageConsult::EXTERNAL_CLASS;
            case GenericImport::PATIENT:
                return Patient::EXTERNAL_CLASS;
            case GenericImport::SEJOUR:
                return Sejour::EXTERNAL_CLASS;
            case GenericImport::CONSULTATION:
            case ExternalReference::CONSULTATION_AUTRE:
                return Consultation::EXTERNAL_CLASS;
            case GenericImport::ANTECEDENT:
                return Antecedent::EXTERNAL_CLASS;
            case ExternalReference::EVENEMENT:
                return EvenementPatient::EXTERNAL_CLASS;
            case ExternalReference::INJECTION:
                return Injection::EXTERNAL_CLASS;
            case ExternalReference::INTERVENTION:
                return 'INTER';
            case GenericImport::CONSTANTE:
                return Constante::EXTERNAL_CLASS;
            case GenericImport::DOSSIER_MEDICAL:
                return DossierMedical::EXTERNAL_CLASS;
            case GenericImport::AFFECTATION:
                return Affectation::EXTERNAL_CLASS;
            case GenericImport::OPERATION:
                return Operation::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_RESULT_SET:
                return ObservationResultSet::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_RESULT:
                return ObservationResult::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_IDENTIFIER:
                return ObservationIdentifier::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_VALUE_UNIT:
                return ObservationValueUnit::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_EXAM:
                return ObservationExam::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_RESPONSIBLE:
                return ObservationResponsible::EXTERNAL_CLASS;
            case GenericImport::OBSERVATION_PATIENT:
                return ObservationPatient::EXTERNAL_CLASS;
            default:
                return null;
        }
    }
}
