{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=board value=0}}
{{mb_script module=dPpatients script=ins ajax=true}}

<script>
    Main.add(function () {
        {{if array_key_exists('patient_recherche_avancee_par_defaut',$app->user_prefs) && $app->user_prefs.patient_recherche_avancee_par_defaut}}
        $$(".field_advanced").invoke("toggle");
        $$(".field_basic").invoke("toggle");
        {{/if}}

        var oform = getForm('find');
        var callback = function (e) {
            var elt = Event.element(e);

            if (elt.name !== 'start') {
                $V(elt.form.elements.start, '0');
            }
        };

        oform.on('change', 'input, select', callback);
        oform.on('ui:change', 'input, select', callback);

        oform.onsubmit();
        $V(oform.elements['new'], 1);
    });

    changePagePatient = function (start) {
        var form = getForm('find');
        $V(form.elements.start, start);
        form.onsubmit();
    }
</script>

<form name="find" action="?" method="get" onsubmit="return Patient.checkSearchingFields(this);" class="me-no-align">
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="a" value="searchPatient"/>
    <input type="hidden" name="new" value="0"/>
    <input type="hidden" name="mode" value="{{if $board}}board{{else}}search{{/if}}"/>
    <input type="hidden" name="see_link_prat" value="{{$see_link_prat}}"/>
    <input type="hidden" id="useVitale" name="useVitale" value="{{$useVitale}}"/>
    <input type="hidden" name="start" value="0"/>

    <table class="form me-align-auto me-form-search-patient">
        {{mb_include module=patients template=inc_form_fields_search_patient}}

        <tr>
            <td class="button me-text-align-right" colspan="4" style="white-space: normal;">
                <button style="float: left;" type="button" style="float: right;" class="search me-tertiary me-noicon"
                        title="{{tr}}CPatient.other_fields{{/tr}}" onclick="Patient.toggleSearch();">
                    {{tr}}CPatient.other_fields{{/tr}}
                </button>

                {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
                    <button class="search singleclick" type="button" tabindex="11" onclick="VitaleVision.read();">
                        {{tr}}CPatient.read_card_vitaleVision{{/tr}}
                    </button>
                {{elseif $app->user_prefs.LogicielLectureVitale == 'mbHost'}}
                    {{mb_include module=mbHost template=inc_vitale operation='search' formName='find'}}
                {{elseif $modFSE && $modFSE->canRead()}}
                    {{mb_include module=fse template=inc_button_vitale}}
                {{/if}}
                {{if $conf.ref_pays == "1"}}
                    <button class="me-tertiary fas fa-qrcode" type="button" onclick="INS.openModalReadDatamatrixINS()">
                        INS
                    </button>
                {{/if}}
                <button type="button" class="erase me-tertiary"
                        onclick="emptyForm();$('vw_idx_patient_button_create').hide();"
                        title="{{tr}}CPatient-action-Clear the form fields-desc{{/tr}}">
                    {{tr}}Empty{{/tr}}
                </button>
                {{if $can->edit}}
                    <button id="vw_idx_patient_button_create" class="new" type="button" tabindex="15"
                            style="display:none;"
                            onclick="Patient.createModal(this.form, null, function() {var oform = getForm('find');$V(oform.elements['new'], 0);oform.onsubmit();$V(oform.elements['new'], 1);});">
                        {{tr}}Create{{/tr}}
                    </button>
                {{/if}}
                <button id="ins_list_patient_button_search" class="search me-primary" tabindex="10"
                        type="submit" onclick="$('useVitale').value = 0;">
                    {{tr}}Search{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
