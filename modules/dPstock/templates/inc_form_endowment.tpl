{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=endowment ajax=1}}
<script type="text/javascript">
  Main.add(function () {
    $("list-{{$endowment->_guid}}").addUniqueClassName("selected");
  });
</script>

<button class="new" onclick="loadEndowment(0)">{{tr}}CProductEndowment-title-create{{/tr}}</button>

<form name="edit_endowment" action="" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$endowment}}
  {{mb_key   object=$endowment}}
  <input type="hidden" name="callback" value="loadEndowment"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="group_id" value="{{$group_id}}"/>
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$endowment}}
    <tr>
      <th>{{mb_label object=$endowment field="name"}}</th>
      <td>{{mb_field object=$endowment field="name"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$endowment field="service_id"}}</th>
      <td>{{mb_field object=$endowment field="service_id" form="edit_endowment" autocomplete="true,1,50,false,true"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$endowment field=actif}}</th>
      <td>{{mb_field object=$endowment field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        {{if $endowment->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$endowment->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
          <button class="hslip" type="button" onclick="Endowment.duplicateEndowment({{$endowment->_id}})">{{tr}}Duplicate{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $endowment->_id}}
  <div id="endowment_list_products">
    {{mb_include module=stock template=inc_list_endowment_product page=$page step=$step}}
  </div>
{{/if}}