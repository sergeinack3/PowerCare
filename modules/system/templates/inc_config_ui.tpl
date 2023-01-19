{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-ui" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form" style="table-layout: fixed;">
    {{mb_include module=system template=inc_config_str var=page_title}}
    {{mb_include module=system template=inc_config_str var=company_name}}
    {{mb_include module=system template=inc_config_str var=issue_tracker_url size=60}}
    {{mb_include module=system template=inc_config_str var=help_page_url size=60}}
    {{mb_include module=system template=inc_config_str var=currency_symbol}}
    {{mb_include module=system template=inc_config_enum var=ref_pays values="1|3"}}
    {{mb_include module=system template=inc_config_bool var=hide_confidential}}
    {{mb_include module=system template=inc_config_bool var=modal_windows_draggable}}
    {{mb_include module=system template=inc_config_bool var=locale_warn}}
    {{mb_include module=system template=inc_config_str var=locale_alert}}
    {{mb_include module=system template=inc_config_bool var=login_browser_check}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
