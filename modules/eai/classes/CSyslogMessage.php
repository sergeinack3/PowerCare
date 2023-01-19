<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CMbDT;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Hl7\CHL7v2Message;

class CSyslogMessage extends CMbXMLDocument {
  /** Facility */
  const KERNEL_FACILITY                 = '0';
  const USER_LEVEL_FACILITY             = '1';
  const MAIL_SYSTEM_FACILITY            = '2';
  const SYSTEM_DAEMONS_FACILITY         = '3';
  const SECURITY_FACILITY               = '4';
  const SYSLOGD_FACILITY                = '5';
  const LINE_PRINTER_FACILITY           = '6';
  const NETWORK_NEWS_FACILITY           = '7';
  const UUCP_SUBSYSTEM_FACILITY         = '8';
  const CLOCK_FACILITY                  = '9';
  const SECURITY_AUTHORIZATION_FACILITY = '10';
  const FTP_DAEMON_FACILITY             = '11';
  const NTP_SUBSYSTEM_FACILITY          = '12';
  const LOG_AUDIT_FACILITY              = '13';
  const LOG_ALERT_FACILITY              = '14';
  const CLOCK_DAEMON_FACILITY           = '15';
  const LOCAL0_FACILITY                 = '16';
  const LOCAL1_FACILITY                 = '17';
  const LOCAL2_FACILITY                 = '18';
  const LOCAL3_FACILITY                 = '19';
  const LOCAL4_FACILITY                 = '20';
  const LOCAL5_FACILITY                 = '21';
  const LOCAL6_FACILITY                 = '22';
  const LOCAL7_FACILITY                 = '23';

  /** Severity */
  const EMERGENCY_SEVERITY     = '0';
  const ALERT_SEVERITY         = '1';
  const CRITICAL_SEVERITY      = '2';
  const ERROR_SEVERITY         = '3';
  const WARNING_SEVERITY       = '4';
  const NOTICE_SEVERITY        = '5';
  const INFORMATIONAL_SEVERITY = '6';
  const DEBUG_LEVEL_SEVERITY   = '7';

  const VERSION  = '1';
  const HOSTNAME = '-';
  const APPNAME  = 'MEDIBOARD';
  const PROCID   = '-';

  // SYSLOG-MSG
  /** @var string SYSLOG message */
  public $syslog_msg;

  /** @var string PRI VERSION SP TIMESTAMP SP HOSTNAME SP APP-NAME SP PROCID SP MSGID */
  public $header;

  /** @var string Not implemented here (IHE ATNA) since the MSG field itself holds structured data */
  public $structured_data;

  /** @var string MSG-ANY / MSG-UTF8 */
  public $msg;

  // HEADER
  /** @var string "<" PRIVAL ">" */
  public $pri;

  /** @var integer NONZERO-DIGIT 0*2DIGIT */
  public $msg_version;

  /** @var string "-" / FULL-DATE "T" FULL-TIME */
  public $timestamp;

  /** @var string "-" / 1*255PRINTUSASCII */
  public $hostname;

  /** @var string "-" / 1*48PRINTUSASCII */
  public $app_name;

  /** @var string "-" / 1*128PRINTUSASCII */
  public $procid;

  /** @var string "-" / 1*32PRINTUSASCII */
  public $msgid;

  // PRI
  /** @var integer 1*3DIGIT ; range 0..191 */
  public $prival;

  // TIMESTAMP
  /** @var string DATE-FULLYEAR "-" DATE-MONTH "-" DATE-MDAY */
  public $full_date;
  /** @var string PARTIAL-TIME TIME-OFFSET */
  public $full_time;

  // FULL-DATE
  /** @var integer 4DIGIT */
  public $date_fullyear;

  /** @var integer 2DIGIT ; 01-12 */
  public $date_month;

  /** @var integer 2DIGIT ; 01-31 */
  public $date_mday;

  // FULL-TIME
  /** @var string TIME-HOUR ":" TIME-MINUTE ":" TIME-SECOND [TIME-SECFRAC] */
  public $partial_time;

  /** @var string "Z" / TIME-NUMOFFSET */
  public $time_offset;

  // PARTIAL-TIME
  /** @var integer 2DIGIT ; 00-23 */
  public $time_hour;

  /** @var integer 2DIGIT ; 00-59 */
  public $time_minute;

