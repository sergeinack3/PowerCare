<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\InsuranceTypeClient;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Exceptions\Insurance\InsuranceException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CMaternityInsurance;

class InsuranceTypeClientTest extends UnitTestJfse
{
    /** @var Client */
    private $client;

    /**
     * Set a generic client which returns empty data following the generic Jfse structure
     */
    public function setUp(): void
    {
        parent::setUp();

        $empty_data = '{"method": {"output": {}}}';
        $this->client = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $empty_data)]);
    }

    /**
     * Test if the response returns a Response object
     */
    public function testGetAllTypes(): void
    {
        $this->assertInstanceOf(Response::class, (new InsuranceTypeClient($this->client))->getAllTypes());
    }

    public function testSave(): void
    {
        $guzzle_response = $this->makeJsonGuzzleResponse(200, '{"method": {"output": {}}}');
        $client          = $this->makeClientFromGuzzleResponses([$guzzle_response]);

        $insurance = MaternityInsurance::hydrate(
            ['date' => new DateTimeImmutable('2020-10-19'), 'force_exoneration' => true]
        );

        $client = new InsuranceTypeClient($client);

        $expected = Response::forge('FDS-setNatureAssurance', ['method' => ['output' => []]]);

        $this->assertEquals($expected, $client->save($insurance, 1));
    }
}
