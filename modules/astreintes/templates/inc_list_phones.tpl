{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
      {{if $user->user_astreinte}}
        setPhone('{{$user->user_astreinte}}');
      {{elseif $user->user_astreinte_autre}}
      setPhone('{{$user->user_astreinte_autre}}');
      {{/if}}
    });
</script>

<table class="tbl">
  <tr>
    <th colspan="2" class="title">
      {{tr}}CPlageAstreinte-List of available phone|pl{{/tr}}
    </th>
  </tr>
  {{foreach from=$phones key=type item=_phone}}
    <tr>
      <th>{{tr}}{{$type}}{{/tr}}</th>
      <td><button type="button" class="phone" onclick="setPhone('{{$_phone}}');">{{$_phone}}</button></td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">
        {{tr}}Phone.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>