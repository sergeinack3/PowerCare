{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=new_profile value=0}}
{{if !$compare.old_profile->_id}}
  {{assign var=new_profile value=1}}
{{/if}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="8">
      {{if !$new_profile}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$compare.old_profile->_guid}}');">
          {{tr}}CUser-import-compare{{/tr}} : {{$compare.old_profile}}
        </span>
      {{else}}
        {{tr}}CUser-import-new{{/tr}}
      {{/if}}
    </th>
  </tr>

  <tr>
    <th class="title" colspan="2"></th>
    {{if !$new_profile}}
      <th class="title" colspan="2">{{tr}}CUser-import-actual-profile{{/tr}}</th>
    {{/if}}
    <th class="title" colspan="{{if $new_profile}}4{{else}}2{{/if}}">{{tr}}CUser-import-new-profile{{/tr}}</th>
  </tr>

  {{mb_include module=mediusers template=inc_compare_users}}

  {{mb_include module=mediusers template=inc_compare_users_perms_module}}

  {{mb_include module=mediusers template=inc_compare_users_perms_objects}}

  {{mb_include module=mediusers template=inc_compare_users_prefs table=$compare.prefs title='CPreferences' type='prefs'}}

  {{mb_include module=mediusers template=inc_compare_users_prefs table=$compare.perms_functionnal
    title='mod-dPpatients-tab-functional_perms' type='perms_functionnal'}}

</table>

<hr/>

<form name="import-existing-mediusers" method="post">
  <input type="hidden" name="file_name" value="{{$file_name}}"/>
  <input type="hidden" name="new_name" value=""/>

  <table class="main form">
    <tr>
      <th width="50%"><label for="perms">{{tr}}CMediusers-import-perms{{/tr}}</label></th>
      <td><input type="checkbox" name="perms" value="1" checked/></td>
    </tr>

    <tr>
      <th><label for="prefs">{{tr}}CMediusers-import-prefs{{/tr}}</label></th>
      <td><input type="checkbox" name="prefs" value="1" checked/></td>
    </tr>

    <tr>
      <th><label for="perms_functionnal">{{tr}}CMediusers-import-perms-functionnal{{/tr}}</label></th>
      <td><input type="checkbox" name="perms_functionnal" value="1" checked/></td>
    </tr>

    <tr>
      <td class="button" colspan="2">
      {{if $new_profile}}
        <button type="button" class="import" onclick="ImportUsers.importNewProfile('{{$file_name}}'); Control.Modal.close();">
          {{tr}}Import{{/tr}}
        </button>
      {{else}}
        <button type="button" class="import" onclick="ImportUsers.submitCreateNewProfile();">
          {{tr}}CUser-import-new-profil{{/tr}}
        </button>
        <button type="button" class="import" onclick="ImportUsers.submitUpdateProfile();">
          {{tr}}CUser-import-update-infos{{/tr}}
        </button>
      {{/if}}
      </td>
    </tr>
  </table>
</form>
