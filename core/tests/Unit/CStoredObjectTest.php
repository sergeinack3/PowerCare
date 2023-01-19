<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Request\RequestLimit;
use Ox\Core\Api\Request\RequestSort;
use Ox\Core\CAppUI;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CStoredObject;
use Ox\Core\Exceptions\CanNotMerge;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Generators\CUserGenerator;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\CUserLog;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class CStoredObjectTest extends OxUnitTestCase
{
    /**
     * @var CStoredObject $object
     */
    private static $object;

    /**
     * Set Up
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $object          = new CSearchHistory();
        $object->date    = 'now';
        $object->user_id = CAppUI::$user->_id;
        $object->entry   = uniqid('entry', true);
        $object->hits    = rand(1, 999);
        $object->store();
        static::$object = $object;
        sleep(1);
    }

    /**
     * @return CStoredObject|CSearchHistory
     * @throws Exception
     */
    public function testStoreObject()
    {
        $this->assertNotNull(static::$object->_id);
        $this->assertInstanceOf(CUserLog::class, static::$object->_ref_current_log);
        // update = store
        static::$object->date  = 'now';
        static::$object->hits  = rand(1, 999);
        static::$object->entry = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit';
        $msg                   = static::$object->store();
        $this->assertNull($msg);
        $user_log = static::$object->_ref_current_log;
        $this->assertInstanceOf(CUserLog::class, $user_log);

        $extra = (array)json_decode($user_log->extra);

        $this->assertTrue(count($extra) >= 2);

        return static::$object;
    }


    public function testLoadLogs(): void
    {
        static::$object->loadLogs();

        $this->assertIsArray(static::$object->_ref_logs);

        $this->assertInstanceOf(CUserLog::class, static::$object->_ref_first_log);
        $this->assertInstanceOf(CUserLog::class, static::$object->_ref_last_log);
    }


    public function testLoadHistory(): void
    {
        static::$object->loadHistory();
        $this->assertIsArray(static::$object->_history);
        $first = reset(static::$object->_history);
        $this->assertEquals($first['date'], static::$object->date);
    }

    public function testLoadLogForField(): void
    {
        static::$object->hits = rand(1, 999);
        static::$object->store();

        static::$object->loadHistory();

        $logs = static::$object->loadLogsForField('hits');
        $this->assertIsArray($logs);
        $this->assertInstanceOf(CUserLog::class, reset($logs));

        // first  =  older log
        $first = static::$object->loadFirstLogForField('hits');
        $this->assertInstanceOf(CUserLog::class, $first);

        $last = static::$object->loadLastLogForField('hits');
        $this->assertInstanceOf(CUserLog::class, $last);

        $this->assertGreaterThanOrEqual($first->date, $last->date);
    }

    public function testHasRecentLog(): void
    {
        $true = (bool)static::$object->hasRecentLog(1);
        $this->assertTrue($true);

        $obj   = new CSearchHistory();
        $false = (bool)$obj->hasRecentLog(1);
        $this->assertFalse($false);
    }

    public function testLoadLog(): void
    {
        $first = static::$object->loadFirstLog();
        $last  = static::$object->loadLastLog();
        $this->assertGreaterThanOrEqual($first->date, $last->date);
    }

    public function testLoadCreationLog(): void
    {
        $log = static::$object->loadCreationLog();
        $this->assertInstanceOf(CUserLog::class, $log);
        $this->assertEquals('create', $log->type);
    }

    /**
     * @param string $guid
     * @param string $expected
     *
     * @throws Exception
     */
    public function testLoadFromGuidOk(): void
    {
        $patient = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        $this->assertEquals($patient, CStoredObject::loadFromGuid("CPatient-{$patient->_id}"));

        $sejour = new CSejour();
        $this->assertEquals($sejour, CStoredObject::loadFromGuid('CSejour-none'));
    }

    /**
     * @param string $guid
     *
     * @throws Exception
     * @dataProvider loadFromGuidKoProvider
     */
    public function testLoadFromGuidKo($guid): void
    {
        $this->assertNull(CStoredObject::loadFromGuid($guid));
    }

    /**
     * @param array  $fields
     * @param array  $values
     * @param string $operator
     * @param string $condition
     * @param string $mode
     * @param string $expected_result
     *
     * @dataProvider prepareMatchOkProvider
     */
    public function testPrepareMatchOk(
        $fields,
        $values,
        $operator,
        string $condition,
        string $mode,
        string $expected_result
    ): void {
        $result = CStoredObject::prepareMatch($fields, $values, $operator, $condition, $mode);
        $this->assertEquals($expected_result, $result);
    }


    public function prepareMatchOkProvider(): array
    {
        return [
            'SearchBooleanEnd'        => [
                'adresse',
                'ute de tranche',
                'end',
                'and',
                'boolean',
                "MATCH (adresse) AGAINST('+*ute de tranche' IN BOOLEAN MODE)",
            ],
            'SearchBooleanEqual'      => [
                'adresse',
                '15 route de tranche',
                'equals',
                'and',
                'boolean',
                "MATCH (adresse) AGAINST('+15 route de tranche' IN BOOLEAN MODE)",
            ],
            'SearchBooleanBegin'      => [
                'adresse',
                '15 route de t',
                'begin',
                'and',
                'boolean',
                "MATCH (adresse) AGAINST('+15 route de t*' IN BOOLEAN MODE)",
            ],
            'SearchMultiBooleanBegin' => [
                ['adresse', 'cp', 'ville'],
                ['foobar', 'is', 'Here'],
                'begin',
                'and',
                'boolean',
                "MATCH (adresse,cp,ville) AGAINST('+foobar* +is* +Here*' IN BOOLEAN MODE)",
            ],
            'SearchMultiNatural'      => [
                ['adresse', 'cp', 'ville'],
                ['foobar', 'is', 'Here'],
                'begin',
                'and',
                'natural',
                "MATCH (adresse,cp,ville) AGAINST('foobar is Here' IN NATURAL LANGUAGE MODE)",
            ],
            'ConditionOr'             => [
                ['adresse', 'cp', 'ville'],
                ['foobar', 'is', 'Here'],
                'begin',
                'or',
                'boolean',
                "MATCH (adresse,cp,ville) AGAINST('foobar* is* Here*' IN BOOLEAN MODE)",
            ],
        ];
    }

    /**
     * @param array  $fields
     * @param array  $values
     * @param string $operator
     * @param string $condition
     * @param string $mode
     * @param string $expected_message
     *
     * @dataProvider prepareMatchExceptionsProvider
     */
    public function testPrepareMatchExceptions(
        ?array $fields,
        ?array $values,
        ?string $operator,
        string $condition,
        string $mode,
        string $expected_message
    ): void {
        $this->expectExceptionMessage($expected_message);
        CStoredObject::prepareMatch($fields, $values, $operator, $condition, $mode);
    }

    public function prepareMatchExceptionsProvider(): array
    {
        return [
            'ModeIsNotValid'      => [
                [],
                [],
                'equal',
                'and',
                'foo',
                'foo is not a valid query language mode. Allowed values are : '
                . implode(', ', array_keys(CStoredObject::$fulltext_query_language_modes)),
            ],
            'ConditionIsNotValid' => [
                [],
                [],
                'equal',
                'bar',
                'boolean',
                'bar is not a valid query condition. Allowed values are : '
                . implode(', ', CStoredObject::$fulltext_query_operators),
            ],
            'No fields'           => [
                null,
                null,
                null,
                'and',
                'boolean',
                'Fields cannot be null',
            ],
            'No values'           => [
                ['test'],
                null,
                null,
                'and',
                'boolean',
                'Values cannot be null',
            ],
        ];
    }

    public function loadFromGuidKoProvider(): array
    {
        return [
            'object-'                  => ['CSejour-'],
            '-number'                  => ['-150'],
            'object-char'              => ['CConsultation-test'],
            'object not instanciable'  => ['CMbString-10'],
            'object not CStoredObject' => ['CCSVImportPatients-5'],
        ];
    }


    public function testLoadList()
    {
        $user  = new CUser();
        $users = $user->loadList("user_sexe = 'm'", "user_username desc", 5);
        $this->assertCount(5, $users);

        return $users;
    }

    /**
     * @depends testLoadList
     */
    public function testLoadListFormRequestApi(array $users_actual): void
    {
        $req = new Request();
        $req->query->set(RequestLimit::QUERY_KEYWORD_LIMIT, 5);
        $req->query->set(RequestFilter::QUERY_KEYWORD_FILTER, 'user_sexe.equal.m');
        $req->query->set(RequestSort::QUERY_KEYWORD_SORT, '-user_username');

        $request_api    = RequestApi::createFromRequest($req);
        $user           = new CUser();
        $users_expected = $user->loadListFromRequestApi($request_api);
        $this->assertCount(5, $users_expected);
        $this->assertEquals($users_expected, $users_actual);
    }

    public function testCountList()
    {
        $user  = new CUser();
        $count = $user->countList("user_sexe = 'm'");
        $this->assertTrue($count > 0);

        return (int)$count;
    }

    /**
     * @depends testCountList
     */
    public function testCountListFromRequestApi(int $count_actual): void
    {
        $req = new Request();
        $req->query->set(RequestFilter::QUERY_KEYWORD_FILTER, 'user_sexe.equal.m');
        $request_api = RequestApi::createFromRequest($req);
        $user        = new CUser();
        $this->assertEquals($user->countListFromRequestApi($request_api), $count_actual);
    }

    public function testMerge(): void
    {
        $base   = $this->forgeUser();
        $object = $this->forgeUser();

        $object_id        = $object->_id;
        $object_last_name = $object->user_last_name;

        $base->user_last_name = $object->user_last_name;

        $base->merge([$object], false, new CMergeLog());

        $this->assertEquals($base->user_last_name, $object_last_name);
        $this->assertNull($object->_id);

        $this->expectException(CMbModelNotFoundException::class);
        $this->assertFalse(CUser::findOrFail($object_id));
    }

    public function testMergeNotAdmin(): void
    {
        $base     = $this->forgeUser();
        $object_1 = $this->forgeUser();
        $object_2 = $this->forgeUser();

        $objects = [$object_1, $object_2];

        $user             = CMediusers::get();
        $user->_user_type = 'Dentiste';

        $this->expectException(CouldNotMerge::class);
        $this->expectExceptionMessage('mergeTooFewObjects');
        $base->merge($objects, false, new CMergeLog());

        $user->_user_type = 'Administrator';
    }

    /**
     * @depends testMerge
     */
    public function testMergeWithMergeLogIsCalled(): void
    {
        $base   = $this->forgeUser();
        $object = $this->forgeUser();

        $merge_log = $this->getMockBuilder(CMergeLog::class)
            ->getMock();

        // Since the object is totally mocked, CMergeLog::store will still do nothing.
        // We enforce the id to truly value to pass the check in CStoredObject::merge.
        $merge_log->_id = 'mock';

        $merge_log->expects($this->once())->method('logBefore');
        $merge_log->expects($this->once())->method('logAfter');

        $base->merge([$object], false, $merge_log);
    }

    /**
     * @return array[]
     */
    public function checkMergeWithExceptionProvider(): array
    {
        return [
            'object not CMbObject'   => [new CUser(), [new CStoredObject(), new CStoredObject()], 'mergeNotCMbObject'],
            'no object id'           => [new CUser(), [new CUser(), new CUser()], 'mergeNoId'],
            'object different class' => [new CUser(), [CMediusers::get(), CUser::get()], 'mergeDifferentType'],
        ];
    }

    private function forgeUser(): CUser
    {
        $user = (new CUserGenerator())->setForce(true)->generate();

        if (!$user || !$user->_id) {
            throw new TestsException('Unable to generate user');
        }

        return $user;
    }

    /**
     * @dataProvider checkMergeWithExceptionProvider
     */
    public function testCheckMerge(CStoredObject $base, array $objects, string $message): void
    {
        $this->expectException(CanNotMerge::class);
        $this->expectExceptionMessage($message);
        $base->checkMerge($objects);
    }

    public function testCountListGroupByNoModule(): void
    {
        $patient              = new CPatient();
        $patient->_ref_module = null;
        $this->assertEquals(0, $patient->countListGroupBy());
    }

    public function testCountListGroupByOk(): void
    {
        $patients = [
            $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT),
            $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT_BIS),
        ];
        $adresse  = uniqid();

        foreach ($patients as $_pat) {
            for ($i = 0; $i < 2; $i++) {
                $correspondant             = new CCorrespondantPatient();
                $correspondant->patient_id = $_pat->_id;
                $correspondant->nom        = uniqid();
                $correspondant->adresse    = $adresse;
                if ($msg = $correspondant->store()) {
                    $this->fail($msg);
                }
            }
        }

        $correspondant = new CCorrespondantPatient();
        $where         = ['adresse' => "= '$adresse'"];
        $this->assertEquals(2, $correspondant->countListGroupBy($where, null, 'patient_id'));
    }

    public function testCountListGroupByWithoutGroup(): void
    {
        $user = new CUser();
        $this->assertEquals(1, $user->countListGroupBy());
    }

    public function testCountListGroupByWithZeroResult(): void
    {
        $user     = new CUser();
        $username = uniqid();
        $where    = ['user_username' => "= '{$username}'"];
        $this->assertEquals(0, $user->countListGroupBy($where, null, 'user_last_name'));
    }

    public function testGetUuid(): void
    {
        $object = static::$object;
        $object->getUuid();

        $this->assertIsString($object->_uuid);
        $this->assertMatchesRegularExpression(
            "/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-4[0-9A-Fa-f]{3}-[89ABab][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/",
            $object->_uuid
        );
    }

    public function testLoadByUuid(): void
    {
        $object = static::$object;
        $object->getUuid();

        $object_uuid = $object::loadByUuid($object->_uuid);

        $this->assertInstanceOf(get_class($object), $object_uuid);
        $this->assertEquals($object->_id, $object_uuid->_id);
    }

    public function testCountMatchingListEsc(): void
    {
        $user                = new CUser();
        $user->user_username = UsersFixtures::REF_USER_LOREM_IPSUM;
        $this->assertEquals(1, $user->countMatchingListEsc());
    }

    public function testDuplicateObject(): void
    {
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $user->duplicateObject("user_username");

        $user_copied                = new CUser();
        $user_copied->user_username = UsersFixtures::REF_USER_LOREM_IPSUM . " (Copy)";
        $this->assertEquals(1, $user_copied->countMatchingListEsc());
    }
}
