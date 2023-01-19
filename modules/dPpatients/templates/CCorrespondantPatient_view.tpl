{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=CMbObject_view}}

{{if $object->_can->edit}}
  <script type="text/javascript">
    refreshAfterEdit = function () {
      if (!Object.isUndefined(document.form_prescription)) {
        Soins.loadSuiviClinique(document.form_prescription.sejour_id.value);
      } else if (window.reloadSynthese) {
        reloadSynthese();
      }
    }
  </script>
  <table class="tbl">
    <tr>
      <td class="button">
        <button class="button edit"
                onclick="this.up('div').hide(); try { Correspondant.edit('{{$object->_id}}', null, afterEditCorrespondant); } catch(e){}">
          {{tr}}Modify{{/tr}}
        </button>
      </td>
    </tr>
  </table>
{{/if}}
