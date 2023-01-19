{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=plage ajax=true}}
{{if count($plages_astreinte) >= 100}}
  <div class="small-info">{{tr var1=100}}first-results{{/tr}}</div>
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="10"class="title">Plages pour {{mb_value object=$user field="_user_last_name"}} {{mb_value object=$user field="_user_first_name"}}</th>
  </tr>
  <tr>
    <th>{{mb_title class=CPlageAstreinte field=libelle}}</th>
    <th>{{tr}}Dates{{/tr}}</th>*
    <th>{{mb_title class=CPlageAstreinte field=phone_astreinte}}</th>
  </tr>
  {{foreach from=$plages_astreinte item=_plageastreinte}}
    <tr id="p{{$_plageastreinte->_id}}" class="{{if $plage_id == $_plageastreinte->_id}} selected{{/if}}" >
      <td>
        <a href="#Edit-{{$_plageastreinte->_guid}}"
           onclick="PlageAstreinte.modal('{{$_plageastreinte->_id}}')">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_plageastreinte->_guid}}')">
          {{if $_plageastreinte->libelle}}
            {{mb_value object=$_plageastreinte field="libelle"}}
          {{else}}
            {{tr}}CPlageAstreinte.noLibelle{{/tr}}
          {{/if}}
          </span>
        </a>
      </td>
      <td {{if $today <= $_plageastreinte->end && $today >= $_plageastreinte->start}}class="highlight"{{/if}}>
        {{mb_include module=system template=inc_interval_date from=$_plageastreinte->start to=$_plageastreinte->end}}
      </td>
      <td>{{mb_value object=$_plageastreinte field=phone_astreinte}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CPlageAstreinte.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>