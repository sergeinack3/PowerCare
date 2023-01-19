{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=CTunnel ajax=true}}

<button class="new" onclick="CTunnel.editTunnel('0')">{{tr}}CHTTPTunnelObject-title-create{{/tr}}</button>
<div id="listTunnel">
  {{mb_include template=inc_list_tunnel}}
</div>
<br/>
<div id="result_action">
</div>