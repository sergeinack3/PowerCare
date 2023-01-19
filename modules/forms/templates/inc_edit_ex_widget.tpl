{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_ex_class_id value=$widget->_ref_ex_group->ex_class_id}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("editWidget");
  ExFieldPredicate.initAutocomplete(form, '{{$_ex_class_id}}');
});
</script>

<form name="editWidget" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$widget}}
  {{mb_key object=$widget}}
  {{mb_field object=$widget field=ex_group_id hidden=true}}
  
  <input type="hidden" name="callback" value="ExWidget.editCallback" />
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$widget colspan="4"}}
    
    <tr>
      <th>{{mb_label object=$widget field=name}}</th>
      <td colspan="3">{{mb_field object=$widget field=name typeEnum="select"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$widget field=predicate_id}}</th>
      <td colspan="3">
        <input type="text" name="predicate_id_autocomplete_view" size="70"
               value="{{$widget->_ref_predicate->_view}}" placeholder=" -- Toujours afficher -- " />
        {{mb_field object=$widget field=predicate_id hidden=true}}
        <button class="new notext" onclick="ExFieldPredicate.create(null, null, this.form)" type="button">
          {{tr}}New{{/tr}}
        </button>
      </td>
    </tr>
    
    {{if $widget->_ref_ex_group->_ref_ex_class->pixel_positionning}}
    <tr>
      <th class="narrow">{{mb_label object=$widget field=coord_left}}</th>
      <td class="narrow">{{mb_field object=$widget field=coord_left increment=true form=editWidget}}</td>
      <th class="narrow">{{mb_label object=$widget field=coord_top}}</th>
      <td>{{mb_field object=$widget field=coord_top increment=true form=editWidget}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$widget field=coord_width}}</th>
      <td>{{mb_field object=$widget field=coord_width increment=true form=editWidget}}</td>
      <th>{{mb_label object=$widget field=coord_height}}</th>
      <td>{{mb_field object=$widget field=coord_height increment=true form=editWidget}}</td>
    </tr>
    {{/if}}
    
    <tr>
      <th></th>
      <td colspan="3">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $widget->_id}}
          <button type="button" class="trash" 
                  onclick="confirmDeletion(this.form,{ajax:true,typeName:'la widget ',objName:'{{$widget->_view|smarty:nodefaults|JSAttribute}}'}, {
                    check: function(){ return true; }, onComplete: function() {Control.Modal.close()}
                    })">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
