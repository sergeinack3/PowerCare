{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="print">
  <col style="width: 40%" />

  <tr>
    <th class="title" colspan="2">
      <a href="#" onclick="window.print();">{{$medecin->_longview}} &mdash; {{$date|date_format:$conf.date}}</a>
    </th>
  </tr>

  {{if $medecin->disciplines || $medecin->orientations || $medecin->complementaires}}
    <tr>
      <th class="category" colspan="2">Spécialité</th>
    </tr>
    {{if $medecin->disciplines}}
      <tr>
        <th>{{mb_label object=$medecin field=disciplines}}</th>
        <td>{{mb_value object=$medecin field=disciplines}}</td>
      </tr>
    {{/if}}

    {{if $medecin->orientations}}
      <tr>
        <th>{{tr}}CMedecin-orientations-long{{/tr}}</th>
        <td>{{mb_value object=$medecin field=orientations}}</td>
      </tr>
    {{/if}}

    {{if $medecin->complementaires}}
      <tr>
        <th>{{tr}}CMedecin-complementaires-desc{{/tr}}</th>
        <td>{{mb_value object=$medecin field=complementaires}}</td>
      </tr>
    {{/if}}
  {{/if}}

  {{if $medecin->adresse || $medecin->cp || $medecin->ville || $medecin->tel || $medecin->fax || $medecin->portable
  || $medecin->email || $medecin->email_apicrypt}}
    <tr>
      <th class="category" colspan="2">Coordonnées</th>
    </tr>
    {{if $medecin->adresse || $medecin->cp || $medecin->ville}}
      <tr>
        <th>{{mb_label object=$medecin field=adresse}}</th>
        <td>
          {{$medecin->adresse|nl2br}} <br />
          {{$medecin->cp}} {{$medecin->ville}}
        </td>
      </tr>
    {{/if}}

    {{if $medecin->tel}}
      <tr>
        <th>{{mb_label object=$medecin field=tel}}</th>
        <td>{{mb_value object=$medecin field=tel}}</td>
      </tr>
    {{/if}}

    {{if $medecin->fax}}
      <tr>
        <th>{{mb_label object=$medecin field=fax}}</th>
        <td>{{mb_value object=$medecin field=fax}}</td>
      </tr>
    {{/if}}

    {{if $medecin->portable}}
      <tr>
        <th>{{mb_label object=$medecin field=portable}}</th>
        <td>{{mb_value object=$medecin field=portable}}</td>
      </tr>
    {{/if}}

    {{if $medecin->email}}
      <tr>
        <th>{{mb_label object=$medecin field=email}}</th>
        <td>{{mb_value object=$medecin field=email}}</td>
      </tr>
    {{/if}}

    {{if $medecin->email_apicrypt}}
      <tr>
        <th>{{mb_label object=$medecin field=email_apicrypt}}</th>
        <td>{{mb_value object=$medecin field=email_apicrypt}}</td>
      </tr>
    {{/if}}

    {{if $medecin->mssante_address}}
      <tr>
        <th>{{mb_label object=$medecin field=mssante_address}}</th>
        <td>{{mb_value object=$medecin field=mssante_address}}</td>
      </tr>
    {{/if}}
  {{/if}}

  {{if $medecin->adeli || $medecin->rpps}}
    <tr>
      <th class="category" colspan="2">Identifiants professionnels</th>
    </tr>
    {{if $medecin->adeli}}
      <tr>
        <th>{{mb_label object=$medecin field=adeli}}</th>
        <td>{{mb_value object=$medecin field=adeli}}</td>
      </tr>
    {{/if}}

    {{if $medecin->rpps}}
      <tr>
        <th>{{mb_label object=$medecin field=rpps}}</th>
        <td>{{mb_value object=$medecin field=rpps}}</td>
      </tr>
    {{/if}}
  {{/if}}
</table>