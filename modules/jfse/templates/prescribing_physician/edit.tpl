{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=PrescribingPhysician ajax=$ajax}}

<script>
    Main.add(() => {
        PrescribingPhysician.physicianSearchAutocomplete(getForm('edit-prescribing-physician'), '{{$jfse_user_id}}');
        {{if !$prescription->origin}}
            PrescribingPhysician.clear();
        {{/if}}
    });
</script>

<form name="edit-prescribing-physician" id="edit-prescribing-physician" method="post" onsubmit="return false;">
    {{mb_field object=$prescription field=invoice_id hidden=true}}
    {{mb_field object=$prescribing_physician field=id hidden=true onchange="PrescribingPhysician.onChangePhysicianId(this);"}}
    <input type="hidden" name="jfse_user_id" value="{{$jfse_user_id}}">
    <table class="form">
        <tr>
            {{me_form_field nb_cells=1 mb_object=$prescription mb_field=date class='me-padding-5'}}
                {{mb_field object=$prescription field=date register=true form='edit-prescribing-physician'}}
            {{/me_form_field}}
            {{me_form_field nb_cells=1 mb_object=$prescription mb_field=origin class='me-padding-5'}}
                {{mb_field object=$prescription field=origin emptyLabel="Select"}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='CPrescribingPhysician' layout=true class='me-padding-5'}}
                <input type="text" id="physician_autocomplete" name="physician_autocomplete">
                <button id="button-empty-prescribing_physician" class="erase notext"
                        type="button" onclick="PrescribingPhysician.clear()"
                        {{if !$prescribing_physician->id}} style="display: none;"{{/if}}>
                    {{tr}}Empty{{/tr}}
                </button>
                <button id="button-edit-prescribing_physician" class="edit notext"
                        type="button"
                        onclick="PrescribingPhysician.edit(this.form.id.value)"
                        {{if !$prescribing_physician->id}} style="display: none;"{{/if}}>
                    {{tr}}CPrescribingPhysician-action-edit{{/tr}}
                </button>
                <button id="button-create-prescribing_physician" class="add notext"
                        type="button" onclick="PrescribingPhysician.add('{{$jfse_user_id}}');"
                        {{if $prescribing_physician->id}} style="display: none;"{{/if}}>
                    {{tr}}CPrescribingPhysician-action-add{{/tr}}
                </button>
                <button class="search notext" type="button" onclick="PrescribingPhysician.search('{{$jfse_user_id}}');">
                    {{tr}}CPrescribingPhysician-action-search{{/tr}}
                </button>
            {{/me_form_field}}
        </tr>

        <tr class="name edit">
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=last_name class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=last_name readonly=true}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=first_name class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=first_name readonly=true}}
            {{/me_form_field}}
        </tr>

        <tr class="edit">
            {{me_form_field nb_cells=2 mb_object=$prescribing_physician mb_field=speciality class='me-padding-5'}}
                <input type="hidden" name="speciality_id" value="{{$prescribing_physician->speciality}}">
                <select name="speciality" id="" disabled onchange='$V(this.form.speciality_id, this.value)'>
                    <option value="">{{tr}}Select{{/tr}}</option>
                    {{foreach from=$specialities item=_speciality}}
                        <option value="{{$_speciality->getCode()}}"{{if $_speciality->getCode() == $prescribing_physician->speciality}} selected="selected"{{/if}}>
                            {{$_speciality->getLabel()}}
                        </option>
                    {{/foreach}}
                </select>
            {{/me_form_field}}
        </tr>

        <tr class="edit">
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=invoicing_number class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=invoicing_number}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=type class='me-padding-5'}}
                <input type="hidden" name="type_id" value="{{$prescribing_physician->type}}">
                {{mb_field object=$prescribing_physician field=type readonly=true onchange='$V(this.form.type_id, this.value)' emptyLabel="Select"}}
            {{/me_form_field}}
        </tr>

        <tr class="edit">
            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=national_id class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=national_id readonly=true}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$prescribing_physician mb_field=structure_id class='me-padding-5'}}
                {{mb_field object=$prescribing_physician field=structure_id readonly=true}}
            {{/me_form_field}}
        </tr>

        <tr>
            <td class="button" colspan="2">
                <div id="prescribing_physician_message_container" style="display: none;"></div>
                <button class="save"
                        type="button"
                        onclick="PrescribingPhysician.store(this.form)">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
