<?php

/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework;

use Ox\Core\Cache;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\Manager;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Mapper\MapperInterface;
use Ox\Import\Framework\Matcher\DefaultMatcher;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\AbstractPersister;
use Ox\Import\Framework\Persister\DefaultPersister;
use Ox\Import\Framework\Repository\GenericRepository;
use Ox\Import\Framework\Repository\RepositoryInterface;
use Ox\Import\Framework\Strategy\AbstractStrategy;
use Ox\Import\Framework\Strategy\BFSStrategy;
use Ox\Import\Framework\Transformer\AbstractTransformer;
use Ox\Import\Framework\Transformer\DefaultTransformer;
use Ox\Import\Framework\Validator\AbstractValidator;
use Ox\Import\Framework\Validator\DefaultValidator;

/**
 * Description
 */
abstract class CFwImport
{
    /** @var int */
    protected $import_count = 0;

    /** @var CImportCampaign */
    protected $campaign;

    public function listUsers(CImportCampaign $campaign, int $start, int $step): array
    {
        $builder = $this->getMapperBuilderInstance();

        if (method_exists($builder, 'setConfiguration')) {
            $config = $this->getConfiguration();
            $builder->setConfiguration($config);
        }

        $mapper = $builder->build($this->getUserTable());

        return $this->buildUserList($mapper, $start, $step, $campaign);
    }

    public function count(string $type, string $patient_id = null): int
    {
        $configs = $this->getConfiguration();

        if ($patient_id) {
            $configs->offsetSet('patient_id', $patient_id);
        }

        $builder = $this->getMapperBuilderInstance();

        if (method_exists($builder, 'setConfiguration')) {
            $builder->setConfiguration($configs);
        }

        $mapper = $builder->build($type);

        return $mapper->count();
    }

    public function getLastId(string $type): ?string
    {
        $last_id = null;

        // Todo: Take care of LSB here
        $cache = new Cache('CFwImport.getLastId', $type, Cache::INNER_OUTER);
        if ($cache->exists()) {
            $last_id = $cache->get();
        }

        return $last_id;
    }

    public function getImportCount(): int
    {
        return $this->import_count;
    }

    public function import(
        CImportCampaign $campaign,
        string $type,
        int $start = 0,
        int $step = 100,
        string $patient_id = null,
        bool $update = false
    ) {
        $config = $this->getConfiguration(['update' => $update]);

        if ($patient_id) {
            $config->offsetSet('patient_id', $patient_id);
        }

        $mapper_builder = $this->getMapperBuilderInstance();
        if (method_exists($mapper_builder, 'setConfiguration')) {
            $mapper_builder->setConfiguration($config);
        }

        $repository = $this->getRepositoryInstance($mapper_builder, $type);

        $validator   = $this->getValidatorInstance();
        $transformer = $this->getTransformerInstance();
        $matcher     = $this->getMatcherInstance();
        $persister   = $this->getPersisterInstance();
        $strategy    = $this->getStrategyInstance(
            $repository,
            $validator,
            $transformer,
            $matcher,
            $persister,
            $campaign
        );

        $import = new Manager($strategy, $config);

        try {
            $this->import_count = $import->import($step, $start);
        } catch (ImportException $e) {
            return $e->getMessage();
        }

        return $import;
    }

    abstract protected function getMapperBuilderInstance(): MapperBuilderInterface;

    abstract protected function getUserTable(): string;

    abstract public function getImportOrder(): array;

    protected function getConfiguration(array $additionnal_confs = []): Configuration
    {
        return new Configuration($additionnal_confs);
    }

    protected function getRepositoryInstance(MapperBuilderInterface $builder, string $type): RepositoryInterface
    {
        return new GenericRepository($builder, $type);
    }

    protected function getValidatorInstance(): AbstractValidator
    {
        return new DefaultValidator();
    }

    protected function getTransformerInstance(): AbstractTransformer
    {
        return new DefaultTransformer();
    }

    protected function getMatcherInstance(): MatcherVisitorInterface
    {
        return new DefaultMatcher();
    }

    protected function getPersisterInstance(): AbstractPersister
    {
        return new DefaultPersister();
    }

    protected function getStrategyInstance(
        RepositoryInterface $repository,
        AbstractValidator $validator,
        AbstractTransformer $transformer,
        MatcherVisitorInterface $matcher,
        AbstractPersister $persister,
        CImportCampaign $campaign
    ): AbstractStrategy {
        return new BFSStrategy($repository, $validator, $transformer, $matcher, $persister, $campaign);
    }

    protected function buildUserList(MapperInterface $mapper, int $start, int $step, CImportCampaign $campaign): array
    {
        $user_list = [];
        foreach ($mapper->get($step, $start) as $_user) {
            $entity = $campaign->getImportedEntity($_user->getExternalClass(), $_user->getExternalID());

            $user_list[$_user->getExternalID()] = [
                'username' => $_user->getUsername(),
                'mb_user'  => ($entity) ? $entity->getInternalObject() : null,
            ];
        }

        return $user_list;
    }

    public function setCampaign(CImportCampaign $campaign): void
    {
        $this->campaign = $campaign;
    }
}
