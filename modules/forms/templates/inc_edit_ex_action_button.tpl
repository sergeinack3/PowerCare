{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_ex_class_id value=$action_button->_ref_ex_group->ex_class_id}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("editActionButton");
  form.elements.text.select();
  ExFieldPredicate.initAutocomplete(form, '{{$_ex_class_id}}');
  ExActionButton.initAutocomplete(form.ex_class_field_source_id, form._ex_class_field_source_id_keywords, '{{$_ex_class_id}}');
  ExActionButton.initAutocomplete(form.ex_class_field_target_id, form._ex_class_field_target_id_keywords, '{{$_ex_class_id}}');

  var action = form.elements.action;
  
  action.observe("change", function(event){
    var element = Event.element(event);
    ExActionButton.toggleSourceTargetTrigger(element);
  });

  ExActionButton.toggleSourceTargetTrigger(action);
});
</script>

<form name="editActionButton" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$action_button}}
  {{mb_key object=$action_button}}
  {{mb_field object=$action_button field=ex_group_id hidden=true}}
  
  <input type="hidden" name="callback" value="ExActionButton.editCallback" />
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$action_button colspan="4"}}
    
    <tr>
      <th>{{mb_label object=$action_button field=action}}</th>
      <td>{{mb_field object=$action_button field=action typeEnum="select"}}</td>
      
      <th>{{mb_label object=$action_button field=text}}</th>
      <td>{{mb_field object=$action_button field=text size="40"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$action_button field=icon}}</th>
      <td colspan="3">{{mb_field object=$action_button field=icon typeEnum="radio"}}</td>
    </tr>

    <tr {{if $action_button->action !== "copy"}} style="display: none;" {{/if}}>
      <th>{{mb_label object=$action_button field=ex_class_field_source_id}}</th>
      <td colspan="3">
        <input type="text" name="_ex_class_field_source_id_keywords" size="30" value="{{$action_button->_ref_ex_class_field_source}}" />
        {{mb_field object=$action_button field=ex_class_field_source_id hidden=true}}
        
        <div class="info" style="display: inline-block;">
          Les champs absents des listes ne sont pas compatibles avec l'autre champ choisi.
        </div>
      </td>
    </tr>

    <tr {{if $action_button->action === "open"}} style="display: none;" {{/if}}>
      <th>{{mb_label object=$action_button field=ex_class_field_target_id}}</th>
      <td colspan="3">
        <input type="text" name="_ex_class_field_target_id_keywords" size="30" value="{{$action_button->_ref_ex_class_field_target}}" />
        {{mb_field object=$action_button field=ex_class_field_target_id hidden=true}}
      </td>
    </tr>

    <tr {{if $action_button->action !== "open"}} style="display: none;" {{/if}}>
      <th>{{mb_label object=$action_button field=trigger_ex_class_id}}</th>

      {{if $triggerables_cond|@count || $triggerables_others|@count}}
        <td colspan="3">
          <select name="trigger_ex_class_id" style="max-width: 20em;">
            <option value=""> &mdash; </option>
            <optgroup label="Sous-formulaires">
              {{foreach from=$triggerables_cond item=_triggerable}}
                <option value="{{$_triggerable->_id}}" {{if $action_button->trigger_ex_class_id === $_triggerable->_id}}selected="selected"{{/if}}>
                  {{$_triggerable->name}}

                  {{if !$_triggerable->group_id}}
                    (Multi-étab.)
                  {{/if}}
                </option>
              {{/foreach}}
            </optgroup>

            <optgroup label="Autres">
              {{foreach from=$triggerables_others item=_triggerable}}
                <option value="{{$_triggerable->_id}}" {{if $action_button->trigger_ex_class_id === $_triggerable->_id}}selected="selected"{{/if}}>
                  {{$_triggerable->name}}
                </option>
              {{/foreach}}
            </optgroup>
          </select>
        </td>
      {{else}}
        <td colspan="3" class="empty">Aucun formulaire à déclencher</td>
      {{/if}}
    </tr>

    <tr>
      <th>{{mb_label object=$action_button field=predicate_id}}</th>
      <td colspan="3">
        <input type="text" name="predicate_id_autocomplete_view" size="70"
               value="{{$action_button->_ref_predicate->_view}}" placeholder=" -- Toujours afficher -- " />
        {{mb_field object=$action_button field=predicate_id hidden=true}}
        <button class="new notext" onclick="ExFieldPredicate.create(null, null, this.form)" type="button">
          {{tr}}New{{/tr}}
        </button>
      </td>
    </tr>
    
    {{if $action_button->_ref_ex_group->_ref_ex_class->pixel_positionning}}
    <tr>
      <th class="narrow">{{mb_label object=$action_button field=coord_left}}</th>
      <td class="narrow">{{mb_field object=$action_button field=coord_left increment=true form=editActionButton}}</td>
      <th class="narrow">{{mb_label object=$action_button field=coord_top}}</th>
      <td>{{mb_field object=$action_button field=coord_top increment=true form=editActionButton}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$action_button field=coord_width}}</th>
      <td>{{mb_field object=$action_button field=coord_width increment=true form=editActionButton}}</td>
      <th>{{mb_label object=$action_button field=coord_height}}</th>
      <td>{{mb_field object=$action_button field=coord_height increment=true form=editActionButton}}</td>
    </tr>
    {{/if}}
    
    <tr>
      <th></th>
      <td colspan="3">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $action_button->_id}}
          <button type="button" class="trash" 
                  onclick="confirmDeletion(this.form,{ajax:true,typeName:'le bouton ',objName:'{{$action_button->_view|smarty:nodefaults|JSAttribute}}'}, {
                    check: function(){ return true; }, onComplete: function() {Control.Modal.close()}
                    })">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
