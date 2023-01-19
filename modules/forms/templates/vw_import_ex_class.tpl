{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=forms script=ex_class_editor}}

<script>
function displayListItems(select, key) {
  var list_id = $V(select).split(/-/)[1];
  var items = $("list-items-"+key);
  
  if (!list_id || list_id == "__create__") {
    items.update("");
    return;
  }
  
  var url = new Url("forms", "ajax_ex_list_info");
  url.addParam("list_id", list_id);
  url.requestUpdate(items);
}
</script>

<div class="small-info">
  Quelques remarques sur l'importation des formulaires :
  <ul>
    <li>Les valeurs par défaut des champs de type liste ne seront pas importées</li>
    <li>Les sous-formulaires ne seront pas importés</li>
    <li>Les événements déclencheurs ne seront pas importés</li>
    <li>Les tags ne seront pas importés</li>
  </ul>
</div>

<table class="main layout">
  <tr>
    <td style="width: 50%">
      <fieldset>
        <legend>1. Téléversement</legend>
        <iframe name="upload-import-file" id="upload-import-file" style="width: 1px; height: 1px;"></iframe>

        <form method="post" name="upload-import-file-form" enctype="multipart/form-data" target="upload-import-file">
          <input type="hidden" name="m" value="forms" />
          <input type="hidden" name="dosql" value="do_upload_import_ex_class" />

          {{if !$in_hermetic_mode}}
            <input type="checkbox" name="ignore_similar" value="1" checked/>
            <label for="ignore_similar">{{tr}}forms-ignore similar{{/tr}}</label>
            <br/>
          {{/if}}

          <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
          <input type="file" name="import" style="width: 400px;" onchange="ExClass.uploadReset()" />

          <button type="submit" class="submit">{{tr}}Upload{{/tr}}</button>
          <span class="upload-ok" style="display: none;">
            {{me_img src="tick.png" icon="tick" class="me-success"}}
            Le fichier est prêt à être importé
          </span>
          <span class="upload-error" style="display: none;">
            {{me_img src="cancel.png" icon="cancel" class="me-error"}}
            Le fichier n'est pas valide, ce doit être un fichier XML exporté depuis Mediboard
          <span>
        </form>
      </fieldset>

      <div id="import-steps"></div>
    </td>
    <td>
      <fieldset>
        <legend>Rapport d'importation</legend>
        <div id="ex_class-import-report"><span class="empty">Aucune importation réalisée</span></div>
      </fieldset>
    </td>
  </tr>
</table>
