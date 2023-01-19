{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
    <div class="small-info">
        {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{mb_script module=urgences    script=urgences ajax=1}}
{{mb_script module=compteRendu script=document ajax=1}}
{{mb_script module=admissions script=admissions ajax=1}}

{{assign var=consult value=$object->_ref_consult}}
{{assign var=sejour  value=$object->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}

<table class="tbl tooltip">
    <tr>
        <th class="title text" colspan="4">
            {{mb_include module=system template=inc_object_notes     }}
            {{mb_include module=system template=inc_object_idsante400}}
            {{mb_include module=system template=inc_object_history   }}
            {{mb_include module=system template=inc_object_uf}}

            {{if $sejour->presence_confidentielle}}{{mb_include module=planningOp template=inc_badge_sejour_conf}}{{/if}} {{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$sejour->entree to=$sejour->sortie}}

            {{if $app->_ref_user->isAdmin() && ('admin CBrisDeGlace enable_bris_de_glace'|gconf || 'admin CLogAccessMedicalData enable_log_access'|gconf)}}
                <a href="#" onclick="guid_access_medical('{{$sejour->_guid}}')" style="float:right;"><img
                      src="images/icons/planning.png" alt=""/></a>
            {{/if}}
        </th>
    </tr>

    <tr>
        <td rowspan="{{if $object->_ref_extract_passages|@count}}4{{else}}2{{/if}}" class="me-w0 me-valign-top">
            {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
        </td>
        <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
        {{$patient->_view}} {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
      </span>
            <br/> <br/>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
        {{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$sejour->entree to=$sejour->sortie}}
      </span>
        </td>
    </tr>
    <tr>
      <td>
          {{tr}}CRPU-_count_extract_passages-court{{/tr}} : {{$object->_ref_extract_passages|@count}}
      </td>

    </tr>
    {{if $object->_ref_extract_passages|@count}}
      <tr>
        <td>
            {{tr}}CRPU-_first_extract_passages{{/tr}}
          : {{mb_value object=$object->_first_extract_passages field=date_extract}}
        </td>
      </tr>
      <tr>
        <td>
            {{tr}}CRPU-_last_extract_passages{{/tr}}
          : {{mb_value object=$object->_last_extract_passages field=date_extract}}
        </td>
      </tr>
    {{/if}}
    <tr>
        <td class="button" colspan="3">
            {{assign var=curr_user value=$app->_ref_user}}

            {{if $object->canEdit()}}
                {{assign var=is_inf       value=$curr_user->isInfirmiere()}}
                {{assign var=is_as        value=$curr_user->isAideSoignant()}}

                {{* Prise en charge médicale *}}
                {{if $consult->_id}}
                    <button type="button" class="search" onclick="Urgences.pecMed('{{$consult->_id}}', 'rpuConsult');">
                        {{tr}}CRPU-see_pec_med{{/tr}}
                    </button>
                {{else}}
                    <button type="button" class="save" onclick="Urgences.createPecMed('{{$object->_id}}');">
                        {{tr}}CRPU-pec{{/tr}}
                    </button>
                {{/if}}

                {{* Prise en charge infirmier *}}
                {{if $object->pec_inf || (!$is_inf && !$is_as)}}
                    <button type="button" class="search"
                            onclick="Urgences.pecInf('{{$sejour->_id}}', '{{$object->_id}}');">
                        {{tr}}CRPU-see_pec_inf{{/tr}}
                    </button>
                {{else}}
                    <form name="pecInf" method="post" onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$object}}
                        {{mb_key object=$object}}
                        <input type="hidden" name="callback" value="Urgences.pecInf.curry({{$sejour->_id}})"/>
                        <input type="hidden" name="pec_inf" value="now"/>

                        <button type="button" class="save" onclick="this.form.onsubmit();">
                            {{tr}}CRPU-pec_inf{{/tr}}
                        </button>
                    </form>
                {{/if}}
                <button type="button" class="search"
                        onclick="Urgences.actions('{{$object->_id}}', '{{$object->sejour_id}}');">{{tr}}Actions{{/tr}}</button>
                {{if $consult->_id && !$object->mutation_sejour_id}}
                    <button type="button" class="search"
                            onclick="Urgences.synthese('{{$consult->_id}}', '{{$object->sejour_id}}');">{{tr}}CRPU.synthese{{/tr}}</button>
                {{elseif !$consult->_id}}
                    <button type="button" class="search"
                            onclick="Urgences.syntheseRPU('{{$object->_id}}');">{{tr}}CRPU.synthese{{/tr}}</button>
                {{/if}}
            {{/if}}

            <br/>

            {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$sejour display_center=0}}

            {{mb_include module=patients template=inc_button_add_doc patient_id=$patient->_id context_guid=$sejour->_guid}}

            {{if "web100T"|module_active}}
                {{mb_include module=web100T template=inc_button_iframe _sejour=$sejour notext=""}}
            {{/if}}

            <button type="button" class="print" onclick="Urgences.printDossier({{$object->_id}})">
                {{tr}}Print{{/tr}} dossier
            </button>

            <button type="button" class="print"
                    onclick="Document.printSelDocs('{{$sejour->_id}}', '{{$sejour->_class}}');">
                {{tr}}CCompteRendu.global_print{{/tr}}
                {{if $object->_nb_files_docs}}
                    ({{$object->_nb_files_docs}} doc{{if $object->_nb_files_docs > 1}}s{{/if}}.)
                {{/if}}
            </button>

            {{if $consult->_id}}
                <button type="button" class="edit" onclick="Urgences.pecMed('{{$consult->_id}}', 'facturation');">
                    {{tr}}Reglement{{/tr}}
                </button>
            {{/if}}

            <button class="tick" type="button"
                    onclick="Admissions.validerSortie('{{$sejour->_id}}', false, function() { window.location.reload(); });">
                {{tr}}CSejour-action-Validate the output{{/tr}}
            </button>
        </td>
    </tr>
</table>
