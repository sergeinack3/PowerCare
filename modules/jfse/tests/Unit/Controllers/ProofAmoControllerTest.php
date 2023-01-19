<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Controllers;

use DateTimeImmutable;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Controllers\ProofAmoController;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoService;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoType;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProofAmoControllerTest extends UnitTestJfse
{
    /**
     * List of proof types request returns an empty request object
     */
    public function testFillOutProofAmoRequestReturnsAnEmptyRequestObject(): void
    {
        $_POST = ["invoice_id" => 1];

        $types_request = (new ProofAmoController('add'))->addProofAmoRequest();

        $this->assertEquals($types_request, new Request([], ["invoice_id" => 1]));
    }

    /**
     * Save a proof request function returns a request
     */
    public function testSaveAProofRequestReturnsARequest(): void
    {
        $types = [
            ProofAmoType::hydrate(['code' => 0, 'libelle' => 'Proof 1']),
            ProofAmoType::hydrate(['code' => 1, 'libelle' => 'Proof 2']),
            ProofAmoType::hydrate(['code' => 2, 'libelle' => 'Proof 3']),
        ];

        $service = $this->getMockBuilder(ProofAmoService::class)
            ->disableOriginalConstructor()
            ->setMethods(['listProofTypes'])
            ->getMock();
        $service->method('listProofTypes')->willReturn($types);

        $controller = new ProofAmoController('store', $service);

        $_POST          = [
            "invoice_id" => 1,
            "nature"     => 1,
            "date"       => "2020-10-06",
            "origin"     => 1,
        ];
        $actual_request = $controller->storeProofRequest();

        $data             = [
            "invoice_id" => 1,
            "nature"     => 1,
            "date"       => new DateTimeImmutable("2020-10-06"),
            "origin"     => 1,
        ];
        $expected_request = new Request([], $data);

        $this->assertEquals($expected_request, $actual_request);
    }
}
