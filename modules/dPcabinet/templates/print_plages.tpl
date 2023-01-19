{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=main_colspan value="7"}}
{{assign var=patient_colspan value="2"}}
{{mb_default var=visite_domicile value=0}}

{{if $filter->_coordonnees}}
  {{math equation="x+2" x=$main_colspan assign=main_colspan}}
  {{math equation="x+2" x=$patient_colspan assign=patient_colspan}}
{{elseif $filter->_telephone}}
  {{math equation="x+1" x=$main_colspan assign=main_colspan}}
  {{math equation="x+1" x=$patient_colspan assign=patient_colspan}}
{{/if}}

{{if $show_lit}}
  {{math equation="x+1" x=$main_colspan assign=main_colspan}}
  {{math equation="x+1" x=$patient_colspan assign=patient_colspan}}
{{/if}}

<table class="tbl">
  <tr class="clear">
    <th colspan="{{$main_colspan}}">
      <h1 class="no-break">
        <a href="#" onclick="window.print()">
          {{if $filter->plageconsult_id}}
            {{tr var1=$filter->_ref_plageconsult->date|date_format:$conf.longdate
            var2=$filter->_ref_plageconsult->debut|date_format:$conf.time
            var3=$filter->_ref_plageconsult->fin|date_format:$conf.time}}
              CPlageHoraire-of %s from %s to %s
            {{/tr}}
          {{else}}
            {{tr}}Planning-of{{/tr}} {{mb_value object=$filter field=_date_min}}
            {{if $filter->_date_min != $filter->_date_max}}
              {{tr}}date.to{{/tr}} {{mb_value object=$filter field=_date_max}}
            {{/if}}
          {{/if}}
          {{if $visite_domicile == 1 }}
            - {{tr}}oxCabinet-filter_visite_domicile{{/tr}}
          {{/if}}
        </a>
      </h1>
    </th>
  </tr>

  {{foreach from=$listPlage item=curr_plage}}
    <tr class="clear">
      <td colspan="{{$main_colspan}}" class="text">
        <h2>
          <span style="float: right">
            {{$curr_plage->_ref_consultations|@count}} {{tr}}CConsultation|(pl)-lower{{/tr}}
          </span>

          {{$curr_plage->date|date_format:$conf.longdate}}
          -
          {{if $curr_plage->_ref_chir->isPraticien()}}Dr{{/if}} {{$curr_plage->_ref_chir->_view}}
          -
          {{$curr_plage->libelle}}
        </h2>
      </td>
    </tr>
    <tr>
      <th rowspan="2" colspan="2" class="narrow"><b>{{tr}}Hour{{/tr}}</b></th>
      <th colspan="{{$patient_colspan}}"><b>{{tr}}CPatient{{/tr}}</b></th>
      <th colspan="3"><b>{{tr}}CConsultation{{/tr}}</b></th>
    </tr>
    <tr>
      <th style="width: 20%;">{{tr}}CPatient-Last name / First name{{/tr}}</th>
      {{if $filter->_coordonnees}}
        <th style="width: 15%;">{{tr}}CPatient-adresse{{/tr}}</th>
        <th class="narrow">{{tr}}CPatient-_p_phone_number-court{{/tr}}</th>
      {{elseif $filter->_telephone}}
        <th class="narrow">{{tr}}CPatient-_p_phone_number-court{{/tr}}</th>
      {{/if}}
      <th class="narrow">{{tr}}CPatient-_age{{/tr}}</th>
      {{if $show_lit}}
        <th class="narrow">{{tr}}CLit{{/tr}}</th>
      {{/if}}
      <th style="width: 20%;">{{tr}}CConsultation-motif{{/tr}}</th>
      <th style="width: 20%;">{{tr}}CConsultation-rques{{/tr}}</th>
      <th class="narrow">{{tr}}CConsultation-duree{{/tr}}</th>
    </tr>
    {{assign var=previous_consult_id value=0}}
    {{foreach from=$curr_plage->listPlace item =_place}}
      {{if $_place.consultations|@count}}
        {{foreach from=$_place.consultations item=curr_consult}}
          {{if $curr_consult->_id != $previous_consult_id}}
            {{assign var=consult_anesth value=$curr_consult->_ref_consult_anesth}}
            <tbody class="hoverable consult" data-consult_id="{{$curr_consult->_id}}">
            <tr {{if $curr_consult->annule == "1"}}class="hatching"{{/if}}>
              {{assign var=categorie value=$curr_consult->_ref_categorie}}
              <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} {{if !$categorie->_id}}colspan="2"{{/if}}
                  style="text-align: center; {{if $curr_consult->premiere}}background-color:#eaa;{{/if}}">
                {{mb_value object=$curr_consult field=heure}}
              </td>
              {{mb_include template=inc_print_plages_line}}
            </tr>
            </tbody>
          {{/if}}
          {{assign var=previous_consult_id value=$curr_consult->_id}}
        {{/foreach}}
      {{elseif $filter->_non_pourvues}}
        <tbody class="hoverable">
        <tr>
          <td colspan="2" style="text-align: center;">
            {{$_place.time|date_format:$conf.time}}
          </td>
          <td colspan="{{math equation="x-2" x=$main_colspan}}"></td>
        </tr>
        </tbody>
      {{/if}}
      {{foreachelse}}
      <tr>
        <td colspan="{{$main_colspan}}" class="empty">
          {{tr}}CConsultation.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CPlageconsult.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

<script>
  Main.add(function () {
    //remove lines with the same patient
    var consult_ids = [];

    $$('tbody.hoverable.consult').each(function (tbody) {
      var consult_id = tbody.get('consult_id');

      if (consult_ids.includes(consult_id)) {
        tbody.hide();
      }

      consult_ids.push(tbody.get('consult_id'));
    });

    window.print();
  });
</script>
