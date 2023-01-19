{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var oFormPrestas = getForm('stats_prestas');
    Calendar.regField(oFormPrestas._date_min);
    Calendar.regField(oFormPrestas._date_max);
  });
  getStatsPrestas = function () {
    new Url("stats", "ajax_stats_prestations")
      .addFormData(getForm('stats_prestas'))
      .requestUpdate('stats_prestations');
  }
</script>

<form name="stats_prestas" action="?" method="get">
  <input type="hidden" name="_chir" value="{{$app->user_id}}"/>
  <input type="hidden" name="_class" value=""/>
  <table class="main form">
    <tr>
      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>{{mb_field object=$filter field="_date_min" form="stats_prestas" canNull="false" register=true}}</td>
      <th>{{mb_label object=$filter field="_service"}}</th>
      <td>
        <select name="service_id">
          <option value="0">&mdash; {{tr}}CService.all{{/tr}}</option>
          {{foreach from=$listServices item=curr_service}}
            <option value="{{$curr_service->service_id}}"
                    {{if $curr_service->service_id == $filter->_service}}selected{{/if}}>
              {{$curr_service->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>{{mb_field object=$filter field="_date_max" form="stats_prestas" canNull="false" register=true}} </td>
      <th>{{mb_label object=$filter field="praticien_id"}}</th>
      <td>
        <select name="prat_id">
          <option value="0">&mdash; {{tr}}CMediusers.praticiens.all{{/tr}}</option>
          {{foreach from=$listPrats item=curr_prat}}
            <option value="{{$curr_prat->user_id}}" {{if $curr_prat->user_id == $filter->praticien_id}}selected{{/if}}>
              {{$curr_prat->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="type"}}</th>
      <td>
        <select name="type">
          <option value="">&mdash; {{tr}}CSejour-type.all{{/tr}}</option>
          <option value="1" {{if $filter->type == "1"}}selected="selected"{{/if}}>{{tr}}CSejour-type.comp_and_ambu{{/tr}}</option>
          {{foreach from=$filter->_specs.type->_locales key=key_hospi item=curr_hospi}}
            <option value="{{$key_hospi}}" {{if $key_hospi == $filter->type}}selected{{/if}}>
              {{$curr_hospi}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>{{mb_label object=$filter field="_specialite"}}</th>
      <td>
        <select name="discipline_id">
          <option value="0">&mdash; {{tr}}CDiscipline.all{{/tr}}</option>
          {{foreach from=$listDisciplines item=curr_disc}}
            <option value="{{$curr_disc->discipline_id}}"
                    {{if $curr_disc->discipline_id == $filter->_specialite}}selected{{/if}}>
              {{$curr_disc->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{tr}}Only{{/tr}} {{mb_label object=$filter field="septique" typeEnum="checkbox"}}</th>
      <td>{{mb_field object=$filter field="septique" typeEnum="checkbox"}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="search" type="button" onclick="getStatsPrestas();">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
<div id="stats_prestations"></div>
