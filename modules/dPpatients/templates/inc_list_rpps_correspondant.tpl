{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name='form' method="get" action="?">
  <br>
  <tr>
    <td>
        {{mb_include module=system template=inc_pagination change_page='Correspondant.refreshPageCorrespondant' total=$nb_correspondants current=$start step=$step}}
    </td>
  </tr>
  <table class="tbl">
    <tr>
      <th></th>
      <th class="narrow"></th>
      <th>{{mb_title class=CMedecin field=nom}}</th>
      <th>{{mb_title class=CMedecin field=rpps}}</th>
      <th class="narrow">{{mb_title class=CMedecin field=sexe}}</th>
      <th>{{mb_title class=CExercicePlace field=raison_sociale}}</th>
      <th>{{mb_title class=CMedecin field=adresse}}(s)</th>
      <th>{{mb_title class=CMedecin field=type}}</th>
      <th>{{mb_title class=CMedecin field=disciplines}}</th>
    </tr>
      {{foreach from=$correspondants item=_correspondant}}
      {{assign var=medecins value=$_correspondant.medecin}}
      {{assign var=disciplines value=$_correspondant.disciplines}}
      {{assign var=medecin value=$_correspondant.medecin[0]}}
      {{assign var=exercice_places value=$_correspondant.exercicePlaces}}
      {{assign var=already_imported value=$medecin->_alreadyImported}}
    <tr>
      <td style="text-align: center">
          {{if !$already_imported}}
        <input type="checkbox" name="medecins_rpps" value="{{$medecin->rpps}}"
               onchange="Correspondant.enableAddButton()"/>
          {{else}}
        <i title="{{tr}}mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-CPersonneExercice already added{{/tr}}"
           class="fas fa-check me-success"></i>
        {{/if}}
      </td>
      <td>
          {{if $already_imported}}
        <button
          title="{{tr}}mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-CPersonneExercice-action-Update{{/tr}}"
          type="button" class="change notext"
          onclick="Correspondant.updateCorrespondant({{$medecin->rpps}})">
          {{/if}}
      </td>

      <!-- Nom et prénom -->
      <td class="text">
          {{if $medecin->nom || $medecin->prenom}}
        <p>{{$medecin->nom}} {{$medecin->prenom|strtolower|ucfirst}}</p>
          {{else}}
        <p><div class="empty">N/A</div></p>
        {{/if}}
      </td>

      <!-- RPPS -->
      <td class="text">
        {{if $medecin->rpps}}
          <p>{{$medecin->rpps}}</p>
        {{else}}
          <p><div class="empty">N/A</div></p>
        {{/if}}
      </td>

      <!-- Sexe -->
      <td style="text-align: center">
        {{if $medecin->sexe == "f"}}
          <i class="fas fa-venus" style="color: deeppink;"></i>
        {{elseif $medecin->sexe == "m"}}
          <i class="fas fa-mars" style="color: blue;"></i>
        {{else}}
          <i class="fas fa-genderless" style="color: grey;"></i>
        {{/if}}
      </td>

      <!-- Raison sociale -->
      <td class="text">
        {{foreach from=$exercice_places item=_exercice_place}}
          {{if $_exercice_place->raison_sociale}}
            <p>{{mb_value object=$_exercice_place field=raison_sociale}}</p>
          {{else}}
            <p><div class="empty">N/A</div></p>
          {{/if}}
        {{/foreach}}
      </td>

      <!-- Adresse -->
      <td class="text">
        {{foreach from=$exercice_places item=_exercice_place}}
          {{if $_exercice_place->adresse || $_exercice_place->cp || $_exercice_place->commune}}
            <p>
                {{mb_value object=$_exercice_place field=adresse}}
                {{mb_value object=$_exercice_place field=cp}}
                {{mb_value object=$_exercice_place field=commune}}
            </p>
          {{else}}
            <p><div class="empty">N/A</div></p>
          {{/if}}
        {{/foreach}}
      </td>

      <!-- Types -->
      <td class="text">
        {{foreach from=$medecins item=_medecin}}
          {{if $_medecin->type}}
            <p>{{mb_ditto name=type_$medecin value=$_medecin->getFormattedValue('type')}}</p>
          {{else}}
            <p><div class="empty">N/A</div></p>
          {{/if}}
        {{/foreach}}
      </td>

      <!-- Disciplines -->
      <td class="text">
        {{foreach from=$disciplines item=_discipline}}
          {{if $_discipline}}
            <p>{{mb_ditto name=discipline_$medecin value=$_discipline}}</p>
          {{else}}
            <p><div class="empty">N/A</div></p>
          {{/if}}
        {{/foreach}}
      </td>

    </tr>
      {{foreachelse}}
    <tr>
      <td colspan="20" class="empty">{{tr}}CMedecin.none{{/tr}}</td>
    </tr>
    {{/foreach}}
  </table>
</form>
