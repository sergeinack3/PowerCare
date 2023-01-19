{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="15">
        {{$medecin->_view}}
    </th>
  </tr>

  <tr>
    <th>{{mb_title class=CMedecinExercicePlace field=adeli}}</th>
    <th class="narrow">{{mb_title class=CMedecinExercicePlace field=type}}</th>
    <th class="narrow">{{mb_title class=CMedecinExercicePlace field=disciplines}}</th>
    <th class="narrow">{{mb_title class=CMedecinExercicePlace field=mode_exercice}}</th>
    <th class="narrow">{{mb_title class=CMedecinExercicePlace field=categorie_pro}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=siret}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=siren}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=finess}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=finess_juridique}}</th>
    <th>{{mb_title class=CExercicePlace field=raison_sociale}}</th>
    <th>{{mb_title class=CExercicePlace field=enseigne_comm}}</th>
    <th>{{mb_title class=CExercicePlace field=adresse}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=tel}} / {{mb_title class=CExercicePlace field=tel2}}
      / {{mb_title class=CExercicePlace field=fax}}</th>
    <th class="narrow">{{mb_title class=CExercicePlace field=email}}</th>
    <th>{{mb_title class=CMedecinExercicePlace field=mssante_address}}</th>
  </tr>

    {{foreach from=$medecin->_ref_medecin_exercice_places item=_medecin_exercice_place}}
    {{assign var=_exercice_place value=$_medecin_exercice_place->_ref_exercice_place}}
  <tr>
    <td>{{mb_value object=$_medecin_exercice_place field=adeli}}</td>
    <td>{{mb_value object=$_medecin_exercice_place field=type}}</td>
    <td>{{mb_value object=$_medecin_exercice_place field=disciplines}}</td>
    <td>{{mb_value object=$_medecin_exercice_place field=mode_exercice}}</td>
    <td>{{mb_value object=$_medecin_exercice_place field=categorie_pro}}</td>
    <td>{{mb_value object=$_exercice_place field=siret}}</td>
    <td>{{mb_value object=$_exercice_place field=siren}}</td>
    <td>{{mb_value object=$_exercice_place field=finess}}</td>
    <td>{{mb_value object=$_exercice_place field=finess_juridique}}</td>
    <td class="text compact">{{mb_value object=$_exercice_place field=raison_sociale}}</td>
    <td class="text compact">
      <div>
          {{mb_value object=$_exercice_place field=enseigne_comm}}
      </div>
      <div>
          {{mb_value object=$_exercice_place field=comp_destinataire}}
      </div>
      <div>
          {{mb_value object=$_exercice_place field=comp_point_geo}}
      </div>
    </td>
    <td class="text compact">
      <div>
          {{mb_value object=$_exercice_place field=adresse}}
      </div>
      <div>
          {{mb_value object=$_exercice_place field=cp}} {{mb_value object=$_exercice_place field=commune}}
      </div>
      <div>
          {{mb_value object=$_exercice_place field=pays}}
      </div>
    </td>
    <td>
      <div>
          {{if $_exercice_place->tel}}
          {{mb_value object=$_exercice_place field=tel}}
          {{else}}
        &mdash;
        {{/if}}
      </div>
      <div>
          {{if $_exercice_place->tel2}}
          {{mb_value object=$_exercice_place field=tel2}}
          {{else}}
        &mdash;
        {{/if}}
      </div>
      <div>
          {{if $_exercice_place->fax}}
          {{mb_value object=$_exercice_place field=fax}}
          {{else}}
        &mdash;
        {{/if}}
      </div>
    </td>
    <td>{{mb_value object=$_exercice_place field=email}}</td>
    <td>
        {{foreach from=$_medecin_exercice_place->_mssante_addresses item=_address}}
      <p>{{$_address}}</p>
      {{/foreach}}
    </td>
  </tr>
  {{/foreach}}
</table>
