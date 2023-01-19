{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-cpi" method="post" action="?"
      onsubmit="ParametrageMode.submitSaveForm(this); return false;">
  <input type="hidden" name="m" value="planningOp" />
  {{mb_class object=$charge}}
  {{mb_key object=$charge}}
  {{mb_field object=$charge field=group_id hidden=true}}
  
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$charge}}
    
    <tr>
      <th>{{mb_label object=$charge field=code}}</th>
      <td>{{mb_field object=$charge field=code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$charge field=libelle}}</th>
      <td>{{mb_field object=$charge field=libelle}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$charge field=color}}</th>
      <td>{{mb_field object=$charge field=color form="edit-cpi"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$charge field=type}}</th>
      <td>{{mb_field object=$charge field=type}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$charge field=type_pec}}</th>
      <td>{{mb_field object=$charge field=type_pec emptyLabel="Tous"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$charge field=hospit_de_jour}} {{if "dPplanningOp CSejour hdj_seance"|gconf}}{{tr}}CSejour-Hdj / Seance{{/tr}}{{/if}}</th>
      <td>{{mb_field object=$charge field=hospit_de_jour}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$charge field=actif}}</th>
      <td>{{mb_field object=$charge field=actif}}</td>
    </tr>
    
    <tr>
      <td colspan="2" class="button">
        {{if $charge->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="ParametrageMode.submitRemoveForm(this.form, '{{$charge->_view|smarty:nodefaults|JSAttribute}}')">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
