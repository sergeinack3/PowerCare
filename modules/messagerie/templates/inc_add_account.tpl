{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  selectAccountType = function(elt) {
    var type = $V(elt);
    var url;
    switch (type) {
      case 'smtp':
        url = new Url('eai', 'ajax_edit_source');
        url.addParam('source_guid', '{{$source_smtp->_guid}}');
        url.addParam('source_name', '{{$source_smtp->name}}');
        url.addParam('light', true);
        url.requestUpdate('add_account');
        break;
      case 'pop':
        url = new Url('eai', 'ajax_edit_source');
        url.addParam('source_guid', '{{$source_pop->_guid}}');
        url.addParam('source_name', '{{$source_pop->name}}');
        url.addParam('object_guid', '{{$source_pop->object_class}}-{{$source_pop->object_id}}');
        url.addParam('light', true);
        url.requestUpdate('add_account');
        break;
      {{if $mssante}}
        case 'mssante':
          url = new Url('mssante', 'ajax_edit_user_account');
          url.requestUpdate('add_account');
          break;
      {{/if}}
      {{if $apicrypt}}
        case 'apicrypt':
          url = new Url('apicrypt', 'ajax_apicrypt_account');
          url.requestUpdate('add_account');
          break;
      {{/if}}
      {{if $medimail}}
        case 'medimail':
          var url = new Url('medimail', 'editAccount');
          url.requestUpdate('add_account');
          break;
      {{/if}}
    }
  }
</script>

<table class="layout">
  <tr>
    <td>
      Type de compte :
      <select name="account" onchange="selectAccountType(this);">
        <option value="">&mdash; Choisir un type de compte</option>
        <option value="smtp"{{if $source_smtp->_id}} disabled="disabled"{{/if}}>{{tr}}CExchangeSource.smtp-desc{{/tr}}</option>
        <option value="pop">{{tr}}CExchangeSource.pop-desc{{/tr}}</option>
        {{if $mssante}}
          <option value="mssante">{{tr}}CMSSanteUserAccount{{/tr}}</option>
        {{/if}}
        {{if $apicrypt}}
          <option value="apicrypt">{{tr}}apicrypt-account{{/tr}}</option>
        {{/if}}
        {{if $medimail}}
          <option value="medimail">{{tr}}CMedimailAccount{{/tr}}</option>
        {{/if}}
      </select>
    </td>
  </tr>
  <tr>
    <td id="add_account" style="text-align: center;"></td>
  </tr>
</table>
