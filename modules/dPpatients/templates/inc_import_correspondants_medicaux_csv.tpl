{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="22">{{$results|@count}} correspondants médicaux trouvés</th>
  </tr>
  <tr>
    <th class="narrow">Etat</th>
    <th>{{mb_title class=CMedecin field=nom}}</th>
    <th>{{mb_title class=CMedecin field=prenom}}</th>
    <th>{{mb_title class=CMedecin field=jeunefille}}</th>
    <th>{{mb_title class=CMedecin field=sexe}}</th>
    <th>{{mb_title class=CMedecin field=actif}}</th>
    <th>{{mb_title class=CMedecin field=titre}}</th>
    <th>{{mb_title class=CMedecin field=adresse}}</th>
    <th>{{mb_title class=CMedecin field=ville}}</th>
    <th>{{mb_title class=CMedecin field=cp}}</th>
    <th>{{mb_title class=CMedecin field=tel}}</th>
    <th>{{mb_title class=CMedecin field=fax}}</th>
    <th>{{mb_title class=CMedecin field=portable}}</th>
    <th>{{mb_title class=CMedecin field=email}}</th>
    <th>{{mb_title class=CMedecin field=disciplines}}</th>
    <th>{{mb_title class=CMedecin field=orientations}}</th>
    <th>{{mb_title class=CMedecin field=complementaires}}</th>
    <th>{{mb_title class=CMedecin field=type}}</th>
    <th>{{mb_title class=CMedecin field=adeli}}</th>
    <th>{{mb_title class=CMedecin field=rpps}}</th>
  </tr>
  {{foreach from=$results item=_code}}
    <tr>
      {{if $_code.error == "0" || $_code.error == "1"}}
        <td class="text ok">
          {{if $_code.error == "0"}}
            Correspondant importé
          {{else}}
            Correspondant existant
          {{/if}}
        </td>
      {{else}}
        <td class="text warning compact">
          <div>{{$_code.error}}</div>
        </td>
      {{/if}}

      <td>{{$_code.nom}}</td>
      <td>{{$_code.prenom}}</td>
      <td>{{$_code.jeunefille}}</td>
      <td>{{$_code.sexe}}</td>
      <td>{{$_code.actif}}</td>
      <td>{{$_code.titre}}</td>
      <td>{{$_code.adresse}}</td>
      <td>{{$_code.ville}}</td>
      <td>{{$_code.cp}}</td>
      <td>{{$_code.tel}}</td>
      <td>{{$_code.fax}}</td>
      <td>{{$_code.portable}}</td>
      <td>{{$_code.email}}</td>
      <td>{{$_code.disciplines}}</td>
      <td>{{$_code.orientations}}</td>
      <td>{{$_code.complementaires}}</td>
      <td>{{$_code.type}}</td>
      <td>{{$_code.adeli}}</td>
      <td>{{$_code.rpps}}</td>
    </tr>
  {{/foreach}}
</table>