{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="4">{{tr}}common-Information of vital card{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CPatient-prenom{{/tr}}</th>
    <th>{{tr}}CPatient-naissance{{/tr}}</th>
    <th>{{tr}}CPatient-_vitale_nir_certifie{{/tr}}</th>
    <th>{{tr}}CPatient-INSC{{/tr}}</th>
  </tr>
  {{foreach from=$list_person item=_person}}
    <tr>
      <td>{{$_person->prenom}}</td>
      <td>{{$_person->date}}</td>
      <td>{{$_person->nirCertifie}}</td>
      <td>{{$_person->insc}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td>{{tr}}No result{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
