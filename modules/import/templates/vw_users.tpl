{{*
 * @package Mediboard\Studiovision
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=a_value value=ajax_list_users}}

<form name="list-users" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-list-users')">
  <input type="hidden" name="m" value="{{$module}}"/>
  <input type="hidden" name="a" value="{{$a_value}}"/>
  <input type="hidden" name="import_type" value="{{$import_type}}"/>

  <table class="main form">
    <tr>
      <th>{{tr}}CImportCampaign{{/tr}}</th>
      <td>
          {{mb_include module=import template=inc_vw_import_campaign_select}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="change">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-list-users"></div>
