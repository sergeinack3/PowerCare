{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=hide_cible value=0}}
{{mb_default var=hide_button_add value=0}}
{{mb_default var=data_id value=""}}
{{mb_default var=action_id value=""}}
{{mb_default var=result_id value=""}}
{{mb_default var=focus_area value=0}}
{{mb_default var=callback value=""}}

<script>
  Main.add(function () {
    var oFormTrans = getForm("editTrans");
    {{if !$hide_cible}}
    if (oFormTrans.cible) {
      new Url("prescription", "httpreq_cible_autocomplete")
        .autoComplete(oFormTrans.cible, "cible_auto_complete", {
            minChars:      3,
            dropdown:      1,
            width:         '500px',
            updateElement: function (selected) {
              var oForm = getForm("editTrans");
              $V(oForm.cible_id, "");

              Element.cleanWhitespace(selected);
              var data = selected.get("data");

              if (!data) {
                return;
              }

              if (isNaN(data)) {
                $V(oForm.libelle_ATC, data);
                $V(oForm._cible, data);

                {{if $transmission->libelle_ATC}}
                if (data == "{{$transmission->libelle_ATC}}") {
                  $V(oForm.cible_id, "{{$transmission->cible_id}}");
                }
                {{/if}}
                updateListTransmissions(data, null, $V(oForm.cible_id));
              }
              else {
                // Cas des perfusions : on transforme le select en input caché
                if (oForm.object_id.tagName === 'SELECT') {
                  oForm.object_id.remove();
                  oForm.append(DOM.input({type: 'hidden', name: 'object_id'}));
                }
                $V(oForm.object_id, data);
                $V(oForm.object_class, "CCategoryPrescription");
                $V(oForm._cible, data);

                {{if $transmission->object_id && $transmission->object_class}}
                if (data == "{{$transmission->object_id}}" && "CCategoryPrescription" == "{{$transmission->object_class}}") {
                  $V(oForm.cible_id, "{{$transmission->cible_id}}");
                }
                {{/if}}
                updateListTransmissions(data, "CCategoryPrescription", $V(oForm.cible_id));
                updateObjectifs('{{$transmission->sejour_id}}', $V(oForm.object_class), $V(oForm.object_id));
              }
              var view = $(selected).down(".view").innerHTML.split(" : ")[1];
              var Macro_cible = $(selected).down("#macro").innerHTML;

              $V(oFormTrans.cible, view);

              $$(".is_macrocible").invoke(Macro_cible == "1" ? "hide" : "show");

              {{if "planSoins general use_transmission_durees"|gconf}}
                var duree = selected.get('duree') ? selected.get('duree') : '';
                $V(oForm.duree, duree);
                $V(oForm.duree_da, duree.substr(0, 5));
              {{/if}}
              $$(".validate_and_close").invoke("show");
            },
            callback:
                           function (input, queryString) {
                             return (queryString + "&cible_importante=" + $V(oFormTrans.cible_importante));
                           }
          }
        );
    }

    updateObjectifs('{{$transmission->sejour_id}}', $V(oFormTrans.object_class), $V(oFormTrans.object_id));

    {{/if}}

    {{if $transmission->object_id}}
    updateListTransmissions('{{$transmission->object_id}}', '{{$transmission->object_class}}', '{{$transmission->cible_id}}');
    {{elseif $transmission->libelle_ATC}}
    updateListTransmissions('{{$transmission->libelle_ATC|smarty:nodefaults|JSAttribute}}', null, '{{$transmission->cible_id}}');
    {{/if}}

    toggleDateMax();

    {{if $focus_area}}
    oFormTrans.elements["_text_{{$focus_area}}"].focus();
    {{/if}}
  });

  updateCible = function (elt) {
    if (elt.value == '') {
      $V(elt.form.object_id, '');
      $V(elt.form.object_class, '');
      $V(elt.form.libelle_ATC, '');
      updateListTransmissions(elt.value);
    }
  };

  completeTrans = function (type, button) {
    var oFormTrans = getForm("editTrans");
    var fieldName = "_text_" + type;
    var oField = oFormTrans.elements["_text_" + type];
    var text = button.get("text");
    $V(oField, $V(oField) ? $V(oField) + "\n" + text : text);
  };

  toggleDateMax = function () {
    var oForm = getForm("editTrans");
    if ($V(oForm.degre) == "high") {
      $('date-max-{{$transmission->sejour_id}}').show();
    } else {
      $('date-max-{{$transmission->sejour_id}}').hide();
      $V(oForm.date_max, '');
    }
  };

  submitCibleObjectif = function (objectif_soin_id, object_class, object_id) {
    var oForm = getForm("editObjectif");
    var oFormTrans = getForm("editTrans");

    $V(oForm.objectif_soin_id, objectif_soin_id);
    $V(oForm.object_class, object_class);
    $V(oForm.object_id, object_id);
    return onSubmitFormAjax(oForm, function () {
      updateObjectifs($V(oFormTrans.sejour_id), $V(oFormTrans.object_class), $V(oFormTrans.object_id));
    });
  };

  removeObjectif = function (objectif_soin_cible_id) {
    var oForm = getForm("delObjectif");
    var oFormTrans = getForm("editTrans");

    $V(oForm.objectif_soin_cible_id, objectif_soin_cible_id);
    $V(oForm.del, "1");
    return onSubmitFormAjax(oForm, function () {
      updateObjectifs($V(oFormTrans.sejour_id), $V(oFormTrans.object_class), $V(oFormTrans.object_id));
    });
  };

  updateObjectifs = function (sejour_id, object_class, object_id) {
    new Url("soins", "ajax_edit_objectifs")
      .addParam("sejour_id", sejour_id)
      .addParam("object_class", object_class)
      .addParam("object_id", object_id)
      .requestUpdate("objectifs-" + sejour_id);
  };

  askObjectifs = function (callback) {
    var select_objectif = $("objectif_soin_id");

    window.list_objectifs = [];
    if (select_objectif && select_objectif.next("ul")) {
      window.list_objectifs = select_objectif.next("ul").select("button.trash");
    }

    if (!window.list_objectifs) {
      callback();
      return;
    }

    askObjectif(callback);
  };

  askObjectif = function (callback) {
    var objectif = window.list_objectifs.shift();

    if (!objectif) {
      callback();
      return;
    }

    if (confirm("Souhaitez-vous marquer l'objectif " + objectif.get("libelle") + " comme atteint ?")) {
      var form = getForm("editObjectifSoin");
      $V(form.statut, "atteint");
      $V(form.objectif_soin_id, objectif.get("objectif_soin_id"));
      onSubmitFormAjax(form, askObjectif.curry(callback));
    }
    else {
      askObjectif(callback);
    }
  };
