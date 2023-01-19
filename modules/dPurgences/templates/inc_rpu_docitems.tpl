{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$sejour->_ref_patient}}

<table class="form">
  <tr>
    <td class="halfPane">
      <fieldset>
        <legend>{{tr}}CFile{{/tr}} - {{tr}}CSejour{{/tr}}</legend>
        <div id="files-CSejour">
          <script>
            File.register('{{$sejour->_id}}','{{$sejour->_class}}', 'files-CSejour');
          </script>
        </div>
      </fieldset>
    </td>
    <td class="halfPane">
      <fieldset>
        <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}CSejour{{/tr}}</legend>
        <div id="documents-CSejour">
          <script>
            Document.register('{{$sejour->_id}}','{{$sejour->_class}}','{{$sejour->_praticien_id}}','documents-CSejour');
          </script>
        </div>
      </fieldset>
    </td>
  </tr>

  {{if $consult->_id}}
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CFile{{/tr}} - {{tr}}CConsultation{{/tr}}</legend>
          <div id="files-CConsultation">
            <script>
              File.register('{{$consult->_id}}','{{$consult->_class}}', 'files-CConsultation');
            </script>
          </div>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}CConsultation{{/tr}}</legend>
          <div id="documents-CConsultation">
            <script>
              Document.register('{{$consult->_id}}','{{$consult->_class}}','{{$consult->_praticien_id}}','documents-CConsultation');
            </script>
          </div>
        </fieldset>
      </td>
    </tr>
  {{else}}
    <tr>
      <td colspan="2">
        <div class="small-info">Consultation non réalisée</div>
      </td>
    </tr>
  {{/if}}
</table>