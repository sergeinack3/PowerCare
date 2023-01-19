{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('populate_deleted_file');
    form.elements.step.addSpinner({min: 0});
  });
  
  submitPopulateForm = function(form) {
    var auto = form.elements.continue.checked;
    
    if (!auto) {
      return false;
    }
    
    return form.onsubmit();
  };
</script>

{{if is_countable($file_report->_error_count_by_type) && $file_report->_error_count_by_type|count}}
  {{assign var='db_unfound_count' value=$file_report->_error_count_by_type.db_unfound}}
{{else}}
  <div class="small-info">Merci de lancer la vérification de l'intégrité pour supprimer les fichiers</div>
  {{mb_return}}
{{/if}}

<div class="small-warning">
  <strong>Renomme</strong> les fichiers du système de fichiers non présents en base de données en ajoutant l'extension <strong>.trash</strong> (<code>db_unfound</code>).
</div>

<table class="main tbl">
  <tr>
    <td colspan="4">
      <form name="populate_deleted_file" method="post" onsubmit="return onSubmitFormAjax(this, null, 'populate_results_deleted');">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_list_deleted_file" />
        <input type="hidden" name="delete" value="0" />
        <table class="main layout">
          <tr>
            <td>
              <div style="display: inline-block;" class="{{if $db_unfound_count != 0}}warning{{else}}info{{/if}}">
                {{$db_unfound_count}} objet(s) à supprimer
              </div>
              
              <button type="button" class="play" onclick="$V(this.form.delete, '0'); this.form.onsubmit();" {{if $db_unfound_count == 0}}disabled{{/if}}>
                {{tr}}Run{{/tr}}
              </button>
              
              <button type="button" onclick="$V(this.form.delete, '1'); this.form.onsubmit();"  {{if $db_unfound_count == 0}}disabled{{/if}}>
                <i class="fas fa-trash-alt" aria-hidden="true" style="margin-right: 5px;"></i>
                {{tr}}Delete{{/tr}}
              </button>
              
              <label>
                {{tr}}common-Step{{/tr}} :
                <input type="text" name="step" value="50" size="3" />
              </label>
              
              <label>
                Dernier identifiant :
                <input type="text" name="file_report_id" value="{{$file_report_id}}" size="8" />
              </label>
              
              <label>
                {{tr}}common-Auto{{/tr}} :
                <input type="checkbox" name="continue" />
              </label>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tbody id="populate_results_deleted"></tbody>
</table>
