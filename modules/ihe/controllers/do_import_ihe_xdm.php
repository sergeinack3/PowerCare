<?php

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7v3RecordDistributeDocumentSetOnMedia;
use Ox\Interop\Hl7\Events\XDM\CHL7v3AcknowledgmentXDM;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$file = CValue::read($_FILES, "import");

$patient = new CPatient();
$data = array();
$data["file"] = $file;

$vsm = new CHL7v3RecordDistributeDocumentSetOnMedia();
$vsm->handle($patient, $data);


