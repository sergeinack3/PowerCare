{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $lite}}
  <h3>Importation multi praticien simplifiée</h3>
{{else}}
  <h3>Importation de l'agenda de {{$prat}}</h3>
{{/if}}

<script>
  displayMsg = function (data) {
    var report = $("upload-report");
    report.update("");

    if (!data.length) {
      report.update(DOM.div({className: "small-info"}, "Aucun problème d'import"));
      return;
    }

    $A(data).each(function (row) {
      report.insert(DOM.div({className: "warning"}, "Erreur ligne n°" + row.line + " : " + row.msg));
    });
  }
</script>


<div class="small-info">
  Voici les colonnes qui doivent être présentes, avec leur nom en première ligne du fichier CSV.<br/>
  <br/>
  {{if $lite}}
  Dans ce mode seules les consultations seront créées,
  avec une durée de 1 créneau de la plage de consultation, qui doit exister au préalable. <br/>
  <ul>
    <li>ipp</li>
    <li>date</li>
    <li>heure</li>
    <li>rpps</li>
    <li>motif</li>
  </ul>
</div>
{{else}}
  Dans ce mode, les plages, les consultations et les patients seront créés s'ils ne sont pas retrouvés auparavant.
  <ul>
    <li>plage date</li>
    <li>plage debut</li>
    <li>plage fin</li>
    <li>plage frequence</li>
    <li>plage libelle</li>
    <li>plage couleur</li>
    <li>rdv debut</li>
    <li>rdv creneaux</li>
    <li>rdv motif</li>
    <li>patient nom</li>
    <li>patient prenom</li>
    <li>patient prenom 2</li>
    <li>patient prenom 3</li>
    <li>patient nom jf</li>
    <li>patient naissance</li>
    <li>patient sexe</li>
    <li>patient civilite</li>
    <li>patient tel</li>
    <li>patient mob</li>
    <li>patient email</li>
    <li>patient numero ss</li>
    <li>patient adresse</li>
    <li>patient cp</li>
    <li>patient ville</li>
    <li>patient pays</li>
  </ul>
{{/if}}
</div>

<form name="upload-planning" method="post" action="?m=cabinet&dosql=do_import_planning_csv"
      enctype="multipart/form-data" onsubmit="return checkForm(this);" target="upload-planning">
  <input type="hidden" name="MAX_FILE_SIZE" value="20000000"/>
  <input type="hidden" name="lite" value="{{$lite}}"/>
  <input type="hidden" name="prat_id" value="{{$prat->_id}}"/>
  <input type="hidden" name="dosql" value="do_import_planning_csv"/>

  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CConsultation-action-Import planning{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        <label for="upload_file">{{tr}}File{{/tr}}</label>
      </th>
      <td>
        <input type="file" name="upload_file"/>
      </td>
    </tr>

    {{if !$lite}}
      <tr>
        <th>
          <label for="upload_identifier">Identifiant d'import</label>
        </th>
        <td>
          <input type="text" name="upload_identifier" value="import-cab"/>
        </td>
      </tr>

      <tr>
        <th>
          <label for="force_update">Remplacer tous les champs des éléments retrouvés</label>
        </th>
        <td>
          <input type="checkbox" name="force_update" value="1"/>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Upload{{/tr}}</button>

        <progress id="import-progress" style="display: none; width: 200px;" class="process-progress"></progress>
      </td>
    </tr>
  </table>
</form>

<div id="upload-report"></div>

<iframe name="upload-planning" id="upload-planning" style="width: 1px; height: 1px;"></iframe>