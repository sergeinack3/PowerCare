{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("editSubgroup");
  form.elements.title.select();
  ExFieldPredicate.initAutocomplete(form, '{{$ex_group->ex_class_id}}');

  Control.Tabs.create("subgroups-edit-tab", true);
});
</script>

<form name="editSubgroup" method="post" action="?" onsubmit="return onSubmitFormAjax(this, {onComplete: ExClass.edit.curry({{$ex_group->ex_class_id}})})">
  <input type="hidden" name="m" value="system" />
  {{mb_key object=$ex_subgroup}}
  {{mb_class object=$ex_subgroup}}
  {{mb_field object=$ex_subgroup field=parent_class hidden=true}}
  {{mb_field object=$ex_subgroup field=parent_id hidden=true}}

  <input type="hidden" name="_ex_group_id" value="{{$ex_group->_id}}" />
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ex_subgroup colspan="4"}}
    
    <tr>
      <th>{{mb_label object=$ex_subgroup field=title}}</th>
      <td colspan="3">{{mb_field object=$ex_subgroup field=title size=50}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_subgroup field=predicate_id}}</th>
      <td colspan="3">
        <input type="text" name="predicate_id_autocomplete_view" size="70" value="{{$ex_subgroup->_ref_predicate->_view}}" placeholder=" -- Toujours afficher -- " />
        {{mb_field object=$ex_subgroup field=predicate_id hidden=true}}
        <button class="new notext" onclick="ExFieldPredicate.create(null, null, this.form)" type="button">
          {{tr}}New{{/tr}}
        </button>
      </td>
    </tr>
    
    {{if $ex_group->_ref_ex_class->pixel_positionning}}
    <tr>
      <th class="narrow">{{mb_label object=$ex_subgroup field=coord_left}}</th>
      <td class="narrow">{{mb_field object=$ex_subgroup field=coord_left increment=true form=editSubgroup}}</td>
      <th class="narrow">{{mb_label object=$ex_subgroup field=coord_top}}</th>
      <td>{{mb_field object=$ex_subgroup field=coord_top increment=true form=editSubgroup}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$ex_subgroup field=coord_width}}</th>
      <td>{{mb_field object=$ex_subgroup field=coord_width increment=true form=editSubgroup}}</td>
      <th>{{mb_label object=$ex_subgroup field=coord_height}}</th>
      <td>{{mb_field object=$ex_subgroup field=coord_height increment=true form=editSubgroup}}</td>
    </tr>
    {{/if}}
    
    <tr>
      <th></th>
      <td colspan="3">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_subgroup->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'le sous groupe ',objName:'{{$ex_subgroup->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

<ul class="control_tabs small" id="subgroups-edit-tab">
  <li>
    <a href="#subgroup-properties" {{if $ex_subgroup->_ref_properties|@count == 0}} class="empty" {{/if}}>
      {{tr}}CExClassFieldSubgroup-back-properties{{/tr}}
      ({{$ex_subgroup->_ref_properties|@count}})
    </a>
  </li>
  <li>
    <a href="#subgroup-children" {{if $ex_subgroup->_ref_children_groups|@count == 0}} class="empty" {{/if}}>
      {{tr}}CExClassFieldSubgroup-back-subgroups{{/tr}}
      ({{$ex_subgroup->_ref_children_groups|@count}})
    </a>
  </li>
</ul>

<div id="subgroup-properties">
  {{mb_include module=forms template=inc_list_entity_properties object=$ex_subgroup}}
</div>

<div id="subgroup-children">
  <table class="main tbl">
    <tr>
      <th class="narrow"></th>
      <th>{{mb_title class=CExClassFieldSubgroup field=title}}</th>
    </tr>
  {{foreach from=$ex_subgroup->_ref_children_groups item=_subgroup}}
    <tr>
      <td class="narrow">
        <button class="edit notext compact" onclick="ExSubgroup.edit('{{$_subgroup->_id}}', '{{$ex_group->_id}}');">{{tr}}Edit{{/tr}}</button>
      </td>
      <td>{{mb_value object=$_subgroup field=title}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CExClassFieldSubgroup-back-subgroups.empty{{/tr}}</td>
    </tr>
  {{/foreach}}
  </table>
</div>

