{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=rgpd}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true, {
      afterChange: function (container) {
        if (container.id == "conf_etab") {
          Configuration.edit('admin', ['CGroups'], $('conf_etab_part'));
        }
      }
    });
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-permissions">{{tr}}config-permissions{{/tr}}</a></li>
  <li><a href="#config-ldap">{{tr}}config-ldap{{/tr}}</a></li>
  <li><a href="#actions">{{tr}}Maintenance{{/tr}}</a></li>
  <li><a href="#conf_etab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="config-permissions" style="display: none;">
  {{mb_include template=inc_config_permissions}}
</div>

<div id="config-ldap" style="display: none;">
  {{mb_include template=inc_config_ldap}}
</div>

<div id="actions" style="display: none;">
  {{mb_include template=inc_config_actions activer_user_action=$activer_user_action}}
</div>

<div id="conf_etab" style="display: none;">
  <div>
    <button type="button" class="search" onclick="RGPD.compareConfigurations();">
      {{tr}}CRGPDConsent-action-Compare configuration|pl{{/tr}}
    </button>
  </div>

  <div id="conf_etab_part"></div>
</div>