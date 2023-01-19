{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Ajout de s�jours dans Mediboard par CSV</h2>

<div class="small-info">
  Veuillez s�lectionner le fichier CSV � importer dont les champs sont s�par�s par <strong>,</strong> et les textes par
  <strong>"</strong> :
  <ul>
    <li>Les praticiens utilis�s doivent �tre cr��s au pr�alable.</li>
    <li>La colonne "Praticien" du fichier csv correspond au nom d'utilisateur (username) du praticien.</li>
    <li>Les protocoles doivent �tre cr��s, le nom dans le fichier csv doit correspondre au libell� du protocole.</li>
    <li>Les objets cr��s seront rattach�s � l'�tablissement courant.</li>
    <li>Les s�jours et les op�rations sont hors plages.</li>
    <li>Si les diff�rentes dates ne sont pas renseign�es, la date du jour sera alors prise.</li>
    <li>Le format des dates doit correspondre � : <strong>jj/mm/aaaa</strong></li>
    <li>Le format des heures doit correspondre � : <strong>hh:mm:ss</strong></li>
  </ul>
</div>

<form method="post" name="upload-import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="dosql" value="do_import_sejour_csv" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button class="submit">{{tr}}Upload{{/tr}}</button>
</form>