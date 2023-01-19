{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=filter_history}}
{{mb_script module=system script=HistoryViewer ajax=$ajax}}

<div id="history_content">
  {{mb_include module=system template=inc_view_history}}
</div>
