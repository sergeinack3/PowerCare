<?php

/**
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Error;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CMergeLog;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use Throwable;


class CMergeLogTest extends OxUnitTestCase
{

    private function createPatient()
    {
        $patient = CPatient::getSampleObject();
        $this->storeOrFailed($patient);

        return $patient;
    }

    /**
     * @return array
     * @throws TestsException
     */
    private function getObjects(): array
    {
        $base    = $this->createPatient();
        $objects = [
            $this->createPatient(),
            $this->createPatient(),
        ];

        foreach ($objects as $_k => $_object) {
            if ($_object->_id === $base->_id) {
                // Probability of having twice the same object should be lower...
                $objects[$_k] = $this->createPatient();
            }
        }

        return [
            $base,
            $objects,
        ];
    }

    public function parametersProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function throwableProvider(): array
    {
        // Do not put <html> like message because it will be purified and it is not that we want to test here.
        return [
            [new Exception('test')],
            [new Exception('')],
            [new Exception()],
            [new Error('test')],
            [new Error('')],
            [new Error()],
        ];
    }

    //    /**
    //     * Todo: Not robust enough.
    //     *
    //     * @return array[]
    //     */
    //    public function durationProvider(): array
    //    {
    //        $now = CMbDT::dateTime();
    //
    //        $step_1 = 5;
    //        $step_2 = 8;
    //        $step_3 = 12;
    //
    //        $merge_log_1                   = new CMergeLog();
    //        $merge_log_1->date_start_merge = $now;
    //
    //        $merge_log_2                    = clone $merge_log_1;
    //        $merge_log_2->date_before_merge = CMbDT::dateTime("+{$step_1} seconds", $now);
    //
    //        $merge_log_3                   = clone $merge_log_2;
    //        $merge_log_3->date_after_merge = CMbDT::dateTime("+{$step_2} seconds", $now);
    //
    //        $merge_log_4                 = clone $merge_log_3;
    //        $merge_log_4->date_end_merge = CMbDT::dateTime("+{$step_3} seconds", $now);
    //
    //        return [
    //            [$merge_log_1, 0],
    //            [$merge_log_2, $step_1],
    //            [$merge_log_3, $step_2],
    //            [$merge_log_4, $step_3],
    //        ];
    //    }

    /**
     * @dataProvider parametersProvider
     */
    public function testMergeLogStart(bool $fast): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, $fast);

        $this->assertStartMerge($merge_log, $user_id, $base, $objects, $fast);
    }

    public function testMergeLogCheck(): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, true);
        $merge_log->logCheck();

        $this->assertCheckMerge($merge_log);
    }

    public function testCheckWithoutStartDoesNotDoAnything(): void
    {
        $merge_log = new CMergeLog();

        $merge_log->logCheck();
        $this->assertNull($merge_log->merge_checked);
        $this->assertNull($merge_log->_id);
    }

    public function testMergeLogBefore(): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, true);
        $merge_log->logBefore();

        $this->assertBeforeMerge($merge_log);
    }

    public function testBeforeWithoutStartDoesNotDoAnything(): void
    {
        $merge_log = new CMergeLog();

        $merge_log->logBefore();
        $this->assertNull($merge_log->date_before_merge);
        $this->assertNull($merge_log->_id);
    }

    public function testMergeLogAfter(): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, true);
        $merge_log->logAfter();

        $this->assertAfterMerge($merge_log);
    }

    public function testAfterWithoutStartDoesNotDoAnything(): void
    {
        $merge_log = new CMergeLog();

        $merge_log->logAfter();
        $this->assertNull($merge_log->date_after_merge);
        $this->assertNull($merge_log->_id);
    }

    public function testMergeLogEnd(): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, true);
        $merge_log->logEnd();

        $this->assertEndMerge($merge_log);
    }

    public function testEndWithoutStartDoesNotDoAnything(): void
    {
        $merge_log = new CMergeLog();

        $merge_log->logEnd();
        $this->assertNull($merge_log->date_end_merge);
        $this->assertEquals(0, $merge_log->duration);
        $this->assertNull($merge_log->_id);
    }

    /**
     * @dataProvider parametersProvider
     * @throws TestsException
     */
    public function testFullProcess(bool $fast): void
    {
        [$base, $objects] = $this->getObjects();
        $user_id = CUser::get()->_id;

        $merge_log = CMergeLog::logStart($user_id, $base, $objects, $fast);
        $merge_log->logCheck();
        $merge_log->logBefore();
        $merge_log->logAfter();
        $merge_log->logEnd();

        $this->assertIntrinsicStart($merge_log, $user_id, $base, $objects, $fast);
        $this->assertCheckMerge($merge_log);
        $this->assertBeforeMerge($merge_log);
        $this->assertAfterMerge($merge_log);
        $this->assertEndMerge($merge_log);
    }

    /**
     * @dataProvider throwableProvider
     *
     * @param Throwable $t
     *
     * @throws TestsException
     */
    public function testFromThrowable(Throwable $t): void
    {
        [$base, $objects] = $this->getObjects();

        $merge_log = CMergeLog::logStart(1, $base, $objects, true);
        $merge_log->logFromThrowable($t);

        $this->assertEquals($t->getMessage(), $merge_log->last_error_handled);
        $this->assertNotNull($merge_log->duration);
    }

    //    /**
    //     * @dataProvider durationProvider
    //     *
    //     * @param CMergeLog $merge_log
    //     * @param int       $duration
    //     *
    //     * @throws TestsException
    //     * @throws ReflectionException
    //     */
    //    public function testDurationComputing(CMergeLog $merge_log, int $duration): void
    //    {
    //        $this->assertEquals($duration, $this->invokePrivateMethod($merge_log, 'computeDuration'));
    //    }

    /**
     * @param CMergeLog     $merge_log
     * @param int           $user_id
     * @param CStoredObject $base
     * @param array         $objects
     * @param bool          $fast
     */
    private function assertStartMerge(
        CMergeLog $merge_log,
        int $user_id,
        CStoredObject $base,
        array $objects,
        bool $fast
    ): void {
        $this->assertIntrinsicStart($merge_log, $user_id, $base, $objects, $fast);

        $this->assertEquals('0', $merge_log->merge_checked);
        $this->assertNull($merge_log->date_before_merge);
        $this->assertNull($merge_log->date_after_merge);
        $this->assertNull($merge_log->date_end_merge);
        $this->assertEquals(0, $merge_log->duration);
        $this->assertEquals(0, $merge_log->count_merged_relations);
        $this->assertEquals('{}', $merge_log->detail_merged_relations);
        $this->assertNull($merge_log->last_error_handled);
    }

    private function assertIntrinsicStart(
        CMergeLog $merge_log,
        int $user_id,
        CStoredObject $base,
        array $objects,
        bool $fast
    ): void {
        $this->assertInstanceOf(CMergeLog::class, $merge_log);
        $this->assertNotNull($merge_log->_id);
        $this->assertEquals($user_id, $merge_log->user_id);
        $this->assertEquals(($fast) ? '1' : '0', $merge_log->fast_merge);
        $this->assertEquals($base->_class, $merge_log->object_class);
        $this->assertEquals($base->_id, $merge_log->base_object_id);
        $this->assertEquals(implode('-', array_column($objects, '_id')), $merge_log->object_ids);
        $this->assertNotNull($merge_log->date_start_merge);
    }

    private function assertCheckMerge(CMergeLog $merge_log): void
    {
        $this->assertEquals('1', $merge_log->merge_checked);
    }

    private function assertBeforeMerge(CMergeLog $merge_log): void
    {
        $this->assertNotNull($merge_log->date_before_merge);
    }

    private function assertAfterMerge(CMergeLog $merge_log): void
    {
        $this->assertNotNull($merge_log->date_after_merge);
    }

    private function assertEndMerge(CMergeLog $merge_log): void
    {
        $this->assertNotNull($merge_log->date_end_merge);
        $this->assertNotNull($merge_log->duration);
    }
}
