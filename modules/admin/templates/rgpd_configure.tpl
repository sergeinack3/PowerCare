{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('rgpd_configure_tabs', true);
  });
</script>

<div class="small-info me-no-align me-margin-top-8">
  {{tr}}CRGPDConsent-msg-In order to configure GDPR message content, please check Configure tab.{{/tr}}
</div>

<table class="main layout">
  <tr>
    <td style="width: 10%;">
      <ul id="rgpd_configure_tabs" class="control_tabs_vertical small">
        <li><a href="#rgpd_user">{{tr}}CRGPDConsent-title-User{{/tr}}</a></li>
        <li><a href="#rgpd_source">{{tr}}CSourceSMTP{{/tr}}</a></li>
      </ul>
    </td>

    <td id="rgpd_user" style="display: none;">
      <script>
        Main.add(function () {
          var form = getForm('editConfigRGPD');

          var element_value = form.elements['admin[CRGPDConsent][user_id]'];

          var element_assigned = form.elements._rgpd_user;
          var url = new Url('system', 'ajax_seek_autocomplete');
          url.addParam('object_class', 'CMediusers');

          url.addParam('input_field', element_assigned.name);
          url.autoComplete(element_assigned, null, {
            minChars:      2,
            method:        'get',
            select:        'view',
            dropdown:      true,
            updateElement: function (selected) {
              $V(element_assigned, selected.down('span.view').getText().trim());
              $V(element_value, selected.get('id'));
            }
          });
        });
      </script>

      <form name="editConfigRGPD" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_configure module=$m}}
        <input type="hidden" name="admin[CRGPDConsent][user_id]" value="{{if $rgpd_user && $rgpd_user->_id}}{{$rgpd_user->_id}}{{/if}}" />

        <table class="main form me-no-box-shadow">
          <col style="width: 10%;" />

          <tr>
            <th>
              <label title="{{tr}}config-admin-CRGPDConsent-user_id-desc{{/tr}}">
                {{tr}}config-admin-CRGPDConsent-user_id{{/tr}}
              </label>
            </th>

            <td>
              <input type="text" name="_rgpd_user" value="{{if $rgpd_user && $rgpd_user->_id}}{{$rgpd_user->_view}}{{/if}}" />
              <button type="button" class="erase notext"
                      onclick="$V(this.form.elements['admin[CRGPDConsent][user_id]'], ''); $V(this.form.elements._rgpd_user, '');">
                {{tr}}common-action-Reset{{/tr}}
              </button>
            </td>
          </tr>

          <tr>
            <td class="button" colspan="2">
              <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>

    <td id="rgpd_source" style="display: none;">
      {{mb_include module=system template=inc_config_exchange_source source=$source_smtp}}
    </td>
  </tr>
</table>