{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=kerberos_ldap}}

<table class="tbl">
  <tr>
    <th>{{tr}}Classname{{/tr}}</th>
    <th>{{tr}}Action{{/tr}}</th>
  </tr>
  
  <tr>
    <td>
        {{tr}}CUser{{/tr}}
    </td>
    <td>
      <script type="text/javascript">
        CUser = {
          checkSiblings: function () {
            new Url('admin', 'check_siblings').requestModal(400);
          }
        }
      </script>
      <button class="tick" onclick="CUser.checkSiblings()">{{tr}}mod-admin-tab-check_siblings{{/tr}}</button>
    </td>
  </tr>


  <tr>
    <td>
        {{tr}}CUserLog{{/tr}}
    </td>
    <td>
      <script type="text/javascript">
        CUserLog = {
          sanitize: function (form) {
            var url = new Url('admin', 'sanitize_userlogs');

            if (form) {
              url.addNotNullElement(form.execute);
              url.addNotNullElement(form.offset);
              url.addNotNullElement(form.step);
              url.addElement(form.auto);
            }

            var modal = Control.Modal.stack.last();
            if (modal) {
              url.requestUpdate(modal.container.down('.content'));
            } else {
              url.requestModal(900);
            }

            return false;
          },

          auto: function () {
            var form = getForm("Sanitize");
            if ($V(form.auto) == 1) {
              CUserLog.sanitize(form);
            }
          }
        }
      </script>
      <script type="text/javascript">
        CUserAction = {
          sanitize: function (form) {
            var url = new Url('admin', 'sanitize_useractions');

            if (form) {
              url.addNotNullElement(form.execute);
              url.addNotNullElement(form.offset);
              url.addNotNullElement(form.step);
              url.addElement(form.auto);
            }

            var modal = Control.Modal.stack.last();
            if (modal) {
              url.requestUpdate(modal.container.down('.content'));
            } else {
              url.requestModal(900);
            }

            return false;
          },

          auto: function () {
            var form = getForm("Sanitize");
            if ($V(form.auto) == 1) {
              CUserAction.sanitize(form);
            }
          }
        }
      </script>
        {{if $activer_user_action}}
          <button class="tick" onclick="CUserAction.sanitize()">{{tr}}mod-admin-tab-sanitize_userlogs{{/tr}}</button>
        {{else}}
          <button class="tick" onclick="CUserLog.sanitize()">{{tr}}mod-admin-tab-sanitize_userlogs{{/tr}}</button>
        {{/if}}
    </td>
  </tr>

  <tr>
    <td>{{tr}}CKerberosLdapIdentifier|pl{{/tr}}</td>

    <td>
      <button type="button" class="search" onclick="KerberosLDAP.openImportModal();">
        {{tr}}CKerberosLdapIdentifier-action-Import identifier|pl{{/tr}}
      </button>
    </td>
  </tr>

</table>