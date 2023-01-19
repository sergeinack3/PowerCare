{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mediuser value=$object}}

{{if !$mediuser->_can->read}}
    <div class="small-info">
        {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

<table class="tbl tooltip">
    <tr>
        <th class="title text" colspan="2">
            {{mb_include module=system template=inc_object_idsante400 object=$mediuser}}
            {{mb_include module=system template=inc_object_history    object=$mediuser}}
            {{mb_include module=system template=inc_object_notes      object=$mediuser}}

            {{$mediuser}}
        </th>
    </tr>

    <tr>
        <td class="text" style="width: 1px;" rowspan="4">
            {{assign var=border value=$mediuser->_ref_function->color}}
            {{mb_include module=files template=inc_named_file name=identite.jpg default=identity_user.png size=50}}
        </td>
        <td class="text">
            <strong>{{mb_value object=$mediuser->_ref_function}}</strong>
        </td>
    </tr>

    <tr>
        <td>
            {{mb_label object=$mediuser field=_user_phone}} :
            {{mb_value object=$mediuser field=_user_phone}}
        </td>
    </tr>

    {{if $mediuser->_internal_phone}}
        <tr>
            <td>
                {{mb_label object=$mediuser field=_internal_phone}} :
                {{mb_value object=$mediuser field=_internal_phone}}
            </td>
        </tr>
    {{/if}}

    <tr>
        <td>
            {{mb_label object=$mediuser field=_user_astreinte}} :
            {{mb_value object=$mediuser field=_user_astreinte}}
        </td>
    </tr>

    <tr>
        <td>
            {{mb_label object=$mediuser field=_user_email}} :
            {{mb_value object=$mediuser field=_user_email}}
        </td>
    </tr>

    {{if $mediuser->_is_praticien}}
        <tr>
            <th colspan="2">{{tr}}common-Practitioner{{/tr}}</th>
        </tr>
        {{if $mediuser->discipline_id}}
            <tr>
                <td colspan="2">
                    {{mb_label object=$mediuser field=discipline_id}} :
                    {{mb_value object=$mediuser field=discipline_id}}
                </td>
            </tr>
        {{/if}}

        {{if $mediuser->spec_cpam_id}}
            <tr>
                <td colspan="2">
                    {{mb_label object=$mediuser field=spec_cpam_id}} :
                    {{mb_value object=$mediuser field=spec_cpam_id}}
                </td>
            </tr>
        {{/if}}

        {{if $mediuser->titres}}
            <tr>
                <td colspan="2">
                    {{mb_value object=$mediuser field=titres}}
                </td>
            </tr>
        {{/if}}
        <tr>
            <td colspan="2">
                {{mb_label object=$mediuser field=rpps}} :
                {{mb_value object=$mediuser field=rpps}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{mb_label object=$mediuser field=adeli}} : {{mb_value object=$mediuser field=adeli}}
            </td>
        </tr>
    {{/if}}

    {{if $mediuser->commentaires}}
        <tr>
            <td colspan="2">
                {{mb_label object=$mediuser field=commentaires}} :
                {{mb_value object=$mediuser field=commentaires}}
            </td>
        </tr>
    {{/if}}

    <tr>
        <td colspan="2" class="button">
            {{mb_script module=personnel script=plage ajax=true}}

            {{if "dPpersonnel"|module_active && $modules.dPpersonnel->_can->edit}}
                <button type="button" class="search" onclick="PlageConge.showForUser('{{$mediuser->_id}}');">
                    {{tr}}Holidays{{/tr}}
                </button>
                {{if $mediuser->_can->edit && $app->_ref_user->isSecretaire()}}
                    <button type="button" class="new" onclick="PlageConge.editModal('', '{{$mediuser->_id}}');">
                        {{tr}}CPlageConge-action-Create holiday|pl{{/tr}}
                    </button>
                {{/if}}
            {{/if}}

            {{if "messagerie"|module_active && $modules.messagerie->_can->edit && 'messagerie access allow_internal_mail'|gconf}}
                <a class="action" href="#nothing" onclick="UserMessage.createWithSubject('{{$mediuser->_id}}', null)">
                    <button type="button">
                        {{me_img src="usermessage.png" icon="mail me-primary" title="common-action-Send a message-desc"}} {{tr}}common-Message{{/tr}}
                    </button>
                </a>
            {{/if}}

            {{if "astreintes"|module_active && $modules.astreintes->_can->edit}}
                {{mb_script module=astreintes script=plage ajax=true}}
                <button type="button" class="search" onclick="PlageAstreinte.showForUser('{{$mediuser->_id}}');">
                    {{tr}}CPlageAstreinte.plural{{/tr}}
                </button>
            {{/if}}
        </td>
    </tr>

    {{assign var=modPerm value="admin"|module_active}}
    {{if $mediuser->canEdit()}}
        <tr>
            <th colspan="2">{{tr}}common-Administration{{/tr}}</th>
        </tr>
        <tr>
            <td colspan="2" {{if !$mediuser->actif}}class="cancelled"{{/if}}>
                {{mb_label object=$mediuser field=_user_username}} :
                {{mb_value object=$mediuser field=_user_username}}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                {{mb_label object=$mediuser field=_user_type_view}} :
                {{mb_value object=$mediuser field=_user_type_view}} [{{mb_value object=$mediuser field=_user_type}}]
            </td>
        </tr>
        <tr>
            <td colspan="3">
                {{mb_label object=$mediuser field=_profile_id}} :
                {{mb_value object=$mediuser field=_profile_id}}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                {{mb_label object=$mediuser field=_user_last_login}} :
                {{mb_value object=$mediuser field=_user_last_login}}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                {{mb_label object=$mediuser field=remote}} :
                {{mb_value object=$mediuser field=remote}}
            </td>
        </tr>
        {{assign var=modPerm value='admin'|module_active}}
        {{if $modPerm && $modPerm->_can->edit}}
            <tr>
                <td colspan="3" class="button">
                    <div>
                        <a class="button search"
                           href="?m=admin&tab=vw_edit_users&user_username={{$mediuser->_user_username}}&user_id={{$mediuser->_id}}">
                            {{tr}}CMediusers_administer{{/tr}}
                        </a>

                        <a class="button search"
                           href="?m=admin&tab=vw_edit_users&user_id={{$mediuser->_id}}&tab_name=edit_perms">
                            {{tr}}common-Right|pl{{/tr}}
                        </a>

                        <a class="button search"
                           href="?m=admin&tab=vw_edit_users&user_id={{$mediuser->_id}}&tab_name=edit_prefs">
                            {{tr}}common-Preference|pl{{/tr}}
                        </a>

                        <a class="button search" href="?m=admin&tab=vw_users_auth&user_id={{$mediuser->_id}}">
                            {{tr}}common-Connection|pl{{/tr}}
                        </a>
                    </div>

                    <div>
                        {{mb_include module=admin template=loginas loginas_user=$mediuser->_ref_user}}

                        {{if $modPerm->_can->admin}}
                            {{mb_include module=admin template=unlock _user=$mediuser->_ref_user}}
                        {{/if}}
                    </div>
                </td>
            </tr>
        {{/if}}
    {{/if}}
</table>
