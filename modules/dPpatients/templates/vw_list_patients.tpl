{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listPatients = function (page) {
    var form = getForm("find_patients");
    var url = new Url("patients", "ajax_list_export_patients");
    if (page) {
      url.addParam('page', page);
    }
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addParam("praticien_id", form.praticien_id.value);
    url.addParam("function_id", form.function_id.value);
    url.requestUpdate('result_patient');
  }

  refreshListPatients = function () {
    var url = new Url("patients", "ajax_list_export_patients");
    url.requestUpdate('result_patient');
  }

  Main.add(function () {
    listPatients(0);
  });
</script>

<div id="search_patients">
  <form name="find_patients" action="?" method="get">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
    <table class="main form">
      <tr>
        <th class="title" colspan="6">{{tr}}CPatient.search{{/tr}}</th>
      </tr>
      <tr>
        <th>{{mb_label object=$filter field="_date_min"}}</th>
        <td>{{mb_field object=$filter field="_date_min" form="find_patients" canNull="false" register=true}}</td>
        <th>{{tr}}CMediusers-praticien|pl{{/tr}}</th>
        <td>
          <select name="praticien_id" class="ref">
            <option value="">&mdash; {{tr}}CMediusers-select-praticien{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser selected=$prat->_id list=$praticiens}}
          </select>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$filter  field="_date_max"}}</th>
        <td>{{mb_field object=$filter field="_date_max" form="find_patients" canNull="false" register=true}}</td>
        <th>{{tr}}CMediusers-cabinet|pl{{/tr}}</th>
        <td colspan="3">
          <select name="function_id">
            <option value="">&mdash; {{tr}}CMediusers-select-cabinet{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_function selected=$cabinet list=$cabinets}}
          </select>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="6">
          <button type="button" class="button search" onclick="listPatients();">{{tr}}Search{{/tr}}</button>
          <a class="button download" href="?m=patients&raw=ajax_list_export_patients&export=1"
             target="_blank">{{tr}}Export-CSV{{/tr}}</a>
        </td>
      </tr>
    </table>
  </form>
  <div id="result_patient"></div>
</div>