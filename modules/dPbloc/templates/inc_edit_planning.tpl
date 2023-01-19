{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  checkMaxInterv = function(field) {
    var max_interv = parseInt($V(field.form.elements['max_intervention']));
    if (field.name == 'max_intervention') {
      var max_ambu = field.form.elements['max_ambu'];
      var max_hospi = field.form.elements['max_hospi'];
      if (max_interv && parseInt($V(max_ambu)) > max_interv) {
        $V(max_ambu, max_interv, false);
      }
      if (max_interv && parseInt($V(max_hospi)) > max_interv) {
        $V(max_hospi, max_interv, false);
      }
    }
    else {
      var max_interv = parseInt($V(field.form.elements['max_intervention']));
      if (max_interv && parseInt($V(field)) > max_interv) {
        $V(field, max_interv, false);
      }
    }
  };

  window.synchro_time_ref = true;

  checkPlage = function(oform) {

    if (oform.chir_id.value == "" && oform.spec_id.value == "" && $V(oform.urgence) == '0') {
      alert("Merci de choisir un chirurgien ou une spécialité");
      oform.chir_id.focus();
      return false;
    }

    if (parseInt($V(oform._repeat)) > 1) {
      checkRepetitions(oform);
      return false;
    }

    return onSubmitFormAjax(oform, {onComplete: Control.Modal.close});
  };

  checkRepetitions = function(form) {
    var url = new Url('bloc', 'ajax_check_plage_repetitions');
    url.addFormData(form);
    url.requestModal();
  };

  toggleDel = function(input) {
    if (input.disabled) {
      input.enable();
    }
    else {
      input.disable();
    }
    input.up('span').toggleClassName('opacity-40');
  };

  refreshFunction = function(chir_id) {
    var url = new Url("dPcabinet", "ajax_refresh_secondary_functions");
    url.addParam("chir_id"   , chir_id);
    url.addParam("field_name", "secondary_function_id");
    url.addParam("empty_function_principale", 1);
    url.addParam("change_active", 0);
    url.requestUpdate("secondary_functions");
  };

  onChangeUrgence = function(input) {
    $V(input.form.chir_id, '', false);
    refreshFunction($V(input.form.chir_id));
  };

  synchroTimeRef = function(input) {
    if (!window.synchro_time_ref) {
      return;
    }
    $V(input.form.elements[input.name + "_reference"], $V(input));
    $V(input.form.elements[input.name + "_reference_da"], $V(input.form.elements[input.name + "_da"]));
  };

  Main.add(function() {
    var options = {
      exactMinutes: false,
      minInterval: {{'Ox\Mediboard\Bloc\CPlageOp'|static:minutes_interval}},
      minHours: {{'Ox\Mediboard\Bloc\CPlageOp'|static:hours_start|intval}},
      maxHours: {{'Ox\Mediboard\Bloc\CPlageOp'|static:hours_stop|intval}}
    };
    var form = getForm('editFrm');
    Calendar.regField(form.debut, null, options);
    Calendar.regField(form.fin  , null, options);

    {{if "dPbloc CPlageOp original_owner"|gconf && $plagesel->_id}}
    Calendar.regField(form.prevenance_date);
    {{/if}}

    {{if $plagesel->_id &&
      ($plagesel->debut != $plagesel->debut_reference) || ($plagesel->fin != $plagesel->fin_reference)}}
      form.synchro_times.click();
    {{/if}}
  });
</script>

{{mb_script module=bloc script=edit_planning}}

<form name="emptyPlage" method="post">
  <input type="hidden" name="m" value="bloc"/>
  <input type="hidden" name="dosql" value="do_empty_plage" />
  {{mb_key object=$plagesel}}
</form>

<form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkPlage(this)" class="{{$plagesel->_spec}}">
<input type="hidden" name="m" value="bloc"/>
<input type="hidden" name="dosql" value="do_plagesop_aed" />
<input type="hidden" name="del" value="0" />
{{mb_key object=$plagesel}}

<table class="form">
{{mb_include module=system template=inc_form_table_header object=$plagesel}}
  <tr>
    <td colspan="2">
      <fieldset>
        <legend>Attributs de la plage</legend>
        <table class="form me-no-box-shadow">
          {{if "dPbloc CPlageOp original_owner"|gconf && $plagesel->_id}}
            <tr>
              <th style="width: 15%;">
                {{mb_label object=$plagesel field="original_owner_id"}}
              </th>
              <td colspan="3">
                <select name="original_owner_id" style="width: 15em;" onchange="$V(this.form.original_function_id, '', false)">
                  <option value="">&mdash; Choisir un praticien</option>
                  {{if $chirs|@count}}
                    <optgroup label="Chirurgiens">
                    </optgroup>
                    {{mb_include module=mediusers template=inc_options_mediuser selected=$plagesel->original_owner_id list=$chirs}}
                  {{/if}}
                  {{if $anesths|@count}}
                    <optgroup label="Anesthésistes"></optgroup>
                    {{mb_include module=mediusers template=inc_options_mediuser selected=$plagesel->original_owner_id list=$anesths}}
                  {{/if}}
                </select>
                <select name="original_function_id" style="width: 15em;" onchange="$V(this.form.original_owner_id, '', false)">
                  <option value="">&mdash; Choisir un cabinet</option>
                  {{mb_include module=mediusers template=inc_options_function selected=$plagesel->original_function_id list=$specs}}
                </select>
              </td>
            </tr>
          {{else}}
            {{mb_field object=$plagesel field=original_owner_id hidden=true}}
            {{mb_field object=$plagesel field=original_function_id hidden=true}}
          {{/if}}
          <tr>
            <th style="width: 15%;">{{mb_label object=$plagesel field="chir_id"}}</th>
            <td>
              <select name="chir_id" class="{{$plagesel->_props.chir_id}}" style="width: 15em;"
                      onchange="if (this.value) { $V(this.form.spec_id, '', false); }
                        refreshFunction(this.value);{{if !$plagesel->_id}} $V(this.form.original_owner_id, $V(this));{{/if}}">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{if $chirs|@count}}
                <optgroup label="Chirurgiens">
                </optgroup>
                {{mb_include module=mediusers template=inc_options_mediuser selected=$plagesel->chir_id list=$chirs}}
                {{/if}}
                {{if $anesths|@count}}
                <optgroup label="Anesthésistes"></optgroup>
                {{mb_include module=mediusers template=inc_options_mediuser selected=$plagesel->chir_id list=$anesths}}
                {{/if}}
              </select>

              <span id="secondary_functions">
                {{assign var=chir value=$plagesel->_ref_chir}}
                {{assign var=selected value=$plagesel->secondary_function_id}}
                {{mb_ternary var=onchange test=!$plagesel->_id value="\$V(this.form.original_owner_id, \$V(this));" other=""}}

                {{mb_include module=cabinet template=inc_refresh_secondary_functions field_name=secondary_function_id empty_function_principale=1 type_onchange=$onchange change_active=0}}
              </span>
            </td>
            <th style="width: 15%;">{{mb_label object=$plagesel field="salle_id"}}</th>
            <td style="width: 25%;" colspan="3">
              <select name="salle_id" class="{{$plagesel->_props.salle_id}}" style="width: 15em;">
                <option value="">&mdash;  {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$listBlocs item=_bloc}}
                    <optgroup label="{{$_bloc}}">
                    {{foreach from=$_bloc->_ref_salles item=_salle}}
                      <option value="{{$_salle->_id}}" {{if $_salle->_id == $plagesel->salle_id}}selected="selected"{{/if}}>
                        {{$_salle}}
                      </option>
                    {{foreachelse}}
                      <option value="" disabled>{{tr}}CSalle.none{{/tr}}</option>
                    {{/foreach}}
                    </optgroup>
                  {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="spec_id"}}</th>
            <td>
              <select name="spec_id" class="{{$plagesel->_props.spec_id}}" style="width: 15em;" onchange="$V(this.form.chir_id, '');">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_function selected=$plagesel->spec_id list=$specs}}
              </select>
            </td>
            <th>{{mb_label object=$plagesel field=date}}</th>
            <td>
              {{if $plagesel->_count_all_operations}}
                {{mb_value object=$plagesel field=date}} ({{$plagesel->_count_all_operations}} interventions)
              {{else}}
                {{mb_field object=$plagesel field=date form=editFrm register=true}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="anesth_id"}}</th>
            <td>
              <select name="anesth_id" style="width: 15em;">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_mediuser selected=$plagesel->anesth_id list=$anesths}}
              </select>
            </td>
            <th>{{mb_label object=$plagesel field="debut"}}</th>
            <td class="narrow">{{mb_field object=$plagesel field="debut" onchange="synchroTimeRef(this)"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="unique_chir"}}</th>
            <td>{{mb_field object=$plagesel field="unique_chir"}}</td>
            <th>{{mb_label object=$plagesel field="fin"}}</th>
            <td>{{mb_field object=$plagesel field="fin" onchange="synchroTimeRef(this)"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$plagesel field="max_intervention"}}</th>
            <td>{{mb_field object=$plagesel field="max_intervention" size=1 increment=true form="editFrm" min=0 onchange='checkMaxInterv(this);'}}</td>
            <th>{{mb_label object=$plagesel field="temps_inter_op"}}</th>
            <td>{{mb_field object=$plagesel field="temps_inter_op" form=editFrm register=true}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="max_ambu"}}</th>
            <td>{{mb_field object=$plagesel field="max_ambu" size=1 increment=true form="editFrm" min=0 onchange='checkMaxInterv(this);'}}</td>
            <th>* {{mb_label object=$plagesel field="verrouillage"}}</th>
            <td>{{mb_field object=$plagesel field="verrouillage"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="max_hospi"}}</th>
            <td>{{mb_field object=$plagesel field="max_hospi" size=1 increment=true form="editFrm" min=0 onchange='checkMaxInterv(this);'}}</td>
            <th>{{mb_label object=$plagesel field=urgence}}</th>
            <td>{{mb_field object=$plagesel field=urgence typeEnum="checkbox" onclick="onChangeUrgence(this);"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field=status}}</th>
            <td>{{mb_field object=$plagesel field=status}}</td>
            <th>{{mb_label object=$plagesel field=pause}}</th>
            <td>{{mb_field object=$plagesel field=pause form=editFrm register=true}}</td>
          </tr>
          {{if "dPbloc CPlageOp original_owner"|gconf && $plagesel->_id}}
            <tr>
              <th class="text">{{mb_label object=$plagesel field="prevenance_date"}}</th>
              <td>{{mb_field object=$plagesel field="prevenance_date" form="editFrm"}}</td>
              <td colspan="2"></td>
            </tr>
          {{/if}}

          {{if $plagesel->_id}}
            <tr>
              <td colspan="6">
                <div class="small-info">Cette plage contient {{$plagesel->_count_operations}} intervention(s) et {{$plagesel->_count_operations_annulees}} intervention(s) annulée(s)</div>
              </td>
            </tr>
          {{/if}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Heures de référence</legend>
        <table class="form me-no-box-shadow">
          {{assign var=readonly_reference value=true}}
          {{if $plagesel->_id && (($plagesel->debut_reference != $plagesel->debut) || ($plagesel->fin_reference != $plagesel->fin))}}
            {{assign var=readonly_reference value=null}}
          {{/if}}
          <tr>
            <th class="narrow">{{mb_label object=$plagesel field="debut_reference"}}</th>
            <td class="narrow">{{mb_field object=$plagesel field="debut_reference" form=editFrm readonly=$readonly_reference}}</td>
            <td rowspan="2" style="vertical-align: middle;">
              <button type="button" class="link notext" style="float: left;" name="synchro_times"
                      title="Synchroniser / Désynchroniser avec les heures de début et fin"
                      onclick="window.synchro_time_ref = !window.synchro_time_ref; Element.classNames(this).flip('link', 'unlink');"></button>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$plagesel field="fin_reference"}}</th>
            <td>{{mb_field object=$plagesel field="fin_reference" form=editFrm readonly=$readonly_reference}}</td>
          </tr>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Répétition</legend>
        <table class="form me-no-box-shadow">
          <tr>
            <th class="narrow">
              <label for="_repeat" title="Nombre de semaines de répétition">Nombre de semaines</label>
            </th>
            <td class="narrow">
              <input type="text" class="notNull num min|1" name="_repeat" size="1" value="1" />
            </td>
            <td rowspan="2" class="text">
              <div class="small-info">
                Pour modifier plusieurs plages (nombre de semaines > 1),
                veuillez <strong>ne pas changer les champs début et fin en même temps</strong>.
                <br />
                * Cette valeur ne sera pas propagée sur les plages suivantes.
              </div>
            </td>
          </tr>
          {{if $plagesel->_id}}
            <tr>
              <th>Nombre de plage similaires</th>
              <td>{{$plagesel->_count_duplicated_plages}}</td>
            </tr>
          {{/if}}
          <tr>
            <th>{{mb_label object=$plagesel field="_type_repeat"}}</th>
            <td>{{mb_field object=$plagesel field="_type_repeat" style="width: 15em;" typeEnum="select"}}</td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Personnel en salle</legend>
        <table class="form me-no-box-shadow">
          <tr>
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.iade             type="iade"}}
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.sagefemme        type="sagefemme"}}
          </tr>
          <tr>
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.op               type="op"}}
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.manipulateur     type="manipulateur"}}
          </tr>
          <tr>
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.op_panseuse      type="op_panseuse"}}
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.aux_puericulture type='aux_puericulture'}}
          </tr>
          <tr>
            {{mb_include module=bloc template=inc_edit_plage_personnel  list=$listPers.instrumentiste   type="instrumentiste"}}
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr style="display: none;">
    <td>
      <fieldset>
        <legend>Remplacement</legend>
        <table class="form me-no-box-shadow">
          <tr>
            <th>{{mb_label object=$plagesel field="delay_repl"}}</th>
            <td>{{mb_field object=$plagesel field="delay_repl" size=1 increment=true form="editFrm" min=0}} jours</td>
            <th>{{mb_label object=$plagesel field="spec_repl_id"}}</th>
            <td>
              <select name="spec_repl_id" class="{{$plagesel->_props.spec_repl_id}}" style="width: 15em;">
                <option value="">&mdash; Spécialité de remplacement</option>
                {{foreach from=$specs item=spec}}
                  <option value="{{$spec->function_id}}" class="mediuser" style="border-color: #{{$spec->color}};"
                  {{if $spec->function_id == $plagesel->spec_repl_id}}selected="selected"{{/if}}>
                    {{$spec->text}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button type="submit" class="save me-primary">{{tr}}Save{{/tr}}</button>
      {{if $plagesel->_id}}
        <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'la plage opératoire',objName:'{{$plagesel->_view|smarty:nodefaults|JSAttribute}}', ajax:1}, {onComplete:Control.Modal.close})">
          {{tr}}Delete{{/tr}}
        </button>
      {{/if}}

      {{if $plagesel->_count_operations}}
      <fieldset style="display: inline-block;">
        <legend>{{tr}}CPlageOp-Other actions{{/tr}}</legend>

        <button type="button" class="tick singleclick me-tertiary"
                onclick="if (confirm($T('CPlageOp-Ask_empty_plage', '{{$plagesel->_count_operations}}'))) {
                           onSubmitFormAjax(getForm('emptyPlage'), Control.Modal.close);
                         } ">{{tr}}CPlageOp-Empty_plage{{/tr}}
        </button>
      </fieldset>
      {{/if}}
    </td>
  </tr>
</table>
</form>
