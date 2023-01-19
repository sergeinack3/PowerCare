{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=plage_selector ajax=$ajax}}
{{mb_script module=cabinet script=rdv_multiples ajax=$ajax}}

<style>
  #plage_list_container > div {
    margin:3px;
    border-radius: 3px;
    box-shadow: 1px 1px 3px black;
  }
</style>

<script>
  var consultationRdV = Class.create ({
    plage_id            : null,
    consult_id          : null,
    date                : null,
    heure               : null,
    _chirview           : null,
    chir_id             : null,
    is_cancelled        : 0,
    rques               : null,
    el_prescrip_id      : null,
    el_prescrip_libelle : null,

    initialize: function (plage_id, consult_id, date, heure, chir_id, chir_view, todelete, rques, el_prescrip_id, el_prescrip_libelle) {
      this.plage_id            = plage_id;
      this.consult_id          = consult_id;
      this.date                = date;
      this.heure               = heure;
      this.chir_id             = chir_id;
      this._chirview           = chir_view;
      this.is_cancelled        = todelete;
      this.rques               = rques;
      this.el_prescrip_id      = el_prescrip_id;
      this.el_prescrip_libelle = el_prescrip_libelle;
    }
  });





  Main.add(function () {
    {{* Calendar.regField(getForm("Filter").date, null, {noView: true}); *}}
    PlageConsultSelector.updatePlage({{$multipleMode}}, null, RDVmultiples.init.curry({{$app->user_prefs.NbConsultMultiple}}, {{$consultation_ids|@json}}, '{{$multipleMode}}'));

    {{if $multipleMode}}
      var form = getForm("Filter");
      if (form.nb_semaines) {
        form.nb_semaines.addSpinner({min: 1});
      }
    {{/if}}

    $('plage_list_container').on("click", '{{if !$multipleMode}}button.validPlage{{else}}input.validPlage{{/if}}', function(event, element) {
      var consult_id          = element.get("consult_id");
      var plage_id            = element.get("plageid");
      var time                = element.get("time");
      var date                = element.get("date");
      var chir_id             = element.get("chir_id");
      var chir_view           = element.get("chir_name");

      var slot_id             = element.get("slot_id");
      var el_prescrip_id      = element.get("consult_element");
      var el_prescrip_libelle = element.get("consult_element_libelle");

      {{if $multipleMode}}
        RDVmultiples.addSlot(slot_id, plage_id, consult_id, date, time, chir_id, chir_view, el_prescrip_id, el_prescrip_libelle);
      {{else}}
        PlageConsultSelector.sendData(plage_id, consult_id, date, time, chir_id, chir_view, el_prescrip_id, el_prescrip_libelle);
      {{/if}}
    });
    {{if $multipleMode}}
      RDVmultiples.switchWeeklytoMonthly();
    {{/if}}
  });


</script>

<form name="Filter" action="?" method="get">
  <input type="hidden" name="m" value="dPcabinet" />
  <input type="hidden" name="a" value="plage_selector" />
  <input type="hidden" name="dialog" value="1" />
  <input type="hidden" name="chir_id" value="{{$chir_id}}" />
  <input type="hidden" name="function_id" value="{{$function_id}}" />
  <input type="hidden" name="plageconsult_id" value="{{$plageconsult_id}}" />
  <input type="hidden" name="_line_element_id" value="{{$_line_element_id}}" />
  <input type="hidden" name="consultation_id" value="{{$consultation_id}}"/>

  <table class="form">
    <tr>
      <!-- planning type -->
      {{me_form_field nb_cells=2 title_label="CPlageConsult-change_period" label="CPlageConsult-planning"}}
        <select name="period" onchange="PlageConsultSelector.updatePlage('{{$multipleMode}}'); PlageConsultSelector.guessNexts('{{$multipleMode}}');">
          {{foreach from=$periods item="_period"}}
          <option value="{{$_period}}" {{if $_period == $period}}selected="selected"{{/if}}>
            {{tr}}Period.{{$_period}}{{/tr}}
          </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}

      <!-- date -->
      <td class="button" style="width: 250px;">
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="$V(getForm('Filter').plageconsult_id, '');" />
      </td>

      <!-- filter -->
      {{me_form_field nb_cells=2 label="CPlageConsult.filter_by_hour" title_label="CPlageConsult.filter_by_hour.title"}}
        <select name="hour" onchange="PlageConsultSelector.updatePlage('{{$multipleMode}}')">
          <option value="">&mdash; {{tr}}common-all|f|pl{{/tr}}</option>
          {{foreach from=$hours item="_hour"}}
            <option value="{{$_hour}}" {{if $_hour == $hour}} selected="selected" {{/if}}>
              {{$_hour|string_format:"%02d h"}}
            </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}

      <td>
        {{if $multipleMode}}
          <button type="button" onclick="Modal.open('repeat_modal'); PlageConsultSelector.updateNbRdvs();" class="change me-tertiary">{{tr}}Repeat{{/tr}}</button>

          <div id="repeat_modal" style="display:none;">
            <div class="small-info">
              {{tr}}CPlageConsult-select_repeat{{/tr}}
            </div>
            <div style="margin: 10px 0 10px 0;">
              <p>
                {{tr}}Repeat{{/tr}}
                <input type="text" name="nb_rdvs" value="1" size="2">
                  {{tr}}rendez-vous{{/tr}}

                <select name="repeat_type">
                  <option value="week">{{tr}}Period.week-adv{{/tr}}</option>
                  <option value="month">{{tr}}Period.month-adv{{/tr}}</option>
                </select>
                {{tr}}common-for{{/tr}}
                <select name="repeat_number">
                  <option value="0">&mdash;</option>
                  {{foreach from=1|range:$app->user_prefs.NbConsultMultiple-1 item=_nb}}
                    <option value="{{$_nb}}">{{$_nb}}</option>
                  {{/foreach}}
                </select>
                <span class="repeat-type-txt">{{tr}}weeks{{/tr}}</span>
              </p>
            </div>
            <div>
              <button type="button" class="tick me-primary" onclick="PlageConsultSelector.guessNexts('{{$multipleMode}}'); Control.Modal.close();">{{tr}}Repeat{{/tr}}</button>
              <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
            </div>
          </div>
        {{/if}}
      </td>

      <!-- hide -->
      <td>
        {{me_form_bool label="Hide_finished|f|pl"}}
          <input type="radio" name="hide_finished" value="0" onclick="PlageConsultSelector.updatePlage('{{$multipleMode}}')" {{if !$hide_finished}}checked="checked" {{/if}} />
          <label for="hide_finished_0">{{tr}}No{{/tr}}</label>
          <input type="radio" name="hide_finished" value="1" onclick="PlageConsultSelector.updatePlage('{{$multipleMode}}')" {{if $hide_finished == "1"}}checked="checked" {{/if}} />
          <label for="hide_finished_1">{{tr}}Yes{{/tr}}</label>
        {{/me_form_bool}}
      </td>

      {{me_form_field nb_cells=2 label="Filter-by_fct"}}
        <select name="_function_id" style="width: 15em;" onchange="PlageConsultSelector.updatePlage('{{$multipleMode}}')">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$listFunctions item=_function}}
            <option value="{{$_function->_id}}" class="mediuser" style="border-color: #{{$_function->color}};" {{if $function_id == $_function->_id}}selected="selected" {{/if}}>
              {{$_function->_view}}
            </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}

      {{if $multipleMode}}
        <td>
          <button type="button" id="consult_multiple_button_validate" class="button tick" onclick="RDVmultiples.sendData(); Control.Modal.close()">{{tr}}Validate{{/tr}}</button>
          <button type="button" class="button erase notext me-tertiary" onclick="RDVmultiples.resetSlots()">{{tr}}CPlageConsult-erase{{/tr}}</button>
          <button type="button" class="help me-tertiary me-dark" onclick="Modal.open('help_consult_multiple');">{{tr}}Help{{/tr}}</button>
        </td>
      {{/if}}
    </tr>
  </table>
