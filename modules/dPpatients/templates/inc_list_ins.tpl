{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="13">{{tr}}CINSiLog history call{{/tr}}</th>
  </tr>
  <tr>
    <th>
        {{mb_title class=CINSiLog field=user_id}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=call_service}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=call_datetime}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=return_code}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=authentification_type}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=automatic_call}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=patient_id}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=birth_name}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=birth_firstname}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=birth_date}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=gender}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=ins}}
    </th>
    <th>
        {{mb_title class=CINSiLog field=oid}}
    </th>
  </tr>
  {{foreach from=$insi_logs item=_log_insi}}
      <tr>
        <td class="narrow me-text-align-center">
          {{mb_ditto name=user value=$_log_insi->_ref_user->_view}}
        </td>
        <td class="narrow">
            {{mb_value object=$_log_insi field=call_service}}
        </td>
        <td class="narrow">
            {{mb_value object=$_log_insi field=call_datetime}}
        </td>
        <td class="narrow">
            {{$_log_insi->return_code}} - {{mb_value object=$_log_insi field=return_code}}
        </td>
        <td class="narrow">
            {{mb_value object=$_log_insi field=authentification_type}}
        </td>
        <td class="narrow text-align-center">
            {{if $_log_insi->automatic_call}}
                <i class="fa fa-check me-color-success" style="font-size: 1.3em"></i>
            {{else}}
                <i class="fa fa-times me-color-error" style="font-size: 1.3em"></i>
            {{/if}}
        </td>
        <td class="narrow">
            {{mb_value object=$_log_insi->_ref_patient field=patient_id}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=birth_name}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=birth_firstname}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=birth_date}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=gender}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=ins}}
        </td>
        <td class="narrow">
            {{mb_include module=ameli template=services/insi_icir/inc_check_field_identity object=$_log_insi
            field=oid}}
        </td>
      </tr>
  {{/foreach}}
</table>
