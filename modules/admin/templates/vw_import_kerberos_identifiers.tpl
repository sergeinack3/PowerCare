{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  Le fichier contenant l'association entre identifiant Mediboard et identifiant de domaine doit être au <strong>format CSV</strong>.
  <br />
  La première valeur doit désigner l'identifiant Mediboard, la seconde l'identifiant de domaine.
  <br />
  <strong>La première ligne comportant le nom des colonnes ne doit pas être présente au sein du fichier.</strong>
</div>

<table class="main form">
  <tr>
    <td>
      <iframe name="upload-import-file" id="upload-import-file" style="width: 1px; height: 1px;"></iframe>

      <form name="upload-import-file-form" method="post" enctype="multipart/form-data" target="upload-import-file">
        <input type="hidden" name="m" value="admin"/>
        <input type="hidden" name="dosql" value="do_upload_import_kerberos_identifiers"/>

        <input type="hidden" name="MAX_FILE_SIZE" value="4096000"/>

        <input type="file" name="import" style="width: 400px;" onchange="KerberosLDAP.uploadReset();"/>

        <button type="submit" class="submit">{{tr}}Submit{{/tr}}</button>

        <br/>

        <span class="upload-ok" style="display: none;">
          {{me_img src="tick.png" icon="tick" class="me-success"}}
          Le fichier est prêt à être importé.
        </span>

        <span class="upload-error" style="display: none;">
          {{me_img src="cancel.png" icon="cancel" class="me-error"}}
          Le fichier n'est pas valide.
        </span>
      </form>
    </td>
  </tr>

  <tr>
    <td id="import-steps"></td>
  </tr>
</table>
