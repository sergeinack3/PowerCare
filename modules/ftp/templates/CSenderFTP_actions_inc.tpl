{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ftp script=sender_ftp ajax=true}}

<table class="tbl">
  <tr>
    <th colspan="2" class="category">{{tr}}CSenderFTP-utilities{{/tr}}</th>
  </tr>   
  <tr>
    <td class="narrow">
      <button type="button" class="fas fa-sync" onclick="SenderFTP.dispatch('{{$actor->_guid}}');">
        {{tr}}CSenderFTP-utilities_dispatch{{/tr}}
      </button> 
    </td>
    <td id="CSenderFTP-utilities_dispatch"></td>
  </tr> 
</table>
