{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="8">
      Délai de codage des interventions
    </th>
  </tr>
  <tr>
    <th style="width: 200px;"></th>
    <th title="Codages effectués le jour de l'intervention">J</th>
    <th title="Codages effectués le lendemain de l'intervention">J+1</th>
    <th title="Codages effectués 2 jours après l'intervention">J+2</th>
    <th title="Codages effectués 3 jours après l'intervention">J+3</th>
    <th title="Codages effectués 4 jours après l'intervention">J+4</th>
    <th title="Codages effectués 5 jours ou plus après l'intervention">J+5+</th>
    <th title="Nombre total d'intervention codées">Total</th>
  </tr>
  {{foreach from=$results.functions item=data}}
    <tr>
      <th style="text-align: left;">
        {{mb_include module=mediusers template=inc_vw_function function=$data.function}}
      </th>
      {{foreach from=0|range:5 item=i}}
        {{assign var=name value="j$i"}}
        <td style="text-align: right; width: 35px; font-weight: bold;">
          {{$data.$name}}%
        </td>
      {{/foreach}}
      <td style="text-align: right; width: 35px; font-weight: bold;">
        {{$data.total}}
      </td>
    </tr>
    {{foreach from=$data.users item=_data}}
      <tr>
        <th style="text-align: left; padding-left: 10px;">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_data.user}}
        </th>
        {{foreach from=0|range:5 item=i}}
          {{assign var=name value="j$i"}}
          <td style="text-align: right;">
            {{$_data.$name}}%
          </td>
        {{/foreach}}
        <td style="text-align: right;">
          {{$_data.total}}
        </td>
      </tr>
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="8">Aucune donnée pour les critères sélectionnés</td>
    </tr>
  {{/foreach}}

  {{if $results.total}}
    <tr>
      <th style="text-align: left;">
        Total
      </th>
      {{foreach from=0|range:5 item=i}}
        {{assign var=name value="j$i"}}
        <td style="text-align: right; font-weight: bold;">
          {{$results.$name}}%
        </td>
      {{/foreach}}
      <td style="text-align: right; font-weight: bold;">
        {{$results.total}}
      </td>
    </tr>
  {{/if}}
</table>