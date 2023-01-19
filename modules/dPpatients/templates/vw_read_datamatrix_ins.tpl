{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="read_datamatrix_ins" method="post"
      onsubmit="INS.readDatamatrixINS(this, '{{$search}}'); return false">
  <table class="form">
    <tr>
      <th>{{tr}}CPatientINSNIR_datamatrix_ins{{/tr}}</th>
      <td>
        <input type="text" name="datamatrix_ins" class="barcode"/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="search">
            {{if $search}}{{tr}}Search{{/tr}}{{else}}{{tr}}common-action-Fill{{/tr}}{{/if}}
        </button>
      </td>
    </tr>
  </table>
</form>
