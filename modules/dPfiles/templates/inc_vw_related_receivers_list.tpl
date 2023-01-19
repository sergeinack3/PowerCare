{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="narrow"></th>
  <th>{{mb_title class=CFilesCategoryToReceiver field=active}}</th>
  <th>{{mb_title class=CFilesCategoryToReceiver field=receiver_id}}</th>
  <th>{{mb_title class=CFilesCategoryToReceiver field=description}}</th>
</tr>

{{foreach from=$related_receivers item=_related_receiver}}
  <tbody id="line_{{$_related_receiver->_id}}">
    {{mb_include template="inc_vw_related_receiver"}}
  </tbody>
{{foreachelse}}
  <tr>
    <td colspan="5" class="empty">{{tr}}CFilesCategory-back-receivers.empty{{/tr}}</td>
  </tr>
{{/foreach}}