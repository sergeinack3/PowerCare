{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAccidentTravail-title-patient_situation{{/tr}}
    </th>
  </tr>
  <tbody id="AAT_part_employee{{$uid}}">
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_nom}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_nom onchange="AccidentTravail.syncField(this);"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_adresse}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_adresse}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_cp}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_cp}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_ville}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_ville}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_phone}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_phone}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_employeur_email}}
      </th>
      <td>
        {{mb_field object=$at field=patient_employeur_email}}
      </td>
    </tr>
  </tbody>
  <tr>
    <th>
      {{mb_label object=$at field=_patient_adresse_visite}}
    </th>
    <td>
      {{mb_field object=$at field=_patient_adresse_visite typeEnum="checkbox" onchange="AccidentTravail.checkVisitAddress(this);"}}
    </td>
  </tr>
  <tbody id="AAT_visit_address_part{{$uid}}" style="display: none;">
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_escalier}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_escalier}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_etage}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_etage}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_appartement}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_appartement}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_batiment}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_batiment}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_code}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_code}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_adresse}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_adresse}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_cp}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_cp}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_ville}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_ville}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$at field=patient_visite_phone}}
      </th>
      <td>
        {{mb_field object=$at field=patient_visite_phone}}
      </td>
    </tr>
  </tbody>
  <tr>
    <td class="button" colspan="2">
      {{mb_include module=cabinet template=at/inc_navigation actual='patient_situation' previous="at_duration$uid" next="at_sorties$uid"}}
    </td>
  </tr>
</table>
