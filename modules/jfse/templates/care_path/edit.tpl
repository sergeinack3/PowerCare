{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=CarePath ajax=$ajax}}

<script>
    Main.add(() => CarePath.changeIndicator($$('#indicator_field select')[0], true));
</script>

<form name="care_path_edit" action="" method="post" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
    <input type="hidden" name="referring_physician_last_name" value="{{if $referring_physician}}{{$referring_physician->last_name}}{{/if}}">
    <input type="hidden" name="referring_physician_first_name" value="{{if $referring_physician}}{{$referring_physician->first_name}}{{/if}}">
    <table class="care_path" style="width: 100%;">
        <tr class="all" id="indicator_field">
            {{me_form_field nb_cells=2 mb_object=$care_path mb_field=indicator class="button me-padding-5"}}
                {{mb_field object=$care_path field=indicator onchange="CarePath.changeIndicator(this)" emptyLabel='Select'}}
            {{/me_form_field}}
        </tr>
        <tr class="indicator-s1 indicator-d indicator-h indicator-b indicator-j indicator-m indicator-s2">
            {{me_form_field nb_cells=2 mb_object=$care_path mb_field=declaration layout=true class="me-padding-5"}}
                <label for="care_path_edit_declaration_1">
                    <input type="radio" name="declaration" value="1" class="bool" id="care_path_edit_declaration_1"
                           onclick="CarePath.onChangeDeclaration(this.form);" {{if $care_path->declaration == '1' || ($care_path->declaration == '' && $referring_physician)}} checked="checked"{{/if}}>
                    {{tr}}Yes{{/tr}}
                </label>
                <label for="care_path_edit_declaration_0" class="me-margin-left-5">
                    <input type="radio" name="declaration" value="0" class="bool" id="care_path_edit_declaration_0"
                           onclick="CarePath.onChangeDeclaration(this.form);" {{if $care_path->declaration == '0'}} checked="checked"{{/if}}>
                    {{tr}}No{{/tr}}
                </label>
            {{/me_form_field}}
        </tr>
        <tr class="indicator-j">
            {{me_form_field nb_cells=2 mb_object=$care_path mb_field=install_date class="button me-padding-5"}}
                {{mb_field object=$care_path field=install_date register=true form=care_path_edit onchange="CarePath.onChangeInstallDate(this);"}}
            {{/me_form_field}}
        </tr>
        <tr class="indicator-b">
            {{me_form_field nb_cells=2 mb_object=$care_path mb_field=poor_md_zone_install_date class="button me-padding-5"}}
                {{mb_field object=$care_path field=poor_md_zone_install_date register=true form=care_path_edit onchange="CarePath.onChangeInstallDate(this);"}}
            {{/me_form_field}}
        </tr>
        {{assign var=no_physician_selected value=true}}
        <tr class="indicator-m">
            {{me_form_field nb_cells=2 label='CJfsePrescription.origin.O' class="button me-padding-5"}}
                <select name="corresponding_physician" onchange="CarePath.onSelectCorrespondingPhysician(this);">
                    <option value="" data-first_name="" data-last_name="">&mdash;{{tr}}Select{{/tr}}</option>
                    {{if is_array($corresponding_physicians) && count($corresponding_physicians)}}
                        {{foreach from=$corresponding_physicians item=_physician name=corresponding_physicians}}
                            {{assign var=selected value=false}}
                            {{if strtoupper($_physician->first_name) == $care_path->doctor->first_name && strtoupper($_physician->last_name) == $care_path->doctor->last_name}}
                                {{assign var=selected value=true}}
                                {{assign var=no_physician_selected value=false}}
                            {{/if}}
                            <option value="{{$smarty.foreach.corresponding_physicians.index}}"
                                    data-first_name="{{$_physician->first_name}}" data-last_name="{{$_physician->last_name}}"
                                    {{if $selected}} selected{{/if}}
                            >
                                {{$_physician->last_name}} {{$_physician->first_name}}
                            </option>
                        {{/foreach}}
                    {{/if}}
                    <option value="other"
                            {{if $no_physician_selected && $care_path->doctor->first_name !== '' && $care_path->doctor->last_name !== ''}} selected{{/if}}>
                        {{tr}}CCarePathDoctor-title-Other{{/tr}}
                    </option>
                </select>
            {{/me_form_field}}
        </tr>
        <tr class="indicator-o{{if !is_array($corresponding_physicians) || !count($corresponding_physicians)}} indicator-m{{/if}}" id="row-doctor">
            {{me_form_field nb_cells=1 mb_object=$care_path->doctor mb_field=first_name class="me-padding-5"}}
                {{mb_field object=$care_path->doctor field=first_name maxLength=25 onchange="CarePath.onChangeReferringPhysician(this.form);"}}
            {{/me_form_field}}
            {{me_form_field nb_cells=1 mb_object=$care_path->doctor mb_field=last_name class="me-padding-5"}}
                {{mb_field object=$care_path->doctor field=last_name maxLength=25 onchange="CarePath.onChangeReferringPhysician(this.form);"}}
            {{/me_form_field}}
        </tr>
    </table>

    <div id="care_path_message_container" style="display: none;"></div>
</form>
