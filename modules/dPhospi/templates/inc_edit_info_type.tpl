{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCInfoType" action="?" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$type}}
  {{mb_key object=$type}}

  {{mb_field object=$type field=user_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$type}}
    <tr>
      <th>
        {{mb_label object=$type field=name}}
      </th>
      <td>
        {{mb_field object=$type field=name}}
      </td>
    </tr>
    {{mb_include module=system template=inc_form_table_footer object=$type
    options="{typeName: 'le type d\'information', objName: '$type', ajax: true}"
    options_ajax="Control.Modal.close"}}
  </table>
</form>