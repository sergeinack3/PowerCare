{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl ssr-technicien">
    
  {{assign var=conge value=$_technicien->_ref_conge_date}}
  <tr {{if $conge->_id}} class="ssr-kine-conges" {{/if}}>
    <th class="text" id="technicien-{{$technicien_id}}">
      {{if !$can->edit}}
        {{assign var=readonly value=1}}
      {{/if}}
      <script>
        Repartition.registerTechnicien('{{$technicien_id}}',{{$readonly}})
      </script>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_technicien->_fwd.kine_id}}
      <small class="count">(-)</small>
     </th>
  </tr>
  {{if $conge->_id}} 
  <tr class="ssr-kine-conges">
    <td>
      <strong onmouseover="ObjectTooltip.createEx(this, '{{$conge->_guid}}')">
        {{$conge}}
      </strong>
    </td>
  </tr>
  {{/if}}
  
  <tbody id="sejours-technicien-{{$technicien_id}}">

  </tbody>

</table>
