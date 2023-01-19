{{*
 * @package Mediboard\RPPS
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=rpps script=fictifDoctor ajax=true}}

<script>
    refreshPageMedecin = function (page) {
        var oform = getForm('find_medecin');
        if (oform) {
            $V(oform.start_med, page);
            oform.onsubmit();
        }
    };
</script>

<div id="medicaux_result">
    <button type="button" class="add" onclick="FictifDoctor.addEditDoctor()">{{tr}}Rpps-msg-Create fictif doctor{{/tr}}</button>

    <form name="find_medecin" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'medicaux_result')">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="a" value="vw_fictif_doctor"/>
        <input type="hidden" name="start_med" value="{{$start_med}}"/>
        <input type="hidden" name="step_med" value="{{$step_med}}"/>

        {{mb_include module=system template=inc_pagination current=$start_med step=$step_med total=$count_medecins change_page=refreshPageMedecin}}

        <table class="tbl">
            <tr>
                <th class="narrow"></th>
                <th>{{mb_title class=CMedecin field=medecin_id}}</th>
                <th>{{mb_title class=CMedecin field=nom}}</th>
                <th>{{mb_title class=CMedecin field=rpps}}</th>
                <th class="narrow">{{mb_title class=CMedecin field=sexe}}</th>
                <th>{{mb_title class=CMedecin field=adresse}}</th>
                <th class="narrow">{{mb_title class=CMedecin field=type}}</th>
                <th>{{mb_title class=CMedecin field=disciplines}}</th>
                <th class="narrow">{{mb_title class=CMedecin field=tel}}</th>
                <th class="narrow">{{mb_title class=CMedecin field=fax}}</th>
                <th class="narrow">{{mb_title class=CMedecin field=email}}</th>
            </tr>
            {{foreach from=$fictif_doctors item=_medecin}}
                {{assign var=medecin_id value=$_medecin->_id}}
                <tr {{if !$_medecin->actif}}class="hatching"{{/if}}>
                    <td>
                        <button type="button" class="edit notext me-tertiary"
                                onclick="FictifDoctor.addEditDoctor('{{$_medecin->_id}}')">
                        </button>
                    </td>

                    <td>{{mb_value object=$_medecin field=medecin_id}}</td>

                    <td class="text">
                        {{$_medecin->nom}} {{$_medecin->prenom|strtolower|ucfirst}}
                    </td>

                    <td class="me-text-align-left">{{mb_value object=$_medecin field=rpps}}</td>

                    <td style="text-align: center;"
                        class="me-text-align-left {{if $_medecin->sexe == 'u'}}empty{{/if}}">{{mb_value object=$_medecin field=sexe}}</td>

                    <td class="text compact">
                        {{$_medecin->adresse}}<br/>
                        {{mb_value object=$_medecin field=cp}} {{mb_value object=$_medecin field=ville}}
                    </td>

                    <td style="text-align: center;"
                        class="me-text-align-left">{{mb_value object=$_medecin field=type}}</td>
                    <td class="text">{{mb_value object=$_medecin field=disciplines}}</td>
                    <td style="text-align: center;"
                        class="me-text-align-left">{{mb_value object=$_medecin field=tel}}</td>
                    <td style="text-align: center;"
                        class="me-text-align-left">{{mb_value object=$_medecin field=fax}}</td>
                    <td>{{mb_value object=$_medecin field=email}}</td>
                </tr>
                {{foreachelse}}
                <tr>
                    <td colspan="20" class="empty">{{tr}}CMedecin.none{{/tr}}</td>
                </tr>
            {{/foreach}}
        </table>
    </form>
</div>
