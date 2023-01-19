{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info" id="data-sharing-help" style="display: none;">
  <table class="main tbl">
    <col style="width: 100px;" />

    <tr>
      <td style="text-align: center;">
        {{tr}}common-CGroup{{/tr}}
        <span class="compact">(<i class="fa fa-ban" style="color: firebrick;"></i>)</span>
      </td>

      <td>{{tr}}CPatientGroup-msg-Authorisation has never been asked{{/tr}}</td>
    </tr>

    <tr>
      <td style="text-align: center;">
        <input type="checkbox" checked disabled />
        {{tr}}common-CGroup{{/tr}}
        <span class="compact">([{{tr}}common-Initial|pl{{/tr}}] &bull; [{{tr}}Date{{/tr}}])</span>
      </td>

      <td>{{tr}}CPatientGroup-msg-Authorisation has been asked by this person at this date and patient approved{{/tr}}</td>
    </tr>

    <tr>
      <td style="text-align: center;">
        <input type="checkbox" disabled />
        {{tr}}common-CGroup{{/tr}}
        <span class="compact">([{{tr}}common-Initial|pl{{/tr}}] &bull; [{{tr}}Date{{/tr}}])</span>
      </td>

      <td>{{tr}}CPatientGroup-msg-Authorisation has been asked by this person at this date and patient refused{{/tr}}</td>
    </tr>

    <tr>
      <td colspan="2">
        <hr />
      </td>
    </tr>

    <tr>
      <td colspan="2" class="text">
        {{tr}}CPatientGroup-msg-Current group is checked by default if not set.{{/tr}}
      </td>
    </tr>
  </table>
</div>