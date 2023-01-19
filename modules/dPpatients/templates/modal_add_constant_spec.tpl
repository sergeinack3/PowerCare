{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    constantSpec.updatecheckField();
    Control.Tabs.create('add_constantSpec_tabs');
  });
</script>

<ul id="add_constantSpec_tabs" class="control_tabs ">
  <li><a href="#add_constantSpec">{{tr}}CAbstractConstant{{/tr}}</a></li>
  <li><a href="#add_alert" {{if !$spec->hasAlert()}}class="empty"{{/if}}>{{tr}}CConstantAlert|pl{{/tr}}</a></li>
  {{if $spec->_is_constant_base}}
    <li><a href="#calculated_constant"
           {{if !$spec->isCalculatedConstant()}}class="empty"{{/if}}>{{tr}}CAbstractConstant-titile-constant calculated{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="add_constantSpec" style="display:none">
  <form name="constantSpec_form">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="dosql" value="do_aed_constant_spec" />
    <input type="hidden" name="constant_spec_id" value="{{$spec->_id}}" />
    <input type="hidden" name="alert_id" value="{{$alert->_id}}" />
    <input type="hidden" name="del" value="0" />
    <table class="form">
      <tr>
        <td>
          <fieldset>
            <legend>{{tr}}CConstantSpec-msg-data required|pl{{/tr}}</legend>
            <table class="form">

              <tr>
                <th>{{mb_label object=$spec field=code}}</th>
                {{if $spec->_is_constant_base}}
                  <td>{{mb_field object=$spec field=code}}</td>
                {{else}}
                  <td>{{mb_value object=$spec field=code}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=name}}</th>
                {{if $spec->_is_constant_base}}
                  <td>{{mb_field object=$spec field=name form="constantSpec_form"}}</td>
                {{else}}
                  <td>{{tr}}{{$spec->name}}{{/tr}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=unit}}</th>
                {{if $spec->_is_constant_base}}
                  <td><input type="text" name="primary_unit" id="primary_unit" value="{{$spec->_primary_unit}}"
                             onchange="constantSpec.updatecheckField();"></td>
                {{else}}
                  <td>{{tr}}CConstantSpec.unit.{{$spec->_primary_unit}}{{/tr}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=category}}</th>
                {{if $spec->_is_constant_base}}
                  <td>
                    {{mb_field object=$spec field="category" form="constantSpec_form" emptyLabel="CConstantSpec.category."}}
                  </td>
                {{else}}
                  <td>{{tr}}CConstantSpec.category.{{$spec->category}}{{/tr}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=period}}</label></th>
                {{if $spec->_is_constant_base}}
                  <td>
                    {{mb_field object=$spec field=period form="constantSpec_form" emptyLabel="CConstantSpec.category."}}
                  </td>
                {{else}}
                  <td>{{tr}}CConstantSpec.period.{{$spec->period}}{{/tr}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=value_class}}</th>
                {{if $spec->_is_constant_base}}
                  <td>{{mb_field object=$spec field=value_class form="constantSpec_form"
                    emptyLabel="CConstantSpec.value_class." onchange="constantSpec.updatefield(this.value)"}}
                  </td>
                {{else}}
                  <td>{{tr}}CConstantSpec.value_class.{{$spec->value_class}}{{/tr}}</td>
                {{/if}}
              </tr>
              <tbody id="constant_spec_type_value"></tbody>
            </table>
          </fieldset>
        </td>
      </tr>

      <tr>
        <td>
          <fieldset>
            <legend>{{tr}}CConstantSpec-msg-data optional|pl{{/tr}}</legend>

            <table class="form">
              <tr>
                <th>{{mb_label object=$spec field=min_value}}</label></th>
                {{if $spec->_is_constant_base}}
                  <td>{{mb_field object=$spec field=min_value form="constantSpec_form"}}</td>
                {{else}}
                  <td>{{$spec->min_value}}</td>
                {{/if}}
              </tr>

              <tr>
                <th>{{mb_label object=$spec field=max_value}}</th>
                {{if $spec->_is_constant_base}}
                  <td>{{mb_field object=$spec field=max_value form="constantSpec_form"}}</td>
                {{else}}
                  <td>{{$spec->max_value}}</td>
                {{/if}}
              </tr>
            </table>
          </fieldset>
        </td>
      </tr>

      {{if $spec->_is_constant_base}}
        <tr>
          <td>
            <fieldset>
              <legend>{{tr}}CConstantSpec-msg-data unit optional|pl{{/tr}}</legend>
              <table class="form">
                <tr>
                  <td colspan="2">{{tr}}CConstantSpec-msg-add unit secondary{{/tr}}</td>
                  <td><button type="button" class="button notext add" onclick="constantSpec.addFormUnitSecondary({{$spec->_secondary_unit|@count}});"></button></td>
                </tr>
                <tbody id="list_unit_secondary">

                    <tr>
                      <td class="narrow"></td>
                      <td id="constantspec_label_unit" {{if $spec->_secondary_unit|@count == 0}}class="hidden"{{/if}}>{{tr}}CConstantSpec-unit{{/tr}}</td>
                      <td id="constantspec_label_coeff" {{if $spec->_secondary_unit|@count == 0}}class="hidden"{{/if}}>{{tr}}CConstantSpec-coeff{{/tr}}</td>
                    </tr>

                  {{assign var=index value=0}}
                  {{foreach from=$spec->_secondary_unit item=_s_unit}}
                    <tr>
                      <td class="narrow"><button class="notext trash narrow" type="button" onclick="constantSpec.deleteUnitSecondary({{$index}})"></td>
                      <td><input type="text" name="unit_{{$index}}" value="{{$_s_unit.label}}"></td>
                      <td><input type="text" name="coeff_{{$index}}" value="{{$_s_unit.coeff}}"></td>
                    </tr>
                    {{assign var=index value=$index+1}}
                  {{/foreach}}
                </tbody>
              </table>
            </fieldset>
          </td>
        </tr>
      {{/if}}

      <tr>
        <td class="tbl_center button">
          <button type="button" class="save tbl_center" onclick="constantSpec.addConstantSpec(this.form);">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>


  <div id="add_alert" style="display: none">
    {{mb_include module=dPpatients template=inc_edit_alert modal=0}}
  </div>
{{if $spec->_is_constant_base}}
  <div id="calculated_constant">
    <form name="form_calculated_constant">
      <table class="form">
        <tr>
          <td></td>
        </tr>

        <tr>
          <td>
            {{if !$spec->isCalculatedConstant()}}
              {{mb_include module=dPpatients template=inc_edit_calculated}}
            {{else}}
              <button class="edit notext" type="button"></button>
            {{/if}}
          </td>
        </tr>
      </table>
    </form>
  </div>
{{/if}}
