{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$tree key=_name item=_subtree}}
  {{mb_include module=monitoringPatient template=inc_supervision_picture_tree tree=$_subtree name=$_name depth=0}}
{{/foreach}}