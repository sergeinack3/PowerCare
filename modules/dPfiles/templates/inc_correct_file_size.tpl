{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('populate_file_size');
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

{{if $file_report->_error_count_by_type|count}}
  {{assign var='size_mismatch_count' value=$file_report->_error_count_by_type.size_mismatch}}
{{else}}
  <div class="small-info">Merci de lancer la vérification de l'intégrité pour corriger la taille des fichiers</div>
  {{mb_return}}
{{/if}}

<div class="small-info">
  Corrige la taille des fichiers de la base de données ne correspondant pas à celle du système de fichiers (<code>size_mismatch</code>).
  <br />
  Merci de corriger les erreurs de taille juste après la vérification d'intégrité.
</div>

<table class="main tbl">
  <tr>
    <td colspan="5">
      <form name="populate_file_size" method="post" onsubmit="return onSubmitFormAjax(this, null, 'populate_results_size_mismatch');">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_list_size_mismatch_file" />
        <input type="hidden" name="repair" value="0" />
        <table class="main layout">
          <tr>
            <td>
              <div style="display: inline-block;" class="{{if $size_mismatch_count != 0}}warning{{else}}info{{/if}}">
                {{$size_mismatch_count}} fichier(s) en erreur
              </div>
              
              <button type="button" class="play" onclick="$V(this.form.repair, '0'); this.form.onsubmit();" {{if $size_mismatch_count == 0}}disabled{{/if}}>
                {{tr}}Run{{/tr}}
              </button>
              
              <button type="button" onclick="$V(this.form.repair, '1'); this.form.onsubmit();"  {{if $size_mismatch_count == 0}}disabled{{/if}}>
                <i class="fa fa-wrench" aria-hidden="true" style="margin-right: 5px;"></i>
                {{tr}}Repair{{/tr}}
              </button>
              
              <label>
                {{tr}}common-Step{{/tr}} :
                <input type="text" name="step" value="100" size="3" />
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
  <tbody id="populate_results_size_mismatch"></tbody>
</table>
