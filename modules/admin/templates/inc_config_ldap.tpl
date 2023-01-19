{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=ldap}}

<script>
  var stop = false;

  function LDAPMassiveImport(button){
    if (stop) {
      stop = false;
      return;
    }
    var action = $V(button.form.elements.do_import);
    if (!action) {
      stop = true;
    }
    var url = new Url("admin", "ajax_ldap_massive_import");
    url.addParam("do_import", $V(button.form.elements.do_import) ? 1 : 0);
    url.addParam("count", $V(button.form.elements.count));
    url.requestUpdate("ldap-massive-import-search", LDAPMassiveImport.curry(button));
  }

  Main.add(function() {
    Control.Tabs.create('tabs-configure-ldap', true);
    LDAPSource.list();
  });
</script>

<table class="main">
  <tr>
    <td style="vertical-align: top; width: 130px">
      <ul id="tabs-configure-ldap" class="control_tabs_vertical">
        <li><a href="#ldap">{{tr}}ldap{{/tr}}</a></li>
        <li><a href="#ldap-sources">{{tr}}CSourceLDAP|pl{{/tr}}</a></li>
      </ul>
    </td>
    <td style="vertical-align: top;">
      <div id="ldap" style="display: none;">
        <form name="editConfigLDAP" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

          <table class="form" style="">
            {{assign var="class" value="LDAP"}}
            <tr>
              <th class="title" colspan="2">{{tr}}config-{{$m}}-{{$class}}{{/tr}}</th>
            </tr>
            {{mb_include module=system template=inc_config_bool var=ldap_connection}}
            {{mb_include module=system template=inc_config_bool var=allow_change_password}}
            {{mb_include module=system template=inc_config_bool var=allow_login_as_admin}}
            {{mb_include module=system template=inc_config_bool var=check_ldap_password_expiration}}

            <tr>
              <td class="button" colspan="2">
                <button class="modify">{{tr}}Save{{/tr}}</button>
              </td>
            </tr>
          </table>
        </form>

        <form name="LDAPMassiveImportSearchForm" method="get">
          <table class="tbl">
            <tr>
              <th class="title" colspan="2">{{tr}}ldap-massive-import{{/tr}}</th>
            </tr>

            <tr>
              <td class="narrow">
                <div class="small-info">
                  Les comptes seront recherchés dans les annuaires de l'établissement : <strong>{{$current_group}}</strong>
                </div>

                <button type="button" class="tick" onclick="LDAPMassiveImport(this);">
                  {{tr}}ldap-massive-import-search{{/tr}}
                </button>
                <button type="button" class="stop" onclick="stop=true;">{{tr}}Stop{{/tr}}</button>
              </td>
              <td rowspan="2" id="ldap-massive-import-search"></td>
            </tr>
            <tr>
              <td class="narrow">
                <label><input type="checkbox" name="do_import" />{{tr}}Import{{/tr}}</label>
                <input type="text" name="count" value="5" size="10"/>
                <script>
                  Main.add(function() {
                    getForm("LDAPMassiveImportSearchForm")["count"].addSpinner({min:1, max:100, step:1});
                  });
                </script>
              </td>
            </tr>
          </table>
        </form>
      </div>

      <div id="ldap-sources" style="display: none;"></div>
    </td>
  </tr>
</table>
