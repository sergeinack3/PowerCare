{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=geste_selected value=false}}

<script>
  Main.add(function () {
    {{if $geste_selected}}
      $('counter_precision').innerHTML = '{{$precisions|@count}}';
    {{/if}}
  });
</script>

<ul id="precisions_geste">
  {{if $geste_selected}}
    {{foreach from=$precisions item=_precision}}
      {{assign var=geste value=$_precision->_ref_geste_perop}}
      {{assign var=counter_valeurs value=$_precision->_ref_valeurs|@count}}
      <li id="precision_{{$_precision->_id}}">
        <div class="precisions-container">
          <input class="input_element" type="checkbox" name="precision[{{$_precision->_id}}]" value="{{$_precision->_id}}"
                 onchange="GestePerop.bindElementPrecision(this, '{{$geste->_id}}', '{{$geste->_view|smarty:nodefaults|JSAttribute}}');"/>
          <div style="display: block;" {{if $counter_valeurs}}onclick="GestePerop.showMenuValeurs(this, '{{$_precision->_id}}', null, 1);"{{/if}}>
            {{if $counter_valeurs}}
              <span class="fold">
                <i class="far fa-caret-square-right fa-lg"></i>
              </span>
            {{/if}}
            <span id="precision_element_{{$_precision->_id}}" title="{{$_precision->description|smarty:nodefaults}}">
              {{$_precision->libelle}}
            </span>
          </div>
        </div>
      </li>
    {{foreachelse}}
      <li>
        <div class="precisions-container" onclick="">
        <span class="empty">
          {{tr}}CGestePeropPrecision.none{{/tr}}
        </span>
      </li>
    {{/foreach}}
  {{else}}
    <li>
      <div class="precisions-container" onclick="">
        <span class="empty">
          {{tr}}CGestePerop-No gesture of selected{{/tr}}
        </span>
      </div>
    </li>
  {{/if}}
</ul>
