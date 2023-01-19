{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  prepareHDDatabase = function () {
    var url = new Url('openData', 'ajax_update_hd');
    url.requestUpdate('systemMsg');
  }
</script>

<form name="editConfig" action="?m=openData&amp;{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}
  
  <table class="form">
    <tr>
      <th class="title" colspan="3">Configuration</th>
    </tr>

    {{mb_include module=system template=configure_dsn dsn=hospi_diag inline=true}}

    <tr>
      <th class="title" colspan="3">{{tr}}openData-msg-Hd prepare database{{/tr}}</th>
    </tr>

    <tr>
      <td colspan="3">
        <button type="button" class="tick" onclick="prepareHDDatabase();">{{tr}}openData-Hd-action prepare database{{/tr}}</button>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>