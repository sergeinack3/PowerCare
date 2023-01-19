{{*
 * @package Mediboard\jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(() => {
      UserManagement.initializeEstablishmentView({{if $establishment->id}}'{{$establishment->id}}'{{/if}});
    });
</script>

{{if $establishment->id}}
    <ul id="tabs-CJfseEstablishmentView-{{$establishment->id}}" class="control_tabs">
        <li><a href="#establishment-container">{{tr}}CJfseEstablishmentView{{/tr}}</a></li>
        <li><a href="#establishment-configuration-container">{{tr}}CEstablishmentConfiguration{{/tr}}</a></li>
        <li><a href="#linked-users-container">{{tr}}CJfseEstablishmentView-title-linked_users{{/tr}}</a></li>
        <li><a href="#linked-employee_cards-container">{{tr}}CEmployeeCard-title-list{{/tr}}</a></li>
    </ul>

    <div id="establishment-container" style="display: none;">
{{/if}}
<form name="edit-CJfseEstablishment{{if $establishment->id}}-{{$establishment->id}}{{/if}}" method="post" action="?" onsubmit="return false;">
    {{mb_field object=$establishment field=id hidden=true}}
    {{mb_field object=$establishment field=jfse_establishment_id hidden=true}}
    {{mb_field object=$establishment field=_object_id hidden=true}}
    {{mb_field object=$establishment field=_object_class hidden=true}}

    <table class="form">
        <tr>
            <th>
                <div class="CJfseEstablishment-group_container"{{if $establishment->id && $establishment->_object && $establishment->_object->_class !== 'CGroups'}} style="display: none;"{{/if}}>
                    <label for="edit-CJfseEstablishment{{if $establishment->id}}-{{$establishment->id}}{{/if}}-group_view">{{tr}}CGroups{{/tr}}</label>
                </div>
                <div class="CJfseEstablishment-function_container"{{if $establishment->id && $establishment->_object && $establishment->_object->_class !== 'CFunctions'}} style="display: none;"{{/if}}>
                    <label for="edit-CJfseEstablishment{{if $establishment->id}}-{{$establishment->id}}{{/if}}-function_view">{{tr}}CFunctions{{/tr}}</label>
                </div>
            </th>
            <td>
                <div class="CJfseEstablishment-group_container"{{if $establishment->id && $establishment->_object && $establishment->_object->_class !== 'CGroups'}} style="display: none;"{{/if}}>
                    <input type="text" name="group_view"
                           {{if $establishment->_object && $establishment->_object->_class === 'CGroups'}} value="{{$establishment->_object->_view}}" disabled="disabled"{{/if}}>
                </div>
                <div class="CJfseEstablishment-function_container"{{if $establishment->id && $establishment->_object && $establishment->_object->_class !== 'CFunctions'}} style="display: none;"{{/if}}>
                    <input type="text" name="function_view"
                           {{if $establishment->_object && $establishment->_object->_class === 'CFunctions'}}value="{{$establishment->_object->_view}}" disabled="disabled"{{/if}}>
                </div>
            </td>
            <td class="narrow">
                {{if $establishment->id}}
                    <button id="unlink-CJfseEstablishment-{{$establishment->id}}-button" type="button" class="unlink notext" onclick="UserManagement.unlinkEstablishmentToObject('{{$establishment->id}}');"{{if !$establishment->_object_id}} style="display: none;"{{/if}}>{{tr}}CJfseEstablishmentView-action-unlink{{/tr}}</button>
                    <button id="link-CJfseEstablishment-{{$establishment->id}}-button" type="button" class="link notext" onclick="UserManagement.linkEstablishmentToObject('{{$establishment->id}}');"{{if $establishment->_object_id}} style="display: none;"{{/if}}>{{tr}}CJfseEstablishmentView-action-link{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=name}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=name}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=type}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=type}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=health_center_number}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=health_center_number}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=category}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=category pattern="[0-9A-Za-z]{0, 3}" maxLength=3 size=3}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=exoneration_label}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=exoneration_label}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=status}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=status pattern="[0-9A-Za-z]{0, 2}" maxLength=2 size=2}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$establishment field=invoicing_mode}}
            </th>
            <td colspan="2">
                {{mb_field object=$establishment field=invoicing_mode pattern="[0-9A-Za-z]{0, 2}" maxLength=2 size=2}}
            </td>
        </tr>
        <tr>
            <td class="button" colspan="3">
                <button type="button" class="save" onclick="UserManagement.storeEstablishment(this.form);">{{tr}}Save{{/tr}}</button>
                {{if $establishment->id}}
                    <button type="button" class="trash" onclick="UserManagement.deleteEstablishment(this.form);">{{tr}}Delete{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>

{{if $establishment->id}}
    </div>
    {{assign var=configuration value=$establishment->configuration}}
    <div id="establishment-configuration-container" style="display: none;">
        <form name="CJfseEstablishment-{{$establishment->id}}-configuration" method="post" action="?" onsubmit="return false;">
            <input type="hidden" name="establishment_id" value="{{$establishment->id}}">

            <table class="form">
                <tr>
                    <th>{{mb_label object=$configuration field=invoice_number}}</th>
                    <td>{{mb_field object=$configuration field=invoice_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=invoice_set_number}}</th>
                    <td>{{mb_field object=$configuration field=invoice_set_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=refund_demand_number}}</th>
                    <td>{{mb_field object=$configuration field=refund_demand_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=maximum_invoice_set_number}}</th>
                    <td>{{mb_field object=$configuration field=maximum_invoice_set_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=maximum_refund_demand_number}}</th>
                    <td>{{mb_field object=$configuration field=maximum_refund_demand_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=desired_invoice_number}}</th>
                    <td>{{mb_field object=$configuration field=desired_invoice_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=file_number}}</th>
                    <td>{{mb_field object=$configuration field=file_number}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=invoice_number_range_activation}}</th>
                    <td>{{mb_field object=$configuration field=invoice_number_range_activation}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=invoice_number_range_start}}</th>
                    <td>{{mb_field object=$configuration field=invoice_number_range_start}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$configuration field=invoice_number_range_end}}</th>
                    <td>{{mb_field object=$configuration field=invoice_number_range_end}}</td>
                </tr>
                <tr>
                    <td class="button" colspan="2">
                        <button type="button" class="save" onclick="UserManagement.storeEstablishmentConfiguration(this.form);">
                            {{tr}}Save{{/tr}}
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <div id="linked-users-container" style="display: none;">
        {{mb_include module=jfse template=user_management/establishment_users_list users=$establishment->users establishment_id=$establishment->id}}
    </div>

    <div id="linked-employee_cards-container" style="display: none;">
        {{mb_include module=jfse template=user_management/employee_cards_list employee_cards=$establishment->employee_cards}}
    </div>
{{/if}}
