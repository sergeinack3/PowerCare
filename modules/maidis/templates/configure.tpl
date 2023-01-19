{{*
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" action="?m=maidis&amp;{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}
  
  <table class="form">
    <tr>
      <th class="title" colspan="2">Configuration</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=plage_consult_start_hour}}
    {{mb_include module=system template=inc_config_str var=plage_consult_end_hour}}

    <tr>
      <td class="button" colspan="2">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{mb_include module=system template=configure_dsn dsn=maidis_import}}
