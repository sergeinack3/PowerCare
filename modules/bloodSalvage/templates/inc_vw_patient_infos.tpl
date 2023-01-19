{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-no-box-shadow">
  <tr>
    <th class="category" colspan="6">{{tr}}CPatient.infos{{/tr}}</th>
  </tr>
  {{assign var=consult_anesth value=$selOp->_ref_consult_anesth}}
  {{assign var=constantes value=$patient->_ref_constantes_medicales}}
  {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
  <tr>
    <th style="width: 16%; font-weight: bold;">{{mb_label object=$dossier_medical field=groupe_sanguin}}</th>
    <td style="width: 16%">{{$dossier_medical->groupe_sanguin}} {{$dossier_medical->rhesus}}</td>
    <th style="width: 16%; font-weight: bold;">{{mb_label object=$consult_anesth field=rai}}</th>
    <td style="width: 16%">{{$consult_anesth->rai}} </td>
    <th style="width: 16%; font-weight: bold;">{{mb_label object=$selOp field=ASA}}</th>
    <td style="width: 16%">{{$selOp->ASA}}</td>
  </tr>
  <tr>
    <th style="font-weight: bold">{{mb_label object=$constantes field=poids}}</th>
    <td>{{mb_value object=$constantes field=poids}} kg</td>
    <th style="font-weight: bold">{{mb_label object=$constantes field=taille}}</th>
    <td>{{mb_value object=$constantes field=taille}} cm</td>
    <th style="font-weight: bold">{{mb_label object=$constantes field=_imc}}</th>
    <td>{{mb_value object=$constantes field=_imc}}</td>
  </tr>
  <tr>
    <th style="font-weight: bold">{{mb_label object=$consult_anesth field="_psa"}}</th>
    <td> {{mb_value object=$consult_anesth field="_psa"}} ml/GR</td>
    <th style="font-weight: bold">{{mb_label object=$constantes field="_vst"}}</th>
    <td colspan="3">{{mb_value object=$constantes field="_vst"}} ml</td>
  </tr>
</table>