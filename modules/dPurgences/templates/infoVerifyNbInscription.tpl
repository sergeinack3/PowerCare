{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">{{tr}}CRPU-inscription in patient file{{/tr}}</th>
  </tr>
  <tr>
    <td>
        {{tr}}CRPU-inscription in patient file-desc{{/tr}}
    </td>
  </tr>
  <tr>
    <td class="button">
      <button type="button" onclick="Control.Modal.close(); {{$callback}}()" class="tick">{{tr}}Poursuivre{{/tr}}</button>
      <button type="button" onclick="Urgences.showDossierPrescription('{{$sejour_id}}');" class="cancel">{{tr}}cancel{{/tr}}</button>
    </td>
  </tr>
</table>

