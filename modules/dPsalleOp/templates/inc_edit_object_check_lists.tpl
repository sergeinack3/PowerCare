{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  printCheckList = function(id) {
    var url = new Url('salleOp', 'print_check_list_pose_disp_vasc');
    url.addParam('pose_disp_vasc_id', id);
    url.popup(800, 600, 'check_list');
  }
</script>

<button class="print" onclick="printCheckList('{{$object->_id}}')" style="float: right;">{{tr}}Print{{/tr}}</button>

<h1>{{tr}}{{$object->_class}}{{/tr}}</h1>

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire'}}
  <table class="main tbl">
    <tr>
      <td>
        <strong>{{mb_label object=$object field=date}}</strong>: {{mb_value object=$object field=date}}<br />
        <strong>{{mb_label object=$object field=lieu}}</strong>: {{mb_value object=$object field=lieu}}<br />
        <strong>{{mb_label object=$object field=urgence}}</strong>: {{mb_value object=$object field=urgence}}<br />
      </td>
      <td>
        <strong>{{mb_label object=$object field=operateur_id}}</strong>: {{mb_value object=$object field=operateur_id}}<br />
        <strong>{{mb_label object=$object field=encadrant_id}}</strong>: {{mb_value object=$object field=encadrant_id}}<br />
      </td>
      <td>
        <strong>{{mb_label object=$object field=type_materiel}}</strong>: {{mb_value object=$object field=type_materiel}}<br />
        <strong>{{mb_label object=$object field=voie_abord_vasc}}</strong>: {{mb_value object=$object field=voie_abord_vasc}}<br />
      </td>
    </tr>
  </table>
{{/if}}

<table class="main form" id="checkList-container">
  {{foreach from=$check_item_categories item=_cat key=_key}}
    <col style="width: 33%" />
  {{/foreach}}
  
  <tr class="{{$type_group}}">
    {{foreach from=$check_lists item=_cat key=_key}}
    <td class="button" id="{{$_key}}-title">
      <h3 style="margin: 2px;">
        <img src="images/icons/{{$_cat->_readonly|ternary:"tick":"cross"}}.png" />
        {{tr}}CDailyCheckItemCategory.type.{{$_key}}{{/tr}}
      </h3>
    </td>
    {{/foreach}}
  </tr>
  
  <tr class="{{$type_group}}">
    {{foreach from=$check_lists item=_cat key=_key}}
      <td style="padding:0;">
        <div id="check_list_{{$_key}}_{{$_cat->list_type_id}}">
        {{assign var=check_list value=$_cat}}
        {{mb_include module=salleOp template=inc_edit_check_list
             check_item_categories=$check_item_categories.$_key
             personnel=$validateurs_list}}
        </div>
      </td>
    {{/foreach}}
  </tr>
  
  <tr>
    <td colspan="3" class="button text">
      <hr />
      Le r�le du coordonnateur check-list sous la responsabilit� du(es) chirurgien(s) et anesth�siste(s) responsables 
      de l'intervention est de ne cocher les items de la check list  que (1) si la v�rification a bien �t� effectu�e,  
      (2) si elle a �t� faite oralement en pr�sence des membres de l'�quipe concern�e et (3) si les non conformit�s (marqu�es d'un *) 
      ont fait l'objet d'une concertation en �quipe et d'une d�cision qui doit le cas �ch�ant �tre rapport�e dans l'encart sp�cifique.
    </td>
  </tr>
</table>