
{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=macrocible value=0}}
{{mb_default var=hide_cible value=0}}
{{mb_default var=update_plan_soin value=0}}
{{mb_default var=refreshTrans value=0}}
{{mb_default var=patient value=0}}
{{mb_default var=macrocible_id value=""}}

{{assign var=cible_mandatory_trans value="soins Transmissions cible_mandatory_trans"|gconf}}
{{assign var=hour_quantum value="dPhospi General nb_hours_trans"|gconf}}

<script>
  updateListTransmissions = function (data, object_class, cible_id) {
    var url = new Url("hospi", "ajax_list_transmissions_short");
    url.addParam("sejour_id", "{{$transmission->sejour_id}}");
    if (isNaN(data)) {
      url.addParam("libelle_ATC", data);
    }
    else {
      url.addParam("object_id", data);
      url.addParam("object_class", object_class);
    }
    url.addParam("cible_id", cible_id);
    url.requestUpdate("list_transmissions");
  };

  submitTrans = function (form, macrocible) {
    if (!macrocible) {
      {{if $cible_mandatory_trans}}
      if (!$V(form.libelle_ATC) && !$V(form.object_class) && !$V(form.object_id)) {
        alert("{{tr}}CTransmissionMedicale.cible_mandatory_trans{{/tr}}");
        return;
      }
      {{/if}}
    }
    {{if $refreshTrans || $update_plan_soin}}
    onSubmitFormAjax(form, function () {
      {{if $refreshTrans}}
      Soins.loadSuivi('{{$transmission->sejour_id}}');
      {{if !"soins Other vue_condensee_dossier_soins"|gconf}}
      Control.Modal.close();
      {{/if}}
      {{else}}
      Control.Modal.close();
      PlanSoins.updatePlanSoinsPatients();
      {{/if}}
      var suivi_nutrition = $('suivi_nutrition');
      if (suivi_nutrition && suivi_nutrition.visible()) {
        loadDietetique($V(form.sejour_id));
      }
      if (Object.isFunction(Soins.callbackClose)) {
        Soins.callbackClose();
      }
    });
    {{else}}
    if (window.submitSuivi) {
      submitSuivi(form);
    }
    else if (window.submitTransmissions) {
      submitTransmissions();
    }
    else {
      var form = getForm("editTrans");
      onSubmitFormAjax(form, function () {
        Control.Modal.close();
      });
    }
    {{/if}}
  };

  updateCibles = function () {
    var form = getForm("editTrans");
    new Url("hospi", "ajax_list_cibles")
      .addParam("sejour_id", "{{$transmission->sejour_id}}")
      .addParam("object_id", $V(form.object_id))
      .addParam("object_class", $V(form.object_class))
      .addParam("libelle_ATC", $V(form.libelle_ATC))
      .addParam("cible_id", "{{$transmission->cible_id}}")
      .addParam("focus_area", "{{$focus_area}}")
      .addParam("transmission_id", '{{$transmission->_id}}')
      .addParam("data_id", "{{$data_id}}")
      .addParam("action_id", "{{$action_id}}")
      .addParam("result_id", "{{$result_id}}")
      .requestUpdate("cibles_area");
  };

  Main.add(function () {
    var form = getForm("editTrans");

    var options = null;

    {{if "soins Transmissions blocking_hour"|gconf}}
    options = {
      minHours: '{{math equation="x-y" x=$hour|intval y=$hour_quantum|intval}}',
      maxHours: '{{math equation="x+y" x=$hour|intval y=$hour_quantum|intval}}'
    };
    {{/if}}

    var dates = {};
    dates.limit = {
      start: '{{$date}}',
      stop:  '{{$date}}'
    };
    Calendar.regField(form.date, dates, options);

    {{if !$transmission->_id && !$data_id && !$action_id && !$result_id}}
    //Initialisation du champ dates
    form.date_da.value = "Heure actuelle";
    $V(form.date, "now");
    {{/if}}

    updateCibles();
  });
