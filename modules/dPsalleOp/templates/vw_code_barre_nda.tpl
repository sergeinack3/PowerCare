{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if $modal}}
    openOperationTimings = function(operation_id, callback) {
      if (!callback) {
        callback = Prototype.emptyFunction;
      }

      new Url('salleOp', 'httpreq_vw_timing')
        .addParam('operation_id', operation_id)
        .addParam('submitTiming', 'submitTiming')
        .addParam('operation_header', 1)
        .addParam('modal', 1)
        .requestModal(1200, null, {
          onClose: callback
        }
      );
    };
  {{/if}}

  getPatient = function () {
    var form = getForm('get_nda_number');
    new Url('salleOp', 'vw_code_barre_nda')
      .addElement(form.NDA)
      .addParam('sejour_id', '{{$sejour_id}}')
      .addParam('operation_id', '{{$operation_id}}')
      .addParam('refreshPatient', 1)
      .addParam('auto_entree_bloc', {{$auto_entree_bloc}})
    {{if $modal}}
      .addParam('modal', '{{$modal}}')
      {{if $onclose_modal}}
      .addParam('onclose_modal', '{{$onclose_modal}}')
      {{/if}}
    {{/if}}
    .requestUpdate('find_patient_with_nda');
  };
  Main.add(
    function () {
      $('NDA').focus();
    }
  );
</script>

<form name="get_nda_number" method="get" onsubmit="return onSubmitFormAjax(this, getPatient);">
  <table class="form">
    <tr>
      <th>
        <label for="NDA" title="{{tr}}CPatient-Choose an administrative file number{{/tr}}">{{tr}}CPatient-NDA{{/tr}}</label>
      </th>
      <td>
        <input type="text" name="NDA" id="NDA" size="30" class="barcode" value="" placeholder="{{tr}}common-Barcode{{/tr}}"/>
        <button type="submit" class="search notext compact">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="find_patient_with_nda"></div>
