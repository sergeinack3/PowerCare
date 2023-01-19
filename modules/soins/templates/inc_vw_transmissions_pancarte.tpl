{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
refreshtransmissions = function(){
  var oForm = document.filter_trans;
  viewTransmissions($V(document.selService.service_id), $V(oForm.user_id), $V(oForm._degre), $V(oForm.observations), $V(oForm.transmissions), true);
}

tri_transmissions = function(order_col, order_way){
  var oForm = document.filter_trans;
  viewTransmissions($V(document.selService.service_id), $V(oForm.user_id), $V(oForm._degre), 
                    $V(oForm.observations), $V(oForm.transmissions), true, order_col, order_way);
}

</script>

<table class="form">
  <tr>
    <th class="title">
      <form name="filter_trans" method="get" action="?">
        <span style="float: right">
          <input type="checkbox" name="observations" onclick="refreshtransmissions();" checked="checked" /> Observations         
          <input type="checkbox" name="transmissions" onclick="refreshtransmissions();" checked="checked" /> Transmissions
          <select name="_degre" onchange="refreshtransmissions();">
            <option value="">Toutes</option>
            <option value="urg_normal">Urgentes + normales</option>
            <option value="urg">Urgentes</option>
          </select>
          <select name="user_id" onchange="refreshtransmissions();">
            <option value="">&mdash; Tous les utilisateurs</option>
            {{foreach from=$users item=_user}}
            <option class="mediuser" style="border-color: #{{$_user->_ref_function->color}};" value="{{$_user->_id}}" {{if $_user->_id == $filter_obs->user_id}}selected{{/if}}>{{$_user->_view}}</option>
            {{/foreach}}
          </select>
        </span>
      </form>
      {{if $real_time}}
        Transmission des affectations en cours
      {{else}}
        Dernieres transmissions (du {{$date_min|date_format:$conf.datetime}} au {{$date_max|date_format:$conf.datetime}})
      {{/if}}
    </th>
  </tr>
</table>
<div id="_transmissions">
  {{mb_include module=prescription template=inc_vw_transmissions}}
</div>