</script>

<fieldset>
  <legend>
    {{tr}}CTransmissionMedicale.caracteristiques{{/tr}}
    {{if $patient}}<span style="font-weight: bold"> - {{mb_value object=$patient field=_view}}</span>{{/if}}
  </legend>

  {{if "dPprescription"|module_active}}
    <span style="float:right;">
      {{mb_label object=$transmission field=dietetique typeEnum=checkbox}}
      {{mb_field object=$transmission field=dietetique typeEnum=checkbox}}
    </span>
    {{if !$hide_cible}}
      {{if $macrocible}}
        Macrocible :
        <select name="cible" onchange="$V(this.form.object_id, this.value);
                updateListTransmissions(this.value, 'CCategoryPrescription', null, $V(this.form.cible_id));
                updateCibles();">
          {{foreach from=$macrocibles item=_macrocible}}
            <option value="{{$_macrocible->_id}}" {{if $_macrocible->_id == $macrocible_id}}selected{{/if}}>{{$_macrocible}}</option>
          {{/foreach}}
        </select>
        <span id="cibles_area"></span>
      {{else}}
        <div style="white-space: nowrap">
          {{tr}}CTransmissionMedicale-object_class{{/tr}} :
          <input name="cible" type="text"
                 value="{{if $transmission->_ref_object}}{{$transmission->_ref_object->_view}}{{else}}{{$transmission->libelle_ATC}}{{/if}}"
                 class="autocomplete" style="width: 300px;"
                 onchange="updateCible(this); updateCibles();" />

          <label>
            {{mb_field class="CCategoryPrescription" field="cible_importante" typeEnum=checkbox value=$app->user_prefs.check_show_macrocible}}
            Dont Macrocible
          </label>

          <span id="cibles_area"></span>

          <div style="display:none; width: 300px; white-space: normal; text-align: left;" class="autocomplete"
               id="cible_auto_complete"></div>
        </div>
        <br />
      {{/if}}
    {{/if}}
  {{/if}}
  {{mb_label object=$transmission field=degre}} : {{mb_field object=$transmission field=degre onchange="toggleDateMax();"}} &mdash;
  {{mb_label object=$transmission field=date}} : {{mb_field object=$transmission field=date}}
  <span id="date-max-{{$transmission->sejour_id}}" style="display: none;">
            &mdash;
    {{mb_label object=$transmission field=date_max}} : {{mb_field object=$transmission field=date_max form="editTrans" register=true}}
          </span>

  {{if $transmission->_id && !$transmission->type}}
    &mdash;
    {{mb_label object=$transmission field=type}} : {{mb_field object=$transmission field="type" typeEnum="radio"}}
    <button type="button" onclick="$V(this.form.type, '')" class="cancel notext"></button>
  {{elseif $transmission->_id}}
    &mdash;
    {{mb_label object=$transmission field=type}} : {{mb_value object=$transmission field="type"}}
  {{/if}}
  {{if "planSoins general use_transmission_durees"|gconf}}
    <span id="default-duree-trans-{{$transmission->sejour_id}}">
      &mdash;
      {{mb_label object=$transmission field=duree}} :
      {{mb_field object=$transmission field=duree form=editTrans register=true increment=1 min=1 size=1}}
    </span>
  {{/if}}

  {{* Mapping des object_id des mix items sur un select pour l'ajout d'option à la volée *}}
  {{if $transmission->object_class === "CAdministration" && isset($prescription_line_mix|smarty:nodefaults)}}
    <select name="object_id">
      {{foreach from=$prescription_line_mix->_ref_lines item=_mix_item}}
        {{if !$_mix_item->solvant || ($prescription_line_mix->_ref_lines|@count === 1)}}
          <option value="{{$_mix_item->_id}}">
            {{$_mix_item}}
          </option>
        {{/if}}
      {{/foreach}}
    </select>
  {{/if}}
</fieldset>
