{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if $result_id}}
    $$("form[data-result_id={{$result_id}}]")[0].focusFirstElement();
    {{else}}
    $$(".result-form")[0].focusFirstElement();
    {{/if}}

    {{if $limit_date_min}}
      var form = getForm('form-edit-observation-result-set');
      Calendar.regField(form.datetime, {limit:
          {
            start: '{{$limit_date_min}}'
          }
      });
    {{/if}}

    // For IE8
    $$("div.outlined input").each(function (input) {
      input.observe("click", function () {
        var form = input.form;

        form.select("div.outlined input.checked").invoke("removeClassName", "checked");
        input.addClassName("checked");

        $V(form.elements._value, "FILE");
        $V(form.elements.del, "0");
      });
    });
  });

  resetPicture = function (radio) {
    var elements = radio.form.elements;
    $V(elements.file_id, '');
    $V(elements.del, '1');
    radio.form.select("div.outlined input.checked").invoke("removeClassName", "checked");
  };

  submitObservationResults = function (id, obj) {
    var forms = $$(".result-form").filter(function (form) {
      return $V(form.elements.del) == 1 || $V(form.elements._value) !== "";
    });

    forms.each(function (form) {
      $V(form.observation_result_set_id, id);
    });

    Form.chainSubmit(forms, Control.Modal.close);
  };

  submitObservationResultSet = function () {
    var form = getForm('form-edit-observation-result-set');

    {{if $limit_date_min}}
    var limit_date_min = '{{$limit_date_min}}';

      if ($V(form.datetime) < limit_date_min) {
        alert($T('CObservationResultSet-msg-You can no longer record observations on a date and time earlier than %s', '{{$limit_date_min|date_format:$conf.datetime}}'));
        return false;
      }
    {{/if}}

    var check = function () {
      if (!checkForm(form)) {
        return false;
      }

      return $$(".result-form").any(function (form) {
        // delete or has value
        return $V(form.elements.del) == 1 || $V(form.elements._value) !== "";
      });
    };

    return onSubmitFormAjax(form, {check: check});
  };

  formTimedDataCallback = function (origin, memo, id, obj) {
    if (obj._formula_result) {
      var input = origin.up("form").elements._value;
      $V(input, obj._formula_result);
    }
  }
</script>

<form name="form-edit-observation-result-set" method="post" onsubmit="return false;">
  {{mb_class class=CObservationResultSet}}
  {{mb_key object=$result_set}}
  {{mb_field object=$result_set field=patient_id hidden=true}}
  {{mb_field object=$result_set field=context_class hidden=true}}
  {{mb_field object=$result_set field=context_id hidden=true}}
  <input type="hidden" name="callback" value="submitObservationResults" />

  <table class="main form me-small-form me-no-align">
    <col style="width: 12em;" />

    <tr>
      <th colspan="2" class="title">
        {{$pack}}
        {{if $result_set->_id}}
          {{mb_include module=system template=inc_object_history object=$result_set tabindex="-1"}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$result_set field=datetime}}
      </th>
      <td>
        {{mb_field object=$result_set field=datetime register=true form="form-edit-observation-result-set"}}
      </td>
    </tr>
  </table>
</form>

