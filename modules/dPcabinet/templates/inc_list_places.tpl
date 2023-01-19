{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=heure value=null}}
{{mb_default var=multipleMode value=0}}

{{if $online && !$plage->locked}}

  <script>
    addPlaceBefore_{{$slot_id}} = function(plage_id, slot_id, consult_id) {
      var form = getForm("editPlage-"+plage_id+"-"+slot_id);
      var date = new Date();
      date.setHours({{$plage->debut|date_format:"%H"}});
      date.setMinutes({{$plage->debut|date_format:"%M"}} - {{$plage->freq|date_format:"%M"}});
      date.setSeconds({{$plage->debut|date_format:"%S"}});
      form.debut.value = printf('%02d:%02d:%02d',date.getHours(), date.getMinutes(), date.getSeconds());
      return onSubmitFormAjax(form, function() { RDVmultiples.refreshSlot(slot_id, plage_id, consult_id, "{{$multipleMode}}"); });
    };

    addPlaceAfter_{{$slot_id}} = function(plage_id, slot_id, consult_id) {
      var form = getForm("editPlage-"+plage_id+"-"+slot_id);
      var date = new Date();
      date.setHours({{$plage->fin|date_format:"%H"}});
      date.setMinutes({{$plage->fin|date_format:"%M"}} + {{$plage->freq|date_format:"%M"}});
      date.setSeconds({{$plage->fin|date_format:"%S"}});
      form.fin.value = printf('%02d:%02d:%02d', date.getHours(), date.getMinutes(), date.getSeconds());
      return onSubmitFormAjax(form, function() {RDVmultiples.refreshSlot(slot_id, plage_id, consult_id, "{{$multipleMode}}"); });
    };

    reparePlage_{{$slot_id}} = function(plage_id, slot_id, hour_min_valid, hour_max_valid) {
      var form = getForm("editPlage-"+plage_id+"-"+slot_id);
      $V(form.debut, hour_min_valid);
      $V(form.fin, hour_max_valid);
      return onSubmitFormAjax(form, function() {RDVmultiples.refreshSlot(slot_id, plage_id, null, "{{$multipleMode}}"); });
    };

    Main.add(function() {
      //multiple edit init
      {{if $consultation->_id}}
        var dom = $("Places_{{$plage->_id}}");
        var consult_target = dom.up().up().down("input[name='consult_id']");
        $V(consult_target,'{{$consultation->_id}}');
      {{/if}}
      });
  </script>

  <form action="?m=dPcabinet" method="post" name="editPlage-{{$plage->_id}}-{{$slot_id}}" onsubmit="return checkForm(this);">
    <input type="hidden" name="m" value="dPcabinet" />
    <input type="hidden" name="dosql" value="do_plageconsult_aed" />
    <input type="hidden" name="plageconsult_id" value="{{$plage->_id}}" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="debut" value="{{$plage->debut}}" />
    <input type="hidden" name="fin" value="{{$plage->fin}}" />
    <input type="hidden" name="chir_id" value="{{$plage->chir_id}}" />
    <input type="hidden" name="_repeat" value="1" />
  </form>
{{/if}}

