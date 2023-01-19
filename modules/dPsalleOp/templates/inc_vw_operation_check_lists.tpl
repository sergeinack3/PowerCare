{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=active_list_type value=null}}
{{assign var=types value='Ox\Mediboard\SalleOp\CDailyCheckList'|static:types}}
{{assign var=checks_list_group value='Ox\Mediboard\SalleOp\CDailyCheckListGroup::loadChecklistGroup'|static_call:null}}
{{assign var=checks_list_by_values value='Ox\Mediboard\SalleOp\CDailyCheckList::getTypeByValues'|static_call:null}}

{{foreach from=$operation_check_lists item=_check_list key=_type}}
  {{if $_check_list->_readonly && !$active_list_type}}
    {{assign var=active_list_type value=$types.$_type}}
  {{/if}}
{{/foreach}}

{{foreach from=$check_lists_no_has item=_check_list key=_type}}
    {{if $_check_list->_readonly && !$active_list_type}}
        {{assign var=active_list_type value="CDailyCheckListGroup-`$_check_list->_ref_list_type->check_list_group_id`"}}
    {{/if}}
{{/foreach}}

{{if !$active_list_type}}
  {{if $selOp->_ref_salle->checklist_defaut_has}}
    {{assign var=active_list_type value=$selOp->_ref_salle->checklist_defaut_has}}
  {{elseif $selOp->_ref_salle->checklist_defaut_id}}
    {{assign var=active_list_type value="CDailyCheckListGroup-`$selOp->_ref_salle->checklist_defaut_id`"}}
  {{/if}}
{{/if}}

{{mb_script module=salleOp script=edit_checklist ajax=1}}
<script>
  Main.add(function() {
    {{foreach from=$checks_list_group item=_checklist_group}}
      EditDailyCheck.checkListTypes[EditDailyCheck.checkListTypes.length+1] = '{{$_checklist_group->_guid}}';
    {{/foreach}}

    var first_checklist = $('select_checktlist_type').getElementsByTagName('option')[0] ? $('select_checktlist_type').getElementsByTagName('option')[0].value : null;
    EditDailyCheck.showCheckListType($("checkList-container"), "{{$active_list_type}}" || first_checklist);
  });
</script>

