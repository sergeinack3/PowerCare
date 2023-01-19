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
use Ox\Interop\Eai\handlers\CFilesObjectHandler;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Hl7\CSenderMLLP;
use Ox\Interop\Hprimsante\CReceiverHprimSante;
use Ox\Interop\Hprimxml\CDestinataireHprim;
use Ox\Interop\Sa\CSaEventObjectHandler;
use Ox\Interop\Sa\CSaObjectHandler;
use Ox\Interop\Sip\CSipObjectHandler;
use Ox\Interop\Smp\CSmpObjectHandler;
use Ox\Interop\Webservices\CSenderSOAP;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Galaxie\CReceiverHL7v2Galaxie;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\System\CSenderHTTP;
use Ox\Tests\OxUnitTestCase;

class CInteropHandlerTest extends OxUnitTestCase
{
    public function providerHandlerInteropHasOnlyIgroupRelatedObjects(): array
    {
        $interop_handlers = [
            CFilesObjectHandler::class,
            CSaEventObjectHandler::class,
            CSaObjectHandler::class,
            CSipObjectHandler::class,
            CSmpObjectHandler::class,
        ];

        $classes = [];
        foreach ($interop_handlers as $handler) {
            $classes = array_merge($classes, $handler::$handled);
        }

        $classes = array_unique($classes);

        $data = [];
        foreach ($classes as $class) {
            $data[$class] = ['class' => $class];
        }

        return $data;
    }

    /**
     * Tous les objects donnés au handler d'interop doivent implements l'interface IGroupRelated
     *
     * @param string $class
     *
     * @dataProvider providerHandlerInteropHasOnlyIgroupRelatedObjects
     */
    public function testHandlerInteropHasOnlyIgroupRelatedObjects(string $class): void
    {
        $this->assertInstanceOf(IGroupRelated::class, new $class());
    }
}
