<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

/**
 * Supervision timeline item interface
 */
interface ISupervisionTimelineItem {
  public function getIdentifier();

  public function getData();
}