<table class="main form" id="checkList-container">
  <col style="width: 33%" />
  <col style="width: 33%" />
  <col style="width: 33%" />
  
  <tr>
    <th colspan="10" class="title">

      <button class="down" onclick="$('check-lists').toggle(); $(this).toggleClassName('down').toggleClassName('up')">
        Check list Sécurité du Patient
      </button>
      
      <select onchange="EditDailyCheck.showCheckListType($(this).up('table'), $V(this))" style="max-width: 18em;" id="select_checktlist_type">
        {{foreach from=$checks_list_group item=_checklist_group}}
          <option value="{{$_checklist_group->_guid}}" {{if $active_list_type == $_checklist_group->title ||
          $active_list_type == $_checklist_group->_guid}} selected {{/if}}>
            {{$_checklist_group->title}}
          </option>
        {{/foreach}}
        {{if $checks_list_group|@count}}
          <option value="" disabled>&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</option>
        {{/if}}
        {{foreach from='Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_lists key=ref_pays item=tab_list_checklist}}
          {{if $ref_pays == $conf.ref_pays }}
            {{foreach from=$tab_list_checklist key=_type item=_label}}
              <option value="{{$_type}}" {{if $active_list_type == $_type}} selected {{/if}}>{{$_label}}</option>
            {{/foreach}}
          {{/if}}
        {{/foreach}}
      </select>

      {{if $conf.ref_pays == 1}}
        <img height="20" src="images/pictures/logo-has-small.png" />
      {{/if}}

      <button class="print" onclick="(new Url('dPsalleOp', 'print_check_list_operation')).addParam('operation_id', '{{$selOp->_id}}').popup(800, 600, 'check_list')">
        {{tr}}Print{{/tr}}
      </button>

      {{mb_include module=forms template=inc_widget_ex_class_register object=$selOp event_name=checklist cssStyle="display: inline-block; font-size: 0.8em;"}}
      {{mb_include module=bloc template=inc_button_besoins_ressources type=operation_id object_id=$selOp->_id usage_bloc=1}}
    </th>
  </tr>

  {{foreach from=$checks_list_by_values key=name_checklist item=check_list_type}}
    {{if $name_checklist == "cesarienne"}}
      <tr class="{{$name_checklist}}" style="display: none;">
        <td></td>
        <td class="text">
          <span style="font-size: 1.6em;color:red;font-weight: bold;">
            {{mb_label class=CDailyCheckList field=code_red}}
          </span>
          <form name="code_red_cesarienne" method="post" action="?">
            <input name="code_red" type="checkbox" value="" onclick="EditDailyCheck.codeRouge(this)"/>
          </form>
        </td>
        <td></td>
      </tr>
    {{/if}}
    <tr class="{{$name_checklist}}" style="display: none;">
      {{foreach from=$check_list_type item=check_list_cell name=check_list_cell_loop}}
        <td class="button" id="{{$check_list_cell}}-title"
            {{if $smarty.foreach.check_list_cell_loop.first && $check_list_type|@count == 2}}colspan="2"{{/if}} >
          <h3 style="margin: 2px;">
            {{me_img src=$operation_check_lists.$check_list_cell->_readonly|ternary:"tick.png":"cross.png" icon=$operation_check_lists.$check_list_cell->_readonly|ternary:"tick me-success":"cancel me-error"}}
            {{tr}}CDailyCheckList.{{$name_checklist}}.{{$check_list_cell}}.title{{/tr}}
          </h3>
          <small>{{tr}}CDailyCheckList.{{$name_checklist}}.{{$check_list_cell}}.small{{/tr}}</small>
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}

  {{* Checklist paramétrées*}}
  {{foreach from=$checks_list_group item=_checklist_group}}
    <tr class="{{$_checklist_group->_guid}}" style="display: none;">
      {{foreach from=$_checklist_group->_ref_check_liste_types item=check_list_type name=check_list_cell_loop}}
        <td class="button" id="{{$check_list_type->_id}}-param-title"
            {{if $smarty.foreach.check_list_cell_loop.first && $_checklist_group->_ref_check_liste_types|@count == 2}}colspan="2"{{/if}} >
          <h3 style="margin: 2px;">
            {{assign var=check_list_type_id value=$check_list_type->_id}}
            {{me_img src=$check_lists_no_has.$check_list_type_id->_readonly|ternary:"tick.png":"cross.png" icon=$check_lists_no_has.$check_list_type_id->_readonly|ternary:"tick me-success":"cancel me-error"}}
            {{$check_list_type->title}}
          </h3>
          <small>{{$check_list_type->description}}</small>
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}

  <tbody id="check-lists" style="display: none;">
    {{if !$selOp->_ref_sejour->entree_reelle}}
      <tr>
        <td colspan="{{$checks_list_by_values|@count}}">
          <div class="small-error">{{tr}}CDailyCheckList-error-entree_reelle-sejour{{/tr}}</div>
        </td>
      </tr>
    {{/if}}
    {{if $selOp->date != $dnow}}
      <tr>
        <td colspan="{{$checks_list_by_values|@count}}">
          <div class="small-warning">{{tr}}CDailyCheckList-warning-date_diff_interv{{/tr}}</div>
        </td>
      </tr>
    {{/if}}
    {{foreach from=$checks_list_by_values key=name_checklist item=check_list_type}}
      <tr class="{{$name_checklist}}" style="display: none;">
        {{foreach from=$check_list_type item=check_list_cell name=check_list_cell_loop}}
          <td class="me-valign-top" style="padding:{{if $check_list_type|@count == 2}}0{{else}}1{{/if}}px;"
              {{if $smarty.foreach.check_list_cell_loop.first && $check_list_type|@count == 2}}colspan="2"{{/if}}>
            <div id="check_list_{{$check_list_cell}}_">
              {{mb_include module=salleOp template=inc_edit_check_list
              check_list=$operation_check_lists.$check_list_cell
              check_item_categories=$operation_check_item_categories.$check_list_cell
              personnel=$listValidateurs}}
            </div>
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}

    {{* Checklist paramétrées*}}
    {{foreach from=$checks_list_group item=_checklist_group}}
      <tr class="{{$_checklist_group->_guid}}" style="display: none;">
        {{foreach from=$_checklist_group->_ref_check_liste_types item=check_list_type name=check_list_cell_loop}}
          {{assign var=check_list_type_id value=$check_list_type->_id}}
          <td class="me-valign-top" style="padding:{{if $_checklist_group->_ref_check_liste_types|@count == 2}}0{{else}}1{{/if}}px;"
              {{if $smarty.foreach.check_list_cell_loop.first && $_checklist_group->_ref_check_liste_types|@count == 2}}colspan="2"{{/if}}>
            <div id="check_list__{{$check_list_type_id}}">
              {{mb_include module=salleOp template=inc_edit_check_list
              check_list=$check_lists_no_has.$check_list_type_id
              check_item_categories=$check_items_no_has_categories.$check_list_type_id
              personnel=$listValidateurs_no_has last_item=$smarty.foreach.check_list_cell_loop.last}}
            </div>
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="3" class="button text">
        <hr />
        Le rôle du coordonnateur check-list sous la responsabilité du(es) chirurgien(s) et anesthésiste(s) responsables
        de l'intervention est de ne cocher les items de la check list  que (1) si la vérification a bien été effectuée,
        (2) si elle a été faite oralement en présence des membres de l'équipe concernée et (3) si les non conformités <span id="asterisque_checklist">(marquées d'un *)</span>
        ont fait l'objet d'une concertation en équipe et d'une décision qui doit le cas échéant être rapportée dans l'encart spécifique.
      </td>
    </tr>
  </tbody>
</table>
