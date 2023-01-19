<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;

CCanDo::checkRead();

$msg = new CHL7v2Message;
$msg->version = "2.5";
$msg->name = "ADT_A08";

//$msg->fieldSeparator = "a";
//$msg->componentSeparator = "b";
//$msg->subcomponentSeparator = "e";
//$msg->repetitionSeparator = "c";
//$msg->escapeCharacter = "d";

$segment = CHL7v2Segment::create("MSH", $msg);

$data = array(
  null,         // MSH-1: Field Separator (ST)
  null,        // MSH-2: Encoding Characters (ST)
  "Mediboard", // MSH-3: Sending Application (HD) (optional)
  "Mediboard_finess", // MSH-4: Sending Facility (HD) (optional)
  "no_receiver", // MSH-5: Receiving Application (HD) (optional)
  null, // MSH-6: Receiving Facility (HD) (optional)
  CMbDT::dateTime(), // MSH-7: Date/Time Of Message (TS)
  null, // MSH-8: Security (ST) (optional)
  null, // MSH-9: Message Type (MSG)
  "Msg ctrl id", // MSH-10: Message Control ID (ST) 
  "A", // MSH-11: Processing ID (PT) 
  null, // MSH-12: Version ID (VID) 
  15, // MSH-13: Sequence Number (NM) (optional)
  null, // MSH-14: Continuation Pointer (ST) (optional)
  null, // MSH-15: Accept Acknowledgment Type (ID) (optional)
  null, // MSH-16: Application Acknowledgment Type (ID) (optional)
  null, // MSH-17: Country Code (ID) (optional)
  "8859/1", // MSH-18: Character Set (ID) (optional repeating)
  null, // MSH-19: Principal Language Of Message (CE) (optional)
  null, // MSH-20: Alternate Character Set Handling Scheme (ID) (optional)
  null // MSH-21: Message Profile Identifier (EI) (optional repeating) 
);
    
$segment->fill($data);
$msg->appendChild($segment);

$segment = CHL7v2Segment::create("PID", $msg);

$data = array (
  0 => 1,
  1 => null,
  2 => 
  array (
    0 => 
    array (
      0 => '323241',
      1 => null,
      2 => null,
      3 => 
      array (
        0 => 'Mediboard',
        1 => '1.2.250.1.2.3.4',
        2 => 'OpenXtrem',
      ),
      4 => 'RI',
    ),
    1 => 
    array (
      0 => 'fgfg',
      1 => null,
      2 => null,
      3 => 
      array (
        0 => null,
        1 => '1.2.250.1.213.1.4.2',
        2 => 'ISO',
      ),
      4 => 'INS-C',
      5 => null,
      6 => '1946-10-17',
    ),
  ),
  3 => null,
  4 => 
  array (
    0 => 
    array (
      0 => 'EZZHJ',
      1 => 'Rtaso',
      2 => 'qldax',
      3 => null,
      4 => 'dr',
      5 => null,
      6 => 'D',
      7 => 'A',
    ),
    1 => 
    array (
      0 => 'MEBJO',
      1 => 'Rtaso',
      2 => 'qldax',
      3 => null,
      4 => 'dr',
      5 => null,
      6 => 'L',
      7 => 'A',
    ),
  ),
  5 => null,
  6 => '1987-09-24',
  7 => 'F',
  8 => null,
  9 => null,
  10 => 
  array (
    0 => 
    array (
      0 => "adresse test 
 \\ | & ^ ~",
      1 => null,
      2 => 'rtckkljfgrw',
      3 => null,
      4 => '4294967295',
      5 => null,
      6 => 'H',
    ),
    1 => 
    array (
      0 => null,
      1 => null,
      2 => 'vlfxif',
      3 => null,
      4 => '40048',
      5 => '000',
      6 => 'BR',
    ),
  ),
  11 => null,
  12 => 
  array (
    0 => 
    array (
      0 => '43502',
      1 => 'PRN',
      2 => 'PH',
    ),
    1 => 
    array (
      0 => '42287',
      1 => 'ORN',
      2 => 'CP',
    ),
    2 => 
    array (
      0 => 'oezym',
      1 => 'ORN',
      2 => 'PH',
    ),
  ),
  13 => null,
  14 => null,
  15 => null,
  16 => null,
  17 => null,
  18 => null,
  19 => null,
  20 => null,
  21 => null,
  22 => null,
  23 => null,
  24 => null,
  25 => null,
  26 => null,
  27 => null,
  28 => '1905-05-06',
  29 => 'Y',
  /*30 => null,
  31 => 
  array (
    0 => 'VALI',
  ),
  31 => array('2011-09-01 15:34:27'),
  32 => null,
  33 => null,
  34 => null,
  35 => null,
  36 => null,
  37 => null,*/
);
    
$segment->fill($data);
$msg->appendChild($segment);

$msg->validate();

foreach($msg->errors as $error) {
  CApp::log(CAppUI::tr("CHL7v2Exception-".$error->code)." - ".$error->data, @$error->entity->getPathString());
}

echo "Généré";
echo $msg->flatten(true);

$message_str = $msg->flatten();

echo "Parsé";
$msg2 = new CHL7v2Message;
$msg2->parse($message_str);
$msg2->validate();

foreach($msg2->errors as $error) {
  CApp::log(CAppUI::tr("CHL7v2Exception-".$error->code)." - ".$error->data, @$error->entity->getPathString());
}

echo $msg2->flatten(true);