</form>


<div id="help_consult_multiple" style="display: none;">
  <button onclick="Control.Modal.close();" class="cancel button me-tertiary" style="float:right;">{{tr}}Close{{/tr}}</button>
  <h2>{{tr}}CPlageConsult.help_consult_multiple{{/tr}}</h2>
  <ul>
    <li>{{tr}}CPlageConsult.consult_multiple_msg_color{{/tr}}</li>
    <li>{{tr}}CPlageConsult.consult_multiple_msg_action_store{{/tr}}</li>
    <li>{{tr}}CPlageConsult.consult_multiple_msg_button_erase{{/tr}}<button type="button" class="erase notext"></button></li>
  </ul>
</div>

<!-- liste des plages -->
<div style="float: left; width: {{if $multipleMode}}28{{else}}49{{/if}}%" id="listePlages"></div>


<!-- liste du contenu des plages -->
{{if $multipleMode}}
  {{if $app->user_prefs.NbConsultMultiple > 4}}
    {{math assign=width equation="24" b=$app->user_prefs.NbConsultMultiple}}
    {{assign var=height value=60}}
  {{else}}
    {{math assign=width equation="(100/b)-1" b=$app->user_prefs.NbConsultMultiple}}
    {{assign var=height value=95}}
  {{/if}}
  {{assign var=nbConsult value=$app->user_prefs.NbConsultMultiple}}
{{else}}
  {{assign var=nbConsult value=1}}
  {{assign var=width value="95"}}
  {{assign var=height value=95}}
{{/if}}

<div id="plage_list_container">
  {{foreach from=1|range:$nbConsult:-1 item=j}}
    <div id="listPlage_dom_{{$j-1}}" data-slot_number="{{$j-1}}" style="width:{{$width}}%; float:left; height: {{$height}}%; overflow-y: auto;" class="plage_rank">
      {{if $multipleMode}}
        <div id="tools_plage_{{$j-1}}" class="tools_plage" style="text-align: center;">
          <button class="button target" onclick="RDVmultiples.selRank('{{$j-1}}')">{{tr}}CConsultation-rdv{{/tr}} {{$j}}</button>
          <button type="button" class="erase notext" onclick="RDVmultiples.removeSlot('{{$j-1}}')"></button>
          <input type="hidden" name="consult_id" value=""/>
          <div id="cancel_plage_{{$j-1}}" style="display: none;">{{tr}}CConsultation-annulation_rdv_msg{{/tr}}</div>
          <div id="discancel_plage_{{$j-1}}" style="display: none;">{{tr}}CConsultation-no_annulation_rdv_msg{{/tr}}</div>
        </div>
      {{/if}}
      <div id="listPlaces-{{$j-1}}" class="listPlace"></div>
    </div>
  {{/foreach}}
</div>

<script>
  ViewPort.SetAvlHeight('plage_list_container', .99);
</script>


<script>
  ViewPort.SetAvlHeight('listePlages', .95);
</script>
