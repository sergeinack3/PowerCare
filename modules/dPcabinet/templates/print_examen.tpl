{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(window.print);
</script>
<table class="print">
    <tr>
        <th class="title" colspan="4"><a href="#" onclick="window.print()">Consultation</a></th>
    </tr>
    <tr>
        <th>{{tr}}Date{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_plageconsult->date|date_format:$conf.longdate}}</td>
        <th>{{tr}}common-Practitioner{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->_view}}</td>
    </tr>
    <tr>
        <th>{{tr}}common-Patient{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->_view}}
            {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
        </td>
        <th>{{tr}}CMediusers-_user_phone{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->_user_phone}}</td>
    </tr>
    <tr>
        <th>{{tr}}CPatient-naissance{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->naissance}}
        </td>
        <th>{{tr}}CPlageAstreinte-phone_astreinte{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->_user_astreinte}}</td>
    </tr>
    <tr>
        <th>{{tr}}CPatient-adresse{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->adresse}} <br/>{{mb_value object=$patient field="cp"}} {{mb_value object=$patient field="ville"}}
        </td>
        <th>{{tr}}CMediusers-_user_email{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->_user_email}}</td>
    </tr>
    <tr>
        <th>{{tr}}CPatient-tel{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->tel}}
        </td>
        <th>{{tr}}CMediusers-discipline_id{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->_ref_discipline->_view}}</td>
    </tr>
    <tr>
        <th>{{tr}}CPatient-tel2{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->tel2}}
        </td>
        <th>{{tr}}CMediusers-spec_cpam_id{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->spec_cpam_id}}</td>
    </tr>
    <tr>
        <th>{{tr}}CPatient-_matricule{{/tr}}</th>
        <td style="font-size: 1.3em;">
            {{$patient->matricule}}
        </td>
        <th>{{tr}}CMediusers-rpps{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->rpps}}</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <th>{{tr}}CMediusers-ADELI / AM{{/tr}}</th>
        <td style="font-size: 1.3em;">{{$consult->_ref_chir->adeli}}</td>
    </tr>
    <tr>
        <th class="category" colspan="4">Examen</th>
    </tr>
    <tr>
        <th>{{mb_label object=$consult field=motif}}</th>
        <td class="text">{{mb_value object=$consult field=motif}}</td>
        <th>{{mb_label object=$consult field=rques}}</th>
        <td class="text">{{mb_value object=$consult field=rques}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$consult field=histoire_maladie}}</th>
        <td class="text">{{mb_value object=$consult field=histoire_maladie}}</td>
        <th>{{mb_label object=$consult field=examen}}</th>
        <td class="text">{{mb_value object=$consult field=examen}}</td>
    </tr>
    {{if "dPcabinet CConsultation show_projet_soins"|gconf}}
        <tr>
            <th>{{mb_label object=$consult field=projet_soins}}</th>
            <td class="text">{{mb_value object=$consult field=projet_soins}}</td>
        </tr>
    {{/if}}
    <tr>
        <th>{{mb_label object=$consult field=conclusion}}</th>
        <td class="text">{{mb_value object=$consult field=conclusion}}</td>
        <td colspan="2"></td>
    </tr>
</table>
