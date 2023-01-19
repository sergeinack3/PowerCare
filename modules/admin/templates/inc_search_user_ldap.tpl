{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=close_modal value=null}}

{{if $close_modal}}
  <div class="small-info">
    La recherche a été effectuée sur les annuaires de l'établissement : <strong>{{$current_group}}</strong>
  </div>
{{/if}}

<script>
  associateUserLDAP = function (mediuser_id, objectguid, samaccountname, close_modal) {
    var url = new Url("admin", "ajax_associate_user_ldap");
    url.addParam("mediuser_id", mediuser_id);
    url.addParam("samaccountname", samaccountname);

    var callback = function () {
      (function () {
        if (window.ldap_user_id) {
          var urlAdmin = new Url("mediusers", "viewEditMediuser");
          urlAdmin.addParam("user_id",                window.ldap_user_id);
          urlAdmin.addParam("ldap_user_actif",        window.ldap_user_actif);
          urlAdmin.addParam("ldap_user_deb_activite", window.ldap_user_deb_activite);
          urlAdmin.addParam("ldap_user_fin_activite", window.ldap_user_fin_activite);
          urlAdmin.addParam("no_association",         window.no_association);
          urlAdmin.requestModal(800, 600);
        }
      }).defer()
    };

    if (close_modal == '1') {
      callback = Control.Modal.close;
    }

    url.requestUpdate("user-ldap-" + objectguid, callback);
  }
</script>

{{if ($nb_users == 0)}}
  <div class="small-error">
    {{tr}}CUser_no-user-ldap{{/tr}}
  </div>
{{elseif ($nb_users > 1)}}
  <div class="small-warning">
    {{tr}}CUser_many-users-ldap{{/tr}}
  </div>
{{else}}
  <div class="small-info">
    {{tr}}CUser_one-user-ldap{{/tr}}
  </div>
{{/if}}

{{assign var=user value=$mediuser->_ref_user}}

<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}Actions{{/tr}}
    <th>{{mb_title class=CUser field=user_username}}</th>
    <th>{{mb_title class=CUser field=user_last_name}}</th>
    <th>{{mb_title class=CUser field=user_first_name}}</th>
    <th>{{mb_title class=CMediusers field=actif}}</th>
  </tr>

  {{foreach from=$users key=key item=_user}}
    <tr id="user-ldap-{{$_user.objectguid}}" {{if $_user.associate}}class="opacity-60"{{/if}}>
      {{mb_include template=inc_user_ldap user_ldap=$_user}}
    </tr>
  {{/foreach}}
</table>
