{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(window.print);
</script>

{{*<style>*}}
  {{*@page {*}}
    {{*size: landscape;*}}
  {{*}*}}
{{*</style>*}}

{{assign var=main_colspan value="7"}}
{{assign var=patient_colspan value="2"}}
{{assign var=consult_colspan value="5"}}

{{if $show_IPP}}
  {{math equation="x+1" x=$main_colspan assign=main_colspan}}
  {{math equation="x+1" x=$patient_colspan assign=patient_colspan}}
{{/if}}

{{if $show_addr}}
  {{math equation="x+1" x=$main_colspan assign=main_colspan}}
  {{math equation="x+1" x=$patient_colspan assign=patient_colspan}}
{{/if}}

{{if $show_phone}}
  {{math equation="x+1" x=$main_colspan assign=main_colspan}}
  {{math equation="x+1" x=$patient_colspan assign=patient_colspan}}
{{/if}}

<table class="main tbl">
  <tr class="clear">
    <th colspan="{{$main_colspan}}">
      <h1 class="no-break">
        <a href="#1" onclick="window.print();">
          {{if $date_min != $date_max}}
            [{{$date_min|date_format:$conf.date}} &ndash; {{$date_max|date_format:$conf.date}}]
          {{else}}
            {{$date_min|date_format:$conf.date}}
          {{/if}}
        </a>
      </h1>
    </th>
  </tr>

  <tr>
    <th colspan="{{$patient_colspan}}">{{tr}}CPatient{{/tr}}</th>

    <th colspan="{{$consult_colspan}}">{{tr}}CConsultation{{/tr}}</th>
  </tr>

  <tr>
    <th class="narrow">{{mb_title class=CPatient field=naissance}}</th>
    <th>{{mb_title class=CPatient field=nom}}</th>

    {{if $show_IPP}}
      <th class="narrow">{{mb_title class=CPatient field=_IPP}}</th>
    {{/if}}

    {{if $show_addr}}
      <th class="narrow">{{mb_title class=CPatient field=adresse}}</th>
    {{/if}}

    {{if $show_phone}}
      <th class="narrow">{{mb_title class=CPatient field=tel}}</th>
    {{/if}}

    <th class="narrow">{{tr}}common-Practitioner{{/tr}}</th>
    <th>{{mb_title class=CCOnsultation field=motif}}</th>
    <th>{{mb_title class=CCOnsultation field=rques}}</th>
    <th class="narrow">{{tr}}common-Schedule{{/tr}}</th>
    <th class="narrow">{{mb_title class=CCOnsultation field=duree}}</th>
  </tr>

  {{foreach from=$consultations key=_period item=_consults}}
    <tr class="clear">
      <th colspan="{{$main_colspan}}">
        <h2 class="no-break">
          {{if $sorting_mode == 'month'}}
            {{* We need a complete and valid date *}}
            {{"1970-$_period-01"|date_format:'%B'}}
          {{else}}
            {{$_period}}
          {{/if}}
        </h2>
      </th>
    </tr>

    {{foreach from=$_consults item=_consult}}
      {{assign var=patient value=$_consult->_ref_patient}}

      <tr>
        <td>{{mb_value object=$patient field=naissance}}</td>

        <td>
          {{$patient}}
        </td>

        {{if $show_IPP}}
          <td>
            {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          </td>
        {{/if}}

        {{if $show_addr}}
          <td class="compact" style="white-space: nowrap !important;">
            {{mb_value object=$patient field=adresse}}

            {{if $patient->cp}}
              {{mb_value object=$patient field=cp}}
            {{/if}}

            {{if $patient->ville}}
              {{mb_value object=$patient field=ville}}
            {{/if}}
          </td>
        {{/if}}

        {{if $show_phone}}
          <td>
            {{mb_value object=$patient field=tel}}
          </td>
        {{/if}}

        <td>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_praticien}}
        </td>

        <td class="text">
          {{mb_value object=$_consult field=motif}}
        </td>

        <td class="text compact">
          {{mb_value object=$_consult field=rques}}
        </td>

        <td>
          {{mb_value object=$_consult field=_datetime}}
        </td>

        <td>
          {{$_consult->_duree}} {{tr}}common-noun-minutes-court{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
