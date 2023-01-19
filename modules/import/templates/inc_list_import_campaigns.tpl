{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=import script=import_campaign ajax=true}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CImportCampaign field=name}}</th>
    <th>{{mb_title class=CImportCampaign field=creation_date}}</th>
    <th>{{mb_title class=CImportCampaign field=closing_date}}</th>
    <th>{{mb_title class=CImportCampaign field=creator_id}}</th>
  </tr>

  {{foreach from=$campaigns item=_campaign}}

    <tr>
      <td>
        <button class="edit notext" type="button" onclick="ImportCampaign.editCampaign('{{$_campaign->_id}}')">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_campaign field=name}}</td>
      <td>{{mb_value object=$_campaign field=creation_date}}</td>
      <td>{{mb_value object=$_campaign field=closing_date}}</td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, 'CMediusers-{{$_campaign->creator_id}}');">
           {{mb_value object=$_campaign field=creator_id}}
        </span>
      </td>
    </tr>

  {{foreachelse}}

    <tr>
      <td class="empty" colspan="4">
        {{tr}}CImportCampaign.none{{/tr}}
      </td>
    </tr>

  {{/foreach}}
</table>