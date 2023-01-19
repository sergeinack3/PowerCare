<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit;

use Exception;
use Ox\AppFine\Client\CReceiverHL7v2AppFine;
use Ox\Core\CClassMap;
use Ox\Interop\Dicom\CDicomSender;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Hl7\CSenderMLLP;
use Ox\Interop\Hprimsante\CReceiverHprimSante;
use Ox\Interop\Hprimxml\CDestinataireHprim;
use Ox\Interop\Webservices\CSenderSOAP;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Galaxie\CReceiverHL7v2Galaxie;
use Ox\Mediboard\System\CSenderHTTP;
use Ox\Tests\OxUnitTestCase;

class CInteropActorTest extends OxUnitTestCase
{
    /**
     * @return array
     * @throws Exception
     */
    public function providerConstantSet(): array
    {
        /** @var CInteropActor[] $classes */
        $classes           = CClassMap::getInstance()->getClassChildren(CInteropActor::class, true, true);
        $classes_for_actor = array_filter(
            $classes,
            function ($actor) {
                return !$actor->isStandardClass() && !in_array(get_class($actor), [CInteropReceiver::class, CInteropSender::class]);
            }
        );

        return array_map(
            function ($actor) {
                return ['actor' => $actor];
            },
            $classes_for_actor
        );
    }

    /**
     * @dataProvider providerConstantSet
     *
     * @param CInteropActor $actor
     */
    public function testConstantActorSet(CInteropActor $actor): void
    {
        $class = get_class($actor);
        $this->assertNotEquals(
            '',
            $actor::ACTOR_TYPE,
            "Constant 'ACTOR_TYPE' should not be null for this class '$class'"
        );
    }

    /**
     * @dataProvider providerConstantSet
     *
     * @param CInteropActor $actor
     */
    public function testConstantActorCorrect(CInteropActor $actor): void
    {
        $class = get_class($actor);
        $this->assertContains(
            $actor::ACTOR_TYPE,
            $actor::ACTORS_MANAGED,
            "Constant 'ACTOR_TYPE' should be set in constant 'ACTORS_MANAGED' for the class '$class'"
        );
    }

    /**
     * @return string[][]
     */
    public function providerGet(): array
    {
        return [
            ['class' => CReceiverHL7v2Doctolib::class],
            ['class' => CSenderSOAP::class],
            ['class' => CDestinataireHprim::class],
        ];
    }

    /**
     * @dataProvider providerGet
     */
    public function testGet(string $class): void
    {
        /** @var $class CInteropActor */
        $this->assertInstanceOf($class, $class::get());
    }

    /**
     * @return array
     */
    public function providerIsStandardClass(): array
    {
        return [
            ['class' => CSenderSOAP::class],
            ['class' => CSenderMLLP::class],
            ['class' => CSenderSFTP::class],
            ['class' => CSenderFTP::class],
            ['class' => CSenderHTTP::class],
            ['class' => CDicomSender::class],
            ['class' => CReceiverHL7v2::class],
            ['class' => CReceiverFHIR::class],
            ['class' => CReceiverHL7v3::class],
            ['class' => CReceiverHprimSante::class],
            ['class' => CReceiverHL7v2AppFine::class, 'expected' => false],
            ['class' => CReceiverHL7v2Doctolib::class, 'expected' => false],
            ['class' => CReceiverHL7v2Galaxie::class, 'expected' => false],
        ];
    }

    /**
     * @dataProvider providerIsStandardClass
     */
    public function testIsStandardClass(string $class, bool $expected = true): void
    {
        /** @var CInteropActor $actor */
        $actor = new $class();
        $this->assertEquals($expected, $actor->isStandardClass());
    }

    /**
     * @throws Exception
     */
    public function testStoreWithType(): void
    {
        $actor = new CReceiverHL7v2Doctolib();
        $actor->store();

        $this->assertEquals(CInteropActor::ACTOR_DOCTOLIB, $actor->type);
    }

    /**
     * @throws Exception
     */
    public function testStoreWithoutType(): void
    {
        $actor = new CReceiverHL7v2();
        $actor->store();

        $this->assertNull($actor->type);
    }
}
