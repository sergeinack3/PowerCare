{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  submitTimedPictureResult = function (id, obj) {
    var form = getForm("form-edit-observation-timed-picture-result");
    $V(form.observation_result_set_id, id);
    onSubmitFormAjax(form, Control.Modal.close);
  };

  submitTimedPicture = function () {
    getForm('form-edit-observation-timed-picture').onsubmit();
  };

  Main.add(function () {
    if (Prototype.Browser.IE && document.documentMode == 8) {
      $$("div.outlined input").each(function (input) {
        input.observe("click", function () {
          $$("div.outlined input.checked").invoke("removeClassName", "checked");
          input.addClassName("checked");
        });
      });
    }
  });
</script>

<form name="form-edit-observation-timed-picture" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class class=CObservationResultSet}}
  {{mb_key object=$result_set}}
  {{mb_field object=$result_set field=patient_id hidden=true}}
  {{mb_field object=$result_set field=context_class hidden=true}}
  {{mb_field object=$result_set field=context_id hidden=true}}
  <input type="hidden" name="callback" value="submitTimedPictureResult" />

  <table class="main form">
    <tr>
      <th colspan="2" class="title">
        {{$timed_picture}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$result_set field=datetime}}
      </th>
      <td>
        {{mb_field object=$result_set field=datetime register=true form="form-edit-observation-timed-picture"}}
      </td>
    </tr>
  </table>
</form>

<table class="main tbl">
  <tr>
    <td>
      <form name="form-edit-observation-timed-picture-result" method="post">
        {{mb_class object=$result}}
        {{mb_key object=$result}}
        {{mb_field object=$result field=_value_type_id hidden=true}}
        {{mb_field object=$result field=observation_result_set_id hidden=true}}
        {{mb_field object=$result field=_value hidden=true}}

        {{foreach from=$timed_picture->_ref_files item=_file}}
          <div class="outlined">
            <input type="radio" name="file_id" value="{{$_file->_id}}" />
            <label for="file_id_{{$_file->_id}}" ondblclick="submitTimedPicture()">
              <div
                style="background: no-repeat center center url(?m=files&raw=thumbnail&document_guid={{$_file->_class}}-{{$_file->_id}}&profile=medium); background-size: contain; height: 80px; width: 80px;"></div>
              {{$_file->_no_extension}}
            </label>
          </div>
        {{/foreach}}
      </form>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button class="submit" onclick="submitTimedPicture()">{{tr}}Save{{/tr}}</button>
    </td>
  </tr>
</table>