{{foreach from=$pack->_ref_graph_links item=_link}}
  {{assign var=_graph value=$_link->_ref_graph}}

  {{if $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionGraph'&& !$_graph->disabled}}
    {{if !$_graph->automatic_protocol}}
      <table class="main form me-small-form me-no-align">
        <tr>
          <th class="category">{{$_graph}}</th>
        </tr>
      </table>
      {{foreach from=$_graph->_ref_axes item=_axis name=list_axes}}
        {{foreach from=$_axis->_ref_series item=_serie}}
          {{assign var=_result value=$_serie->_result}}
          {{assign var=_value_type value=$_result->_ref_value_type}}
          {{unique_id var=uid_form}}
          <form name="form-edit-observation-{{$uid_form}}" method="post" action="?"
                class="result-form" onsubmit="submitObservationResultSet(); return false;" data-result_id="{{$_result->_id}}">
            <input type="hidden" name="del" value="0" />

            {{mb_class object=$_result}}
            {{mb_key object=$_result}}
            {{mb_field object=$_result field=_value_type_id hidden=true}}
            {{mb_field object=$_result field=_unit_id hidden=true}}
            {{mb_field object=$_result field=observation_result_set_id hidden=true}}

            <table class="main form me-small-form me-no-align">
              <col style="width: 12em;" />
              <col style="width: 8em;" />
              <col style="width: 3em;" />

              <tr>
                <th class="me-w33">
                  {{if $smarty.foreach.list_axes.first && $object|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
                    {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$object cssStyle="display: inline-block;"}}
                  {{/if}}

                  <label for="_value" title="{{$_value_type}}">{{$_result->_serie_title}}</label>
                </th>
                <td style="width: 12em;">
                  {{if $_axis->_labels|@count}}
                    <input type="hidden" name="_value" value="{{$_result->_value}}" />
                    <select name="label_id"
                            onchange="$V(this.form.elements._value, this.selectedIndex ? this.options[this.selectedIndex].get('value') : ''); if (this.selectedIndex) {$V(this.form.del, 0)}">
                      <option value="">&ndash; Valeur</option>
                      {{foreach from=$_axis->_ref_labels item=_label}}
                        <option
                          value="{{$_label->_id}}"
                          data-value="{{$_label->value}}"
                          {{if $_result->label_id == $_label->_id}}selected{{/if}}>
                          {{$_label->title}}
                        </option>
                      {{/foreach}}
                    </select>
                  {{else}}
                    {{assign var=_prop value="float"}}

                    {{mb_field object=$_result field=_value prop="$_prop" onchange="\$V(this.form.del, (\$V(this)?0:1))"}}
                    {{$_result->_ref_value_unit->desc}}
                  {{/if}}
                </td>
                <td>
                  <button type="button" class="erase notext me-tertiary" tabindex="-1"
                          onclick="$V(this.form.del,1); $V(this.form.label_id,''); $V(this.form.elements._value,''); ">
                    {{tr}}Empty{{/tr}}
                  </button>
                </td>

                {{assign var=_value_type_id value=$_result->_value_type_id}}
                {{assign var=_value_unit_id value=$_result->_unit_id}}

                {{if !$_value_unit_id}}
                  {{assign var=_value_unit_id value="none"}}
                {{/if}}

                <td>
                  {{if isset($results.$_value_type_id.$_value_unit_id|smarty:nodefaults)}}
                    {{assign var=_last_result value=$results.$_value_type_id.$_value_unit_id|@last}}
                    {{if $_last_result}}
                      <small style="color: #999;" title="{{$_last_result.datetime|date_format:$conf.datetime}}">
                        {{$_last_result.datetime|date_format:$conf.time}}
                      </small>
                      &nbsp;

                      {{if $_axis->_labels|@count}}
                        {{assign var=_label_id value=$_last_result.label_id}}

                        {{if isset($_axis->_ref_labels.$_label_id|smarty:nodefaults)}}
                          {{assign var=_label value=$_axis->_ref_labels.$_label_id}}
                          {{$_label->title}} ({{$_last_result.value}})
                        {{else}}
                          {{$_last_result.value}}
                        {{/if}}

                      {{else}}
                        {{$_last_result.value}}
                      {{/if}}

                      {{if $_result->_ref_value_unit}}
                        {{$_result->_ref_value_unit->desc}}
                      {{/if}}
                    {{/if}}
                  {{/if}}

                  {{if $_result->_id}}
                  {{mb_include module=system template=inc_object_history object=$_result tabindex="-1"}}
                  {{/if}}
                </td>
              </tr>
            </table>
          </form>
        {{/foreach}}
      {{/foreach}}
    {{/if}}

  {{elseif $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionTimedData' && !$_graph->disabled}}
    {{assign var=_result value=$_graph->_result}}
    {{assign var=_value_type value=$_result->_ref_value_type}}
    {{unique_id var=uid_form}}

    {{if $_graph->type == "enum" || $_graph->type == "set" || $_graph->type == "bool"}}
      <form name="form-edit-observation-{{$uid_form}}" method="post" action="?"
            class="result-form" onsubmit="submitObservationResultSet(); return false;" data-result_id="{{$_result->_id}}">
        <input type="hidden" name="del" value="0" />
        {{mb_class object=$_result}}
        {{mb_key object=$_result}}
        {{mb_field object=$_result field=_value_type_id hidden=true}}
        {{mb_field object=$_result field=_unit_id hidden=true}}
        {{mb_field object=$_result field=observation_result_set_id hidden=true}}

        <table class="main form me-small-form me-no-align">
          <col style="width: 12em;" />

          <tr>
            <td colspan="2">
              <hr />
            </td>
          </tr>

          <tr>
            <th>{{$_graph}}</th>
            <td>
              {{assign var=has_found_value value=false}}

              {{if $_graph->type == "enum"}}
                <select name="_value">
                  <option value=""> &mdash;</option>

                  {{foreach from=$_graph->_items item=_item}}
                    <option value="{{$_item}}"
                      {{if $_result->_value == $_item}}
                      {{assign var=has_found_value value=true}}
                    selected
                      {{/if}}>
                      {{$_item}}
                    </option>
                  {{/foreach}}

                  {{if !$has_found_value}}
                    <option value="{{$_result->_value}}" selected>{{$_result->_value}}</option>
                  {{/if}}
                </select>
              {{elseif $_graph->type == "set"}}
                <script>
                  Main.add(function () {
                    var form = getForm("form-edit-observation-{{$uid_form}}");
                    var tf = new TokenField(form.elements._value, {separator: "\n"});
                    document.on("click", ".values-{{$uid_form}}", function (e) {
                      var elt = Event.element(e);
                      tf.toggle(elt.value, elt.checked);
                      form.elements._value.onchange();
                    });
                  });
                </script>
                <input type="hidden" name="_value" value="{{$_result->_value}}"
                       onchange="this.form.elements.del.value=((this.value=='')?1:0);" />
               <table class="form toto">
                  {{assign var=curr_counter   value=0}}
                  {{assign var=column_counter value=$_graph->column}}
                  {{assign var=column_counter_reset value=0}}

                  {{if $column_counter > 1}}
                    {{math assign=column_counter_reset equation="x-1" x=$column_counter}}
                  {{/if}}

                  {{foreach from=$_graph->_items item=_item}}
                    {{if $curr_counter is div by $column_counter || $curr_counter == 0}}<tr>{{/if}}
                      <td>
                        <label style="display: block;">
                            <input class="values-{{$uid_form}}" type="checkbox" value="{{$_item}}"
                              {{if in_array($_item, $_result->_values)}}
                                {{assign var=has_found_value value=true}}
                                checked
                              {{/if}} />
                            {{$_item}}
                         </label>
                      </td>
                    {{if $curr_counter is div by $column_counter && $curr_counter != 0}}</tr>{{/if}}
                    {{math assign=curr_counter equation="x+1" x=$curr_counter}}

                    {{if $curr_counter > $column_counter_reset}}
                      {{assign var=curr_counter value=0}}
                    {{/if}}
                  {{/foreach}}
               </table>

              {{elseif $_graph->type == "bool"}}
              {{if $_result->_value == "Oui" || $_result->_value == "Non" || $_result->_value == ""}}
              <input type="hidden" name="_value" value="{{$_result->_value}}" />

              <input type="checkbox" onclick="$V(this.form.elements._value,this.checked?'Oui':'Non')"
                     name="__value" {{if $_result->_value == "Oui"}} checked {{/if}} />
              {{else}}
              <input type="text" name="_value" value="{{$_result->_value}}" />
              {{/if}}
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    {{else}}
      <table class="main form me-small-form me-no-align">
        <tr>
          <th class="category">{{$_graph}}</th>
        </tr>
      </table>
      <form name="form-edit-observation-{{$uid_form}}" method="post" action="?"
            class="result-form" onsubmit="submitObservationResultSet(); return false;" data-result_id="{{$_result->_id}}">
        <input type="hidden" name="del" value="0" />
        {{mb_class object=$_result}}
        {{mb_key object=$_result}}
        {{mb_field object=$_result field=_value_type_id hidden=true}}
        {{mb_field object=$_result field=_unit_id hidden=true}}
        {{mb_field object=$_result field=observation_result_set_id hidden=true}}

        <table class="main form">
          <tr>
            <td>
              {{mb_field object=$_result field=_value prop="text helped|_value_type_id|_unit_id" form="form-edit-observation-$uid_form"
              onchange="\$V(this.form.del, (\$V(this)?0:1))"
              aidesaisie="validateOnBlur: 0, classDependField1: 'CObservationValueType', classDependField2: 'CObservationValueUnit'"}}
            </td>
            <td class="narrow">
              {{if $_result->_id}}
                {{mb_include module=system template=inc_object_history object=$_result tabindex="-1"}}
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    {{/if}}

  {{elseif $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture' && !$_graph->disabled}}
    {{assign var=_result value=$_graph->_result}}
    {{assign var=_value_type value=$_result->_ref_value_type}}
    {{unique_id var=uid_form}}
    <table class="main form me-small-form me-no-align">
      <tr>
        <th class="category">{{$_graph}}</th>
      </tr>
    </table>
    <form name="form-edit-observation-{{$uid_form}}" method="post" action="?"
          class="result-form" onsubmit="submitObservationResultSet(); return false;" data-result_id="{{$_result->_id}}">
      <input type="hidden" name="del" value="0" />
      {{mb_class object=$_result}}
      {{mb_key object=$_result}}
      {{mb_field object=$_result field=_value_type_id hidden=true}}
      {{mb_field object=$_result field=observation_result_set_id hidden=true}}
      {{mb_field object=$_result field=_value hidden=true}}

      <button type="button" class="cancel notext me-tertiary" onclick="resetPicture(this)"></button>
      {{if $_result->_id}}
        {{mb_include module=system template=inc_object_history object=$_result tabindex="-1"}}
      {{/if}}

      {{foreach from=$_graph->_ref_files item=_file}}
        {{if !$_file->annule || $_file->_id == $_result->file_id}}
          <div class="outlined">
            <input type="radio" name="file_id" value="{{$_file->_id}}" {{if $_file->_id == $_result->file_id}}checked
                   class="checked"{{/if}} />
            <label for="file_id_{{$_file->_id}}" ondblclick="this.form.onsubmit()">
              <div
                style="background: no-repeat center center url(?m=files&raw=thumbnail&document_guid={{$_file->_class}}-{{$_file->_id}}&profile=medium); background-size: contain; height: 80px; width: 80px;"></div>
              {{$_file->_no_extension}}
            </label>
          </div>
        {{/if}}
      {{/foreach}}
    </form>
  {{/if}}
{{/foreach}}

<table class="main form me-small-form me-no-align">
  <col style="width: 30%;" />

  <tr>
    <td></td>
    <td>
      <button type="submit" class="submit singleclick" onclick="submitObservationResultSet()">{{tr}}Save{{/tr}}</button>
    </td>
  </tr>
</table>

{{mb_include module=forms template=inc_widget_ex_class_register_multiple_end event_name=timed_data object_class=$object->_class callback="formTimedDataCallback"}}
