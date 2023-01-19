<?php

namespace Ox\Mediboard\Hospi\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Hospi\Repository\SejourRepository;
use Ox\Mediboard\Hospi\Tests\Fixtures\HospitalisationFixtures;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class PlacementsTest extends OxUnitTestCase
{
    /**
     * Test to search a list of stay from user action (create or update type)
     *
     * @throws TestsException
     */
    public function testSearchUserActionFromStay()
    {
        $sejour = $this->getObjectFromFixturesReference(
            CSejour::class,
            HospitalisationFixtures::SEJOUR_HOSPITALISATION
        );

        $ds       = CSQLDataSource::get("std");

        $ljoin       = [
            "user_action_data" => "user_action.user_action_id = user_action_data.user_action_id",
            "object_class"     => "user_action.object_class_id = object_class.object_class_id",
        ];
        $where       = [
            "object_class.object_class" => $ds->prepare("= 'CSejour'"),
            "user_action.type"          => $ds->prepare("= 'create'"),
            "user_action.date"          => $ds->prepareBetween($sejour->entree, $sejour->sortie),
        ];

        $sejour_repository = new SejourRepository();
        $sejour_ids = $sejour_repository->findIdsByUserAction($ljoin, $where);

        $this->assertContains($sejour->_id, $sejour_ids);
    }
}
