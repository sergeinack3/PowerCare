<?php

namespace Ox\Mediboard\Mediusers\Tests\Unit\Repository;

use Exception;
use Ox\Core\CMbArray;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\Repository\FunctionsRepository;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class FunctionsRepositoryTest extends OxUnitTestCase
{
    /**
     * Test to find all specialties
     *
     * @throws TestsException
     * @throws Exception
     */
    public function testFindAllSpecialties()
    {
        $function = CFunctions::getSampleObject();
        $function->group_id = CGroups::loadCurrent()->_id;
        $function->type = 'cabinet';
        $this->storeOrFailed($function);

        $function_repository = new FunctionsRepository();
        $functions = $function_repository->findAllSpecialties(null, 1);

        $function_ids = CMbArray::pluck($functions, "_id");

        $this->assertContains($function->_id, $function_ids);
    }
}
