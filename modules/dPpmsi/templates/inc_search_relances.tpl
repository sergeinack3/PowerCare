{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=files script=file ajax=true}}

{{math equation="x+12" x='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs"|@count assign=colspan}}

{{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
    {{if !"dPpmsi relances $doc"|gconf}}
        {{math equation=x-1 x=$colspan assign=colspan}}
    {{/if}}
{{/foreach}}


<div id="print_relances">
    {{mb_include module=pmsi template=inc_print_header_relances}}

    <table class="tbl">
        <tr>
            <th class="title" colspan="{{$colspan}}">
                {{if $sejour_exist && ($relances|@count == 0)}}
                    <button type="button" class="tick" style="float: left;"
                            onclick="Relance.edit(null, '{{$sejour->_id}}', Relance.searchRelances)">{{tr}}pmsi-create_relance{{/tr}}
                        ({{$sejour->_ref_patient->_view}})
                    </button>
                {{/if}}

                <button type="button" class="download not-printable me-primary me-float-right" style="float: left;"
                        onclick="Relance.export();">{{tr}}Export{{/tr}}</button>
                <button type="button" class="print notext not-printable me-tertiary" style="float: right;"
                        onclick="if ($('radio_by_prat').checked) { $('print_by_prat').print(); } else { $('print_relances').print(); }"></button>
                <label style="float: right;">
                    <input id="radio_by_prat" type="checkbox"/> Par praticien
                </label>
                Relances ({{$relances|@count}} résultat(s))
            </th>
        </tr>

        {{mb_include module=pmsi template=inc_header_relance}}

        <tbody id="sorted_lines">
        {{foreach from=$relances item=_relance}}
            {{mb_include module=pmsi print=false template=inc_line_relance}}
        {{foreachelse}}
        </tbody>
        <tr>
            <td class="empty" colspan="{{$colspan}}">{{tr}}CRelancePMSI.none{{/tr}}</td>
        </tr>
        {{/foreach}}
    </table>
</div>

<div id="print_by_prat" class="only-printable">
    {{mb_include module=pmsi template=inc_print_header_relances}}

    {{foreach from=$relances_by_prat item=_relances key=prat_id name=relances_by_prat}}
        <table class="tbl" {{if !$smarty.foreach.relances_by_prat.last}}style="page-break-after: always;"{{/if}}>
            <tr>
                <th class="title" colspan="{{$colspan}}">
                    {{assign var=prat value=$prats.$prat_id}}
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prat}}
                </th>
            </tr>

            {{mb_include module=pmsi template=inc_header_relance}}
            {{foreach from=$_relances item=_relance}}
                {{mb_include module=pmsi print=true template=inc_line_relance}}
            {{/foreach}}
        </table>
        {{foreachelse}}
        {{tr}}CRelancePMSI.none{{/tr}}
    {{/foreach}}
</div>
