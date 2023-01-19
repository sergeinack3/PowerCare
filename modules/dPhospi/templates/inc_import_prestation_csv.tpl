{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="16">{{$results|@count}} prestations trouvés</th>
  </tr>
  <tr>
    <th>Etat</th>
    <th>{{mb_label class=CPrestationExpert field=nom}}</th>
    <th>{{tr}}mod-dPhospi-prestation-type{{/tr}}</th>
    <th>{{mb_label class=CPrestationExpert field=type_hospi}}</th>
    <th>{{mb_label class=CPrestationExpert field=M}}</th>
    <th>{{mb_label class=CPrestationExpert field=C}}</th>
    <th>{{mb_label class=CPrestationExpert field=O}}</th>
    <th>{{mb_label class=CPrestationExpert field=SSR}}</th>
    <th>{{mb_label class=CItemPrestation field=nom}}</th>
    <th>{{mb_label class=CItemPrestation field=rank}}</th>
    <th>Identifiants externes</th>
  </tr>

  {{foreach from=$results item=_item}}
    <tr>
      {{if array_key_exists('error', $_item) && $_item.error}}
        <td class="text warning compact">
          {{$_item.error}}
        </td>
      {{elseif array_key_exists('found', $_item) && $_item.found && $dryrun}}
        <td class="">
          Essai : retrouvé
        </td>
      {{elseif array_key_exists('found', $_item) && $_item.found}}
        <td class="text ok">
          Retrouvé
        </td>
      {{elseif $dryrun}}
        <td class="">
          Essai
        </td>
      {{else}}
        <td class="text ok">
          OK
        </td>
      {{/if}}

      <td class="text">{{$_item.prestation}}</td>
      <td class="text">{{$_item.type}}</td>
      <td class="text">{{$_item.type_admission}}</td>
      <td class="text">{{$_item.M}}</td>
      <td class="text">{{$_item.C}}</td>
      <td class="text">{{$_item.O}}</td>
      <td class="text">{{$_item.SSR}}</td>
      <td class="text">{{$_item.item}}</td>
      <td class="text">{{$_item.rang}}</td>
      <td class="text">{{$_item.identifiant_externe}}</td>
    </tr>
  {{/foreach}}
</table>