{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$_sources name=boucle_source item=_source}}
  {{mb_include module=eai template=inc_vw_source}}
  {{foreachelse}}
  <tr>
    <td colspan="6" class="empty">
      {{tr}}{{$name}}.none{{/tr}}
    </td>
  </tr>
{{/foreach}}