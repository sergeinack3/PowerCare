{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=ex_class value=$object->_ref_ex_class}}

<table class="tbl">
  <tr>
    <th class="title text">
      {{mb_include module=system template=inc_object_notes}}
      {{mb_include module=system template=inc_object_idsante400}}

      <a href="#1" style="float: right;" onclick="ExObject.history('{{$object->_id}}', '{{$object->_ex_class_id}}'); return false;"
         onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'objectViewHistory')">
        {{me_img src="history.gif" width=16 height=16 icon="history" class="me-primary"}}
      </a>
      {{$ex_class}}
    </th>
  </tr>

  <tr>
    <td style="vertical-align: top;" class="text">
      <strong>{{mb_label class=CExObject field=object_id}}</strong> : {{mb_value object=$object field=object_id tooltip=true}} <br />
      <strong>{{mb_label class=CExObject field=owner_id}}</strong> : {{mb_value object=$object field=owner_id tooltip=true}} <br />
      <strong>{{mb_label class=CExObject field=datetime_create}}</strong> : {{mb_value object=$object field=datetime_create}} <br />
      <strong>{{mb_label class=CExObject field=datetime_edit}}</strong> : {{mb_value object=$object field=datetime_edit}} <br />
    </td>
  </tr>
  <tr>
    <td class="button">
      {{if $object->canPerm('v')}}
        {{assign var=object_guid value="`$object->object_class`-`$object->object_id`"}}
        <button class="edit"
                onclick="ExObject.edit('{{$object->_id}}', '{{$object->_ex_class_id}}', '{{$object_guid}}')">{{tr}}Edit{{/tr}}</button>
        <button class="search"
                onclick="ExObject.display('{{$object->_id}}', '{{$object->_ex_class_id}}', '{{$object_guid}}')">{{tr}}Display{{/tr}}</button>
        <button class="print"
                onclick="ExObject.print('{{$object->_id}}', '{{$object->_ex_class_id}}', '{{$object_guid}}')">{{tr}}Print{{/tr}}</button>
        <form name="delForm{{$object->_guid}}" method="post">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="dosql" value="do_ex_object_aed" />
          {{mb_key object=$object}}
          <input type="hidden" name="_ex_class_id" value="{{$object->_ex_class_id}}" />
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {typeName: 'le formulaire', objName: '{{$ex_class->name|smarty:nodefaults|JSAttribute}}'}, function() {
                    if (window.loadAllDocs) {
                      window.loadAllDocs()
                    }
                  })">{{tr}}Delete{{/tr}}</button>

        </form>
      {{/if}}
    </td>
  </tr>
</table>
