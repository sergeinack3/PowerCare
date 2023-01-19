{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=categorie_selected value=false}}

<script>
  Main.add(function () {
    {{if $categorie_selected}}
      $('counter_geste').innerHTML = '{{$gestes|@count}}';
    {{/if}}
  });
</script>

<ul id="gestes_geste">
  {{if $categorie_selected}}
    {{foreach from=$gestes item=_geste}}
        {{assign var=counter_precision value=$_geste->_ref_precisions|@count}}
      <li id="geste_{{$_geste->_id}}">
        <div class="gestes-container">
          <input class="input_element" type="checkbox" name="geste[{{$_geste->_id}}]" value="{{$_geste->_id}}"
                 onchange="GestePerop.bindElementGeste(this);"/>
          <div style="display: block;" {{if $counter_precision}}onclick="GestePerop.showMenuPrecisions(this, '{{$_geste->_id}}', null, 1);"{{/if}}>
            {{if $counter_precision}}
              <span class="fold">
                <i class="far fa-caret-square-right fa-lg"></i>
              </span>
            {{/if}}

            <span id="geste_element_{{$_geste->_id}}" title="{{$_geste->description|smarty:nodefaults}}">
              {{$_geste->_view}}
            </span>
          </div>
        </div>
      </li>
    {{foreachelse}}
      <li>
        <div class="gestes-container" onclick="">
        <span class="empty">
          {{tr}}CGestePerop.none{{/tr}}
        </span>
      </li>
    {{/foreach}}
  {{else}}
    <li>
      <div class="gestes-container" onclick="">
        <span class="empty">
          {{tr}}CAnesthPeropCategorie-No category of selected{{/tr}}
        </span>
      </div>
    </li>
  {{/if}}
</ul>
