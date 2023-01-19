{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

Object.extend(Droppables, {
  addPack: function(pack_id) {
    var oDragOptions = {
      onDrop: function(element) {
        Pack.dropExamenCat(element.id, pack_id)
      }, 
      hoverclass:'selected'
    }
    
    this.add('drop-listpacks-' + pack_id,  oDragOptions);
  }
} );
  
</script>

{{if $pack->_id}}
<table class="tbl" id="drop-listpacks-{{$pack->_id}}">
  <tr>
    <th class="title" colspan="6">
      {{mb_include module=system template=inc_object_idsante400 object=$pack}}
      {{mb_include module=system template=inc_object_history object=$pack}}
      {{$pack->_view}}
      <script type="text/javascript">
        Droppables.addPack({{$pack->_id}});
      </script>
    </th>
  </tr>
  
  <tr>
    <th>Analyse</th>
    <th>Unité</th>
    <th>Références</th>
  </tr>
  
  <!-- Liste des items d'un pack -->
  {{foreach from=$pack->_ref_items_examen_labo item="curr_item"}}
  {{assign var="curr_examen" value=$curr_item->_ref_examen_labo}}
  <tr>
    <td>
      <div class="draggable" id="examenPack-{{$curr_examen->_id}}-{{$curr_item->_id}}">
      <script type="text/javascript">
        new Draggable('examenPack-{{$curr_examen->_id}}-{{$curr_item->_id}}', oDragOptions);
      </script>
      {{$curr_examen->_view}}
      </div>
      <form name="delPackItem-{{$curr_item->_id}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$curr_item}}
        {{mb_key   object=$curr_item}}
        <input type="hidden" name="pack_examens_labo_id" value="{{$pack->_id}}" />
        <input type="hidden" name="del" value="1" />
        <button type="button" class="trash notext" onclick="Pack.delExamen(this.form)">{{tr}}Delete{{/tr}}</button>
        <button type="button" class="search notext" onclick="ObjectTooltip.createEx(this, '{{$curr_examen->_guid}}')">
          view
        </button>
      </form>
    </td>
    {{if $curr_examen->type == "num" || $curr_examen->type == "float"}}
    <td>{{$curr_examen->unite}}</td>
    <td>{{$curr_examen->min}} &ndash; {{$curr_examen->max}}</td>
    {{else}}
    <td colspan="2">{{mb_value object=$curr_examen field="type"}}</td>
    {{/if}}
  </tr>
  {{/foreach}}

</table>
{{/if}}