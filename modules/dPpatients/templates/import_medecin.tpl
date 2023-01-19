{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $verbose}}
  <table class="form">
    <tr>
      <th class="category" colspan="2">Bilan</th>
    </tr>

    <tr>
      <th>Etape #</th>
      <td>{{$step}}</td>
    </tr>
    <tr>
      <th>Médecins</th>
      <td>{{$medecins|@count}}</td>
    </tr>
    <tr>
      <th>Temps pris</th>
      <td>{{$chrono->total}}</td>
    </tr>
    <tr>
      <th>Mise à jours</th>
      <td>{{$updates}}</td>
    </tr>
    <tr>
      <th>Erreurs</th>
      <td>{{$errors}}</td>
    </tr>
  </table>
{{/if}}

{{if !$verbose}}
  <script type="text/javascript">
    {{if $xpath_screwed}}
    Process.updateScrewed();
    {{else}}
    Process.updateTotal(
      {{$medecins|@count}},
      {{$chrono->total}},
      {{$updates}},
      {{$errors}}
    );
    {{/if}}
    {{if !$last_page}}
    Process.endStep();
    {{else}}
    Process.nextDep();
    {{/if}}
  </script>
{{/if}}

{{if $verbose}}
  <table class="tbl">
    <tr>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Nom de naissance</th>
      <th>Adresse</th>
      <th>Ville</th>
      <th>CP</th>
      <th>Tél</th>
      <th>Fax</th>
      <th>Mél</th>
      <th>Disciplines</th>
      <th>Complémentaires</th>
      <th>Orientations</th>
    </tr>

    {{foreach from=$medecins item=_medecin}}
      <tr>
        <td {{if $_medecin->_has_siblings}}style="background: #eef"{{/if}}>{{$_medecin->nom}}</td>
        <td>{{$_medecin->prenom}}</td>
        <td>{{$_medecin->jeunefille}}</td>
        <td>{{$_medecin->adresse|nl2br}}</td>
        <td>{{$_medecin->ville}}</td>
        <td>{{$_medecin->cp}}</td>
        <td>{{mb_value object=$_medecin field=tel}}</td>
        <td>{{mb_value object=$_medecin field=fax}}</td>
        <td>{{$_medecin->email}}</td>
        <td>{{$_medecin->disciplines|nl2br}}</td>
        <td>{{$_medecin->complementaires|nl2br}}</td>
        <td>{{$_medecin->orientations|nl2br}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}