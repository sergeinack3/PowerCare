{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=prestations ajax=1}}

{{mb_default var=with_thead value=0}}

<script type="text/javascript">

    printDossierComplet = function (sejour_id) {
        var url = new Url("soins", "print_dossier_soins");
        url.addParam("sejour_id", sejour_id);
        url.popup(850, 600, "Dossier complet");
    };

    printDossier = function (rpu_id) {
        var url = new Url("dPurgences", "print_dossier");
        url.addParam("rpu_id", rpu_id);
        url.popup(700, 550, "RPU");
    }
</script>

{{assign var="sejour" value=$object}}
{{assign var=limit_prise_rdv value=$app->user_prefs.limit_prise_rdv}}
<table class="tbl print_sejour">
    {{mb_include module=soins template=inc_thead_dossier_soins with_thead=$with_thead}}

    {{if !@$no_header}}
        <tr>
            <th class="title text" colspan="2" style="vertical-align:middle;">
                {{mb_include module=system template=inc_object_notes}}

                <a style="float:left" href="?m=patients&tab=vw_full_patients&patient_id={{$sejour->_ref_patient->_id}}">
                    {{mb_include module=patients template=inc_vw_photo_identite patient=$sejour->_ref_patient size=42}}
                </a>

                <div style="float: right;{{if $limit_prise_rdv}}display:none;{{/if}}">
                    <button type="button" class="print"
                            onclick="printDossierComplet('{{$sejour->_id}}');">{{tr}}CSejour-action-Folder care{{/tr}}</button>
                    {{if $object->_ref_rpu && $object->_ref_rpu->_id}}
                        <button type="button" class="print"
                                onclick="printDossier({{$object->_ref_rpu->_id}})">{{tr}}CSejour-action-Folder emergency|pl{{/tr}}</button>
                    {{/if}}

                    <br/>
                    {{mb_include module=system template=inc_object_idsante400}}
                    {{mb_include module=system template=inc_object_history}}
                    <a style="float:right" class="action" title="Modifier le sejour"
                       href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$object->_id}}">
                        {{me_img src="edit.png" icon="edit" class="me-primary"}}
                    </a>
                </div>

                {{$object}}
                {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
                <br/>
                {{mb_include module=patients template=inc_icon_bmr_bhre patient=$sejour->_ref_patient}}
            </th>
        </tr>
    {{/if}}
    {{if $sejour->annule == 1}}
        <tr>
            <th class="category cancelled" colspan="4">
                {{tr}}CSejour-annule{{/tr}}
            </th>
        </tr>
    {{/if}}

    <tr>
        <td colspan="2" class="text">
            <strong>{{mb_label object=$object field="DP"}}</strong>
            {{if $object->DP}}
                {{$object->_ext_diagnostic_principal->libelle}} ({{$object->DP}})
            {{else}}
                -
            {{/if}}
        </td>
    </tr>

    {{if !$limit_prise_rdv}}
        <tr>
            <td colspan="2" class="text">
                <strong>{{mb_label object=$object field="libelle"}}</strong>
                {{$object->libelle}}
            </td>
        </tr>
    {{/if}}

    <tr>
        <td>
            <strong>{{mb_label object=$object field="group_id"}}</strong>
            {{$object->_ref_group}}
        </td>
        <td>
            <strong>{{mb_label object=$object field="praticien_id"}}</strong>
            <i>{{$object->_ref_praticien}}</i>
        </td>
    </tr>

    <tr>
        <td>
            <strong>{{mb_label object=$object field="_date_entree_prevue"}}</strong>
            {{mb_value object=$sejour field="entree_prevue"}}
        </td>
        <td>
            <strong>{{mb_label object=$object field="entree_reelle"}}</strong>
            {{mb_value object=$sejour field="entree_reelle"}}
        </td>
    </tr>

    <tr>
        <td>
            <strong>{{mb_label object=$object field="_date_sortie_prevue"}}</strong>
            {{mb_value object=$sejour field="sortie_prevue"}}
        </td>
        <td>
            <strong>{{mb_label object=$object field="sortie_reelle"}}</strong>
            {{mb_value object=$sejour field="sortie_reelle"}}
        </td>
    </tr>
    <tr>
        <td>
            <strong>{{mb_label object=$object field="_duree_prevue"}}</strong>
            {{$sejour->_duree_prevue}} jour(s)
        </td>
        <td>
            {{if $object->entree_reelle && $object->sortie_reelle}}
                <strong>{{mb_label object=$object field="_duree_reelle"}}</strong>
                {{$sejour->_duree_reelle}} jour(s)
            {{/if}}
        </td>
    </tr>

    <tr>
        <td>
            {{if $object->adresse_par_prat_id}}
                <strong>{{mb_label object=$object field="adresse_par_prat_id"}}</strong>
                {{mb_value object=$object field="adresse_par_prat_id"}}
            {{/if}}
        </td>
        <td>
            {{if $object->etablissement_entree_id}}
                <strong>{{mb_label object=$object field="etablissement_entree_id"}}</strong>
                {{mb_value object=$object field="etablissement_entree_id"}}
            {{/if}}

            {{if $object->date_entree_reelle_provenance}}
                (
                <strong>{{mb_label object=$object field=date_entree_reelle_provenance}}</strong>
                {{mb_value object=$object field=date_entree_reelle_provenance}})
            {{/if}}
        </td>
    </tr>

    {{if $object->mode_sortie != null}}
        <tr>
            <td>
                <strong>{{mb_label object=$object field="mode_sortie"}}</strong>
                {{tr}}CAffectation._mode_sortie.{{$sejour->mode_sortie}}{{/tr}}
                <br/>
            <td>
        </tr>
    {{/if}}

    {{if $object->rques}}
        <tr>
            <td class="text" colspan="2">
                <strong>{{mb_label object=$object field="rques"}}</strong>
                {{$object->rques|nl2br}}
            </td>
        </tr>
    {{/if}}

    {{if $object->convalescence}}
        <tr>
            <td class="text" colspan="2">
                <strong>{{mb_label object=$object field="convalescence"}}</strong>
                {{$object->convalescence|nl2br}}
            </td>
        </tr>
    {{/if}}

    {{if $object->directives_anticipees_status == 1}}
        <tr>
            <td class="text" colspan="2">
                <strong>{{tr}}CSejour-directives_anticipees{{/tr}}</strong>
                {{$object->directives_anticipees|nl2br}}
            </td>
        </tr>
    {{/if}}

    {{if $object->technique_reanimation_status == 1}}
        <tr>
            <td class="text" colspan="2">
                <strong>{{tr}}CSejour-technique_reanimation{{/tr}}</strong>
                {{$object->technique_reanimation|nl2br}}
            </td>
        </tr>
    {{/if}}

    {{assign var=rpu value=$object->_ref_rpu}}
    {{if $rpu && $rpu->_id && !$limit_prise_rdv}}
        <tr>
            <th class="category" colspan="2">{{tr}}CRPU{{/tr}}</th>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$rpu field="_entree"}}</strong>
                {{mb_value object=$rpu field=_entree}}
            </td>
            <td>
                <strong>{{mb_label object=$rpu field="_sortie"}}</strong>
                {{mb_value object=$rpu field=_sortie}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$rpu field="ccmu"}}</strong>
                {{if $rpu->ccmu}}
                    {{mb_value object=$rpu field=ccmu}}
                {{/if}}
            </td>
            <td>
                <strong>{{mb_label object=$rpu field="_mode_sortie"}}</strong>
                {{if $rpu->_mode_sortie}}
                    {{mb_value object=$rpu field=_mode_sortie}}
                {{/if}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="provenance"}}</strong>
                {{if $object->provenance}}
                    {{mb_value object=$object field=provenance}}
                {{/if}}
            </td>
            <td>
                <strong>{{mb_label object=$rpu field="_etablissement_sortie_id"}}</strong>
                {{if $object->provenance}}
                    {{mb_value object=$rpu field=_etablissement_sortie_id}}
                {{/if}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="transport"}}</strong>
                {{if $object->transport}}
                    {{mb_value object=$object field=transport}}
                {{/if}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="destination"}}</strong>
                {{if $object->destination}}
                    {{mb_value object=$object field=destination}}
                {{/if}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$rpu field="pec_transport"}}</strong>
                {{if $rpu->pec_transport}}
                    {{mb_value object=$rpu field=pec_transport}}
                {{/if}}
            </td>
            <td>
                <strong>{{mb_label object=$rpu field="orientation"}}</strong>
                {{if $rpu->orientation}}
                    {{mb_value object=$rpu field=orientation}}
                {{/if}}
            </td>
        </tr>
    {{elseif !$limit_prise_rdv}}
        <tr>
            <th class="category" colspan="2">{{tr}}CSejour-msg-hospi{{/tr}}</th>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="type"}}</strong>
                {{mb_value object=$sejour field="type"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="ATNC"}}</strong>
                {{mb_value object=$sejour field="ATNC"}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="chambre_seule"}}</strong>
                {{mb_value object=$sejour field="chambre_seule"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="modalite"}}</strong>
                {{mb_value object=$sejour field="modalite"}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="repas_sans_sel"}}</strong>
                {{mb_value object=$sejour field="repas_sans_sel"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="hormone_croissance"}}</strong>
                {{mb_value object=$sejour field="hormone_croissance"}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="repas_diabete"}}</strong>
                {{mb_value object=$sejour field="repas_diabete"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="lit_accompagnant"}}</strong>
                {{mb_value object=$sejour field="lit_accompagnant"}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="repas_sans_residu"}}</strong>
                {{mb_value object=$sejour field="repas_sans_residu"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="isolement"}}</strong>
                {{mb_value object=$sejour field="isolement"}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{mb_label object=$object field="repas_sans_porc"}}</strong>
                {{mb_value object=$sejour field="repas_sans_porc"}}
            </td>
            <td>
                <strong>{{mb_label object=$object field="television"}}</strong>
                {{mb_value object=$sejour field="television"}}
            </td>
        </tr>
        {{if "dPhospi prestations systeme_prestations"|gconf == "expert" && $sejour->_id}}
            <tr>
                <td colspan="2">
                    <button type="button" class="search"
                            onclick="Prestations.edit('{{$sejour->_id}}')">{{tr}}CSejour-back-prestations{{/tr}}</button>
                </td>
            </tr>
        {{/if}}
    {{/if}}
</table>

{{if !$limit_prise_rdv}}
    {{mb_include module=cabinet template=inc_list_actes_ccam subject=$sejour vue=complete}}

    {{mb_include module=planningOp template=inc_infos_hospitalisation with_thead=$with_thead}}
    {{mb_include module=planningOp template=inc_infos_operation with_thead=$with_thead}}

    {{if $sejour->_canRead}}
        {{mb_include module=hospi template=inc_list_transmissions readonly=true list_transmissions=$sejour->_ref_suivi_medical with_thead=$with_thead}}
    {{/if}}
{{/if}}
