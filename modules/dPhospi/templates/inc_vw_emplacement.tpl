{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CEmplacement" action="" method="post" onsubmit="return PlanEtage.onSubmit(this)">
  {{mb_class object=$emplacement}}
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$emplacement}}
  
  <table class="form">
    <tr>
      <th colspan="2" class="title" {{if $emplacement->_id}}style="color:#FD4;"{{/if}}>
        {{if $emplacement->_id}}
          Modification de l'emplacement
          <br />
          de la chambre: '{{$emplacement->_ref_chambre->nom}}'
        {{else}}
          Création d'un box d'urgence
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$emplacement field=chambre_id}}</th>
      <td>{{mb_value object=$emplacement field=chambre_id}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement field="color"}}</th>
      <td>

        {{mb_field object=$emplacement field="color" form="Edit-CEmplacement"}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement field=hauteur}}</th>
      <td>{{mb_field object=$emplacement field=hauteur increment=true form="Edit-CEmplacement"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$emplacement field=largeur}}</th>
      <td>{{mb_field object=$emplacement field=largeur increment=true form="Edit-CEmplacement"}}</td>
    </tr>
    
    <tr>
      <td class="button" colspan="2">
        {{if $emplacement->_id}}
          <button class="modify" type="submit">{{tr}}Modify{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form,{typeName:'l\'emplacement de la chambre',objName: '{{$emplacement->_ref_chambre->nom|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>