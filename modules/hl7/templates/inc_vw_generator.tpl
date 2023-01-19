{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form action="" method="post" name="generatePatient" onsubmit="return onSubmitFormAjax(this, TestHL7.clear)">
  {{mb_class object=$patient}}
  {{mb_key object=$patient}}

  <table class="main form">
    <tr>
      <th>{{mb_label object=$patient field=nom}}</th>
      <td>
      {{mb_field object=$patient field=nom}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('nom', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>

      <th rowspan="2">{{mb_label object=$patient field=adresse}}</th>
      <td rowspan="2">
        <table class="main layout">
          <tr>
            <td style="padding: 0;">{{mb_field object=$patient field=adresse}}</td>
            <td>
              <button type="button" class="calcul notext" onclick="TestHL7.random('adresse', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=nom_jeune_fille}}</th>
      <td>
      {{mb_field object=$patient field=nom_jeune_fille}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('nom_jeune_fille', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=prenom}}</th>
      <td>
      {{mb_field object=$patient field=prenom}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('prenom', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>

      <th>{{mb_label object=$patient field=pays_insee}}</th>
      <td>
      {{mb_field object=$patient field=pays_insee}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('pays_insee', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=naissance}}</th>
      <td>
        {{mb_field object=$patient field=naissance}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('naissance', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>

      <th>{{mb_label object=$patient field=sexe}}</th>
      <td>
        {{mb_field object=$patient field=sexe}}
        <button type="button" class="calcul notext" onclick="TestHL7.random('sexe', '{{$patient->_class}}')">{{tr}}Random{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <td colspan="4" class="button">
        <button type="button" class="trash" onclick="TestHL7.clear()">{{tr}}Clear{{/tr}}</button>
        <button type="button" class="calcul" onclick="TestHL7.randomAll()">{{tr}}RandomizeAll{{/tr}}</button>
        <button type="submit" class="new">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>