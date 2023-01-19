<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Tests\Unit;

use DateInterval;
use DateTime;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CViewAccessTokenTest
 */
class CViewAccessTokenTest extends OxUnitTestCase
{
    /**
     * @return array|int[]
     */
    public function validityDurationProvider(): array
    {
        return [
            '0-length hash'   => [0, 0],
            '1-length hash'   => [1, 0],
            '2-length hash'   => [2, 0],
            '3-length hash'   => [3, 0],
            '4-length hash'   => [4, 11],        // ~11 seconds
            '5-length hash'   => [5, 656],       // ~11 minutes
            '6-length hash'   => [6, 38068],     // ~10 hours
            '7-length hash'   => [7, 2207984],   // ~25 days
            '8-length hash'   => [8, 128063081], // ~04 years
            '> 8-length hash' => [9, null],      // > 10 years
        ];
    }

    /**
     * @return CUser
     * @throws TestsException
     */
    private function getRandomUser(): CUser
    {
        static $user = null;

        if ($user instanceof CUser) {
            return $user;
        }

        return $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function test_token_has_default_hash_length(): void
    {
        $user = $this->getRandomUser();

        $token          = new CViewAccessToken();
        $token->user_id = $user->_id;
        $token->params  = 'dummy';

        $token->_hash_length = null;
        $token->hash         = null;
        $token->datetime_end = null;

        $this->assertNull($token->store());
        $this->assertEquals($token::DEFAULT_HASH_LENGTH, $token->_hash_length);
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function test_token_hash_is_charset_compliant(): void
    {
        $user = $this->getRandomUser();

        $token          = new CViewAccessToken();
        $token->user_id = $user->_id;
        $token->params  = 'dummy';

        // Invalid token hash
        $token->_hash_length = null;
        $token->datetime_end = null;
        $invalid_hash        = 'OlI0יאט';
        $token->hash         = $invalid_hash;

        $this->assertEquals(
            sprintf('CViewAccessToken-error-Invalid hash, characters not allowed: %s', $invalid_hash),
            $token->store()
        );

        // Default token hash
        $token->_hash_length = null;
        $token->datetime_end = null;
        $token->hash         = null;

        $this->assertNull($token->store());
        $this->assertNotNull($token->hash);

        $token          = new CViewAccessToken();
        $token->user_id = $user->_id;
        $token->params  = 'dummy';

        // Valid token hash, must be restricted
        $token->_hash_length = null;
        $token->datetime_end = null;
        $token->restricted   = '1';
        $valid_hash          = CMbSecurity::getRandomBase58String($token::DEFAULT_HASH_LENGTH);
        $token->hash         = $valid_hash;

        $this->assertNull($token->store());
        $this->assertEquals($valid_hash, $token->hash);
    }

    /**
     * @dataProvider validityDurationProvider
     *
     * @param int      $hash_length
     * @param int|null $validity_duration
     *
     * @return void
     * @throws TestsException
     */
    public function test_token_computes_hash_length_according_to_validity_duration(
        int $hash_length,
        ?int $validity_duration = null
    ): void {
        $user = $this->getRandomUser();

        $token                 = new CViewAccessToken();
        $token->user_id        = $user->_id;
        $token->params         = 'dummy';
        $token->datetime_start = CMbDT::dateTime();

        $token->_hash_length = null;
        $token->hash         = null;

        if ($validity_duration !== null) {
            // Use DateTime because of UTC (Daylight saving time)
            $datetime            = new DateTime($token->datetime_start);
            $token->datetime_end = $datetime->add(new DateInterval("PT{$validity_duration}S"))->format('Y-m-d H:i:s');
            //$token->datetime_end = CMbDT::dateTime("+{$validity_duration} seconds", $token->datetime_start);
        }

        if ($hash_length < $token::MINIMUM_HASH_LENGTH) {
            $this->assertNotNull($token->store());
        } else {
            $this->assertNull($token->store());

            if ($validity_duration !== null) {
                $this->assertEquals($hash_length, $token->_hash_length);
            } else {
                $this->assertEquals($token::DEFAULT_HASH_LENGTH, $token->_hash_length);
            }
        }
    }

    /**
     * @dataProvider validityDurationProvider
     *
     * @param int      $hash_length
     * @param int|null $validity_duration
     *
     * @return void
     * @throws TestsException
     */
    public function test_token_computes_validity_duration_according_to_hash_length(
        int $hash_length,
        ?int $validity_duration = null
    ): void {
        $user = $this->getRandomUser();

        $token                 = new CViewAccessToken();
        $token->user_id        = $user->_id;
        $token->params         = 'dummy';
        $token->datetime_start = CMbDT::dateTime();

        $token->_hash_length = $hash_length;
        $token->hash         = null;
        $token->datetime_end = null;

        if ($hash_length < $token::MINIMUM_HASH_LENGTH) {
            if ($hash_length === 0) {
                $this->assertNull($token->store());
                $this->assertEquals($token::DEFAULT_HASH_LENGTH, $token->_hash_length);
            } else {
                $this->assertNotNull($token->store());
            }
        } else {
            $this->assertNull($token->store());

            if ($validity_duration !== null) {
                $this->assertEquals(
                    CMbDT::dateTime("+{$validity_duration} seconds", $token->datetime_start),
                    $token->datetime_end
                );
            } else {
                $this->assertNull($token->datetime_end);
            }
        }
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function test_new_token_with_user_defined_hash_is_restricted(): void
    {
        $user = $this->getRandomUser();

        $token          = new CViewAccessToken();
        $token->user_id = $user->_id;
        $token->params  = 'dummy';

        // Valid token hash, must be restricted
        $token->_hash_length = null;
        $token->datetime_end = null;
        $token->restricted   = '0';
        $token->hash         = CMbSecurity::getRandomBase58String($token::DEFAULT_HASH_LENGTH);

        $this->assertNotNull($token->store());

        $token->restricted = '1';
        $this->assertNull($token->store());
    }

    /**
     * @dataProvider checkAllowedRoutesProvider
     */
    public function testCheckAllowedRoutes(Request $request, CViewAccessToken $token, bool $expected): void
    {
        $this->assertTrue($this->invokePrivateMethod($token, 'checkAllowedRoutes', $request) === $expected);
    }


    public function checkAllowedRoutesProvider(): array
    {
        $token               = new CViewAccessToken();
        $token->routes_names = "system_api_status\nadmin_identicate \ntoto";
        $token->updateFormFields();

        return [
            'token_has_no_routes_names'             => [$this->createRequestApi('toto'), new CViewAccessToken(), true],
            'request_is_not_api'                    => [new Request(), $token, true],
            'route_is_in_allowed_routes'            => [$this->createRequestApi('system_api_status'), $token, true],
            'route_is_in_allowed_routes_with_space' => [$this->createRequestApi('admin_identicate'), $token, true],
            'route_is_in_allowed_routes_last_one'   => [$this->createRequestApi('toto'), $token, true],
            'route_is_not_in_allowed_routes'        => [$this->createRequestApi(uniqid()), $token, false],
        ];
    }


}
