{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$prescriptionItem->_id}}
  <table class="form">
    <tr>
      <th class="title">Veuillez sélectionner une analyse</th>
    </tr>
  </table>
  {{mb_return}}
{{/if}}

{{assign var="examen" value=$prescriptionItem->_ref_examen_labo}}
{{assign var="patient" value=$prescriptionItem->_ref_prescription_labo->_ref_patient}}

{{if $examen->type == "num" || $examen->type == "float"}}
  <script>
  Main.add(function(){
    var data = {{$series|@json}};
    var options = {{$options|@json}};
    var ph = jQuery("#resultGraph");
  
    var plot = jQuery.plot(ph, data, options);
  
    ph[0].insert(DOM.div({
      className: 'axisLabel yaxisLabel', style: 'font-size: 10px; text-indent: -50px;'
    }, 'Valeurs en {{$examen->unite}}'));
  });
  </script>
  
  <div style="text-align: center; margin: 5px;">
    <strong>Résultats pour {{$examen}}</strong>
    <br />
    {{$patient}}
    <br />
    <div id="resultGraph" style="text-align: center; width: 360px; height: 250px; display: inline-block;"></div>
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="3" class="title">Résultats '{{$examen}}'</th>
  </tr>
  <tr>
    <th>Valeur</th>
    <th>Résultat au</th>
    <th>Prescrit le</th>
  </tr>

  {{foreach from=$siblingItems item="_item"}}
  <tbody class="hoverable">
    <tr {{if $_item->_id == $prescriptionItem->_id}}class="selected"{{/if}}>
      {{if $_item->date}}
      <td {{if $_item->commentaire}}rowspan="2"{{/if}}>
        {{assign var=msgClass value=""}}
        {{if $examen->type == "num" || $examen->type == "float"}}
          {{mb_ternary var=msgClass test=$_item->_hors_limite value=warning other=message}}
        {{/if}}
        
        <div class="{{$msgClass}}">
          {{if $examen->type == "bool"}}
          {{tr}}bool.{{$_item->resultat}}{{/tr}}
          {{else}}
          {{$_item->resultat}}
          {{/if}}
          {{mb_value object=$examen field=unite}}
        </div>
      </td>
      <td>{{mb_value object=$_item field=date}}</td>
      {{else}}
      <td colspan="2" class="empty" style="text-align: center;">
        Aucun résultat
      </td>
      {{/if}}
      <td>{{mb_value object=$_item->_ref_prescription_labo field=date format=$conf.date}}</td>
    </tr>
    {{if $_item->commentaire}}
    <tr {{if $_item->_id == $prescriptionItem->_id}}class="selected"{{/if}}>
      <td class="text" colspan="2">
        {{$_item->commentaire|nl2br}}
      </td>
    </tr>
    {{/if}}
  </tbody>
  {{/foreach}}
</table>
