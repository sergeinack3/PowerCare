{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  // Todo: To deport in JS file, but ensure that the file is correctly included everywhere it is needed
  promptUnlockModal = function (user_id) {
    Modal.confirm(
      $T('CUser-confirm-Unlock?'),
      {
        onOK: function () {
          var url = new Url('admin', 'ajax_get_unlock_form');
          url.addParam('user_id', user_id);
          url.addParam('auto_submit', '1');
          url.requestUpdate('unlock-' + user_id);
        }
      }
    );
  }
</script>

{{if $_user->isLocked()}}
  <button type="button" class="tick compact" {{if !$can->admin}}disabled{{/if}} onclick="promptUnlockModal('{{$_user->_id}}');">
    {{tr}}Unlock{{/tr}}
  </button>

  <div id="unlock-{{$_user->_id}}" style="display: none;"></div>
{{/if}}
