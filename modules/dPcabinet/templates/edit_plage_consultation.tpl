{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}



{{mb_ternary var=max_repeat test=$plageSel->_count_duplicated_plages value=$plageSel->_count_duplicated_plages other=100}}
{{mb_script module=cabinet script=lieu ajax=1}}
{{if "oxCabinet"|module_active}}
  {{mb_script module=oxCabinet script=plage_consultation_tamm ajax=1}}
{{/if}}
{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
  {{mb_script module=appFineClient script=appFineClient ajax=true}}
  {{mb_script module=dPpatients script=exercice_place ajax=1}}
  {{mb_script module=dPpatients script=consultation_categorie ajax=true}}
{{/if}}

<script>
  updateFreq = function(elt) {
    var _val = $V(elt);

    var form = getForm('editFrm');
    var elements = $$('select.pause_duration');
    elements.each(function(element) {
      element.select("option").each(function(option) {
        var val = $(option).readAttribute('value');
        var _str = "";
        var minutes_total = val * _val;
        var _hour = Math.floor(minutes_total / 60);
        var _minutes = minutes_total % 60;
        if (_hour > 0) {
          _str = _str + _hour + "h";
        }
        if (_minutes > 0) {
          _str = _str + " " + _minutes + " min";
        }

        option.update(_str);
      });
    });

    var hour = '{{tr}}hour{{/tr}}';
  };

  addPause = function() {
    var table = $('pauses');
    var rows = $$('tr.pause_row');
    var index = 0;
    if (rows.length > 0) {
      index = parseInt(rows.pop().readAttribute('data-pause-index')) + 1;
    }
    var tbody = DOM.tbody();
    var row = DOM.tr({class: 'pause_row', 'data-pause-index': index});
    tbody.insert(row);
    row.insert(DOM.th(null, DOM.label({
      title: '{{tr}}CPlageconsult-_pauses-desc{{/tr}}',
      for: 'editFrm__pause_hour_' + index
    }, '{{tr}}CPlageconsult-_pauses{{/tr}}')));
    var td_hour = DOM.td();
    var inputPauseId = DOM.input({
      type: 'hidden',
      className: 'num',
      name: '_pause_id_' + index,
      id: 'editFrm__pause_id_' + index,
      value: ''
    });
    td_hour.insert(inputPauseId);
    var inputTime = DOM.input({
      type: 'hidden',
      className: 'time',
      name: '_pause_hour_' + index,
      id: 'editFrm__pause_hour_' + index,
      onchange: 'setPauses();',
      value: '{{$plageSel->debut}}'
    });
    td_hour.insert(inputTime);
    row.insert(td_hour);
    row.insert(DOM.th(null, DOM.label({
      title: '{{tr}}CPlageconsult-_pause_repeat_times-desc{{/tr}}',
      for: 'editFrm__pause_duration_' + index
    }, '{{tr}}CPlageconsult-_pause_repeat_times{{/tr}}')));
    var select = DOM.select({
      name: '_pause_duration_' + index,
      className: 'pause_duration',
      onchange: 'setPauses();',
      id: 'editFrm__pause_duration_' + index
    });
    for (var i = 1; i <= 20; i++) {       //augmenter ici
      select.insert(DOM.option({value: i}, i + 'x'));
    }
    row.insert(DOM.td().insert(select));
    row.insert(DOM.th(null, DOM.label({
      title: '{{tr}}CPlageconsult-_pause_motif-desc{{/tr}}',
      for: 'editFrm__pause_motif_' + index
    }, '{{tr}}CPlageconsult-_pause_motif{{/tr}}')));
    var textarea = DOM.textarea({
      name: '_pause_motif_' + index,
      className: 'pause_motif',
      onchange: 'setPauses();',
      id: 'editFrm__pause_motif_' + index
    });
    row.insert(DOM.td().insert(textarea));
    textarea.setResizable();
    row.insert(DOM.td().insert(DOM.button({
      class: 'cancel notext me-tertiary me-dark',
      type: 'button',
      onclick: 'this.up("tr").remove(); setPauses();',
      title: $T('CPlageConsult.remove_pause')
    })));
    table.insert(tbody);

    Calendar.regField(inputTime, null, {datePicker: false, timePicker: true
      {{if $plageSel->debut}}, minHours:'{{$plageSel->debut|date_format:"%H"}}', maxHours:'{{$plageSel->fin|date_format:"%H"}}'{{/if}}});
    updateFreq(getForm('editFrm')._freq);
    setPauses();
  };

  setPauses = function() {
    var pauses = [];
    $$('tr.pause_row').each(function(row) {
      var index = row.readAttribute('data-pause-index');
      pauses.push({hour: $V($('editFrm__pause_hour_' + index)), duration: $V($('editFrm__pause_duration_' + index)),
        motif: $V($('editFrm__pause_motif_'+index)), pause_id: $V($('editFrm__pause_id_'+index))});
    });

    $V(getForm('editFrm')._pauses, Object.toJSON(pauses));
  }

  modifEtatDesistement = function(valeur){
    if(valeur != 0){
      $('remplacant_plage').setVisible(valeur);
      $$('.remplacement_plage').invoke('setVisible', valeur);
      $$('.retrocession').invoke('setVisible', valeur);
    }
    else{
      var form = getForm('editFrm');
      form.remplacant_id.value = '';
      if(form.pour_compte_id.value == ""){
        $('remplacant_plage').hide();
        $$('.remplacement_plage').invoke('hide');
        $$('.retrocession').invoke('hide');
      }
      else{
        $$('.remplacement_plage').invoke('hide');
      }
    }
  };

  extendPlage = function (plage_id, repetition_type, nb_repeat) {
    if (confirm($T('CPlageConsult-msg-Extend the range over %s weeks of %s type', nb_repeat, repetition_type))) {
      var _update_pauses = $(getForm('editFrm')._update_pauses);
      var url = new Url("cabinet", "controllers/do_extend_plage");
      url.addParam("plage_id", plage_id);
      url.addParam("_type_repeat", repetition_type);
      url.addParam("_repeat", nb_repeat);
      url.addParam("_update_pauses", _update_pauses.checked ? 1 : 0);
      url.addParam("_pauses", $V(getForm('editFrm')._pauses));
      url.requestUpdate("systemMsg");
    }
  };

  reparePlage = function (plage_id, hour_min_valid, hour_max_valid) {
    var form = getForm('editFrm');
    $V(form.debut, hour_min_valid);
    $V(form.fin, hour_max_valid);
    $V(form.dosql, 'do_plageconsult_aed');
    return onSubmitFormAjax(form, function() {$V(form.dosql, 'do_plageconsult_multi_aed');PlageConsultation.url.refreshModal();});
  };

  modifPourCompte = function(valeur){
    if(valeur != 0){
      $('remplacant_plage').setVisible(valeur);
      $$('.retrocession').invoke('setVisible', valeur);
    }
    else{
      var form = getForm('editFrm');
      if(form.desistee.value == 0){
        $('remplacant_plage').hide();
      }
    }
  };

  Main.add(function(){
    var form = getForm('editFrm');

    {{if !$can->admin && $plageSel->_id && !$plageSel->_canEdit}}
      makeReadOnly(form);
    {{/if}}


    updateFreq(form._freq);

    Calendar.regField(form.debut);
    Calendar.regField(form.fin  );

    var pauses = $$('input.pause_hour');
    pauses.each(function(pause) {
      Calendar.regField(pause, null, {datePicker: false, timePicker: true
        {{if $plageSel->debut}}, minHours:'{{$plageSel->debut|date_format:"%H"}}', maxHours:'{{$plageSel->fin|date_format:"%H"}}'{{/if}}});
    });
    setPauses();

    {{if $plageSel->_id && $plageSel->_count_duplicated_plages != 0}}
      form._repeat.addSpinner({min: 0});
    {{/if}}

    {{if $chirSel !== null && $chirSel !== ""}}
      Lieu.loadLieuxByPrat($V(form.plageconsult_id), $V(form.chir_id));
      {{if "oxCabinet"|module_active}}
        PlageConsultationTamm.showSelectFunction($V(form.chir_id), $V(form.plageconsult_id));
      {{/if}}

      // Lieu AppFine - Prise RDV
      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
        ExercicePlace.loadExericePlaceByPrat($V(form.plageconsult_id), $V(form.chir_id));
      {{/if}}
    {{/if}}
  });
</script>

<form name='editFrm' action='?m=dPcabinet' method='post' onsubmit="this._type_repeat.disabled = ''; return PlageConsultation.checkForm(this, {{$modal}});">
  <input type='hidden' name='m' value='dPcabinet' />
  <input type='hidden' name='dosql' value='do_plageconsult_multi_aed' />
  <input type='hidden' name='del' value='0' />
  <input type='hidden' name='modal' value='{{$modal}}' />
  {{if "appFineClient"|module_active}}
    <input type="hidden" id="consultation_categorie_ids" name="_consultation_categorie_ids">
  {{/if}}
  {{mb_key object=$plageSel}}

  <table class="form">
    {{if "3333tel"|module_active}}
      <tr>
        <td colspan="2" class="me-padding-1">
          {{mb_include module=3333tel template=inc_check_3333tel object=$plageSel}}
        </td>
      </tr>
    {{/if}}
    {{mb_include module=system template=inc_form_table_header object=$plageSel colspan=2}}
    <tr>
      <td colspan="2" class="me-padding-top-0 me-padding-bottom-0">
        <fieldset class="me-margin-top-0 me-margin-bottom-0">
          <legend>{{tr}}CPlageConsult.infos{{/tr}}</legend>
          <table class="form me-no-box-shadow me-no-align">
            {{if $plageSel->function_id && !"oxCabinet"|module_active}}
              <tr>
                {{me_form_field nb_cells=4 mb_object=$plageSel mb_field="function_id" animated=false}}
                  <div class="me-field-content">
                      {{mb_value object=$plageSel field="function_id"}}
                  </div>
                {{/me_form_field}}
              </tr>
            {{/if}}
            <tr>
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="chir_id"}}
                <select name="chir_id" class="{{$plageSel->_props.chir_id}}" style="width: 15em;"
                        onchange="Lieu.loadLieuxByPrat($V(this.form.plageconsult_id), this.value);
                        {{if "oxCabinet"|module_active}}
                          PlageConsultationTamm.showSelectFunction(this.value, $V(this.form.plageconsult_id));
                        {{/if}}
                        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
                          ExercicePlace.loadExericePlaceByPrat($V(this.form.plageconsult_id), this.value);
                        {{/if}}
                         ">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$listChirs selected=$chirSel}}
                </select>
              {{/me_form_field}}
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="libelle"}}
                {{mb_field object=$plageSel field="libelle" style="width: 15em;"}}
              {{/me_form_field}}
            </tr>
            <tr id="lieux">
            </tr>
            <tr>
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="date" label=CPlageconsult-_jour_semaine}}
                <select name="date" class="{{$plageSel->_props.date}}" style="width: 15em;">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$listDaysSelect item=curr_day}}
                    <option value="{{$curr_day}}"
                      {{if ($curr_day == $plageSel->date) || (!$plageSel->_id && $curr_day == $debut)}}selected{{/if}}
                      {{if !$plageSel->_id && array_key_exists($curr_day, $holidays) && !$allow_plage_holiday}}disabled{{/if}}
                    >
                      {{$curr_day|date_format:"%A"}} {{if array_key_exists($curr_day, $holidays)}}({{tr}}common-holiday{{/tr}}){{/if}}
                    </option>
                  {{/foreach}}
                </select>
              {{/me_form_field}}

              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="color"}}
                {{mb_field object=$plageSel field="color" form=editFrm}}
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="debut"}}
                {{mb_field object=$plageSel field="debut"}}
              {{/me_form_field}}

              {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="locked" label_prefix="*"}}
              {{mb_field object=$plageSel field="locked" typeEnum="checkbox"}}
              {{/me_form_bool}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="fin"}}
                {{mb_field object=$plageSel field="fin"}}
              {{/me_form_field}}

              {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="pour_tiers" label_prefix="*"}}
              {{mb_field object=$plageSel field="pour_tiers" typeEnum=checkbox}}
              {{/me_form_bool}}
            </tr>
            <tr>
              {{if $plageSel->_ref_consultations|@count == 0}}
                {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="_freq" field_class="me-field-max-w25"}}
                  <select name="_freq" onchange="updateFreq(this);">
                    <option value="05" {{if ($selected_freq == "05")}} selected="selected" {{/if}}>05</option>
                    <option value="10" {{if ($selected_freq == "10")}} selected="selected" {{/if}}>10</option>
                    <option value="15" {{if ($selected_freq == "15") || (!$plageSel->_id)}} selected="selected" {{/if}}>15</option>
                    <option value="20" {{if ($selected_freq == "20")}} selected="selected" {{/if}}>20</option>
                    <option value="30" {{if ($selected_freq == "30")}} selected="selected" {{/if}}>30</option>
                    <option value="45" {{if ($selected_freq == "45")}} selected="selected" {{/if}}>45</option>
                    <option value="60" {{if ($selected_freq == "60")}} selected="selected" {{/if}}>60</option>
                  </select> min
                {{/me_form_field}}
              {{else}}
                  <td colspan="2" style="font-size: 12px">
                      {{mb_label object=$plageSel field=_freq}} :{{mb_value object=$plageSel field=_freq}} min
                  </td>
              {{/if}}

              {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="_skip_collisions"}}
              {{mb_field object=$plageSel field="_skip_collisions" typeEnum=checkbox}}
              {{/me_form_bool}}
            </tr>
            {{if "oxCabinet"|module_active}}
              <tr id="function">
              </tr>
            {{/if}}
            <tr>
              <td class="text button" colspan="4">
                {{if $plageSel->_affected}}
                  {{tr var1=$plageSel->_affected var2=$_firstconsult_time var3=$_lastconsult_time}}CPlageconsult-Already %s planned consultation from %s to %s|pl{{/tr}}
                {{/if}}
                <input type='hidden' name='nbaffected' value='{{$plageSel->_affected}}' />
                <input type='hidden' name='_firstconsult_time' value='{{$_firstconsult_time}}' />
                <input type='hidden' name='_lastconsult_time' value='{{$_lastconsult_time}}' />
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td{{if !"notifications"|module_active}} colspan="2"{{/if}} class="me-valign-top">
        <fieldset>
          <legend>
            <label><input class="me-margin-right-8" type="checkbox" name="_update_pauses" value="1" {{if !empty($plageSel->_pause_ids|smarty:nodefaults)}}checked{{/if}} onchange="$('pauses').toggle(); $('add_pause').toggle();" />{{tr}}CPlageConsult.pauses{{/tr}}</label>
            <button id="add_pause" class="add notext me-tertiary" type="button" onclick="addPause();" {{if empty($plageSel->_pause_ids|smarty:nodefaults)}}style=" display: none;"{{/if}}>{{tr}}CPlageConsult.add_pause{{/tr}}</button>
          </legend>
          <input type="hidden" name="_pauses" value="{{$plageSel->_pauses|@json}}" />
          <table class="form me-no-box-shadow" id="pauses" {{if empty($plageSel->_pause_ids|smarty:nodefaults)}}style="display: none;"{{/if}}>
            {{if empty($plageSel->_pause_ids|smarty:nodefaults)}}
              <tbody>
                <tr data-pause-index="0" class="pause_row">
                  <th>
                    <label for="editFrm__pause_hour_0" title="{{tr}}CPlageconsult-_pauses-desc{{/tr}}">
                      {{tr}}CPlageconsult-_pauses{{/tr}}
                    </label>
                  </th>
                  <td>
                    <input type="hidden" class="num" name="_pause_id_0" value=""/>
                    <input type="hidden" class="time pause_hour" name="_pause_hour_0" value="{{$plageSel->debut}}" onchange="setPauses();"/>
                  </td>
                  <th>
                    <label for="editFrm__pause_duration_0" title="{{tr}}CPlageconsult-_pause_repeat_times-desc{{/tr}}">
                      {{tr}}CPlageconsult-_pause_repeat_times{{/tr}}
                    </label>
                  </th>
                  <td>
                    <select class="pause_duration" name="_pause_duration_0" onchange="setPauses();">
                      {{foreach from=1|range:20 item=i}}
                        <option value="{{$i}}">{{$i}}x</option>
                      {{/foreach}}
                    </select>
                  </td>
                  <th>
                    <label for="editFrm__pause_motif_0" title="{{tr}}CPlageconsult-_pause_motif-desc{{/tr}}">
                      {{tr}}CPlageconsult-_pause_motif{{/tr}}
                    </label>
                  </th>
                  <td>
                    <textarea class="pause_motif" name="_pause_motif_0" onchange="setPauses();"></textarea>
                  </td>
                  <td class="narrow">
                    <button class="cancel notext me-tertiary me-dark" type="button" onclick="this.up('tr').remove(); setPauses();">{{tr}}CPlageConsult.remove_pause{{/tr}}</button>
                  </td>
                </tr>
              </tbody>
            {{else}}
              {{foreach from=$plageSel->_pauses key=_index item=_pause}}
                <tbody>
                  <tr data-pause-index="{{$_index}}" class="pause_row">
                    <th>
                      <label for="editFrm__pause_hour_{{$_index}}" title="{{tr}}CPlageconsult-_pauses-desc{{/tr}}">
                        {{tr}}CPlageconsult-_pauses{{/tr}}
                      </label>
                    </th>
                    <td>
                      <input type="hidden" class="num" name="_pause_id_{{$_index}}" value="{{$_pause.pause_id}}"/>
                      <input type="hidden" class="time pause_hour" name="_pause_hour_{{$_index}}" value="{{$_pause.hour}}" onchange="setPauses();"/>
                    </td>
                    <th>
                      <label for="editFrm__pause_duration_{{$_index}}" title="{{tr}}CPlageconsult-_pause_repeat_times-desc{{/tr}}">
                        {{tr}}CPlageconsult-_pause_repeat_times{{/tr}}
                      </label>
                    </th>
                    <td>
                      <select class="pause_duration" name="_pause_duration_{{$_index}}" onchange="setPauses();">
                        {{foreach from=1|range:20 item=i}}
                          <option {{if $_pause.duration == $i}}selected{{/if}} value="{{$i}}">{{$i}}x</option>
                        {{/foreach}}
                      </select>
                    </td>
                    <th>
                      <label for="editFrm__pause_motif_{{$_index}}" title="{{tr}}CPlageconsult-_pause_motif-desc{{/tr}}">
                        {{tr}}CPlageconsult-_pause_motif{{/tr}}
                      </label>
                    </th>
                    <td>
                      <textarea class="pause_motif" name="_pause_motif_{{$_index}}" onchange="setPauses();">{{$_pause.motif}}</textarea>
                    </td>
                    <td class="narrow">
                      <button class="cancel notext me-tertiary me-dark" type="button" onclick="this.up('tr').remove(); setPauses();">{{tr}}CPlageConsult.remove_pause{{/tr}}</button>
                    </td>
                  </tr>
                </tbody>
              {{/foreach}}
            {{/if}}
          </table>
        </fieldset>
      </td>
      {{if "notifications"|module_active}}
        <td class="narrow me-valign-top">
          <fieldset>
            <legend>{{tr}}module-notifications-court{{/tr}}</legend>
            <table class="form me-no-box-shadow">
              <tr>
                {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="send_notifications"}}
                  {{mb_field object=$plageSel field=send_notifications}}
                {{/me_form_bool}}
              </tr>
            </table>
          </fieldset>
        </td>
      {{/if}}
    </tr>
    <tr>
      <td colspan="2">
        <fieldset>
          <legend>{{tr}}CPlageConsult.repetition{{/tr}}</legend>
          <table class="form me-no-box-shadow me-no-align">
            {{if $plageSel->_id && $plageSel->_count_duplicated_plages == 0}}
              <tr>
                <td colspan="3">
                  <div class="small-info">
                    {{tr}}CPlageConsult.warning_repetition{{/tr}}
                  </div>
                </td>
              </tr>
            {{/if}}

              <tr>
                {{me_form_field nb_cells=2 label="CPlageConsult.repetition_nb_week" title_label="CPlageConsult.repetition_nb_week.long" field_class="me-input-field-max-w50"}}
                  <input type="text" size="2" name="_repeat" value="1"
                         onchange="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';"
                         onKeyUp="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';" />
                  {{if $plageSel->_count_duplicated_plages}}
                  (max. modifiables: {{$max_repeat+1}})
                  {{/if}}
                {{/me_form_field}}

                <td rowspan="3" class="text">
                  {{if $plageSel->_count_duplicated_plages}}
                  <div class="small-info">
                    {{tr}}CPlageConsult._count_duplicated_plages{{/tr}}
                  </div>
                  {{/if}}
                </td>
              </tr>
              {{if $plageSel->_id && $plageSel->_count_duplicated_plages}}
                <tr>
                  {{me_form_field nb_cells=2 label="CPlageConsult.similar_plage" animated=false}}
                    <div class="me-field-content">
                        {{$max_repeat}}
                    </div>
                  {{/me_form_field}}
                </tr>
              {{/if}}
              <tr>
                {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="_type_repeat"}}
                  {{mb_field object=$plageSel field="_type_repeat" style="width: 15em;" typeEnum="select" disabled="disabled"}}
                {{/me_form_field}}
              </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <fieldset>
          <legend>{{tr}}CPlageConsult.remplacement{{/tr}}</legend>
          <table class="form me-no-box-shadow me-no-align">
            <tr>
              {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="desistee"}}
                {{mb_field object=$plageSel field="desistee"  typeEnum="checkbox" onchange="modifEtatDesistement(this.value);" }}
              {{/me_form_bool}}

              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="pour_compte_id"}}
                <select name="pour_compte_id" style="width: 15em;"  onchange="modifPourCompte(this.value);">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$listChirs selected=$plageSel->pour_compte_id disabled=$chirSel}}
                </select>
              {{/me_form_field}}
            </tr>
            <tr id="remplacant_plage" {{if !$plageSel->desistee && !$plageSel->pour_compte_id}} style="display:none"{{/if}}>
              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="remplacant_id" field_class="remplacement_plage"}}
                <select name="remplacant_id" style="width: 15em;{{if !$plageSel->desistee}}display:none;{{/if}}" class="remplacement_plage">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$listChirs selected=$plageSel->remplacant_id }}
                </select>
              {{/me_form_field}}

              {{me_form_field nb_cells=2 mb_object=$plageSel mb_field="pct_retrocession" field_class="retrocession"}}
                <span class="retrocession">
                  {{mb_field object=$plageSel field="pct_retrocession" size="2" increment=true form=editFrm  class="retrocession"}}
                </span>
              {{/me_form_field}}
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    {{if "appFineClient"|module_active}}
      <tr>
        <td colspan="2">
          <fieldset>
            <legend>{{tr}}AppFine{{/tr}}</legend>
            <table class="form me-no-box-shadow me-no-align">
              <tr>
              {{if "appFineClient Sync allow_appfine_sync"|gconf}}
                <td>
                    {{me_form_bool mb_object=$plageSel mb_field="sync_appfine" label_prefix="*"}}
                    {{if "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
                        {{mb_field object=$plageSel field=sync_appfine typeEnum="checkbox"}}
                    {{else}}
                        {{mb_field object=$plageSel field=sync_appfine typeEnum="checkbox" readonly=true}}
                    {{/if}}
                    {{/me_form_bool}}
                </td>

                <tr>
                  <th>{{mb_label object=$plageSel field="exercice_place_id"}}</th>
                  {{if $app->user_prefs.allow_appfine_sync}}
                    <td id="exercice_places"></td>
                  {{else}}
                    <td>
                      <div class="small-info">{{tr}}CAppFineClient-msg-You have no right to access{{/tr}}</div>
                    </td>
                  {{/if}}
                </tr>
                {{if $app->user_prefs.allow_appfine_sync}}
                  <tr id="consultation_categories"></tr>
                {{else}}
                  <tr>
                    <td>
                      <div class="small-info">{{tr}}CAppFineClient-msg-You have no right to access{{/tr}}</div>
                    </td>
                  </tr>
                {{/if}}
              {{/if}}
              </tr>
                {{if "teleconsultation"|module_active && $allow_consultation}}
                <tr>
                    {{me_form_bool nb_cells=2 mb_object=$plageSel mb_field="eligible_teleconsultation" label_prefix="*"}}
                    {{mb_field object=$plageSel field="eligible_teleconsultation" typeEnum="checkbox"}}
                    {{/me_form_bool}}
                </tr>
                {{/if}}
              <tr>
                <td>
                    {{me_form_field mb_object=$plageSel mb_field="nb_places"}}
                    {{mb_field object=$plageSel field="nb_places"}}
                    {{/me_form_field}}
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
      </tr>
    {{/if}}
  </table>

  <table class="form">
    <tr>
      {{if !$plageSel->_id}}
        <td class="button" colspan="4">
          <button id="edit_plage_consult_button_create_new_plage" class="submit">{{tr}}Create{{/tr}}</button>
        </td>
      {{else}}
      <td class="button" colspan="4">
        <button type="submit" class="modify" id="edit_plage_consult_button_modify_plage">{{tr}}Modify{{/tr}}</button>
        <button class="trash" type='button'  id="edit_plage_consult_button_delete_plage"
          onclick="
              confirmDeletion(this.form, {
            typeName: $T('CPlageConsult-msg-The consultation range of'),
            objName:'{{$plageSel->date|date_format:$conf.longdate}}',
            {{if $modal}}
              ajax: 1
            {{else}}
              callback: function() {
                var form = getForm('editFrm');
                form._type_repeat.disabled = '';
                form.submit();
              }
            {{/if}}
              }
          {{if $modal}},
              {onComplete: Control.Modal.close}
            {{/if}})">
          {{tr}}Delete{{/tr}}
        </button>
        <button type="button" id="edit_plage_consult_button_extend_plage" class="button add me-secondary" onclick="extendPlage('{{$plageSel->_id}}', $V(this.form._type_repeat), $V(this.form._repeat) );">
          {{tr}}Extend{{/tr}}
        </button>
        {{if !$plageSel->_hours_limit_valid}}
          <button type="button" class="reboot me-tertiary" title="{{tr}}Repair{{/tr}} {{tr}}CPlageConsult{{/tr}}"
             onclick="reparePlage('{{$plageSel->_id}}', '{{$plageSel->_hour_min_valid}}', '{{$plageSel->_hour_max_valid}}')" {{if !$plageSel->_can->edit}}disabled="disabled"{{/if}}>
            {{tr}}Repair{{/tr}}
          </button>
        {{/if}}
      </td>
      {{/if}}
    </tr>
  </table>
</form>
