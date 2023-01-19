{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="new-prescribing-physician" method="post" onsubmit="return false;">
    <input type="hidden" name="jfse_user_id" value="{{$jfse_user_id}}">
    <table class="form">
        <tr>
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=last_name class='last-name me-padding-5'}}
                {{mb_field object=$prescribing_physician field=last_name}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=first_name class='first-name me-padding-5'}}
                {{mb_field object=$prescribing_physician field=first_name}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=invoicing_number class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=invoicing_number}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=type class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=type}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$prescribing_physician mb_field=speciality class='me-padding-5'}}
                <select name="speciality" id="">
                    {{foreach from=$specialities_list item=_speciality}}
                        <option value="{{$_speciality->getCode()}}">{{$_speciality->getLabel()}}</option>
                    {{/foreach}}
                </select>
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=national_id class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=national_id}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=structure_id class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=structure_id}}
            {{/me_form_field}}
        </tr>

        <tr>
            <td class="button" colspan="2">
                <button class="save" type="button" onclick="PrescribingPhysician.storeNewPhysician(this.form)">
                    {{tr}}Save{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
