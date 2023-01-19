{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=modal value=1}}

<form name="form_add_alert">
  <script>
    Main.add(function () {
      Control.Tabs.create('alert_tabs');
      constantSpec.check();
    });
  </script>

  {{if $spec->_is_constant_base || !$spec->_alert}}
    {{assign var=alert_def value=$spec->_ref_alert}}
  {{else}}
    {{assign var=alert_def value=$spec->_alert}}
  {{/if}}

  <ul id="alert_tabs" class="control_tabs small">
    {{foreach from=$alert_num item=index}}
      <li><a href="#edit_alert_{{$index}}" {{if !$alert->hasLevelAlert($index) && !$alert_def->hasLevelAlert($index)}}class="empty"{{/if}}>
          {{tr}}CConstantAlert.level.{{$index}}{{/tr}}
          <i style="display:inline;" class="fa fa-exclamation-circle constant_alert_{{$index}}"></i>
        </a></li>
    {{/foreach}}
    {{if $spec->_ref_alert && $spec->_ref_alert->_id}}
      <li>
        <button type="button" class="trash notext" onclick="constantSpec.deleteAlert('{{$spec->code}}',{{$modal}})"></button>
      </li>
    {{/if}}
  </ul>

  <fieldset>
    <input type="hidden" name="min_value_spec" value="{{$spec->min_value}}">
    <input type="hidden" name="max_value_spec" value="{{$spec->max_value}}">
    <input type="hidden" name="alert_id" value="{{$alert->_id}}">
    <input type="hidden" name="spec_id" value="{{$spec->_id}}">
    {{if $spec->_alert}}
      {{assign var=alert_def value=$spec->_alert}}
    {{/if}}
    {{foreach from=$alert_num item=index}}
      <div id="edit_alert_{{$index}}" style="display: none;">
        <table class="form">
          <tr>
            <th class="category" style="text-align: left"><button type="button" class="notext trash" onclick="constantSpec.deleteAlertLevel({{$index}})"></button></th>
            <th class="category">{{tr}}CConstantAlert-msg-default{{/tr}}</th>
            <th class="category">{{tr}}CConstantAlert-msg-active{{/tr}}</th>
          </tr>

          <tr>
            <th>{{mb_label object=$alert field=seuil_bas_$index}}</th>
            <td>{{mb_value object=$alert_def field=seuil_bas_$index}}
            <td>{{mb_field object=$alert field=seuil_bas_$index onchange="constantSpec.check()"}}
              <i  id="seuil_bas_alert_{{$index}}" style="display: none" class="hidden fa fa-exclamation-circle constant_alert_3"></i>
            </td>
          </tr>

          <tr>
            <th>{{mb_label object=$alert field=seuil_haut_$index}}</th>
            <td>{{mb_value object=$alert_def field=seuil_haut_$index}}
            <td>{{mb_field object=$alert field=seuil_haut_$index onchange="constantSpec.check()"}}
              <i id="seuil_haut_alert_{{$index}}" style="display: none" class="fa fa-exclamation-circle constant_alert_3"></i>
            </td>
          </tr>

          <tr>
            <th>{{mb_label object=$alert field=comment_bas_$index}}</th>
            <td>{{mb_value object=$alert_def field=comment_bas_$index}}</td>
            <td>{{mb_field object=$alert field=comment_bas_$index onchange="constantSpec.check()"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$alert field=comment_haut_$index}}</th>
            <td>{{mb_value object=$alert_def field=comment_haut_$index}}</td>
            <td>{{mb_field object=$alert field=comment_haut_$index onchange="constantSpec.check()"}}</td>
          </tr>

        </table>
      </div>
    {{/foreach}}
  </fieldset>
  <button type="button" onclick="constantSpec.addAlert(this.form, {{$modal}});" class="button save">{{tr}}Save{{/tr}}</button>
</form>
