{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        $("journee_operatoire").fixedTableHeaders();
    });
</script>

{{assign var=colspan_th value=17}}
{{if "dPsalleOp timings use_entry_room"|gconf}}
    {{assign var=colspan_th value=$colspan_th+1}}
{{/if}}

{{assign var=colspan_td value=55}}
{{if "dPsalleOp timings use_entry_room"|gconf}}
    {{assign var=colspan_td value=$colspan_td+1}}
{{/if}}
{{if $conf.ref_pays}}
    {{assign var=colspan_td value=$colspan_td+1}}
{{/if}}
{{if $show_constantes}}
    {{assign var=colspan_td value=$colspan_td+2}}
{{/if}}

{{assign var=sejour_colspan value=11}}

{{assign var=patient_colspan value=3}}
{{if $show_constantes}}
    {{assign var=patient_colspan value=$patient_colspan+2}}
{{/if}}

<div id="journee_operatoire" class="x-scroll">
    <table class="tbl">
        <tbody>
        {{if $type == "prevue"}}
            {{foreach from=$plages item=_plage}}
                <tr>
                    <th colspan="{{$colspan_td}}" class="section">
                        {{$_plage}}
                        &mdash; {{$_plage->_ref_salle}}
                        &mdash; {{$_plage->_ref_owner}}
                    </th>
                </tr>
                {{foreach from=$_plage->_ref_operations item=_operation}}
                    {{mb_include template=inc_bloc2_line}}
                    {{foreachelse}}
                    <tr>
                        <td colspan="{{$colspan_td}}" class="empty">{{tr}}COperation.none{{/tr}}</td>
                    </tr>
                {{/foreach}}
                {{foreachelse}}
                <tr>
                    <td colspan="{{$colspan_td}}" class="empty">{{tr}}CPlageOp.none{{/tr}}</td>
                </tr>
            {{/foreach}}
        {{else}}
            {{foreach from=$operations item=_operation}}
                {{mb_include template=inc_bloc2_line}}
                {{foreachelse}}
                <tr>
                    <td colspan="{{$colspan_td}}" class="empty">{{tr}}COperation.none{{/tr}}</td>
                </tr>
            {{/foreach}}
        {{/if}}
        </tbody>

        <thead>
        <tr>
            <th rowspan="3">Date</th>
            <th colspan="2">Salle</th>
            <th colspan="4">Vacation</th>
            <th colspan="2">N� d'ordre</th>
            <th colspan="{{$patient_colspan}}">Patient</th>
            <th colspan="{{$sejour_colspan}}">Hospitalisation</th>
            <th rowspan="3">Chirurgien</th>
            <th rowspan="3">Anesth�siste</th>
            <th colspan="3">Nature</th>
            <th rowspan="3">Type<br/>anesth�sie</th>
            <th rowspan="3">{{tr}}side{{/tr}}<br/>{{tr}}operated{{/tr}}</th>
            <th rowspan="3">Code<br/>ASA</th>
            <th rowspan="2" colspan="2">Placement<br/>programme</th>
            <th colspan="{{$colspan_th}}">Timings intervention</th>
            <th colspan="5">Timings r�veil</th>
        </tr>

        <tr>
            <th rowspan="2">Pr�vu</th>
            <th rowspan="2">R�el</th>
            <th colspan="2">D�but</th>
            <th colspan="2">Fin</th>
            <th rowspan="2">Pr�vu</th>
            <th rowspan="2">R�el</th>

            {{* Patient *}}
            <th rowspan="2">IPP</th>
            <th rowspan="2">{{tr}}common-Identity{{/tr}}</th>
            <th rowspan="2">{{tr}}common-Age{{/tr}}</th>
            {{if $show_constantes}}
                <th rowspan="2">{{tr}}common-Weight{{/tr}} (kg)</th>
                <th rowspan="2">{{tr}}common-Size{{/tr}} (cm)</th>
            {{/if}}

            {{* S�jour *}}
            <th rowspan="2">NDA</th>
            <th rowspan="2">Type</th>
            <th colspan="2">Entr�e<br/>pr�vue</th>
            <th colspan="2">Entr�e<br/>r�elle</th>
            <th colspan="2">Sortie<br/>pr�vue</th>
            <th colspan="2">Sortie<br/>r�elle</th>
            <th rowspan="2">Dur�e<br/>r�elle</th>

            <th rowspan="2">Libell�</th>
            <th rowspan="2">DP</th>
            <th rowspan="2">Actes</th>

            {{if "dPsalleOp timings use_entry_room"|gconf}}
                <th colspan="2">entr�e<br/>bloc</th>
            {{/if}}

            <th colspan="2">Entr�e<br/>salle</th>
            <th colspan="2">D�but<br/>induction</th>
            <th colspan="2">Fin<br/>induction</th>
            <th colspan="2">Pose<br/>garrot</th>
            <th colspan="2">D�but<br/>intervention</th>
            <th colspan="2">Fin<br/>intervention</th>
            <th colspan="2">Retrait<br/>garrot</th>
            <th colspan="2">Sortie<br/>salle</th>
            <th rowspan="2">Patient<br/>suivant</th>
            <th colspan="2">Entr�e</th>
            <th colspan="2">Sortie</th>
        </tr>

        <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Date</th>
            <th>Heure</th>
            {{if "dPsalleOp timings use_entry_room"|gconf}}
                <th>Date</th>
                <th>Heure</th>
            {{/if}}
        </tr>
        </thead>
    </table>
</div>
