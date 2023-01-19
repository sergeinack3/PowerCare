{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp script=geste_perop ajax=true}}

<fieldset>
  <legend><i class="fas fa-book-open"></i> {{tr}}CGestePerop-Summary of selected items{{/tr}}</legend>
  <form name="bindingGestes" method="post" action="#">
    <div id="flag_codes" style="width: 80%; display: inline-block;">
      <ul id="show_tags_gestes" class="">
        <li id="tag_geste_none" class="empty">{{tr}}CGestePerop-No selected item{{/tr}}</li>
      </ul>
    </div>
  </form>
</fieldset>

<fieldset>
  <legend id="legend-protocole-{{$protocole_geste_perop->_id}}">
    <input type="checkbox" onclick="GestePerop.selectAllLines('{{$protocole_geste_perop->_id}}', this);"
           name="checkbox-protocole-{{$protocole_geste_perop->_id}}" /> {{tr}}CProtocole{{/tr}}: {{$protocole_geste_perop->_view}}
  </legend>

  <table class="main tbl" id="table-protocole-{{$protocole_geste_perop->_id}}">
    <tr>
      <th class="title" colspan="5">{{tr}}CProtocoleGestePerop-List of elements associated with the protocol{{/tr}} ({{$total}})</th>
    </tr>
    <tr>
      <th></th>
      <th>{{mb_title class=CGestePerop field=libelle}}</th>
      <th class="narrow">{{tr}}CGestePeropPrecision|pl{{/tr}}</th>
      <th class="narrow">{{tr}}CPrecisionValeur|pl{{/tr}}</th>
      <th>{{tr}}CGestePerop-_datetime{{/tr}}</th>
    </tr>
    {{foreach from=$protocole_items_by_cat key=category item=_item}}
      <tr>
        <th class="section" colspan="5">{{$category}}</th>
      </tr>
      {{foreach from=$_item item=_context}}
        {{assign var=item_checked value=$_context.checked}}
        {{assign var=item         value=$_context.item}}
        {{foreach from=$_context.gestes item=_geste}}
          {{assign var=precisions value=$_geste->_ref_precisions}}
          <tr>
            <td class="narrow button">
              <input type="checkbox" name="_view_{{$_geste->_id}}" class="geste_perop_item"
                     onchange="GestePerop.selectOneLine('{{$protocole_geste_perop->_id}}', this,'{{$_geste->_id}}', '{{$_geste->libelle|smarty:nodefaults|JSAttribute}}', '{{$item->geste_perop_precision_id}}', '{{$item->precision_valeur_id}}');" value="{{$_geste->_id}}" />
            </td>
            <td class="text">
            <span id="geste_element_{{$_geste->_id}}" onmouseover="ObjectTooltip.createEx(this, '{{$_geste->_guid}}')">
              {{$_geste->_view}}
            </span>
            </td>
            <td>
              {{if $precisions|@count}}
                <select id="precision_{{$_geste->_id}}" class="select_{{$_geste->_id}}" name="precision"
                        onchange="GestePerop.showListValeurs(this, '{{$_geste->_id}}', '{{$_geste->libelle|smarty:nodefaults|JSAttribute}}', null, 0, 1);" style="width: 250px;" disabled>
                  <option value="">&mdash; {{tr}}None|f{{/tr}}</option>
                  {{foreach from=$precisions item=_precision}}
                    <option id="precision_element_{{$_precision->_id}}" value="{{$_precision->_id}}"
                            {{if $_precision->_id == $item->geste_perop_precision_id}}selected{{/if}}>
                      {{$_precision->_view}}
                    </option>
                  {{/foreach}}
                </select>

              {{if $item->geste_perop_precision_id}}
                <script>
                  Main.add(function () {
                    GestePerop.showListValeurs($('precision_{{$_geste->_id}}'), '{{$_geste->_id}}', '{{$_geste->libelle|smarty:nodefaults|JSAttribute}}', '{{$item->_id}}');
                  });
                </script>
              {{/if}}
              {{else}}
                <div class="empty" style="width: 250px;">
                  &mdash; {{tr}}CGestePeropPrecision.none{{/tr}}
                </div>
              {{/if}}
            </td>
            <td id="select_list_valeurs_{{$_geste->_id}}">
              <div class="empty" style="width: 250px;">
                &mdash; {{tr}}CPrecisionValeur.none{{/tr}}
              </div>
            </td>
            <td class="narrow">
              {{if $limit_date_min}}
                <script>
                  Main.add(function () {
                    var form = getForm('selectProtocoleGeste_{{$_geste->_id}}');
                    Calendar.regField(form._datetime, {limit:
                        {
                          start: '{{$limit_date_min}}'
                        }
                    });
                  });
                </script>
              {{/if}}

              <form name="selectProtocoleGeste_{{$_geste->_id}}" method="post" action="?">
                {{mb_field object=$_geste field=_datetime register=true form="selectProtocoleGeste_`$_geste->_id`"}}
              </form>
            </td>
          </tr>

          <script>
            {{if $item_checked}}
              Main.add(function () {
                setTimeout("$$('input[name=\"_view_{{$_geste->_id}}\"]')[0].click()", 1000);
              });
            {{/if}}
          </script>
        {{/foreach}}
      {{/foreach}}
    {{/foreach}}

    {{if !$protocole_items_by_cat|@count}}
      <tr>
        <td class="empty" colspan="5">
          {{tr}}CProtocoleGestePeropItem.none{{/tr}}
        </td>
      </tr>
    {{/if}}
  </table>
</fieldset>

<!-- Formulaire d'ajout des éléments du protocole -->
<form action="?" method="post" name="addProtocoleGestePerop">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_protocole_geste_perop_multi_aed" />
  <input type="hidden" name="_geste_perop_dates" value=""/>
  <input type="hidden" name="_geste_perop_ids" value=""/>
  <input type="hidden" name="operation_id" value="{{$operation_id}}"/>

  <table class="form" style="margin-top: 10px;">
    <tr>
      <td class="button" colspan="3">
        <button type="button" class="singleclick" onclick="GestePerop.checkSelectProtocoleGestes(this.form, '{{$protocole_geste_perop->_id}}', '{{$limit_date_min}}');">
          <i class="fas fa-edit fa-lg"></i> {{tr}}CProtocoleGestePerop-action-Save the application of the protocol{{/tr}}
        </button>
        <button type="button" class="oneclick" onclick="Control.Modal.close();">
          <i class="fas fa-times fa-lg"
             style="color: red;"></i> {{tr}}CProtocoleGestePerop-action-Cancel the application of the protocol{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
