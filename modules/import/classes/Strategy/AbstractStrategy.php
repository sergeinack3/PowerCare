<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Strategy;

use Ox\Core\CStoredObject;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\CImportEntity;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\ExternalReference;
use Ox\Import\Framework\Entity\ExternalReferenceStash;
use Ox\Import\Framework\Entity\ValidationAwareInterface;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Import\Framework\Repository\RepositoryInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * Description
 */
abstract class AbstractStrategy implements StrategyInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ValidatorVisitorInterface */
    private $validator;

    /** @var TransformerVisitorInterface */
    private $transformer;

    /** @var MatcherVisitorInterface */
    private $matcher;

    /** @var PersisterVisitorInterface */
    private $persister;

    /** @var CImportCampaign */
    private $campaign;

    /** @var ExternalReferenceStash */
    private $reference_stash;

    /** @var mixed */
    protected $last_external_id;

    /** @var array */
    protected $messages = [];

    /** @var array */
    protected $errors = [];

    /**
     * AbstractImportStrategy constructor.
     *
     * @param RepositoryInterface         $repository
     * @param ValidatorVisitorInterface   $validator
     * @param TransformerVisitorInterface $transformer
     * @param MatcherVisitorInterface     $matcher
     * @param PersisterVisitorInterface   $persister
     * @param CImportCampaign             $campaign
     */
    public function __construct(
        RepositoryInterface $repository,
        ValidatorVisitorInterface $validator,
        TransformerVisitorInterface $transformer,
        MatcherVisitorInterface $matcher,
        PersisterVisitorInterface $persister,
        CImportCampaign $campaign
    ) {
        $this->repository  = $repository;
        $this->validator   = $validator;
        $this->transformer = $transformer;
        $this->matcher     = $matcher;
        $this->persister   = $persister;

        $this->campaign = $campaign;

        $this->reference_stash = new ExternalReferenceStash();
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;

        if ($this->repository instanceof ConfigurableInterface) {
            $this->repository->setConfiguration($configuration);
        }

        if ($this->validator instanceof ConfigurableInterface) {
            $this->validator->setConfiguration($configuration);
        }

        if ($this->transformer instanceof ConfigurableInterface) {
            $this->transformer->setConfiguration($configuration);
        }

        if ($this->matcher instanceof ConfigurableInterface) {
            $this->matcher->setConfiguration($configuration);
        }

        if ($this->persister instanceof ConfigurableInterface) {
            $this->persister->setConfiguration($configuration);
        }

        $this->campaign->setConfiguration($configuration);
    }

    /**
     * @inheritDoc
     */
    public function import(int $count = 1, int $offset = 0, $id = null): int
    {
        /** @var EntityInterface $_object */
        $i = 0;
        foreach ($this->repository->get($count, $offset, $id) as $_object) {
            $i++;
            $this->importOne($_object, false);
        }

        return $i;
    }

    /**
     * Tell if an entity have been already imported
     * Todo: Do not forget :)
     *
     * @param CImportEntity|null $entity
     *
     * @return bool
     */
    protected function isAlreadyImported(?CImportEntity $entity = null): bool
    {
        return $entity && $entity->_id && $entity->getInternalObject();
    }

    /**
     * @param ExternalReference $reference
     * @param CStoredObject     $mb_object
     *
     * @return void
     */
    protected function addExternalReferenceToStash(ExternalReference $reference, CStoredObject $mb_object): void
    {
        $this->reference_stash->addReference($reference, $mb_object);
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @return ValidatorVisitorInterface
     */
    public function getValidator(): ValidatorVisitorInterface
    {
        return $this->validator;
    }

    /**
     * @return TransformerVisitorInterface
     */
    public function getTransformer(): TransformerVisitorInterface
    {
        return $this->transformer;
    }

    /**
     * @return MatcherVisitorInterface
     */
    public function getMatcher(): MatcherVisitorInterface
    {
        return $this->matcher;
    }

    /**
     * @return PersisterVisitorInterface
     */
    public function getPersister(): PersisterVisitorInterface
    {
        return $this->persister;
    }

    /**
     * // Todo: Add to Interface ?
     *
     * @return ExternalReferenceStash
     */
    public function getReferenceStash(): ExternalReferenceStash
    {
        return $this->reference_stash;
    }

    /**
     * @inheritDoc
     */
    public function setLastExternalId($id): void
    {
        $this->last_external_id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getLastExternalId()
    {
        return $this->last_external_id;
    }

    /**
     * @return CImportCampaign
     */
    public function getCampaign(): CImportCampaign
    {
        return $this->campaign;
    }

    /**
     * @param EntityInterface $object
     *
     * @return void
     * @throws ImportException
     */
    protected function checkViolations(EntityInterface $object): void
    {
        if ($object instanceof ValidationAwareInterface) {
            // Validate the external object's fields
            $violation = $object->validate($this->getValidator());

            if ($violation instanceof SpecificationViolation) {
                throw new ImportException(
                    sprintf('[%s] => %s not imported: %s', static::class, get_class($object), $violation->__toString())
                );
            }
        }
    }

    /**
     * Get the import entity related to external object
     *
     * @param EntityInterface $entity
     *
     * @return CImportEntity|null
     */
    protected function getImportEntity(EntityInterface $entity): ?CImportEntity
    {
        $import_entity = null;
        if ($entity->getExternalId()) {
            $import_entity = $this->campaign->getImportedEntity($entity->getExternalClass(), $entity->getExternalId());
        }

        return $import_entity;
    }

    /**
     * @param ExternalReference $reference
     *
     * @return EntityInterface|null
     */
    protected function findInPoolByReference(ExternalReference $reference): ?EntityInterface
    {
        return $this->repository->findInPoolById($reference->getName(), $reference->getId());
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Import one entity
     *
     * @param EntityInterface $object
     * @param bool            $reference
     *
     * @return CStoredObject|null
     */
    abstract protected function importOne(EntityInterface $object, bool $reference = false): ?CStoredObject;
}
