{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="consentement_edit" action="" method="post" onsubmit="MaintenanceConfig.saveConsentement(this)">
  <table class="form">
    <tr>
      <th colspan="2" class="title">{{tr}}dPpatients-edit-consentement{{/tr}}</th>
    </tr>
    <tr>
      <th>
        <label for="tag"
               title="{{tr}}config-dPpatients-CPatient-tag_ipp{{/tr}}">{{tr}}config-dPpatients-CPatient-tag_ipp{{/tr}}</label>
      </th>
      <td>{{mb_field object=$idex field=tag onChange="MaintenanceConfig.seeCountConsentement(this.form)"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=allow_sms_notification}}</th>
      <td>{{mb_field object=$patient field=allow_sms_notification onChange="MaintenanceConfig.seeCountConsentement(this.form)" value=none}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <div id="count_consentement" class="info"></div>
        <button id="submit" type="submit" class="tick" disabled>
          {{tr}}Validate{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
