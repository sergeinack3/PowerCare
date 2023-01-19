{{*
 * @package Mediboard\OxCabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=vaccination ajax=$ajax}}

<script>
  Main.add(function () {
    Vaccination.editVaccination();
    Vaccination.otherVaccins();
    Vaccination.printCalendar();
  });
</script>

<table id="vaccination_calendar">
  <tr>
    <th class="title" colspan="30">Calendrier de vaccination &dash;
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
        {{$patient->_view}}
      </span>
      &dash;
        {{mb_value object=$patient field=naissance}}
      ({{$full_age.locale}})
    </th>
  </tr>

  {{* Actions *}}
  <tr class="actions not-printable">
    <td colspan="30">
      <button class="see-all-vaccines" data-patient-id="{{$patient->_id}}">
        <i class="fas fa-syringe"></i> {{tr}}Other vaccins{{/tr}}
      </button>
      <button class="print print-calendar" data-patient-id="{{$patient->_id}}">Imprimer</button>
    </td>
  </tr>

  {{* Dates *}}
  <tr class="ages">
    <th>{{tr}}Appropriate-age{{/tr}}</th>
      {{foreach from=$vaccines_dates item=_date}}
        <th class="date">{{$_date}}</th>
      {{/foreach}}
  </tr>

  {{* Vaccinations *}}
  {{foreach from=$vaccinations_array key=_name item=_age_recall}}
    <tr class="vaccines">
        {{assign var=vaccine value=$vaccination_rep->findByType($_name)}}

      {{* Name of the vaccine *}}
      <th style="background: {{$vaccines_colors[$_name]}}{{if $IS_MEDIBOARD_EXT_DARK}}70{{/if}}">{{$vaccine->longname}}</th>

      {{* Vaccinations (empty or not) *}}
      {{assign var=wasEmpty value=0}}
      {{foreach from=$_age_recall key=_recall_age item=_injection}}

        {{if $_injection && $_injection->_ref_vaccinations}}
          {{assign var=vaccination value=$_injection->_ref_vaccinations[0]}}
        {{else}}
          {{assign var=vaccination value=null}}
        {{/if}}

        {{if $vaccination}}
          <td
            id="vaccination-{{$_recall_age}}-{{$_name}}"
                  {{if !$wasEmpty}}
                    style="background: {{if $vaccination->_ref_vaccine}}{{$vaccines_colors[$_name]}}{{if $IS_MEDIBOARD_EXT_DARK}}70{{/if}}{{/if}};"
                  {{/if}}

            {{if $vaccination}}
              data-patient-id="{{$patient->_id}}"
              data-types='["{{$_injection->_ref_vaccinations[0]->type}}"]'
              data-recall-age="{{$_recall_age}}"
              data-mandatory="{{$_injection->_recall->mandatory}}"

              {{* If the vaccine is repeatable on this age, make it different to add severall vaccinations *}}
              {{if $_injection->_recall && $_injection->_recall->repeat > 0}}
                data-repeat="{{$_injection->_recall->repeat}}"
                class="clickable full-cell"
              {{else}}
                data-injection-id="{{$_injection->_id}}"
                class="clickable"
              {{/if}}

              {{if $_injection->_recall && $_injection->_recall->colspan > 1}}
                {{assign var=wasEmpty value=true}}
                colspan="{{$_injection->_recall->colspan}}"
              {{/if}}
            {{/if}}
          >
            <div>
            {{assign var=coherent_date value=$_injection->isDateCoherent($patient->naissance)}}

            <div class="not-printable injection-header injection-mandatory" style="background: #3b9cc1; text-align: center;
            {{if !$coherent_date || !$_injection->_recall->mandatory || $_injection->_recall->repeat || ($_injection->_id && !in_array($_injection->_id, $vaccinated))}}
              display: none;
            {{/if}}">
              <i class="fas fa-info-circle"></i> {{tr}}Mandatory{{/tr}}
            </div>

            <div class="not-printable injection-header injection-warning"
                 style="
                 {{if $_injection->_recall->repeat || $coherent_date || ($_injection->_id && !in_array($_injection->_id, $vaccinated))}}
                   display: none;
                 {{/if}}">
              <i class="fas fa-exclamation-triangle" title="{{tr}}common-Out of bounds reco{{/tr}}"></i>
            </div>

            <div class="not-printable injection-header injection-error"
                 style="background: #ff2100; text-align: center;
                 {{if !$_injection->_id || $_injection->_recall->repeat || in_array($_injection->_id, $vaccinated)}}
                   display: none;
                 {{/if}}">
              <i class="fas fa-times-circle"></i> {{tr}}No{{/tr}} {{tr}}CVaccination-verb{{/tr}}
            </div>

              {{* If the vaccine is repeatable on this age, make it different to add severall vaccinations *}}
              {{if $_injection->_recall && $_injection->_recall->repeat}}
                <div class="not-printable">
                  <i class="fas fa-plus"></i>
                  <span class="me-color-white">
                      {{if $_injection->_recall->repeat > 1}}
                          {{tr var1=$_injection->_recall->repeat}}Every-n-years{{/tr}}
                      {{else}}
                          {{tr}}Each-year{{/tr}}
                      {{/if}}
                    </span>
                </div>
              {{else}}
                  {{* Otherwise, normal clickable square if there's a recall *}}
                <span class="text">
                  {{if $_injection->_id}}
                    {{if in_array($_injection->_id, $vaccinated)}}
                      {{$_injection->speciality}}<br>
                      {{mb_title class=Ox\Mediboard\Cabinet\Vaccination\CInjection field=batch}}: {{$_injection->batch}}<br>
                    {{/if}}
                    {{if $_injection->remarques}}
                      <i class="fa fa-exclamation-triangle" aria-hidden="true" title="{{tr}}Remarques{{/tr}}"></i>
                        {{$_injection->remarques|spancate:15}}<br>
                    {{/if}}
                  {{/if}}
                  </span>
              {{/if}}
            </div>
          </td>
        {{else}}
            {{if !$wasEmpty}}
              <td></td>
            {{else}}
                {{assign var=wasEmpty value=0}}
            {{/if}}
        {{/if}}
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>
