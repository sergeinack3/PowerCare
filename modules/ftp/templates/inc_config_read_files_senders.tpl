{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ftp script=sender_ftp ajax=true}}

<table class="tbl">
  <tr>
    <td class="narrow">
      <button type="button" class="tick" onclick="SenderFTP.readFilesSenders();">
        {{tr}}CSenderFTP-utilities_read-files-senders{{/tr}}
      </button> 
    </td>
    <td id="CSenderFTP-utilities_read-files-senders"></td>
  </tr> 
</table>
