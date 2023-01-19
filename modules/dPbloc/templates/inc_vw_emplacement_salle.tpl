{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="emplacement_salle" action="" method="post" onsubmit="return PlanEtageBloc.onSubmit(this);">
  {{mb_class object=$emplacement_salle}}
  {{mb_key   object=$emplacement_salle}}
  
  <table class="form">
    <tr>
      <th colspan="2" class="title" {{if $emplacement_salle->_id}}style="color:#FD4;"{{/if}}>
        {{if $emplacement_salle->_id}}
          {{tr}}CEmplacementSalle-Changing the location of the room{{/tr}}: '{{$emplacement_salle->_ref_salle->nom}}'
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$emplacement_salle field=salle_id}}</th>
      <td>{{mb_value object=$emplacement_salle field=salle_id}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement_salle field="color"}}</th>
      <td>

        {{mb_field object=$emplacement_salle field="color" form="emplacement_salle"}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement_salle field=hauteur}}</th>
      <td>{{mb_field object=$emplacement_salle field=hauteur increment=true form="Edit_emplacement_salle"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement_salle field=largeur}}</th>
      <td>{{mb_field object=$emplacement_salle field=largeur increment=true form="Edit_emplacement_salle"}}</td>
    </tr>
    
    <tr>
      <td class="button" colspan="2">
        {{if $emplacement_salle->_id}}
          <button class="modify" type="submit">{{tr}}Modify{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {typeName:'l\'emplacement de la salle',objName: '{{$emplacement_salle->_ref_chambre->nom|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
