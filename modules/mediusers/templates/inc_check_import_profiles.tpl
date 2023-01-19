{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  <ul>
    <li>Une case <span style="background-color: #fc0">jaune</span> indique qu'il y a une différence entre le fichier d'import et l'utilisateur présent.</li>
    <li>Une case <span style="background-color: #f66">rouge</span> indique que l'utilisateur du fichier d'import n'existe pas encore.</li>
  </ul>
</div>

<table class="main tbl">
  <tr>
    <th class="title" colspan="7">{{tr}}CUser-import-profile-in-file|pl{{/tr}}</th>
  </tr>

  <tr>
    <th>{{tr}}CUser-import-directory-name{{/tr}}</th>
    <th>
      {{tr}}CUser-template{{/tr}}
      <br/>
      <input size="10" onkeyup="ImportUsers.searchColumn(this);" class="search"/>
    </th>
    <th class="narrow">{{tr}}CUser-import-perms-module-count{{/tr}}</th>
    <th class="narrow">{{tr}}CUser-import-perms-class-count{{/tr}}</th>
    <th class="narrow">{{tr}}CUser-import-prefs-count{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$import_users key=_file_name item=_infos}}
    <tr class="search-import-row">
      <td align="center">{{$_file_name}}</td>
      <td align="center" class="search">{{$_infos.user->user_username}}</td>
      <td align="center" class="{{if $_infos.new}}error{{elseif $_infos.hash_perm_mod && $_infos.hash_perm_mod != $_infos.new_hash_perm_mod}}warning{{/if}}">
        {{$_infos.nb_perms_mod}}
      </td>
      <td align="center" class="{{if $_infos.new}}error{{elseif $_infos.hash_perm_obj && $_infos.hash_perm_obj != $_infos.new_hash_perm_obj}}warning{{/if}}">
        {{$_infos.nb_perms_obj}}
      </td>
      <td align="center" class="{{if $_infos.new}}error{{elseif $_infos.hash_prefs && $_infos.hash_prefs != $_infos.new_hash_prefs}}warning{{/if}}">
        {{$_infos.nb_prefs}}
      </td>
      <td align="center" id="import-user-{{$_file_name}}">
        {{if $_infos.new}}
          <button class="search" type="button" onclick="ImportUsers.displayExistingUser('{{$_file_name}}');">
            {{tr}}CUser-import-show-perms{{/tr}}
          </button>
          <button class="import" type="button" onclick="ImportUsers.importNewProfile('{{$_file_name}}');">{{tr}}Import{{/tr}}</button>
        {{else}}
          <button class="search" type="button"
                  onclick="ImportUsers.displayExistingUser('{{$_file_name}}');">{{tr}}CUser-import-show-compare{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>
