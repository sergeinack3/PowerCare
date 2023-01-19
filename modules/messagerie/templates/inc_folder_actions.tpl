{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" title="{{tr}}CUserMailFolder-action-show_all_mails{{/tr}}" onclick="UserEmail.selectFolder('{{$account->_id}}', '{{$folder->_id}}', 1);">
  <i class="msgicon fa fa-eye"></i>
  {{tr}}CUserMailFolder-action-show_all_mails{{/tr}}
</button>
<br/>
<button type="button" title="{{tr}}Edit{{/tr}}" onclick="UserEmail.editFolder('{{$account->_id}}', '{{$folder->_id}}');">
  <i class="msgicon fas fa-pencil-alt"></i>
  {{tr}}Edit{{/tr}}
</button>
<br/>