  /** @var integer 2DIGIT ; 00-59 */
  public $time_second;

  /** @var string "." 1*6DIGIT */
  public $time_secfrac;

  // TIME-OFFSET
  /** @var string ("+" / "-") TIME-HOUR ":" TIME-MINUTE */
  public $time_numoffset;

  // MSG
  /** @var string *OCTET ; not starting with BOM (%xEF.BB.BF) */
  public $msg_any;

  /** @var string BOM (%xEF.BB.BF) *OCTET ; UTF-8 */
  public $msg_utf8;

  function __construct($encoding = "iso-8859-1") {
    parent::__construct($encoding);

    $this->msg_version = self::VERSION;
    $this->hostname    = $this->guessHostname();
    $this->app_name    = self::APPNAME;
    $this->procid      = ($procid = getmypid()) ? $procid : self::PROCID;

    $this->setTimestamp(CMbDT::dateTime());
  }

  /**
   * Get complete SYSLOG message
   *
   * @return string
   */
  function getSyslogMsg() {
    return $this->syslog_msg = "{$this->getHeaderField()} {$this->getStructuredDataField()} {$this->getMsgField()}";
  }

  /**
   * Get SYSLOG header
   *
   * @return string
   */
  function getHeaderField() {
    return $this->header =
      "{$this->pri}{$this->msg_version} {$this->timestamp} {$this->hostname} {$this->app_name} {$this->procid} {$this->msgid}";
  }

  /**
   * Get SYSLOG structured-data
   *
   * @return string
   */
  function getStructuredDataField() {
    return $this->structured_data = "-";
  }

  /**
   * Get SYSLOG message
   *
   * @return string
   */
  function getMsgField() {
    return $this->msg = ($this->msg_utf8) ? $this->msg_utf8 : $this->msg_any;
  }

  /**
   * @return string
   */
  public function getPri() {
    return $this->pri;
  }

  /**
   * @param integer $facility Facility code
   * @param integer $severity Severity code
   */
  public function setPri($facility, $severity) {
    $this->setPrival($facility, $severity);

    $this->pri = "<{$this->prival}>";
  }

  /**
   * @return int
   */
  public function getVersion() {
    return $this->msg_version;
  }

  /**
   * @return string
   */
  public function getTimestamp() {
    return $this->timestamp;
  }

  /**
   * @param string $timestamp
   */
  public function setTimestamp($timestamp) {
    if (!strtotime($timestamp)) {
      $this->timestamp = '-';
    }
    else {
      $full_date = CMbDT::format($timestamp, '%Y-%m-%d');
      $full_time = CMbDT::format($timestamp, '%H:%M:%S');

      $this->setFullDate($full_date);
      $this->setFullTime($full_time);

      $this->timestamp = "{$full_date}T{$full_time}Z";
    }
  }

  /**
   * @return string
   */
  public function getHostname() {
    return $this->hostname;
  }

  /**
   * @param string $hostname
   */
  public function setHostname($hostname) {
    $this->hostname = $hostname;
  }

  /**
   * @return string
   */
  public function getAppName() {
    return $this->app_name;
  }

  /**
   * @param string $app_name
   */
  public function setAppName($app_name) {
    $this->app_name = $app_name;
  }

  /**
   * @return string
   */
  public function getProcid() {
    return $this->procid;
  }

  /**
   * @param string $procid
   */
  public function setProcid($procid) {
    $this->procid = $procid;
  }

  /**
   * @return int
   */
  public function getPrival() {
    return $this->prival;
  }

  /**
   * @param integer $facility Facility code
   * @param integer $severity Severity code
   */
  function setPrival($facility, $severity) {
    $this->prival = "$facility$severity";
  }

  /**
   * @return string
   */
  public function getFullDate() {
    return $this->full_date;
  }

  /**
   * @param string $full_date
   */
  public function setFullDate($full_date) {
    $date_fullyear = CMbDT::format($full_date, '%Y');
    $date_month    = CMbDT::format($full_date, '%m');
    $date_mday     = CMbDT::format($full_date, '%d');

    $this->setDateFullyear($date_fullyear);
    $this->setDateMonth($date_month);
    $this->setDateMday($date_mday);

    $this->full_date = $full_date;
  }

  /**
   * @return string
   */
  public function getFullTime() {
    return $this->full_time;
  }

