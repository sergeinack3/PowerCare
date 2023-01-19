{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-{{$location->_guid}}" action="" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="context" />
  <input type="hidden" name="callback" value="ContextualIntegration.editLocationCallback" />
  {{mb_class object=$location}}
  {{mb_key   object=$location}}
  {{mb_field object=$location field=integration_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$location}}

    <tr>
      <th>{{mb_label object=$location field=location}}</th>
      <td>{{mb_field object=$location field=location}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$location field=button_type}}</th>
      <td>{{mb_field object=$location field=button_type}}</td>
    </tr>
    <tr>
      <td></td>
      <td>
        {{mb_include module=system template=inc_form_table_footer object=$location}}
      </td>
    </tr>
  </table>
</form>
