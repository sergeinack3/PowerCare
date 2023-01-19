{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('admin-config-tabs', true);
  });
</script>

<table class="main layout">
  <tr>
    <td class="narrow me-ws-nowrap">
      <ul id="admin-config-tabs" class="control_tabs_vertical">
        <li><a href="#admin-config-password-tab">{{tr}}Password{{/tr}}</a></li>
        <li><a href="#admin-config-auth-tab">{{tr}}CUserAuthentication{{/tr}}</a></li>
        <li><a href="#admin-config-source-tab">{{tr}}CExchangeSource{{/tr}}</a></li>
      </ul>
    </td>

    <td id="admin-config-password-tab" style="display: none;">
      <form name="editConfigPermissions-password" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

        <table class="form">
            {{assign var="class" value="CUser"}}
          <tr>
            <th class="category" colspan="2">{{tr}}config-{{$m}}-{{$class}}{{/tr}}</th>
          </tr>

            {{mb_include module=system template=inc_config_str var=max_login_attempts}}
            {{mb_include module=system template=inc_config_num var=lock_expiration_time numeric=true size=2}}
            {{*{{mb_include module=system template=inc_config_str var=force_inactive_old_authentification numeric=true size=2}}*}}
            {{*{{mb_include module=system template=inc_config_str var=probability_force_inactive_old_authentification numeric=true size=2}}*}}

          <tr>
            <th class="category" colspan="2">{{tr}}common-Password|pl{{/tr}}</th>
          </tr>

            {{mb_include module=system template=inc_config_bool var=strong_password}}
            {{mb_include module=system template=inc_config_bool var=apply_all_users}}
            {{mb_include module=system template=inc_config_bool var=enable_admin_specific_strong_password}}
            {{mb_include module=system template=inc_config_bool var=allow_change_password}}
            {{mb_include module=system template=inc_config_bool var=force_changing_password}}
            {{mb_include module=system template=inc_config_enum var=password_life_duration values="15 day|1 month|2 month|3 month|6 month|1 year"}}
            {{mb_include module=system template=inc_config_enum var=reuse_password_probation_period values='none|1-week|2-week|3-week|1-month|2-month|3-month|6-month|1-year|never'}}
            {{mb_include module=system template=inc_config_num var=coming_password_expiration_threshold numeric=true size=2}}
            {{mb_include module=system template=inc_config_str var=custom_password_recommendations textarea=true rows=10}}

          <tbody>
          <tr>
            <th class="category" colspan="2">{{tr}}common-Strong password setting|pl{{/tr}}</th>
          </tr>

          <tr>
            <th></th>
            <td>
              <div class="small-info">
                La politique de mots de passe sécurisés ne s'applique que si la configuration "Forcer des mots de passe
                sécurisés" est active.
              </div>
            </td>
          </tr>

          {{mb_include module=system template=inc_config_str var=strong_password_min_length class='CPasswordSpec' numeric=true size=2}}
          {{mb_include module=system template=inc_config_bool var=strong_password_alpha_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=strong_password_upper_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=strong_password_num_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=strong_password_special_chars class='CPasswordSpec'}}

          {{mb_include module=system template=inc_config_str var=admin_strong_password_min_length class='CPasswordSpec' numeric=true size=2}}
          {{mb_include module=system template=inc_config_bool var=admin_strong_password_alpha_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=admin_strong_password_upper_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=admin_strong_password_num_chars class='CPasswordSpec'}}
          {{mb_include module=system template=inc_config_bool var=admin_strong_password_special_chars class='CPasswordSpec'}}
          </tbody>

          <tr>
            <td class="button" colspan="2">
              <button class="modify">{{tr}}Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>

    <td id="admin-config-auth-tab" style="display: none;">
      <form name="editConfigPermissions-auth" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

        <table class="form">
            {{assign var="class" value="CUser"}}

          <tr>
            <th class="category" colspan="2">{{tr}}common-Kerberos authentication{{/tr}}</th>
          </tr>

            {{mb_include module=system template=inc_config_bool var=enable_kerberos_authentication class='CKerberosLdapIdentifier'}}
            {{mb_include module=system template=inc_config_bool var=enable_login_button class='CKerberosLdapIdentifier'}}
            {{mb_include module=system template=inc_config_bool var=enable_automapping class='CKerberosLdapIdentifier'}}

          <tr>
            <th class="category" colspan="2">{{tr}}common-PSC authentication{{/tr}}</th>
          </tr>

            {{mb_include module=system template=inc_config_bool var=enable_psc_authentication class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_bool var=enable_login_button class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_bool var=enable_automapping class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_bool var=session_mode class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_str var=client_id size=60 class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_str var=client_secret size=60 class='ProSanteConnect'}}
            {{mb_include module=system template=inc_config_str var=redirect_uri size=60 class='ProSanteConnect'}}

          <tr>
            <th class="category" colspan="2">{{tr}}common-FC authentication{{/tr}}</th>
          </tr>

            {{mb_include module=system template=inc_config_bool var=enable_fc_authentication class='FranceConnect'}}
            {{mb_include module=system template=inc_config_bool var=enable_login_button class='FranceConnect'}}
            {{mb_include module=system template=inc_config_str var=client_id size=60 class='FranceConnect'}}
            {{mb_include module=system template=inc_config_str var=client_secret size=60 class='FranceConnect'}}
            {{mb_include module=system template=inc_config_str var=redirect_uri size=60 class='FranceConnect'}}
            {{mb_include module=system template=inc_config_str var=logout_redirect_uri size=60 class='FranceConnect'}}

          <tr>
            <td class="button" colspan="2">
              <button class="modify">{{tr}}Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>

    <td id="admin-config-source-tab" style="display: none;">
        {{mb_include module=system template=inc_config_exchange_source source=$reset_account_source}}
    </td>
  </tr>
</table>
