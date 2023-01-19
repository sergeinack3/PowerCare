{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=configLDAP value=$conf.admin.LDAP.ldap_connection}}

{{assign var=readOnlyLDAP value=null}}
{{if $configLDAP && $user->_ref_user->_ldap_linked}}
  {{assign var=readOnlyLDAP value=true}}
{{/if}}

{{assign var=is_prat value=false}}
{{if $user->isProfessionnelDeSante()}}
  {{assign var=is_prat value=true}}
{{/if}}

<form name="editUser" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="dosql" value="do_mediusers_aed"/>
  <input type="hidden" name="user_id" value="{{$user->user_id}}"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="_user_type" value="{{$user->_user_type}}"/>

  <table class="main form">
    <tr>
      <th class="title modify text" colspan="2">
        {{$user}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="_user_last_name"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_last_name"}}
        {{else}}
          {{mb_value object=$user field="_user_last_name"}}
          {{mb_field object=$user field="_user_last_name" hidden=true}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$user field="_user_first_name"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_first_name"}}
        {{else}}
          {{mb_value object=$user field="_user_first_name"}}
          {{mb_field object=$user field="_user_first_name" hidden=true}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$user field=_user_sexe}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field=_user_sexe}}
        {{else}}
          {{mb_value object=$user field=_user_sexe}}
          {{mb_field object=$user field=_user_sexe hidden=true}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$user field=_user_birthday}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field=_user_birthday}}
        {{else}}
          {{mb_value object=$user field=_user_birthday}}
          {{mb_field object=$user field=_user_birthday hidden=true}}
        {{/if}}
      </td>
    </tr>

    <tbody {{if !$is_prat}}style="display: none;"{{/if}}>
    {{mb_include module=mediusers template="inc_infos_praticien" object=$user name_form="editUser"}}
    </tbody>

    <tr>
      <th>{{mb_label object=$user field="_user_email"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_email"}}
        {{else}}
          {{mb_value object=$user field="_user_email"}}
          {{mb_field object=$user field="_user_email" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="_user_phone"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_phone"}}
        {{else}}
          {{mb_value object=$user field="_user_phone"}}
          {{mb_field object=$user field="_user_phone" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="_user_astreinte"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_astreinte"}}
        {{else}}
          {{mb_value object=$user field="_user_astreinte"}}
          {{mb_field object=$user field="_user_astreinte" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="_user_astreinte_autre"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="_user_astreinte_autre"}}
        {{else}}
          {{mb_value object=$user field="_user_astreinte_autre"}}
          {{mb_field object=$user field="_user_astreinte_autre" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="nom_destinataire_favori"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="nom_destinataire_favori"}}
        {{else}}
          {{mb_value object=$user field="nom_destinataire_favori"}}
          {{mb_field object=$user field="nom_destinataire_favori" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$user field="destinataire_favori"}}</th>
      <td>
        {{if !$readOnlyLDAP}}
          {{mb_field object=$user field="destinataire_favori"}}
        {{else}}
          {{mb_value object=$user field="destinataire_favori"}}
          {{mb_field object=$user field="destinataire_favori" hidden=true}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>

        {{if $is_prat && "dPpatients"|module_active}}
          <button class="search" type="button" onclick="Salutation.manageSalutations('CMedecin', null, User.id);">
            {{tr}}CSalutation-action-Manage salutations{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

