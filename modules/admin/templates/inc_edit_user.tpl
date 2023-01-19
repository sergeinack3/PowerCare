{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers ajax=1}}

{{assign var=configLDAP value=$conf.admin.LDAP.ldap_connection}}
{{if $configLDAP && $user->_ldap_linked}}
  {{assign var=readOnlyLDAP value=true}}
  <div class="small-warning">
    {{tr}}CUser_associate-ldap{{/tr}}
  </div>
{{else}}
  {{assign var=readOnlyLDAP value=null}}
{{/if}}

<form name="Edit-user" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_key object=$user}}

  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_user_aed" />

  <input type="hidden" name="callback" value="UserPermission.callback"/>
  <input type="hidden" name="_duplicate" value="" />
  <input type="hidden" name="_duplicate_username" value="" />
  <input type="hidden" name="del" value="" />

  <input type="hidden" name="@token" value="{{$token}}" />

  <table class="main form me-margin-0 me-padding-top-0 me-padding-bottom-0">
    <tr>
      {{if $user->_id}}
      <th class="title modify me-margin-0 me-padding-top-0 me-padding-bottom-0" colspan="2">
        {{assign var=object value=$user}}
        {{mb_include module=system template=inc_object_idsante400}}
        {{mb_include module=system template=inc_object_history}}
        {{mb_include module=system template=inc_object_notes}}
        Utilisateur '{{$user}}'
      {{else}}
      <th class="title me-th-new" colspan="2">
        {{tr}}CUser-title-create{{/tr}}
      {{/if}}
      </th>
    </tr>
  </table>

  <script>
    reloadEditPerms = function (file, user_id) {
      var prefix = "";
      var suffix = "";

      if (file == "functional_perms") {
        prefix = "vw_";
      }

      var url = new Url('admin', prefix+file+suffix);
      url.addParam('user_id', user_id);
      url.addParam('show_icone', 0);
      url.requestUpdate(file);
    };

    Main.add(function() {
      var tabs = Control.Tabs.create('tabs-user', true ,
        { afterChange: function(container) {
          if (container.id == "edit_perms" || container.id == "edit_prefs" || container.id == "functional_perms") {
            reloadEditPerms(container.id, '{{$user->_id}}');
          }
          else if(container.id == "connexions") {
            new Url('admin','ajax_vw_user_authentications').addParam('user_id', '{{$user->_id}}').requestUpdate("connexions");
          }
        }
        });

      {{if $user->template}}
        Control.Tabs.setTabCount("profiled_users", "{{$user->_ref_profiled_users|@count}}");
      {{/if}}

      tabs.setActiveTab('{{$tab_name}}');
    });
  </script>

  <ul id="tabs-user" class="control_tabs me-small me-margin-top-0">
    <li><a href="#identity">{{tr}}CUser-identity{{/tr}}</a></li>
    {{if $user->template}}
    <li>
      <a href="#profiled_users">
        {{tr}}CUser-back-profiled_users{{/tr}}
        <small>(&ndash;)</small>
      </a>
    </li>
    {{/if}}
    {{if $user->_id}}
      <li>
        <a href="#edit_perms">
          {{tr}}common-Right|pl{{/tr}}
        </a>
      </li>
      <li>
        <a href="#edit_prefs">
          {{tr}}common-Preference|pl{{/tr}}
        </a>
      </li>
      <li>
        <a href="#functional_perms">
          {{tr}}FunctionalPerms{{/tr}}
        </a>
      </li>
      <li>
        <a href="#connexions">
          {{tr}}common-Connection|pl{{/tr}}
        </a>
      </li>
    {{/if}}

    {{if $user->_id}}
      <li><a href="#user-security">{{tr}}common-Security{{/tr}}</a></li>
    {{/if}}
  </ul>

  <div id="identity">
    {{mb_include template=inc_form_user}}
  </div>

  <div id="profiled_users" style="display: none;">
    {{mb_include template=inc_profiled_users}}
  </div>
</form>

<div id="edit_perms" class="me-padding-top-2" style="display: none;"></div>
<div id="edit_prefs" style="display: none;"></div>
<div id="functional_perms" style="display: none;"></div>
<div id="connexions" style="display: none;"></div>

{{if $user->_id}}
  <div id="user-security" style="display: none;">
    {{mb_include module=admin template=inc_user_security user=$user}}
  </div>
{{/if}}
