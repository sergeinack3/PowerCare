{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$actors}}
  <tbody>
  <tr>
    <td colspan="10" class="empty">
      {{tr var1=$role_instance}}CInteropActor-msg-None active actor{{/tr}}
    </td>
  </tr>
  </tbody>

  {{mb_return}}
{{/if}}

{{foreach from=$actors item=_actor}}
  {{mb_include template=inc_actor}}
{{/foreach}}
