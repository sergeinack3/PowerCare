{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id}}
<button type="button" class="new" onclick="MbObject.edit('{{$object->_class}}-0')">
  {{tr}}{{$object->_class}}-title-create{{/tr}}
</button>
{{/if}}

<form name="edit-{{$object->_guid}}" data-object_guid="{{$object->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$object}}
  {{mb_key object=$object}}
  
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="MbObject.editCallback" />
  
  <table class="main form">
    <col class="narrow" />
    
    {{mb_include module=system template=inc_form_table_header css_class="text"}}
    
    {{if $object->_id}}
      {{mb_include module=system template=inc_tag_binder}}
    {{/if}}
    
    {{foreach from=$object->_specs item=_spec key=_field}}
      {{if ($_field.0 !== "_") && ($_field != $object->_spec->key) && ($_spec->show != "0") || ($_field.0 === "_" && $_spec->show == 1)}}
      <tr>
        <th>{{mb_label object=$object field=$_field}}</th>
        <td>{{mb_field object=$object field=$_field register=true increment=true form="edit-`$object->_guid`"}}</td>
      </tr>
      {{/if}}
    {{/foreach}}
    
    <tr>
      <td colspan="2" class="button">
        {{if $object->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$object->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>

</form>