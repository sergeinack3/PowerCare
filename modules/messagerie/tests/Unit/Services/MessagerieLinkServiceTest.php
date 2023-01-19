<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Tests\Unit;

use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Medimail\CMedimailAttachment;
use Ox\Mediboard\Medimail\CMedimailMessage;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\Entities\MessagerieEntity;
use Ox\Mediboard\Messagerie\Exceptions\MessagerieLinkException;
use Ox\Mediboard\Messagerie\Services\MessagerieLinkService;
use Ox\Mediboard\Messagerie\Tests\Fixtures\MessagerieContextFixtures;
use Ox\Mediboard\Messagerie\Tests\Fixtures\MessagerieFixtures;
use Ox\Mediboard\Mssante\CMSSanteMail;
use Ox\Mediboard\Mssante\CMSSanteMailAttachment;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CContentHTML;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

/**
 * Test for MessagingLinkServiceTest class.
 */
class MessagerieLinkServiceTest extends OxUnitTestCase
{
    /** @var CMediusers $user User. */
    private CMediusers $user;

    /** @var CPatient $patient Patient. */
    private CPatient $patient;

    /**
     * @inheritDoc
     * @throws TestsException
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(
            CMediusers::class,
            MessagerieContextFixtures::MESSAGING_USER_TAG
        );

        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            MessagerieContextFixtures::MESSAGING_PATIENT_TAG
        );

        $this->user    = $user;
        $this->patient = $patient;
    }

    /**
     * Test the link of medimail.
     *
     * @dataProvider medimailProvider
     *
     * @param MessagerieEntity $object Link object
     *
     * @return void
     * @throws MessagerieLinkException
     */
    public function testSuccessfulMedimailStoreLink(MessagerieEntity $object): void
    {
        if (CModule::getActive('medimail')) {
            $service = new MessagerieLinkService();
            $msg     = $service->fromMedimail(
                $object,
                $this->user->_id,
                $this->patient->_id,
                $this->patient->_class,
                'Fixtures',
                null
            );

            $this->assertNull($msg);
        } else {
            $this->markTestSkipped('Module: Medimail is not activated.');
        }
    }

    /**
     * Medimail data provider.
     *
     * @return array[]
     * @throws TestsException
     */
    public function medimailProvider(): array
    {
        return [
            'medimailMailLink'       => [
                $this->createMedimailMail()
            ],
            'medimailAttachmentLink' => [
                $this->createMedimailAttachment()
            ],
        ];
    }

    /**
     * Test the link of mailiz.
     *
     * @dataProvider mailizProvider
     *
     * @param MessagerieEntity $object Link object
     *
     * @return void
     * @throws MessagerieLinkException
     */
    public function testSuccessfulMailizStoreLink(MessagerieEntity $object): void
    {
        if (CModule::getActive('mssante')) {
            $service = new MessagerieLinkService();
            $msg     = $service->fromMailiz(
                $object,
                $this->user->_id,
                $this->patient->_id,
                $this->patient->_class,
                'Fixtures',
                null
            );

            $this->assertNull($msg);
        } else {
            $this->markTestSkipped('Module: Mailiz is not activated.');
        }
    }

    /**
     * Medimail data provider.
     *
     * @return array[]
     * @throws TestsException
     */
    public function mailizProvider(): array
    {
        return [
            'mailizMailLink'       => [
                $this->createMailizMail()
            ],
            'mailizAttachmentLink' => [
                $this->createMailizAttachment()
            ],
        ];
    }

    /**
     * Return Medimail mail
     *
     * @throws TestsException
     */
    private function createMedimailMail(): CMedimailMessage
    {
        /** @var CContentHTML $content */
        $content = $this->getObjectFromFixturesReference(
            CContentHTML::class,
            MessagerieFixtures::MESSAGING_CONTENT_TAG
        );

        $medimail_mail = $this->getMockBuilder(CMedimailMessage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContent'])
            ->getMock();
        $medimail_mail->method('getContent')->willReturn($content->content);

        return $medimail_mail;
    }

    /**
     * Return Medimail attachment
     */
    private function createMedimailAttachment(): CMedimailAttachment
    {
        $file = $this->getMockBuilder(CFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBinaryContent'])
            ->getMock();
        $file->method('getBinaryContent')->willReturn('Lorem ipsum dolor sit amet');
        $file->file_type = 'text/plain';

        $medimail_attachment = $this->getMockBuilder(CMedimailAttachment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadFile'])
            ->getMock();
        $medimail_attachment->method('loadFile')->willReturn($file);

        return $medimail_attachment;
    }

    /**
     * Return a Mailiz mail
     *
     * @throws TestsException
     */
    private function createMailizMail(): CMSSanteMail
    {
        /** @var CContentHTML $content */
        $content = $this->getObjectFromFixturesReference(
            CContentHTML::class,
            MessagerieFixtures::MESSAGING_CONTENT_TAG
        );

        $mailiz_mail = $this->getMockBuilder(CMSSanteMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadRefContent'])
            ->getMock();
        $mailiz_mail->method('loadRefContent')->willReturn($content);

        return $mailiz_mail;
    }

    /**
     * Return Mailiz attachment
     */
    private function createMailizAttachment(): CMSSanteMailAttachment
    {
        $file = $this->getMockBuilder(CFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBinaryContent'])
            ->getMock();
        $file->method('getBinaryContent')->willReturn('Lorem ipsum dolor sit amet');
        $file->file_type = 'text/plain';

        $mailiz_attachment = $this->getMockBuilder(CMSSanteMailAttachment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadRefFile'])
            ->getMock();
        $mailiz_attachment->method('loadRefFile')->willReturn($file);

        return $mailiz_attachment;
    }
}
