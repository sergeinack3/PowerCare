<?php

namespace Ox\Mediboard\Hospi\Tests\Unit\Repository;

use Exception;
use Ox\Core\CMbArray;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\Repository\ServiceRepository;
use Ox\Mediboard\Hospi\Tests\Fixtures\HospitalisationFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class ServiceRepositoryTest extends OxUnitTestCase
{
    /**
     * Test to find all services not cancelled with perms
     *
     * @throws TestsException
     * @throws Exception
     */
    public function testFindAllNotCancelledWithPerms()
    {
        $service = $this->getObjectFromFixturesReference(
            CService::class,
            HospitalisationFixtures::SERVICE_HOSPITALISATION
        );

        $service_repository = new ServiceRepository();
        $services = $service_repository->findAllNotCancelledWithPerms();

        $service_ids = CMbArray::pluck($services, "_id");

        $this->assertContains($service->_id, $service_ids);
    }
}
