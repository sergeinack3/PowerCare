{{*
* @package Mediboard\Maternite
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("ChoixSejour-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<form name="ChoixSejour-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />

  <h1>Aucun séjour n'a été selectionné comme séjour de l'accouchement</h1>
  <table class="main layout">
    <tr>
      <td class="halfPane"></td>
      <td>
        <fieldset style="width: 300px;">
          <legend>Selection du séjour d'accouchement</legend>
          <table class="form me-no-box-shadow">
            {{foreach from=$grossesse->_ref_sejours item=sejour}}
              <tr>
                <th>{{$sejour}}</th>
                <td>
                  <input type="radio" value="{{$sejour->_id}}" name="admission_id"
                         {{if $dossier->admission_id == $sejour->_id}}selected="selected"{{/if}} />
                </td>
              </tr>
              {{foreachelse}}
              <tr>
                <td class="empty">{{tr}}CSejour.none{{/tr}}</td>
              </tr>
            {{/foreach}}
            <tr>
              <td colspan="2" class="button">
                <button type="button" class="save not-printable" onclick="submitAllForms(DossierMater.refresh);">
                  Enregistrer
                </button>
                <button type="button" class="close not-printable" onclick="Control.Modal.close();">
                  Fermer
                </button>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane"></td>
    </tr>
  </table>
</form>
