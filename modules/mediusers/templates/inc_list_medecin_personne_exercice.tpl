{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <td colspan="9">
        {{mb_include module=system template=inc_pagination total=$total current=$page change_page='CMediusers.changePageMedecinAnnuaire' step=$step}}
    </td>
  </tr>

  <tr>
    {{if $user_id}}
      <th></th>
    {{/if}}
    <th class="narrow">{{mb_title class=CPersonneExercice field='identifiant'}}</th>
    <th class="narrow">{{mb_title class=CPersonneExercice field='nom'}}</th>
    <th class="narrow">{{mb_title class=CPersonneExercice field='prenom'}}</th>
    <th>{{mb_title class=CExercicePlace field=raison_sociale}}</th>
    <th class="narrow">{{mb_title class=CPersonneExercice field='cp'}}</th>
    <th class="narrow">{{mb_title class=CPersonneExercice field='libelle_commune'}}</th>
    <th class="narrow">{{mb_title class=CPersonneExercice field='libelle_profession'}}</th>
    <th class="text narrow">{{tr}}Action{{/tr}}</th>
  </tr>

    {{foreach from=$praticiens item=_praticien}}
      {{assign var=praticien       value=$_praticien.praticien}}
      {{assign var=exercice_places value=$_praticien.exercicePlaces}}
      <tr class="{{if !$praticien->id_technique_structure}}opacity-50{{/if}}" >
        {{if $user_id}}
          <th class="narrow">
              {{assign var=icon_compare value=$practicioner_mediuser.error|ternary:"cancel me-error":"tick me-success"}}
            <i class='me-icon {{$icon_compare}}'
               onmouseover="ObjectTooltip.createDOM(this, 'compare-practicioners-{{$praticien->_id}}');"></i>

            <div id="compare-practicioners-{{$praticien->_id}}" style="display: none;">
              <table class="tbl">
                <tr>
                  <th class="title" colspan="3">
                      {{tr}}CMediusers-Comparison of the Mediuser and the External doctor{{/tr}}
                  </th>
                </tr>
                <tr>
                  <th></th>
                  <th class="category">{{tr}}CContentHTML._list_classes.CMediusers{{/tr}}</th>
                  <th class="category">{{tr}}CPersonneExercice{{/tr}}</th>
                </tr>
                  {{foreach from=$practicioner_mediuser.fields key=_field item=_values}}
                    <tr>
                      <td>{{tr}}CPersonneExercice-{{$_field}}-court{{/tr}}</td>
                        {{foreach from=$_values key=_medisuer item=_practicioner}}
                          <td>{{$_medisuer}}</td>
                          <td>{{$_practicioner}}</td>
                        {{/foreach}}
                    </tr>
                  {{/foreach}}
              </table>
            </div>
          </th>
        {{/if}}
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}');">
          {{mb_value object=$praticien field='identifiant'}}
        </span>
        </td>
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}');">
          {{mb_value object=$praticien field='nom'}}
        </span>
        </td>
        <td>
            {{mb_value object=$praticien field='prenom'}}
        </td>
        <td class="text">
          {{foreach from=$exercice_places item=_exercice_place}}
            {{mb_value object=$_exercice_place field=raison_sociale}}
            <br/>
          {{/foreach}}
        </td>
        <td>
            {{mb_value object=$praticien field='cp'}}
        </td>
        <td>
            {{mb_value object=$praticien field='libelle_commune'}}
        </td>
        <td class="compact text">
            {{mb_value object=$praticien field='libelle_profession'}}
        </td>
        <td class="text" id="action-medecin-mediuser-{{$praticien->_id}}">
          {{if $praticien->id_technique_structure}}
              {{if $user_id}}
                <button class="edit notext" type="button" onclick="CMediusers.fillMediuserFields(null, '{{$praticien->_id}}', '{{$user_id}}');">
                    {{tr}}Modify{{/tr}}
                </button>
              {{else}}
                <button class="new notext" type="button" onclick="CMediusers.fillMediuserFields(null, '{{$praticien->_id}}');">
                    {{tr}}Create{{/tr}}
                </button>
              {{/if}}
          {{else}}
              <span>{{tr}}CPersonneExercice-id_technique_structure.none{{/tr}}</span>
          {{/if}}
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="7">
            {{tr}}CPersonneExercice.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
</table>
