{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      <form name="provenance" action="?" method="get">
        <input type="hidden" name="m" value="stats" />
        <input type="hidden" name="tab" value="vw_patients_provenance" />
        Répartition des
        <select name="type" onchange="this.form.submit()">
          <option value="traitant" {{if $type == "traitant"}}selected="selected"{{/if}}>
            médecins traitants
          </option>
          <option value="adresse" {{if $type == "adresse"}}selected="selected"{{/if}}>
            médecins adressants
          </option>
          <option value="domicile" {{if $type == "domicile"}}selected="selected"{{/if}}>
            domiciles
          </option>
        </select>
        des patients hospitalisés en
        <select name="year" onchange="this.form.submit()">
          {{foreach from=$years item=_year}}
            <option value="{{$_year}}" {{if $_year == $year}}selected="selected"{{/if}}>
              {{$_year}}
            </option>
          {{/foreach}}
        </select>
      </form>
    </th>
  </tr>
  <tr>
    {{if $type != "domicile"}}
      <th>Correspondant</th>
      <th>Adresse</th>
    {{/if}}
    <th>Code Postal</th>
    <th>Ville</th>
    <th>Nombre d'hospitalisations</th>
  </tr>
  {{foreach from=$listResult item=_result}}
    <tr>
      {{if $type != "domicile"}}
        {{if $_result.nom}}
          <td>{{$_result.nom}} {{$_result.prenom}}</td>
        {{else}}
          <td class="empty">Correspondant Inconnu</td>
        {{/if}}
        <td>
          {{if $_result.adresse}}
            {{$_result.adresse}}
          {{else}}
            <em>Inconnu</em>
          {{/if}}
        </td>
      {{/if}}
      <td>
        {{if $_result.cp}}
          {{$_result.cp}}
        {{else}}
          <em>Inconnu</em>
        {{/if}}
      </td>
      <td>
        {{if $_result.ville}}
          {{$_result.ville}}
        {{else}}
          <em>Inconnu</em>
        {{/if}}
      </td>
      <td>{{$_result.total}}</td>
    </tr>
  {{/foreach}}
</table>