{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function() {
      UserManagement.initializeUserView('{{$user->id}}');
    });
</script>

<div id="user-data-container">
    <table class="form">
        <tr>
            <th>{{mb_label object=$user field=last_name}}</th>
            <td>
                {{mb_value object=$user field=civility_label}} {{mb_value object=$user field=last_name}} {{mb_value object=$user field=first_name}}
            </td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=national_identification_number}}</th>
            <td>
                {{mb_value object=$user field=national_identification_number}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$user->_data_model field=securing_mode}}
            </th>
            <td>
                <form name="link-CJfseUser-{{$user->id}}-securing_mode" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
                    {{mb_class object=$user->_data_model}}
                    {{mb_key object=$user->_data_model}}
                    {{mb_field object=$user->_data_model field=securing_mode onchange="this.form.onsubmit();"}}
                </form>
            </td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=mediuser_id}}</th>
            <td>
                <form name="link-CJfseUser-{{$user->id}}-CMediuser" method="post" action="?" onsubmit="return false;">
                    <input type="hidden" name="mediuser_id" value="{{$user->mediuser_id}}">
                    <input type="text" name="mediuser_view" {{if $user->mediuser_id}}value="{{$user->_mediuser->_view}}" disabled="disabled"{{/if}}>

                    <button id="unlink-CJfseUser-{{$user->id}}-button" type="button" class="unlink notext" onclick="UserManagement.unlinkUserToMediuser('{{$user->id}}');"{{if !$user->mediuser_id}} style="display: none;"{{/if}}>{{tr}}CJfseUserView-action-unlink{{/tr}}</button>
                    <button id="link-CJfseUser-{{$user->id}}-button" type="button" class="link notext" onclick="UserManagement.linkUserToMediuser('{{$user->id}}');"{{if $user->mediuser_id}} style="display: none;"{{/if}}>{{tr}}CJfseUserView-action-link{{/tr}}</button>
                </form>
            </td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=establishment_id}}</th>
            <td>
                <form name="link-CJFseUser-{{$user->id}}-CJfseEstablishment" method="post" action="?" onsubmit="return false;">
                    <input type="hidden" name="establishment_id" value=""/>
                    <input type="text" name="establishment_view" {{if $user->establishment_id}}value="{{$user->_establishment->name}}" disabled="disabled"{{/if}}/>

                    <button id="unlink-CJfseUser-{{$user->id}}-Establishment-button" type="button" class="unlink notext" onclick="UserManagement.unlinkUserToEstablisment('{{$user->id}}');"{{if !$user->establishment_id}} style="display: none;"{{/if}}>{{tr}}CJfseUserView-action-unlink{{/tr}}</button>
                    <button id="link-CJfseUser-{{$user->id}}-Establishment-button" type="button" class="link notext" onclick="UserManagement.linkUserToEstablisment('{{$user->id}}');"{{if $user->establishment_id}} style="display: none;"{{/if}}>{{tr}}CJfseUserView-action-link{{/tr}}</button>
                </form>
            </td>
        </tr>
        <tr>
            <td class="button" colspan="2">
                <button type="button" class="trash me-tertiary-low-emphasis" onclick="UserManagement.confirmUserDeletion('{{$user->id}}');">{{tr}}Delete{{/tr}}</button>
            </td>
        </tr>
    </table>
</div>

<ul id="tabs-user-CJfseUserView-{{$user->id}}" class="control_tabs">
    <li><a href="#situation-container">{{tr}}CJfseUserView-title-situation{{/tr}}</a></li>
    <li><a href="#secondary-data-container">{{tr}}CJfseUserView-title-secondary{{/tr}}</a></li>
    <li><a href="#parameters-container">{{tr}}CJfseUserView-title-parameters{{/tr}}</a></li>
    {{if $user->substitute_number}}
        <li><a href="#substitute-container">{{tr}}CJfseUserView-title-substitute{{/tr}}</a></li>
    {{/if}}
</ul>

<div id="situation-container" style="display: none;">
    {{mb_include module=jfse template=cps/situation situation=$user->situation}}
</div>

<div id="secondary-data-container" style="display: none;">
    <table class="form">
        <tr>
            <th>{{mb_label object=$user field=installation_date}}</th>
            <td>{{mb_value object=$user field=installation_date}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=installation_zone_under_medicalized_date}}</th>
            <td>{{mb_value object=$user field=installation_zone_under_medicalized_date}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=ccam_activation}}</th>
            <td>{{mb_value object=$user field=ccam_activation}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=health_insurance_agency}}</th>
            <td>{{mb_value object=$user field=health_insurance_agency}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=health_center}}</th>
            <td>{{mb_value object=$user field=health_center}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=cnda_mode}}</th>
            <td>{{mb_value object=$user field=cnda_mode}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=cardless_mode}}</th>
            <td>{{mb_value object=$user field=cardless_mode}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=care_path}}</th>
            <td>{{mb_value object=$user field=care_path}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=last_fse_number}}</th>
            <td>{{mb_value object=$user field=last_fse_number}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$user field=formatting}}</th>
            <td>{{mb_value object=$user field=formatting}}</td>
        </tr>
    </table>
</div>

<div id="parameters-container" style="display: none;">
    {{mb_include module=jfse template=user_management/user_parameters_list parameters=$user->parameters}}
</div>

{{if $user->substitute_number}}
    <div id="substitute-container" style="display: none;">
        <table class="form">
            <tr>
                <th>{{mb_label object=$user field=substitute_number}}</th>
                <td>{{mb_value object=$user field=substitute_number}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$user field=substitute_last_name}}</th>
                <td>{{mb_value object=$user field=substitute_last_name}} {{mb_value object=$user field=substitute_first_name}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$user field=substitute_rpps_number}}</th>
                <td>{{mb_value object=$user field=substitute_rpps_number}}</td>
            </tr>
        </table>
    </div>
{{/if}}