  /**
   * @param string $full_time
   */
  public function setFullTime($full_time) {
    $partial_time = CMbDT::format($full_time, '%H:%M:%S');

    $this->setPartialTime($partial_time);

    $this->full_time = $full_time;
  }

  /**
   * @return int
   */
  public function getDateFullyear() {
    return $this->date_fullyear;
  }

  /**
   * @param int $date_fullyear
   */
  public function setDateFullyear($date_fullyear) {
    $this->date_fullyear = $date_fullyear;
  }

  /**
   * @return int
   */
  public function getDateMonth() {
    return $this->date_month;
  }

  /**
   * @param int $date_month
   */
  public function setDateMonth($date_month) {
    $this->date_month = $date_month;
  }

  /**
   * @return int
   */
  public function getDateMday() {
    return $this->date_mday;
  }

  /**
   * @param int $date_mday
   */
  public function setDateMday($date_mday) {
    $this->date_mday = $date_mday;
  }

  /**
   * @return string
   */
  public function getPartialTime() {
    return $this->partial_time;
  }

  /**
   * @param string $partial_time
   */
  public function setPartialTime($partial_time) {
    $time_hour   = CMbDT::format($partial_time, '%H');
    $time_minute = CMbDT::format($partial_time, '%M');
    $time_second = CMbDT::format($partial_time, '%S');

    $this->setTimeHour($time_hour);
    $this->setTimeMinute($time_minute);
    $this->setTimeSecond($time_second);

    $this->partial_time = $partial_time;
  }

  /**
   * @return string
   */
  public function getTimeOffset() {
    return $this->time_offset;
  }

  /**
   * @param string $time_offset
   */
  public function setTimeOffset($time_offset) {
    $this->time_offset = $time_offset;
  }

  /**
   * @return int
   */
  public function getTimeHour() {
    return $this->time_hour;
  }

  /**
   * @param int $time_hour
   */
  public function setTimeHour($time_hour) {
    $this->time_hour = $time_hour;
  }

  /**
   * @return int
   */
  public function getTimeMinute() {
    return $this->time_minute;
  }

  /**
   * @param int $time_minute
   */
  public function setTimeMinute($time_minute) {
    $this->time_minute = $time_minute;
  }

  /**
   * @return int
   */
  public function getTimeSecond() {
    return $this->time_second;
  }

  /**
   * @param int $time_second
   */
  public function setTimeSecond($time_second) {
    $this->time_second = $time_second;
  }

  /**
   * @return string
   */
  public function getTimeSecfrac() {
    return $this->time_secfrac;
  }

  /**
   * @param string $time_secfrac
   */
  public function setTimeSecfrac($time_secfrac) {
    $this->time_secfrac = $time_secfrac;
  }

  /**
   * @return string
   */
  public function getTimeNumoffset() {
    return $this->time_numoffset;
  }

  /**
   * @param string $time_numoffset
   */
  public function setTimeNumoffset($time_numoffset) {
    $this->time_numoffset = $time_numoffset;
  }

  /**
   * @return string
   */
  public function getMsgAny() {
    return $this->msg_any;
  }

  /**
   * @param string $msg_any
   */
  public function setMsgAny($msg_any) {
    $this->msg_any = $msg_any;
  }

  /**
   * @return string
   */
  public function getMsgUtf8() {
    return $this->msg_utf8;
  }

  /**
   * @param string $msg_utf8
   */
  public function setMsgUtf8($msg_utf8) {
    $this->msg_utf8 = $msg_utf8;
  }

  /**
   * Get UTF-8 message function alias
   *
   * @return string
   */
  public function getMsg() {
    return $this->getMsgUtf8();
  }

  /**
   * Set UTF-8 message function alias
   *
   * @param CHL7v2Message $msg Syslog message
   */
  public function setMsg(CHL7v2Message $msg) {
    $this->setMsgUtf8($msg);
  }

  /**
   * Tries to get server IP address, hostname otherwise
   *
   * @return string
   */
  private function guessHostname() {
    $hostname = trim(exec('hostname'));
    $ip       = gethostbyname($hostname);

    $return_value = self::HOSTNAME;
    if ($ip && $ip != '::1' && preg_match('#^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$#', $ip) && $ip != '127.0.0.1') {
      $return_value = $ip;
    }
    else if ($hostname) {
      $return_value = $hostname;
    }

    return $return_value;
  }
}
