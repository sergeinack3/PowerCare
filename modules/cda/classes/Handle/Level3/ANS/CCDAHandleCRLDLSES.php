<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Interop\Cda\Handle\Level3\ANS;

use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDABag;
use Ox\Interop\Cda\Handle\CCDAMetaParticipant;
use Ox\Interop\Cda\Handle\Level3\CCDAHandleLevel3;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultBattery;
use Ox\Mediboard\ObservationResult\CObservationResultComment;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultIsolat;
use Ox\Mediboard\ObservationResult\CObservationResultPerformer;
use Ox\Mediboard\ObservationResult\CObservationResultPrelevement;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultSetComment;
use Ox\Mediboard\ObservationResult\CObservationResultSubject;

class CCDAHandleCRLDLSES extends CCDAHandleLevel3
{
    // sections
    /** @var string */
    public const TEMPLATE_ID = '2.16.840.1.113883.6.1^11490-0';
}
