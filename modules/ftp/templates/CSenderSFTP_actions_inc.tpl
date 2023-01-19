{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ftp script=sender_sftp ajax=true}}

<table class="tbl">
  <tr>
    <th colspan="2" class="category">{{tr}}CSenderSFTP-utilities{{/tr}}</th>
  </tr>   
  <tr>
    <td class="narrow">
      <button type="button" class="fas fa-sync" onclick="SenderSFTP.dispatch('{{$actor->_guid}}');">
        {{tr}}CSenderSFTP-utilities_dispatch{{/tr}}
      </button> 
    </td>
    <td id="CSenderSFTP-utilities_dispatch"></td>
  </tr> 
</table>
