{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Insc_test_saisie" method="post" onsubmit="return Ccda.submitSaisieInsc(this);">
  <table class="form">
    <tr>
      <th class="title" colspan="4">{{tr}}common-Information of vital card{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CPatient-prenom{{/tr}}</th>
      <td><input name="firstName" type="input" value="{{$firstName}}"></td>
    </tr>
    <tr>
      <th>{{tr}}CPatient-naissance{{/tr}}</th>
      <td><input name="birthDate" type="input" value="{{$birthDate}}"></td>
    </tr>
    <tr>
      <th>{{tr}}CPatient-_vitale_nir_certifie{{/tr}}</th>
      <td><input name="nir" type="input" value="{{$nir}}"></td>
    </tr>
    <tr>
      <th>{{tr}}CPatient-_vitale_nir_certifie_key{{/tr}}</th>
      <td><input name="nirKey" type="input" value="{{$nirKey}}"></td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="submit">{{tr}}Send{{/tr}}</button>
      </td>
    </tr>
    {{if $insc}}
      <tr>
        <th>{{tr}}insc{{/tr}}</th>
        <td><input name="insc" type="input" value="{{$insc}}" readonly="true"></td>
      </tr>
    {{/if}}
  </table>
</form>

