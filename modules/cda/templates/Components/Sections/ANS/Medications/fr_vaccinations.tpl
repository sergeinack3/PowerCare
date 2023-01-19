{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{strip}}
<list>
  <item>
    <content styleCode="BoldItalics">
      Vaccination contre
      {{foreach from=$vaccinations item=vaccination name=for_vac}}
        {{$vaccination->type}}
        {{if !$smarty.foreach.for_vac.first}}
          ,
        {{/if}}
      {{/foreach}}

    </content>
  </item>
</list>

<table border="0">
  <thead>
  <tr>
    <th>Date</th>
    <th>Vaccin</th>
    <th>Lot n°</th>
    <th>Rang</th>
    <th>Voie</th>
    <th>Réaction observée</th>
    <th>Région d'administration</th>
    <th>Référence prescription</th>
    <th>Dose à administrer</th>
    <th>Commentaire</th>
    <th>Vaccinateur</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>{{$injection_date}}</td>
    <td>
      <content ID="vac-01">{{$injection->speciality}}</content>
    </td>
    <td>{{$injection->batch}}</td>
    <td>1</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>1</td>
    {{if $injection->remarques}}
      <td ID="{{$injection->_guid}}-comment">{{$injection->remarques}}</td>
    {{else}}
      <td>-</td>
    {{/if}}
    <td>{{if $injection->practitioner_name}}{{$injection->practitioner_name}}{{else}}-{{/if}}</td>
  </tr>
  </tbody>
</table>
{{/strip}}
