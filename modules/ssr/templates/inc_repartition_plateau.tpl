{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="{{$plateau->_guid}}" class="main" style="border-spacing: 4px; border-collapse: separate; width: auto;">
  {{if !$conf.ssr.repartition.show_tabs}}
  <tr>
    <th class="title" colspan="{{$plateau->_ref_techniciens|@count}}">
      {{$plateau}}
    </th>
  </tr>
  {{/if}}
  <tr>
    {{foreach from=$plateau->_ref_techniciens item=_technicien}}
      <td style="width: 150px;">
      {{mb_include module=ssr template=inc_repartition_technicien technicien_id=$_technicien->_id}}
      </td>
    {{foreachelse}}
      <td style="width: 150px;" class="text empty">{{tr}}CPlateauTechnique-back-techniciens.empty{{/tr}}</td>
    {{/foreach}}
  </tr>
</table>