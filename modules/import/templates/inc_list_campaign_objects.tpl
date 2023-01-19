{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination total=$total step=$step current=$start
        change_page=ImportCampaign.changeObjectPage change_page_arg="tab-$class_name"}}
    </td>

    <td class="narrow">
      <button class="trash notext" type="button" onclick="ImportCampaign.resetEntities('{{$campaign->_id}}', '{{$class_name}}')"></button>
    </td>
  </tr>

  <tr>
    <th>{{mb_title class=CImportEntity field=external_class}}</th>
    <th>{{mb_title class=CImportEntity field=external_id}}</th>
    <th>{{mb_title class=CImportEntity field=internal_id}}</th>
    <th>{{mb_title class=CImportEntity field=last_import_date}}</th>
    <th colspan="2">{{mb_title class=CImportEntity field=last_error}}</th>
  </tr>

  {{foreach from=$entities item=_entity}}
    <tr>
      <td>
        {{mb_value object=$_entity field=external_class}}
      </td>

      <td>
        {{mb_value object=$_entity field=external_id}}
      </td>

      <td>
        {{mb_value object=$_entity field=internal_id tooltip=true}}
      </td>

      <td>
        {{mb_value object=$_entity field=last_import_date}}
      </td>

      <td class="text">
        {{mb_value object=$_entity field=last_error}}
      </td>
    </tr>

  {{foreachelse}}

    <tr>
      <td class="empty" colspan="5">
        {{tr}}CImportCampaign-back-import_entities.empty{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
