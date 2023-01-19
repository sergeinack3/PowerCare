{{*
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients  script=evenement_patient ajax=true}}
{{mb_script module=oxCabinet script=alertes_event ajax=$ajax}}

{{if 'oxCabinet'|module_active}}
  {{mb_include module=oxCabinet template=inc_vw_event_alerts_counter}}
{{/if}}

<form name="filtreEvtsRappel" method="get" action="?">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  <input type="hidden" name="only_send" value="{{$only_send}}" />

  <table class="form">
    <tr>
      <th>{{tr}}common-Practitioner{{/tr}}</th>
      <td>
        <select name="praticien_id">
          <option value="">&mdash; {{tr}}CMediusers-select-praticien{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$praticien_id list=$praticiens}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=_date_min}}</th>
      <td>{{mb_field object=$filter field=_date_min form="filtreEvtsRappel" register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=_date_max}}</th>
      <td colspan="3">{{mb_field object=$filter field=_date_max form="filtreEvtsRappel" register=true}}</td>
    </tr>

    <tr>
      <th>{{tr}}CTypeEvenementPatient{{/tr}}</th>
      <td>
        <select name="event_type">
          <option value="">-- {{tr}}Choose{{/tr}}</option>

          <optgroup label="{{tr}}CTypeEvenementPatient-Mailing{{/tr}}">
            {{foreach from=$cat_evenements.mailing item=_type}}
              <option value="{{$_type->_id}}" {{if $_type->_id == $event_type_id}}selected{{/if}}>{{$_type}}</option>
            {{/foreach}}
          </optgroup>

          <optgroup label="{{tr}}CTypeEvenementPatient{{/tr}}">
            {{foreach from=$cat_evenements.normal item=_type}}
              <option value="{{$_type->_id}}" {{if $_type->_id == $event_type_id}}selected{{/if}}>{{$_type}}</option>
            {{/foreach}}
          </optgroup>
        </select>
      </td>
    </tr>

    <tr>
      <th></th>
      <td>
        <label>
          <input type="checkbox" name="only_send_view" value="{{$only_send}}" {{if $only_send}}checked{{/if}}
                 onclick="$V(this.form.only_send, this.checked ? 1 : 0);">
          {{tr}}CEvenementPatient-only_send{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button type="button" class="search me-primary" onclick="this.form.submit();">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl" id="list_evenements_container">
  <tr>
    <th colspan="10" class="title">
      <button class="print not-printable notext me-tertiary"
              style="float:right" onclick="$('list_evenements_container').print();" title="{{tr}}Print{{/tr}}"></button>
      {{if $mailing}}
        <button class="send me-float-right"
                type="button"
                onclick="EvtPatient.downloadMail('mailing_send', this)"
                data-event-type-id="{{$event_type_id}}">
          {{tr}}CPatientEventSendMail-Send postal{{/tr}}
        </button>
        <button class="send me-float-right"
                type="button"
                onclick="EvtPatient.sendEmails('mailing_send', this)"
                data-event-type-id="{{$event_type_id}}">
          {{tr}}CPatientEventSendMail-Send email{{/tr}}
        </button>
      {{/if}}
      {{tr}}CEvenementPatient._list_rappel{{/tr}}
    </th>
  </tr>
  <tr>
    <th colspan="10">
      {{mb_include module=system template=inc_pagination total=$total current=$page step=$step change_page="EvtPatient.changePage()"}}
    </th>
  </tr>

  <tbody id="list_events">
  {{mb_include module=patients template=inc_vw_evenements_patient dossier_medical=0 edit_mode=0
  evenements_patient=$evenements use_table=0}}
  </tbody>
</table>