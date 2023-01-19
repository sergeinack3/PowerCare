{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-{{$commande->_guid}}" action="?m={{$m}}" method="post">
  {{mb_key   object=$commande}}
  {{mb_class object=$commande}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$commande}}
    {{assign var=op value=$commande->_ref_operation}}

    <tr>
      <th>{{mb_label object=$commande field="operation_id"}}</th>
      <td>
        le {{mb_value object=$op field="date"}}
        pour
        <span onmouseover="ObjectTooltip.createEx(this, '{{$op->_ref_patient->_guid}}')">
          {{$op->_ref_patient}}
        </span>
        par le Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$op->_ref_chir}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$commande field="etat"}}</th>
      <td>
        {{if $can->edit}}
          {{mb_field object=$commande field="etat"}}
        {{else}}
          {{mb_value object=$commande field="etat"}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$commande field="commentaire"}}</th>
      <td>{{mb_field object=$commande field="commentaire" textearea=1}}</td>
    </tr>
    
    <tr>
      <td class="button" colspan="2">
        <button class="submit" type="button" onclick="return onSubmitFormAjax(this.form, {onComplete: function() {Control.Modal.close();}});">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>