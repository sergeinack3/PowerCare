<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\ViewSender;

use Ox\Core\CMbDT;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Ox\Mediboard\System\ViewSender\ViewSenderManager;
use Ox\Tests\OxUnitTestCase;

class ViewSenderManagerTest extends OxUnitTestCase
{
    /**
     * @dataProvider prepareSendersProvider
     */
    public function testPrepareSenders(ViewSenderManager $manager, int $expected_count): void
    {
        $this->assertEquals($expected_count, $this->invokePrivateMethod($manager, 'prepareSenders'));

        $this->invokePrivateMethod($manager, 'closeCurl');
    }

    public function testPrepareAndSend(): void
    {
        $manager = $this->getViewSenderManagerMock(
            ['loadActiveSenders', 'executeCurl', 'removeCurlHandle'],
            1,
            CMbDT::date() . ' 20:00:00'
        );

        $view_sender1         = new CViewSender();
        $view_sender1->period = 60;
        $view_sender1->offset = 0;
        $view_sender1->every  = 1;

        /** @var CViewSender $view_sender2 */
        $view_sender2         = CViewSender::getSampleObject();
        $view_sender2->period = 60;
        $view_sender2->offset = 0;
        $view_sender2->every  = 1;
        $this->storeOrFailed($view_sender2);

        $manager->method('loadActiveSenders')->willReturn([$view_sender1, $view_sender2]);

        $senders = $manager->prepareAndSend();
        $this->assertCount(1, $senders);

        $sender = array_pop($senders);
        $this->assertEquals($view_sender2->_id, $sender->_id);
    }

    public function prepareSendersProvider(): array
    {
        $manager_empty = $this->getViewSenderManagerMock(['loadActiveSenders'], 0, CMbDT::dateTime());
        $manager_empty->method('loadActiveSenders')->willReturn([]);

        $test_datetime                = CMbDT::date() . ' 20:00:00';
        $manager_no_export_with_views = $this->getViewSenderManagerMock(['loadActiveSenders'], 0, $test_datetime);

        $view_sender1         = new CViewSender();
        $view_sender1->period = 60;
        $view_sender1->offset = 0;
        $view_sender1->every  = 1;

        $view_sender2         = new CViewSender();
        $view_sender2->period = 1;
        $view_sender2->offset = 0;
        $view_sender2->every  = 1;

        $view_sender3         = new CViewSender();
        $view_sender3->period = 60;
        $view_sender3->offset = 5;
        $view_sender3->every  = 1;

        $manager_no_export_with_views->method('loadActiveSenders')->willReturn(
            [$view_sender1, $view_sender2, $view_sender3]
        );

        $manager_export_with_views = $this->getViewSenderManagerMock(['loadActiveSenders'], 1, $test_datetime);
        $manager_export_with_views->method('loadActiveSenders')->willReturn(
            [$view_sender1, $view_sender2, $view_sender3]
        );

        return [
            'no_active_senders'        => [$manager_empty, 0],
            'active_senders_no_export' => [$manager_no_export_with_views, 2],
            'active_senders_export'    => [$manager_export_with_views, 2],
        ];
    }

    private function getViewSenderManagerMock(array $functions, bool $export, string $datetime): ViewSenderManager
    {
        return $this->getMockBuilder(ViewSenderManager::class)
            ->onlyMethods(array_merge(['registerShutDownClearFiles'], $functions))
            ->setConstructorArgs([$export, $datetime])
            ->getMock();
    }
}
