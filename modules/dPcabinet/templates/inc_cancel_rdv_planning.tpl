{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="form-motif_annulation-{{$consultation->_id}}">
  <form name="cancelFrm{{$consultation->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
    <input type="hidden" name="m" value="dPcabinet" />
    {{mb_key object=$consultation}}
    {{mb_class object=$consultation}}
    <input type="hidden" name="chrono" value="{{$consultation|const:'TERMINE'}}" />
    <input type="hidden" name="annule" value="1" />
    <table class="tbl main">
      <tr>
        <th colspan="2" class="title">
          {{$consultation->_view}}
          <button type="button" class="cancel notext" onclick="Control.Modal.close();" style="float:right;">{{tr}}Close{{/tr}}</button>
        </th>
      </tr>
      <tr>
        <td colspan="2" class="text">
          <div class="small-warning">{{tr}}CConsultation-confirm-cancel-1{{/tr}}</div>
        </td>
      </tr>
      <tr>
        <td style="text-align: right"><strong>{{mb_label object=$consultation field=motif_annulation}}</strong></td>
        <td>{{mb_field object=$consultation field=motif_annulation typeEnum="radio" separator="<br/>"}}</td>
      </tr>
      <tr>
        <td style="text-align: right"><strong>{{mb_label object=$consultation field=rques}}</strong></td>
        <td>{{mb_field object=$consultation field=rques}}</td>
      </tr>
      <tr>
        <td colspan="4" class="button">
          <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Validate{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>
