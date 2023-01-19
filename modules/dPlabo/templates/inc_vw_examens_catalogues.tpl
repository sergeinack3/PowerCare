{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $catalogue->_id || $search}}
<table class="tbl">
  <tr>
    <th class="title" colspan="6">
      <div style="float: left">
        <form name="frmRecherche" method="post">
          <input type="text" name="search" />
        </form>
          <button class="search notext" onclick="search()">Recherche</button>
      </div>
      {{mb_include module=system template=inc_object_history object=$catalogue}}
      {{if $search}}
        {{$listExams|@count}}
        {{if $listExams|@count != $countExams}}/{{$countExams}}{{/if}} 
        Résultats pour la recherche ({{$recherche}})
      {{else}}
        {{$catalogue->_view}}
      {{/if}}
    </th>
  </tr>

  <tr>
    <th>Analyse</th>
    <th>Unité</th>
    <th>Références</th>
  </tr>

  {{if !$search}}
    {{assign var="listExams" value=$catalogue->_ref_examens_labo}}
  {{else}}
    {{assign var="listExams" value=$listExams}}
  {{/if}}
  
  {{foreach from=$listExams item="curr_examen"}}
  <tr>
    <td>
      <div class="draggable" id="examenCat-{{$curr_examen->_id}}">
      <script type="text/javascript">
        new Draggable('examenCat-{{$curr_examen->_id}}', oDragOptions);
      </script>
      {{$curr_examen->_view}}
      </div>
      <button type="button" class="search notext" onclick="ObjectTooltip.createEx(this, '{{$curr_examen->_guid}}')">
        view
      </button>
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