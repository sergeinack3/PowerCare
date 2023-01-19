{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
    <td class="text">{{$_operation->_datetime|date_format:$conf.date}}</td>
    <td class="text">{{$_operation->_ref_salle_prevue}}</td>
    <td class="text">{{$_operation->_ref_salle_reelle}}</td>
    <td class="text">
        {{if $_operation->_ref_plageop}}
            {{$_operation->_ref_plageop->date|date_format:$conf.date}}
            <br/>
        {{/if}}
    </td>
    <td class="text">
        {{$_operation->_deb_plage|date_format:$conf.time}}
    </td>
    <td class="text">
        {{if $_operation->_ref_plageop}}
            {{$_operation->_ref_plageop->date|date_format:$conf.date}}
            <br/>
        {{/if}}
    </td>
    <td class="text">
        {{$_operation->_fin_plage|date_format:$conf.time}}
    </td>

    <td class="text">
        {{if $_operation->rank}}
            #{{$_operation->rank}} à {{$_operation->time_operation|date_format:$conf.time}}
        {{else}}
            Non validé
        {{/if}}
    </td>

    <td class="text">
        {{if $_operation->_rank_reel}}
            #{{$_operation->_rank_reel}} à {{mb_value object=$_operation field=entree_salle}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td style="text-align: right;">
        <span class="idex-special idex-special-IPP">{{$_operation->_ref_sejour->_ref_patient->_IPP}}</span>
    </td>

    <td class="text">{{$_operation->_ref_sejour->_ref_patient->_view}}</td>
    <td style="text-align: right;">{{$_operation->_ref_sejour->_ref_patient->_annees}}</td>

    {{if $show_constantes}}
        <td style="text-align: right;">{{$_operation->_ref_sejour->_ref_patient->_poids}}</td>
        <td style="text-align: right;">{{$_operation->_ref_sejour->_ref_patient->_taille}}</td>
    {{/if}}
    <td>
        <span class="idex-special idex-special-NDA">{{$_operation->_ref_sejour->_NDA}}</span>
    </td>

    <td class="text">{{tr}}CSejour.type.{{$_operation->_ref_sejour->type}}{{/tr}}</td>
    <td class="text">
        {{$_operation->_ref_sejour->entree_prevue|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->_ref_sejour->entree_prevue|date_format:$conf.time}}
    </td>

    <td class="text">
        {{if $_operation->_ref_sejour->entree_reelle}}
            {{$_operation->_ref_sejour->entree_reelle|date_format:$conf.date}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td class="text">
        {{if $_operation->_ref_sejour->entree_reelle}}
            {{$_operation->_ref_sejour->entree_reelle|date_format:$conf.time}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td class="text">
        {{$_operation->_ref_sejour->sortie_prevue|date_format:$conf.date}}
    </td>

    <td class="text">
        {{$_operation->_ref_sejour->sortie_prevue|date_format:$conf.time}}
    </td>

    <td class="text">
        {{if $_operation->_ref_sejour->sortie_reelle}}
            {{$_operation->_ref_sejour->sortie_reelle|date_format:$conf.date}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td class="text">
        {{if $_operation->_ref_sejour->sortie_reelle}}
            {{$_operation->_ref_sejour->sortie_reelle|date_format:$conf.time}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td class="text">
        {{if $_operation->_ref_sejour->_duree_reelle}}
            {{$_operation->_ref_sejour->_duree_reelle}}
        {{else}}
            &ndash;
        {{/if}}
    </td>

    <td class="text">Dr {{$_operation->_ref_chir->_view}}</td>

    <td class="text">
        {{if $_operation->_ref_anesth->_id}}
            Dr {{$_operation->_ref_anesth->_view}}
        {{/if}}
    </td>

    <td class="text">{{$_operation->libelle}}</td>
    <td class="text">{{$_operation->_ref_sejour->DP}}</td>
    <td class="text">{{$_operation->codes_ccam|replace:'|':' '}}</td>
    <td class="text">{{$_operation->_lu_type_anesth}}</td>
    <td class="text">{{tr}}COperation.cote.{{$_operation->cote}}{{/tr}}</td>
    <td class="text">{{$_operation->ASA}}</td>

    <td class="text">
        {{$_operation->_ref_workflow->date_creation|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->_ref_workflow->date_creation|date_format:$conf.time}}
    </td>

    {{if "dPsalleOp timings use_entry_room"|gconf}}
        <td class="text">
            {{$_operation->entree_bloc|date_format:$conf.date}}
        </td>
        <td class="text">
            {{$_operation->entree_bloc|date_format:$conf.time}}
        </td>
    {{/if}}

    <td class="text">
        {{$_operation->entree_salle|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->entree_salle|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->induction_debut|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->induction_debut|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->induction_fin|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->induction_fin|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->pose_garrot|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->pose_garrot|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->debut_op|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->debut_op|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->fin_op|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->fin_op|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->retrait_garrot|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->retrait_garrot|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->sortie_salle|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->sortie_salle|date_format:$conf.time}}
    </td>

    <td class="text">{{mb_value object=$_operation field=_pat_next}}</td>

    <td class="text">
        {{$_operation->entree_reveil|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->entree_reveil|date_format:$conf.time}}
    </td>

    <td class="text">
        {{$_operation->sortie_reveil_reel|date_format:$conf.date}}
    </td>
    <td class="text">
        {{$_operation->sortie_reveil_reel|date_format:$conf.time}}
    </td>
</tr>
