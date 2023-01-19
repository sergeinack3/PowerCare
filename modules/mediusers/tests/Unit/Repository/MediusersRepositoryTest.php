<?php

namespace Ox\Mediboard\Mediusers\Tests\Unit\Repository;

use Exception;
use Ox\Core\CMbArray;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\Repository\MediusersRepository;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class MediusersRepositoryTest extends OxUnitTestCase
{
    /**
     * Test to find all practicioner
     *
     * @throws TestsException
     * @throws Exception
     */
    public function testFindAllPracticioner()
    {
        $mediuser_med = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_MEDECIN);
        $mediuser_anesth = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_ANESTH);

        $mediuser_repository = new MediusersRepository();
        $mediusers = $mediuser_repository->findAllPracticioner(false);

        $mediuser_ids = CMbArray::pluck($mediusers, "_id");

        $this->assertContains($mediuser_med->_id, $mediuser_ids);
        $this->assertContains($mediuser_anesth->_id, $mediuser_ids);
    }
}
