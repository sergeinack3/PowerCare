{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    Control.Tabs.create('onglet_manage_constants', true);
  });
</script>
<ul id="onglet_manage_constants" class="control_tabs">
  <li><a href="#modif_constants">{{tr}}manage-tab-ajax_modal_manage_constants{{/tr}}</a></li>
  <li><a href="#add_constants">{{tr}}adding-tab-ajax_modal_manage_constants{{/tr}}</a></li>
</ul>

<div id="modif_constants" style="display:none;">
  <form name="form_manage_constantes">
    <input type="hidden" name="releve_id" value="{{$releve->_id}}">
    <input type="hidden" name="patient_id" value="{{$releve->patient_id}}">
    <input type="hidden" name="m" value="{{$m}}">

    <table class="form">
      <tbody>
      <tr>
        <th colspan="3" class="title">{{tr}}CConstantReleve|pl{{/tr}}</th>
      </tr>

      <tr>
        <th colspan="3" class="category">{{mb_value object=$releve field=datetime}}</th>
      </tr>

      {{foreach from=$releve->_ref_all_values item=_constant}}
        {{if !$_constant->_ref_spec}}
          <tr class="opacity-60">
            <th class="category button warning">{{tr}}CConstantSpec.name.unknown{{/tr}}</th>
            <td class="warning">{{mb_value object=$_constant field=_view_value}}</td>
          </tr>
        {{else}}
        {{if $_constant|get_parent_class == "CInterval"}}
          <script type="text/javascript">
            Main.add(function () {
              let form = getForm("form_manage_constantes");
              let item = form.elements.min_constant_{{$_constant->spec_id}};
              let item2 = form.elements.max_constant_{{$_constant->spec_id}};
              if (item && item2) {
                Calendar.regField(item);
                Calendar.regField(item2);
              }
            });
          </script>
        {{/if}}
          <tr>
            <th class="category button"><strong>{{tr}}{{$_constant->_ref_spec->name}}{{/tr}}</strong></th>
            {{if $_constant->_ref_spec->value_class|get_parent_class == "CInterval"}}
              <td class="narrow">
                {{mb_value object=$_constant field="min_value"}}
                {{mb_value object=$_constant field="max_value"}}
                {{if $_constant->_ref_spec->value_class == "CStateInterval"}}
                  {{mb_value object=$_constant field="state"}}
                {{/if}}
                {{mb_include module=dPpatients template=inc_view_alert}}
              </td>
              <td>
                <input type="{{$_constant->_input_field}}" class="dateTime" name="min_constant_{{$_constant->spec_id}}">
                <input type="{{$_constant->_input_field}}" class="dateTime" name="max_constant_{{$_constant->spec_id}}">
                {{if $_constant->_ref_spec->value_class == "CStateInterval"}}
                  <input type="number" class="number-display" name="constant_{{$_constant->spec_id}}">
                {{/if}}
                {{$_constant->getViewUnit()}}
              </td>
            {{else}}
              <td>
                {{mb_value object=$_constant field="value"}}
                {{$_constant->getViewUnit()}}
                {{mb_include module=dPpatients template=inc_view_alert}}
              </td>
              {{if !$_constant->isCalculatedConstant()}}
                <td>
                  <input type="{{$_constant->_input_field}}" class="number-display" name="constant_{{$_constant->spec_id}}">
                  <button type="button" class="button trash notext"
                          {{if !$_constant->_id}}disabled title="{{tr}}CValueConstanteMedicale-msg-None value{{/tr}}" {{/if}}
                          onclick="dashboard.deleteConstante('{{$_constant->_guid}}', '{{$releve->_id}}');"></button>
                </td>
              {{/if}}
            {{/if}}
          </tr>
        {{/if}}
      {{/foreach}}
      <tr>
        <td colspan="3" class="button">
          <button type="button" class="save button"
                  onclick="dashboard.updateConstante(this.form, '{{$releve->_id}}');">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
      </tbody>
    </table>
  </form>
</div>

<div id="add_constants" style="display: none;">
  <form name="form_add_constantes">
    <input type="hidden" name="releve_id" value="{{$releve->_id}}">
    <input type="hidden" name="patient_id" value="{{$releve->patient_id}}">
    <input type="hidden" name="m" value="{{$m}}">

    <table class="form">
      <tbody>
      <tr>
        <th colspan="3" class="title">{{tr}}CConstantReleve|pl{{/tr}}</th>
      </tr>

      <tr>
        <th colspan="3" class="category">{{mb_value object=$releve field=datetime}}</th>
      </tr>

      {{foreach from=$unused_specs item=_constant}}
        {{if !$_constant->isCalculatedConstant()}}
          <tr>
            {{mb_include module=dPpatients template=inc_add_constants}}
            <td class="button">
              <button type="button" class="button notext add"
                      onclick="dashboard.addConstantToReleve('{{$releve->_id}}', this.form)"></button>
            </td>
          </tr>
        {{/if}}
      {{/foreach}}

      </tbody>
    </table>
</div>
