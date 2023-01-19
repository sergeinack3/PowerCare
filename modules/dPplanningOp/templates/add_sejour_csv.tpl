{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Ajout de séjours dans Mediboard par CSV</h2>

<div class="small-info">
  Veuillez sélectionner le fichier CSV à importer dont les champs sont séparés par <strong>,</strong> et les textes par
  <strong>"</strong> :
  <ul>
    <li>Les praticiens utilisés doivent être créés au préalable.</li>
    <li>La colonne "Praticien" du fichier csv correspond au nom d'utilisateur (username) du praticien.</li>
    <li>Les protocoles doivent être créés, le nom dans le fichier csv doit correspondre au libellé du protocole.</li>
    <li>Les objets créés seront rattachés à l'établissement courant.</li>
    <li>Les séjours et les opérations sont hors plages.</li>
    <li>Si les différentes dates ne sont pas renseignées, la date du jour sera alors prise.</li>
    <li>Le format des dates doit correspondre à : <strong>jj/mm/aaaa</strong></li>
    <li>Le format des heures doit correspondre à : <strong>hh:mm:ss</strong></li>
  </ul>
</div>

<form method="post" name="upload-import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="dosql" value="do_import_sejour_csv" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button class="submit">{{tr}}Upload{{/tr}}</button>
</form>