<table class="tbl me-no-box-shadow me-no-align" id="Places_{{$plage->_id}}">
  {{assign var=display_nb_consult value="dPcabinet PriseRDV display_nb_consult"|gconf}}
  {{if $plage->_id}}
    <tr>
      <th colspan="
      {{if !$multipleMode}}
        {{if $display_nb_consult}}5{{else}}3{{/if}}
      {{else}}
        {{if $display_nb_consult}}4{{else}}2{{/if}}
      {{/if}}">
        {{if $online}}
          {{*mb_include module=system template=inc_object_notes object=$plage*}}
        <select name="chir_id" onchange="PlageConsultSelector.changePlageChir($V(this), '{{$plage->date}}', {{$multipleMode}}); return false;">
          {{foreach from=$list_users item=_user}}
            <option value="{{$_user->_id}}" {{if $plage->chir_id == $_user->_id}}selected="selected" {{/if}}>{{$_user}}</option>
          {{/foreach}}
        </select>
        <br />
        {{/if}}
        {{if $online && $consultation->_id}}
            {{me_img src="edit.png" icon="edit" class="me-primary"}}
        {{/if}}
        {{if !$multipleMode}}
          {{tr var1=$plage->date|date_format:$conf.longdate
                var2=$plage->debut|date_format:$conf.time
                var3=$plage->fin|date_format:$conf.time}}
            CPlageHoraire-of %s from %s to %s
          {{/tr}}
        {{else}}
          {{if $previous_plage->_id}}<button class="left notext me-tertiary me-dark" style="float:left;" onclick="PlageConsultSelector.previous_plage('{{$previous_plage->_id}}', this);">{{tr}}CPlageHoraire-previous_plage{{/tr}} ({{$previous_plage}})</button>{{/if}}
          {{if $next_plage->_id}}<button class="right notext me-tertiary me-dark" style="float:right;" onclick="PlageConsultSelector.next_plage('{{$next_plage->_id}}', this);">{{tr}}CPlageHoraire-next_plage{{/tr}} ({{$next_plage}})</button>{{/if}}
          {{$plage->date|date_format:"%a %d %b"}}
        {{/if}}
        {{if $plage->_ref_agenda_praticien->sync}}
          <div class="small-warning">{{tr}}CPlageconsult-synchronized{{/tr}}</div>
        {{/if}}
        {{if $online && !$plage->_hours_limit_valid}}
          <button type="button" class="reboot me-tertiary" style="float:right;" title="Réparer la plage de consultation"
                  onclick="reparePlage_{{$slot_id}}('{{$plage->_id}}', '{{$slot_id}}', '{{$plage->_hour_min_valid}}', '{{$plage->_hour_max_valid}}')" {{if !$plage->_can->edit}}disabled="disabled"{{/if}}>
            {{tr}}Repair{{/tr}}
          </button>
        {{/if}}
      </th>
    </tr>
    <tr>
      <th class="narrow" {{if $online && !$multipleMode}}rowspan="2"{{/if}}>{{tr}}common-Hour{{/tr}}</th>
      <th {{if $online && !$multipleMode}}rowspan="2"{{/if}}>{{tr}}CPatient{{/tr}}</th>
      {{if $display_nb_consult != "none" && $online && !$multipleMode}}
        <th colspan="{{if $display_nb_consult == "cab"}}2{{else}}3{{/if}}" class="narrow">{{tr}}CPlageConsult-occupation{{/tr}}</th>
      {{/if}}
    </tr>
    {{if $online && !$multipleMode}}
      <tr>
        {{if $display_nb_consult == "cab" || $display_nb_consult == "etab"}}
          <th>{{tr}}CPlageConsult-occupation_cab{{/tr}}</th>
        {{/if}}
        {{if $display_nb_consult == "etab"}}
          <th>{{tr}}CPlageConsult-occupation_etab{{/tr}}</th>
        {{/if}}
      </tr>
    {{/if}}
  {{else}}
    <tr>
      <th colspan="{{if $display_nb_consult}}5{{else}}3{{/if}}">Pas de plage le {{$date|date_format:$conf.longdate}}</th>
    </tr>
  {{/if}}

  {{foreach from=$listBefore item =_consultation}}
    <tr>
      <td>
        <div style="float:left">
          {{$_consultation->heure|date_format:$conf.time}}
        </div>
        <div style="float:right">
          {{if $_consultation->categorie_id}}
            {{mb_include module=cabinet template=inc_icone_categorie_consult
              consultation=$_consultation categorie=$_consultation->_ref_categorie patient=$_consultation->_ref_patient
            }}
          {{/if}}
        </div>
      </td>
      <td>
        {{if !$_consultation->patient_id}}
          {{assign var="style" value="style='background: #ffa;'"}}
        {{elseif $_consultation->premiere}}
          {{assign var="style" value="style='background: #faa;'"}}
        {{elseif $_consultation->derniere}}
          {{assign var="style" value="style='background: #faf;'"}}
        {{else}}
          {{assign var="style" value=""}}
        {{/if}}
        <div {{$style|smarty:nodefaults}}>
          {{$_consultation->patient_id|ternary:$_consultation->_ref_patient:"[PAUSE]"}}
          {{if $_consultation->duree > 1}}
            x{{$_consultation->duree}}
          {{/if}}
          {{if $_consultation->motif}}
            <div class="compact">
              {{mb_value object=$_consultation field=motif}}
            </div>
          {{/if}}
        </div>
      </td>
      {{if $display_nb_consult}}<td colspan="3"></td>{{/if}}
    </tr>
  {{/foreach}}

  {{if $online && !$plage->locked}}
    <tr>
      <td class="button" colspan="{{if $display_nb_consult}}4{{else}}3{{/if}}">
        <button type="button" class="up singleclick me-tertiary" onclick="addPlaceBefore_{{$slot_id}}('{{$plage->_id}}', '{{$slot_id}}' ,'{{$consultation->_id}}')" {{if !$plage->_can->edit}}disabled="disabled"{{/if}}>
          {{tr}}CPlageHoraire-add_before{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}

  {{foreach from=$listPlace item=_place}}
    {{assign var=count_places value=$_place.consultations|@count}}
    <tr {{if $online && ($_place.time == $consultation->heure)}}class="selected"{{/if}}>
      <td>
        {{if $count_places> 1}}
          <i class="fas fa-exclamation-triangle me-color-primary" title="surbooking : {{$count_places}} patients" style="float:right;"></i>
        {{/if}}
        <div style="float:left">
          {{if $heure && $_place.time == $heure}}
          <script>
            Main.add(function() {
              RDVmultiples.addSlot('{{$slot_id}}', '{{$plage->_id}}', '{{$consultation->_id}}', '{{$plage->date}}', '{{$_place.time}}',
                                   '{{$plage->chir_id}}', '{{$plage->_ref_chir}}', '{{$consultation->element_prescription_id}}',
                                   '{{$consultation->_ref_element_prescription->libelle}}');
            });
          </script>
          {{/if}}

          <label>
            {{if $online && !$plage->locked && ("dPcabinet CConsultation surbooking_readonly"|gconf || $plage->_can->edit || $count_places == 0)}}
              {{if !$multipleMode}}
                 <button type="button" {{if $plage->_ref_agenda_praticien->sync}}disabled{{else}}class="tick validPlage"{{/if}}
              {{else}}
                <input type="radio" name="checkbox-{{$plage->_id}}-{{$slot_id}}" {{if $heure && $_place.time == $heure}}checked="checked"{{/if}} {{if $plage->_ref_agenda_praticien->sync}}disabled{{else}} class="validPlage"{{/if}}
              {{/if}}
                data-consult_id="{{$consultation->_id}}"
                data-chir_name="{{$plage->_ref_chir}}"
                data-plageid="{{$plage->_id}}"
                data-date="{{$plage->date}}"
                data-chir_id="{{$plage->chir_id}}"
                data-time="{{$_place.time}}"
                data-slot_id="{{$slot_id}}"
                data-consult_element="{{$consultation->element_prescription_id}}"
                data-consult_element_libelle="{{if $consultation->_ref_element_prescription}}{{$consultation->_ref_element_prescription->libelle}}{{/if}}"
              {{if !$multipleMode}}
                  >
              {{else}}
                  />{{if $plage->_ref_agenda_praticien->sync}}<i class="me-icon lock me-primary" style="height: 12px; width: 12px;"></i>{{/if}}
              {{/if}}
              {{$_place.time|date_format:$conf.time}}
              {{if !$multipleMode}}
                {{if $plage->_ref_agenda_praticien->sync}}<i class="me-icon lock me-primary" style="height: 12px; width: 12px;"></i>{{/if}}</button>
              {{/if}}
            {{else}} <!-- not online or locked or surbooking -->
              {{$_place.time|date_format:$conf.time}}
            {{/if}}
          </label>
        </div>
      </td>
      <td class="text">
        {{foreach from=$_place.consultations item=_consultation}}
          {{if !$_consultation->patient_id}}
            {{assign var="style" value="style='background: #ffa;'"}}
          {{elseif $_consultation->premiere}}
            {{assign var="style" value="style='background: #faa;'"}}
          {{elseif $_consultation->derniere}}
            {{assign var="style" value="style='background: #faf;'"}}
          {{else}}
            {{assign var="style" value=""}}
          {{/if}}
          <div {{$style|smarty:nodefaults}}>
            {{$_consultation->patient_id|ternary:$_consultation->_ref_patient:"[PAUSE]"}} {{if $_consultation->annule}}<span style="color:red;">({{tr}}CConsultation-annule-upper{{/tr}})</span>{{/if}}
            {{if $_consultation->duree > 1}}
              x{{$_consultation->duree}}
            {{/if}}
            {{assign var=categorie value=$_consultation->_ref_categorie}}
            {{if $categorie->_id}}
              <div class="compact">
                {{mb_include module=cabinet template=inc_icone_categorie_consult
                  consultation=$_consultation
                  categorie=$categorie
                  patient=$_consultation->_ref_patient
                  display_name=true
                }}
              </div>
            {{/if}}
            {{if $_consultation->motif}}
              <div class="compact">
                {{$_consultation->motif|spancate}}
              </div>
            {{/if}}
            {{if $_consultation->rques}}
              <div class="compact">
                {{$_consultation->rques|spancate}}
              </div>
            {{/if}}
          </div>
        {{/foreach}}
      </td>
      {{if $online && !$multipleMode}}
        {{assign var=time value=$_place.time}}
        {{if $display_nb_consult == "cab" || $display_nb_consult == "etab"}}
          <td>
            {{mb_include module=cabinet template=inc_vw_jeton nb=$utilisation_func.$time quotas=$quotas}}
          </td>
        {{/if}}
        {{if $display_nb_consult == "etab"}}
          <td>
            {{mb_include module=cabinet template=inc_vw_jeton nb=$utilisation_etab.$time}}
          </td>
        {{/if}}
      {{/if}}
    </tr>
  {{/foreach}}

  {{if $online && !$plage->locked}}
    <tr>
      <td class="button" colspan="{{if $display_nb_consult}}5{{else}}3{{/if}}">
        <button type="button" class="down singleclick me-tertiary" onclick="addPlaceAfter_{{$slot_id}}('{{$plage->_id}}', '{{$slot_id}}' ,'{{$consultation->_id}}')" {{if !$plage->_can->edit}}disabled="disabled"{{/if}}>
          {{tr}}CPlageHoraire-add_after{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
  {{foreach from=$listAfter item =_consultation}}
    <tr>
      <td>
        <div style="float: left;">
          {{$_consultation->heure|date_format:$conf.time}}
        </div>
        <div style="float: right;">
          {{if $_consultation->categorie_id}}
            {{mb_include module=cabinet template=inc_icone_categorie_consult
              consultation=$_consultation
              categorie=$_consultation->_ref_categorie
              patient=$_consultation->_ref_patient
            }}
          {{/if}}
        </div>
      </td>
      <td>
        {{if !$_consultation->patient_id}}
          {{assign var="style" value="style='background: #ffa;'"}}
        {{elseif $_consultation->premiere}}
          {{assign var="style" value="style='background: #faa;'"}}
        {{elseif $_consultation->derniere}}
          {{assign var="style" value="style='background: #faf;'"}}
        {{else}}
          {{assign var="style" value=""}}
        {{/if}}
        <div {{$style|smarty:nodefaults}}>
          {{$_consultation->patient_id|ternary:$_consultation->_ref_patient:"[PAUSE]"}}
          {{if $_consultation->duree > 1}}
            x{{$_consultation->duree}}
          {{/if}}
          {{if $_consultation->motif}}
            <div class="compact">
              {{$_consultation->motif|spancate}}
            </div>
          {{/if}}
        </div>
      </td>
      <td {{if $display_nb_consult}}colspan="3"{{/if}}></td>
    </tr>
  {{/foreach}}
</table>
