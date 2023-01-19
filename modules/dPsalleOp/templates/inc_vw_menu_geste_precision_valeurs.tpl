{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=precision_selected value=false}}

<script>
  Main.add(function () {
    {{if $precision_selected}}
      $('counter_valeur').innerHTML = '{{$valeurs|@count}}';
    {{/if}}
  });
</script>

<ul id="valeurs_geste">
  {{if $precision_selected}}
    {{foreach from=$valeurs item=_valeur}}
      {{assign var=precision value=$_valeur->_ref_precision}}
      {{assign var=geste     value=$precision->_ref_geste_perop}}

      <li id="category-{{$_valeur->_id}}">
        <div class="valeurs-container">
          <input class="input_element" type="checkbox" name="valeur[{{$_valeur->_id}}]" value="{{$_valeur->_id}}"
                 onchange="GestePerop.bindElementValeur(this, '{{$geste->_id}}', '{{$geste->_view|smarty:nodefaults|JSAttribute}}', '{{$precision->_id}}', '{{$precision->_view|smarty:nodefaults|JSAttribute}}');"/>
          <span id="valeur_{{$_valeur->_id}}">
            {{$_valeur->_view}}
          </span>
        </div>
      </li>
    {{foreachelse}}
      <li>
        <div class="valeurs-container" onclick="">
        <span class="empty">
          {{tr}}CPrecisionValeur.none{{/tr}}
        </span>
      </li>
    {{/foreach}}
  {{else}}
    <li>
      <div class="valeurs-container" onclick="">
        <span class="empty">
          {{tr}}CGestePeropPrecision-No precision of selected{{/tr}}
        </span>
      </div>
    </li>
  {{/if}}
</ul>
