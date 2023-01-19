{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="WhiteList.refreshList" total=$total current=$page step=30}}

<table class="tbl">
  <tr>
    <th class="title">{{tr}}CWhiteList.all{{/tr}}</th>
  </tr>

  {{foreach from=$whitelists item=_whitelist}}
    <tr>
      <td {{if !$_whitelist->actif}}class="opacity-60"{{/if}}>
        <a href="#1" onclick="WhiteList.edit('{{$_whitelist->_id}}');">{{$_whitelist->email}}</a>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CWhiteList.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>