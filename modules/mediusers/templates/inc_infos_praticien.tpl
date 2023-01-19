{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=banque_edit ajax=1}}

{{assign var=module_rpps value="rpps"|module_active}}

<script>
    Main.add(function () {
        BanqueEdit.form = "{{$name_form}}";
        BanqueEdit.autoComplete();
        BanqueEdit.editButton();
    });
</script>

<tr>
    <td colspan="2" class="text">
        <div class="small-info">
            {{tr}}CPatient-msg-Information relevant only to health professionals{{/tr}}
        </div>
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field=discipline_id}}</th>
    <td>{{mb_field object=$object field=discipline_id options=$disciplines style="width: 250px;"}}</td>
</tr>


<tr>
    <th>{{mb_label object=$object field=spec_cpam_id}}</th>
    <td>
        {{mb_include module=mediusers template=inc_select_cpam_speciality field=spec_cpam_id selected=$object->spec_cpam_id specialities=$spec_cpam width="250px" empty_value=true}}
    </td>
</tr>

{{if "eai"|module_active}}
    <tr>
        <th>{{mb_label object=$object field=other_specialty_id}}</th>
        <td>{{mb_field object=$object field=other_specialty_id autocomplete="true,1,50,true,true" form=$name_form}}</td>
    </tr>
{{/if}}

{{if $conf.ref_pays == 1}}
    <tr>
        <th>{{mb_label object=$object field="adeli"}}</th>
        <td>
            {{mb_field object=$object field="adeli"}}
            {{foreach from=$object->_ref_secondary_users item=_user}}
                <br>
                <input type="hidden" name="_secondary_user_id[]" value="{{$_user->_id}}"/>
                <input type="text" name="_secondary_user_adeli[]" value="{{$_user->adeli}}"
                       class="numchar length|9 confidential mask|99S9S99999S9 control|luhn" size="12">
            {{/foreach}}
            {{assign var=u value='Ox\Mediboard\Mediusers\CMediusers::get'|static_call:null}}
            {{if $u->isAdmin()}}
                <button type="button" class="add notext" onclick="addSecondaryAccount();">Ajouter une situation de
                    facturation
                </button>
            {{/if}}
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="rpps"}}</th>
        <td>
            {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="rpps"}}

            {{if $module_rpps}}
                {{if !$object->_id && !$object->_personne_exercice_identifiant_structure}}
                  <button type="button" class="add notext" onclick="Control.Modal.close(); CMediusers.searchMedecinAnnuaire(null, $V(this.form.rpps));">
                      {{tr}}CMediusers-action-Add an RPPS number from the health directory{{/tr}}
                  </button>
                {{elseif $object->_id && $object->rpps && $object->_is_rpps_link_personne_exercice}}
                  <button type="button" class="unlink notext" onclick="CMediusers.unlinkMedecinAnnuaire('{{$object->_id}}');">
                      {{tr}}CMediusers-action-Unlink the RPPS number{{/tr}}
                  </button>
                {{/if}}
            {{/if}}
        </td>
    </tr>
  <tr>
    <th>{{mb_label object=$object field="sub_psc"}}</th>
    <td>{{mb_field object=$object field="sub_psc" readonly=true}}</td>
  </tr>
    <tr>
        <th>{{mb_label object=$object field="cps"}}</th>
        <td>{{mb_field object=$object field="cps"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="mail_apicrypt"}}</th>
        <td>{{mb_field object=$object field="mail_apicrypt"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="mssante_address"}}</th>
        <td>{{mb_field object=$object field="mssante_address"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="secteur"}}</th>
        <td>{{mb_field object=$object field="secteur" emptyLabel="Choose"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field=pratique_tarifaire}}</th>
        <td>{{mb_field object=$object field=pratique_tarifaire emptyLabel="CMediusers.pratique_tarifaire.empty"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field=ccam_context}}</th>
        <td>
            <select name="ccam_context">
                <option value="" {{if !$object->ccam_context}}selected="selected"{{/if}}>
                    &mdash; {{tr}}CMediusers.ccam_context.auto{{/tr}}</option>
                {{assign var=ccam_contexts value='Ox\Mediboard\Ccam\CContexteTarifaireCCAM'|static:'practitioner_context'}}
                {{foreach from=$ccam_contexts item=context_name key=context_id}}
                    {{if $context_id >= 10}}
                        <option value="{{$context_id}}"
                                {{if $object->ccam_context == $context_id}}selected="selected"{{/if}}>{{$context_name}}</option>
                    {{/if}}
                {{/foreach}}
            </select>
        </td>
    </tr>
    {{if 'pyxVital'|module_active || 'oxPyxvital'|module_active}}
        <tr>
            <th>{{mb_label object=$object field=mode_tp_acs}}</th>
            <td>{{mb_field object=$object field=mode_tp_acs emptyLabel="CMediuser-mode_tp_acs.empty"}}</td>
        </tr>
    {{/if}}
    <tr>
        <th>{{mb_label object=$object field="cab"}}</th>
        <td>{{mb_field object=$object field="cab"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="conv"}}</th>
        <td>{{mb_field object=$object field="conv"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="zisd"}}</th>
        <td>{{mb_field object=$object field="zisd"}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="ik"}}</th>
        <td>{{mb_field object=$object field="ik"}}</td>
    </tr>
{{elseif $conf.ref_pays == 3}}
    <tr>
        <th>{{mb_label object=$object field='inami'}}</th>
        <td>{{mb_field object=$object field='inami'}}</td>
    </tr>
{{/if}}

<tr>
    <th>{{mb_label object=$object field="titres"}}</th>
    <td>{{mb_field object=$object field="titres"}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="compta_deleguee"}}</th>
    <td>{{mb_field object=$object field="compta_deleguee"}}</td>
</tr>

{{if $conf.ref_pays == 1}}
    {{assign var=banques value='Ox\Mediboard\Cabinet\CBanque::loadAllBanques'|static_call:null}}
    <tr>
        <th>{{mb_label object=$object field="compte"}}</th>
        <td>{{mb_field object=$object field="compte"}}</td>
    </tr>
    {{if is_array($banques)}}
        <!-- Choix de la banque quand disponible -->
        <tr>
            <th>{{mb_label object=$object field="banque_id"}}</th>
            <td>
                {{mb_field object=$object field=banque_id hidden=true onchange='BanqueEdit.editButton()'}}
                <input type="text" name="banque_id_autocomplete_view" value="{{$object->_ref_banque->_view}}"
                       class="autocomplete styled-element"
                       size="25">
                <button style="display: none" type="button" id="edit_button"
                        onclick="BanqueEdit.edit($V(this.form.banque_id), null, true)" class="edit notext">
                </button>
                <button type="button" class="add notext" onclick="BanqueEdit.edit(null, null, true);"
                        title="{{tr}}CBanque-title-create{{/tr}}">{{tr}}CBanque-title-create{{/tr}}
                </button>
            </td>
        </tr>
    {{/if}}
{{/if}}
