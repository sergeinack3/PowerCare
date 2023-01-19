{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table>
  <tr>
    <th colspan="2" rowspan="2"></th>
    <th colspan="{{$fiche->_specs.vraissemblance->_list|@count}}">
      {{mb_label object=$fiche field=vraissemblance}}
    </th>
  </tr>
  <tr>
    {{foreach from=$fiche->_specs.vraissemblance->_list item=vraissemblance}}
      <th>{{$vraissemblance}}</th>
    {{/foreach}}
  </tr>
  {{assign var=matrice value='Ox\Mediboard\Qualite\CFicheEi'|static:"criticite_matrice"}}
  {{assign var=colors value=","|explode:"none,optimum,min,critical"}}
  {{foreach from=$fiche->_specs.gravite->_list item=gravite name=gravite}}
    <tr>
      {{if $smarty.foreach.gravite.first}}
        <th rowspan="{{$fiche->_specs.gravite->_list|@count}}" style="vertical-align: middle;" class="narrow">
          {{mb_label object=$fiche field=gravite}}
        </th>
      {{/if}}
      <th>{{$gravite}}</th>
      {{foreach from=$fiche->_specs.vraissemblance->_list item=vraissemblance}}
        {{assign var=criticite value=$matrice.$gravite.$vraissemblance}}
        <td style="text-align: center;" class="{{$colors.$criticite}}">{{$criticite}}</td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>