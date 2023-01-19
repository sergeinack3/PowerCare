{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=hide_header         value=false}}
{{mb_default var=prescription_sejour value=false}}

<table class="tbl">
    {{if !$hide_header}}
        <tr>
            <th class="title" colspan="4">
                {{tr}}CDossierMedical-Medical folder of{{/tr}}
                {{tr}}{{$object->object_class}}{{/tr}}
            </th>
        </tr>
    {{/if}}

    <tr>
        <th>{{tr}}CAntecedent(|pl){{/tr}}</th>
        {{if is_array($object->_ref_traitements)}}
            <th>{{tr}}CTraitement(|pl){{/tr}}</th>
        {{/if}}
        <th>{{tr}}CDossierMedical-Diagnosis(|pl){{/tr}}</th>
    </tr>

    <tr>
        <td class="text top" style="width: {{if is_array($object->_ref_traitements)}}33{{else}}50{{/if}}%">
            {{foreach from=$object->_ref_antecedents_by_type_appareil key=_type item=antecedents_by_appareil}}
                {{if $antecedents_by_appareil|@count}}
                    {{foreach from=$antecedents_by_appareil key=_appareil item=antecedents name=foreach_atcd}}
                        {{if $antecedents|@count}}
                            <strong>
                                {{tr}}CAntecedent.type.{{$_type}}{{/tr}} &ndash;
                                {{tr}}CAntecedent.appareil.{{$_appareil}}{{/tr}}
                            </strong>
                            <ul>
                                {{foreach from=$antecedents item=_antecedent}}
                                    <li>
                                        {{if $_antecedent->date}}
                                            {{mb_value object=$_antecedent field="date"}} :
                                        {{/if}}
                                        {{mb_value object=$_antecedent field=rques}}
                                    </li>
                                {{/foreach}}
                            </ul>
                        {{/if}}
                    {{/foreach}}
                {{/if}}
            {{/foreach}}
            {{if !count($object->_all_antecedents)}}
                <div class="empty">{{tr}}CAntecedent.none{{/tr}}</div>
            {{/if}}
        </td>

        {{if (is_array($object->_ref_traitements) && $object->_ref_traitements|@count) ||
        ($object->_ref_prescription && $object->_ref_prescription->_id && $object->_ref_prescription->_ref_prescription_lines|@count)}}
            <td class="text top" style="width: 33%">
                {{if is_array($object->_ref_traitements)}}
                    {{if $object->_ref_traitements|@count}}<ul>{{/if}}
                    {{foreach from=$object->_ref_traitements item=_traitement}}
                        <li>
                            {{mb_include module=system template=inc_interval_date_progressive object=$_traitement from_field=debut to_field=fin}}
                            :
                            {{mb_value object=$_traitement field=traitement}}
                        </li>
                    {{/foreach}}
                    {{if $object->_ref_traitements|@count}}</ul>{{/if}}
                {{/if}}

                {{if $object->_ref_prescription}}
                    {{if !$prescription_sejour}}
                        {{assign var=prescription value=$object->_ref_prescription}}
                    {{/if}}
                    {{if $object->_ref_prescription->_id && $prescription->_ref_prescription_lines|@count}}
                        {{mb_script module=prescription script=prescription ajax=true}}
                        {{if (is_array($object->_ref_traitements) && $object->_ref_traitements|@count)}}
                            <hr style="width: 50%"/>
                        {{/if}}
                        <ul>
                            {{foreach from=$prescription->_ref_prescription_lines item=_line}}
                                <li {{if $_line->date_arret}}style="display: none;"{{/if}}>
                                    {{if $_line->debut || $_line->fin}}
                                        {{mb_include module=system template=inc_interval_date from=$_line->debut to=$_line->fin}} :
                                    {{/if}}
                                    <a href="#1"
                                       onclick="Prescription.viewProduit(null,'{{$_line->code_ucd}}','{{$_line->code_cis}}', null, '{{$_line->bdm}}');">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}', 'objectView');">
                      {{$_line->_ucd_view}}
                    </span>
                                    </a>
                                    {{if $_line->_ref_prises|@count}}
                                        ({{foreach from=`$_line->_ref_prises` item=_prise name=foreach_prise}}
                                        {{$_prise}}
                                        {{if !$smarty.foreach.foreach_prise.last}},{{/if}}
                                    {{/foreach}})
                                    {{/if}}
                                    {{if $_line->commentaire}}
                                        <span class="compact">
                      {{mb_value object=$_line field=commentaire}}
                    </span>
                                    {{/if}}
                                </li>
                            {{/foreach}}
                        </ul>
                    {{/if}}
                {{/if}}

            </td>
        {{elseif $object->absence_traitement}}
            <td class="top" style="width: 33%">
                <div class="empty">{{tr}}CTraitement.absence{{/tr}}</div>
            </td>
        {{else}}
            <td class="top" style="width: 33%">
                <div class="empty">{{tr}}CTraitement.none{{/tr}}</div>
            </td>
        {{/if}}

        <td class="text top">
            {{if $object->_ext_codes_cim|@count}}
            <ul>{{/if}}
                {{foreach from=$object->_ext_codes_cim item=_code}}
                    <li>
                        <strong>{{$_code->code}}:</strong> {{$_code->libelle}}
                    </li>
                    {{foreachelse}}
                    <div class="empty">{{tr}}CDiagnostic.none{{/tr}}</div>
                {{/foreach}}
                {{if $object->_ext_codes_cim|@count}}</ul>{{/if}}
        </td>
    </tr>
</table>
