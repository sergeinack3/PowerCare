{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hermetic_mode value='Ox\Mediboard\System\Forms\CExClass::inHermeticMode'|static_call:false}}

{{if $object->_id}}
  <button type="button" class="new" onclick="MbObject.edit('{{$object->_class}}-0')">
      {{tr}}{{$object->_class}}-title-create{{/tr}}
  </button>
{{/if}}

<form name="edit-{{$object->_guid}}" data-object_guid="{{$object->_guid}}" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this)">
    {{mb_class object=$object}}
    {{mb_key object=$object}}

  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="callback" value="MbObject.editCallback"/>

  <table class="main form">
    <col class="narrow"/>

      {{mb_include module=system template=inc_form_table_header css_class="text"}}

      {{if $object->_id}}
          {{mb_include module=system template=inc_tag_binder}}
      {{/if}}

    <tr>
      <th>{{mb_label object=$object field=name}}</th>
      <td>{{mb_field object=$object field=name register=true increment=true form="edit-`$object->_guid`"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$object field=coded}}</th>
      <td>{{mb_field object=$object field=coded register=true increment=true form="edit-`$object->_guid`"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$object field=group_id}}</th>

      <td>
          {{if $hermetic_mode}}
              {{if $object->_id}}
                  {{mb_value object=$object field=group_id tooltip=true form="edit-`$object->_guid`"}}
              {{else}}
                <div style="display: inline-block;">
                  <select name="group_id" style="width: 20em;" onchange="clearList(this);">
                      {{if $hermetic_mode && $app->_ref_user->isAdmin()}}
                        <option value=""> &ndash; Tous </option>
                      {{/if}}

                      {{foreach from=$object->_groups item=_group}}
                        <option value="{{$_group->_id}}" {{if $g == $_group->_id}} selected="selected" {{/if}}>{{$_group}}</option>
                      {{/foreach}}
                  </select>
                </div>

                <div style="display: inline-block;">
                  <div class="small-warning" style="white-space: nowrap;">Cette opération est irréversible.</div>
                </div>
              {{/if}}
          {{/if}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
          {{if $object->_id}}
            <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            <button type="button" class="trash"
                    onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$object->_view|smarty:nodefaults|JSAttribute}}'})">
                {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
      </td>
    </tr>
  </table>

</form>

{{if $object->_id}}
  <script type="text/javascript">
    Main.add(function () {
      Control.Tabs.create("ex-list-tabs", true);
    });
  </script>
  <ul id="ex-list-tabs" class="control_tabs me-align-auto">
    <li>
      <a
        href="#ex-back-list_items" {{if $object->_back.list_items|@count == 0}} class="empty" {{/if}}>{{tr}}CExList-back-list_items{{/tr}}
        <small>({{$object->_back.list_items|@count}})</small></a>
    </li>
    <li>
      <a
        href="#ex-back-concepts" {{if $object->_back.concepts|@count == 0}} class="empty" {{/if}}>{{tr}}CExList-back-concepts{{/tr}}
        <small>({{$object->_back.concepts|@count}})</small></a>
    </li>
  </ul>
  <div id="ex-back-list_items" style="display: none;" class="me-padding-0 me-align-auto">
      {{mb_include module=forms template=inc_ex_list_item_edit context=$object}}
  </div>
  <div id="ex-back-concepts" style="display: none;" class="me-padding-0 me-align-auto">
    <table class="main tbl me-no-box-shadow me-no-align">
      <tr>
        <th>
            {{mb_title class=CExConcept field=name}}
        </th>
        <th>
            {{mb_title class=CExConcept field=prop}}
        </th>
      </tr>

        {{foreach from=$object->_back.concepts item=_concept}}
          <tr>
            <td>
                {{mb_value object=$_concept field=name}}
            </td>
            <td>
                {{mb_value object=$_concept field=prop}}
            </td>
          </tr>
            {{foreachelse}}
          <tr>
            <td class="empty" colspan="2">{{tr}}CExConcept.none{{/tr}}</td>
          </tr>
        {{/foreach}}
    </table>
  </div>
{{/if}}