</script>

{{if $transmission->_id}}
  <tr>
    <th class="title modify">
      {{mb_include module=system template=inc_object_history object=$transmission}}
    </th>
  </tr>
{{/if}}

{{if isset($objectif_soin_cible|smarty:nodefaults)}}
  <form name="editObjectif" method="post" class="manualSubmit">
    {{mb_class object=$objectif_soin_cible}}
    {{mb_key object=$objectif_soin_cible}}
    <input type="hidden" name="del" value="" />
    {{mb_field object=$objectif_soin_cible field=objectif_soin_id hidden=true}}
    {{mb_field object=$objectif_soin_cible field=object_class hidden=true}}
    {{mb_field object=$objectif_soin_cible field=object_id hidden=true}}
  </form>
  <form name="delObjectif" method="post" class="manualSubmit">
    {{mb_class object=$objectif_soin_cible}}
    {{mb_key object=$objectif_soin_cible}}
    <input type="hidden" name="del" value="1" />
  </form>
  <form name="editObjectifSoin" method="post" class="manualSubmit">
    {{mb_class object=$objectif_soin}}
    {{mb_key object=$objectif_soin}}
    {{mb_field object=$objectif_soin field=statut hidden=true}}
  </form>
{{/if}}

<form name="editTrans" method="post" onsubmit="return checkForm(this)" style="text-align: left;">
  <input type="hidden" name="m" value="hospi" />
  {{if $transmission->_id}}
    <input type="hidden" name="dosql" value="do_transmission_aed" />
  {{else}}
    <input type="hidden" name="dosql" value="do_multi_transmission_aed" />
  {{/if}}
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$transmission}}
  <input type="hidden" name="data_id" value="{{$data_id}}" />
  <input type="hidden" name="action_id" value="{{$action_id}}" />
  <input type="hidden" name="result_id" value="{{$result_id}}" />
  <input type="hidden" name="callback" value="{{$callback}}" />
  <input type="hidden" name="_cible" value="" />
  <input type="hidden" name="_locked" />

  <input type="hidden" name="object_class" value="{{$transmission->object_class}}" onchange="$V(this.form.libelle_ATC, '', false);" />

  {{if $transmission->object_class !== "CAdministration" || !isset($prescription_line_mix|smarty:nodefaults)}}
    <input type="hidden" name="object_id" value="{{$transmission->object_id}}" />
  {{/if}}

  <input type="hidden" name="libelle_ATC" value="{{$transmission->libelle_ATC}}"
         onchange="$V(this.form.object_class, '', false); $V(this.form.object_id, '', false);" />
  <input type="hidden" name="sejour_id" value="{{$transmission->sejour_id}}" />
  <input type="hidden" name="user_id" value="{{$transmission->user_id}}" />
  {{if $transmission->_id && $transmission->type}}
    <input type="hidden" name="type" value="{{$transmission->type}}" />
  {{/if}}
  <input type="hidden" name="_force_new_cible" />

  <table style="width: 100%;">
    <tr>
      <td>
        {{mb_include module=hospi template=inc_transmission_caracs}}
      </td>
      <td id="objectifs-{{$transmission->sejour_id}}" class="me-td-objectifs"></td>
    </tr>
  </table>

  <table class="main" style="width: 100%;">
    <tr>
      {{if !$transmission->_id}}
        <td>
          <fieldset class="me-no-box-shadow">
            <legend>
              {{tr}}CTransmissionMedicale.type.data{{/tr}}
              {{if $data_id}}
                <a href="#1" title="" onclick="guid_log('{{$transmission->_class}}-{{$data_id}}')">
                {{me_img src="history.gif" width=16 height=16 icon="history" class="me-primary"}}
                </a>
              {{/if}}
            </legend>
            <input type="hidden" name="_type_data" value="data" />
            {{if ($action_id || $result_id) && !$data_id}}
              {{mb_field object=$transmission field="_text_data" rows=6 readonly="readonly"}}
            {{else}}
              {{assign var=readonly_trans value=null}}
              {{if $data_id && !$transmission->canEdit()}}
                {{assign var=readonly_trans value="readonly"}}
              {{/if}}
              {{mb_field object=$transmission field="_text_data" rows=6 form="editTrans" readonly=$readonly_trans
              aidesaisie="property: 'text',
                            dependField1: getForm('editTrans')._type_data,
                            dependField2: getForm('editTrans')._cible,
                            classDependField2: 'CCategoryPrescription',
                            validateOnBlur: 0,
                            updateDF: 0,
                            width: '250px',
                            strict: 0"}}
            {{/if}}
          </fieldset>
        </td>
        <td class="is_macrocible">
          <fieldset class="me-no-box-shadow">
            <legend>
              {{tr}}CTransmissionMedicale.type.action{{/tr}}
              {{if $action_id}}
                <a href="#1" title="" onclick="guid_log('{{$transmission->_class}}-{{$action_id}}')">
                  {{me_img src="history.gif" width=16 height=16 icon="history" class="me-primary"}}
                </a>
              {{/if}}
            </legend>
            <input type="hidden" name="_type_action" value="action" />
            {{if ($data_id || $result_id) && !$action_id}}
              {{mb_field object=$transmission field="_text_action" rows=6 readonly="readonly"}}
            {{else}}
              {{mb_field object=$transmission field="_text_action" rows=6 form="editTrans"
              aidesaisie="property: 'text',
                            dependField1: getForm('editTrans')._type_action,
                            dependField2: getForm('editTrans')._cible,
                            classDependField2: 'CCategoryPrescription',
                            validateOnBlur: 0,
                            updateDF: 0,
                            width: '250px',
                            strict: 0"}}
            {{/if}}
          </fieldset>
        </td>
        <td class="is_macrocible">
          <fieldset class="me-no-box-shadow">
            <legend>
              {{tr}}CTransmissionMedicale.type.result{{/tr}}
              {{if $result_id}}
                <a href="#1" title="" onclick="guid_log('{{$transmission->_class}}-{{$result_id}}')">
                  {{me_img src="history.gif" width=16 height=16 icon="history" class="me-primary"}}
                </a>
              {{/if}}
            </legend>
            <input type="hidden" name="_type_result" value="result" />
            {{if ($data_id || $action_id) && !$result_id}}
              {{mb_field object=$transmission field="_text_result" rows=6 readonly="readonly"}}
            {{else}}
              {{mb_field object=$transmission field="_text_result" rows=6 form="editTrans"
              aidesaisie="property: 'text',
                            dependField1: getForm('editTrans')._type_result,
                            dependField2: getForm('editTrans')._cible,
                            classDependField2: 'CCategoryPrescription',
                            validateOnBlur: 0,
                            updateDF: 0,
                            width: '250px',
                            strict: 0"}}
            {{/if}}

          </fieldset>
        </td>
      {{else}}
        <fieldset class="me-no-box-shadow">
          <legend>
            {{mb_label object=$transmission field="text"}}
          </legend>
          {{assign var=readonly_trans value=null}}
          {{if !$transmission->canEdit()}}
            {{assign var=readonly_trans value="readonly"}}
          {{/if}}
          {{mb_field object=$transmission field="text" rows=6 form="editTrans" readonly=$readonly_trans
          aidesaisie="property: 'text',
                            dependField1: getForm('editTrans').type,
                            dependField2: getForm('editTrans')._cible,
                            classDependField2: 'CCategoryPrescription',
                            validateOnBlur: 0,
                            updateDF: 0,
                            strict: 0"}}
        </fieldset>
      {{/if}}
    </tr>
    {{if !$hide_button_add}}
      <tr>
        <td class="button" {{if !$transmission->_id}}colspan="3"{{/if}}>
          <button type="button"
                  class="{{if $transmission->_id || $data_id || $action_id || $result_id}}save{{else}}add{{/if}} singleclick "
                  onclick="submitTrans(this.form);">
            {{if $transmission->_id || $data_id || $action_id || $result_id}}
              {{tr}}Save{{/tr}}
            {{else}}
              {{tr}}Add{{/tr}}
            {{/if}}
          </button>
          {{if !$transmission->_id && !$data_id && !$action_id && !$result_id}}
            <button type="button" class="add singleclick"
                    onclick="$V(this.form._locked, 1); askObjectifs(submitTrans.curry(this.form));">{{tr}}CTransmissionMedicale-action-Add and close the target{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    {{/if}}
  </table>
  <div style="margin-top: 20px;" id="list_transmissions"></div>
</form>