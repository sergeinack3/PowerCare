{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $("vue_tableau").fixedTableHeaders();

    Calendar.regField(getForm("chgAff").date, null, {noView: true, inline: true, container: $('calendar-container').update("")});
  });
</script>

{{assign var=width_service value=100}}
{{if $count_services}}
  {{math equation=100/x x=$count_services assign=width_service}}
{{/if}}

<table class="main layout">
  <tr>
    <td colspan="2">
      <div style="float:right;">
        <strong class="me-h6">Planning du {{$date|date_format:$conf.longdate}} - {{$totalLits}} place(s) de libre</strong>
      </div>
      {{if $alerte}}
        <div class="warning" style="float: left;">
          <a href="#1" onclick="showAlerte('{{$emptySejour->_type_admission}}')">
            Il y a {{$alerte}} patient(s) non placés dans la semaine à venir
            {{if $emptySejour->_type_admission}}
              ({{tr}}CSejour._type_admission.{{$emptySejour->_type_admission}}{{/tr}})
            {{/if}}
          </a>
        </div>
      {{else}}
        <div class="info">
          Tous les patients sont placés pour la semaine à venir
        </div>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td>
      <button type="button" onclick="printTableau()" class="print me-tertiary me-dark">Impression</button>
      <button type="button" onclick="showRapport('{{$date}}')" class="print me-tertiary">Rapport</button>
      {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
    </td>
    <td>
      <form name="chgAff" method="get" onsubmit="return onSubmitFormAjax(this, null, 'tableau')">
        <input type="hidden" name="m" value="hospi" />
        <input type="hidden" name="a" value="vw_affectations" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.onsubmit()" />
        <select name="mode" onchange="this.form.onsubmit();" style="float: right;" class="me-margin-bottom-2 me-small">
          <option value="0" {{if $mode == 0}}selected{{/if}}>{{tr}}Instant view{{/tr}}</option>
          <option value="1" {{if $mode == 1}}selected{{/if}}>{{tr}}Day view{{/tr}}</option>
        </select>
      </form>
    </td>
  </tr>
</table>

<div id="vue_tableau">
  <table class="form me-w100">
    <tbody>
    <tr>
      {{foreach from=$services item=curr_service}}
        {{if $curr_service->_ref_chambres|@count}}
          <td class="fullService narrow me-padding-left-2 me-padding-right-2 me-affectations-lits" id="service{{$curr_service->service_id}}"
              style="width: {{$width_service}}%; vertical-align: top;">
            {{mb_include module=hospi template=inc_affectations_services}}
          </td>
        {{/if}}
      {{/foreach}}
    </tr>
    </tbody>
    <thead>
    <tr>
      {{foreach from=$services item=curr_service}}
        {{if $curr_service->_ref_chambres|@count}}
          <th class="category text {{if $curr_service->externe}}service_externe{{/if}} me-line-height-12">
            {{$curr_service->nom}}
            <br />
            <span style="font-size: 80%;">
          {{if $curr_service->externe}}
            externe
          {{else}}
            {{$curr_service->_nb_lits_dispo}} lit(s) dispo
          {{/if}}
          </span>
          </th>
        {{/if}}
      {{/foreach}}
    </tr>
    </thead>
  </table>
</div>