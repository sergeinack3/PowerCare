{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr}}CFilesCategory-import{{/tr}}</h2>

<div class="big-info">
  Téléversez un fichier CSV, encodé en <code>ISO-8859-1</code> (Western Europe),
  séparé par des point-virgules (<code>;</code>) et
  délimité par des guillemets doubles (<code>"</code>).
  <br />
  La première ligne du fichier doit contenir les champs suivants (noms identiques) séparés par un point-virgule (;) :

  <ol>
    <li><strong>nom</strong> : {{tr}}CFilesCategory-nom{{/tr}}</li>
    <li>nom_court : {{tr}}CFilesCategory-nom_court{{/tr}}</li>
    <li>class : {{tr}}CFilesCategory-class{{/tr}}</li>
    <li>etablissement : Nom de l'établissement auquel lier la catégorie. Catégorie pour tous les établissements si rien n'est renseigné</li>
    <li>importance : {{tr}}CFilesCategory-importance{{/tr}} (normal|high)</li>
    <li>send_auto : {{tr}}CFilesCategory-send_auto{{/tr}}</li>
    <li>eligible_file_view : {{tr}}CFilesCategory-eligible_file_view{{/tr}}</li>
    <li>medicale : {{tr}}CFilesCategory-medicale{{/tr}}</li>
    <li>color : {{tr}}CFilesCategory-color{{/tr}} (hexa)</li>
  </ol>
</div>

<form method="post" name="import" action="?m=files&dosql=do_import_files_category" enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-import');">
  <input type="hidden" name="m" value="files"/>
  <input type="hidden" name="dosql" value="do_import_files_category"/>

  <table class="main form">
    <tr>
      <th style="width: 50%;">{{tr}}File{{/tr}}</th>
      <td>
        {{mb_include module=system template=inc_inline_upload paste=false lite=true extensions=csv multi=false}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button class="import" type="submit">{{tr}}Import{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import"></div>