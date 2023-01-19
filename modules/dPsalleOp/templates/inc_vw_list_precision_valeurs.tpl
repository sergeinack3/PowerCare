{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="7">
      <button type="button" style="float: left;" onclick="GestePerop.editPrecisionValeur(0, '{{$precision->_id}}');">
        <i class="fas fa-plus"></i> {{tr}}CPrecisionValeur-action-Add a value{{/tr}}
      </button>

      {{tr}}CPrecisionValeur-List of value|pl{{/tr}} ({{$precision_valeurs|@count}})
    </th>
  </tr>
  <tr>
    <th class="text">{{mb_label class=CPrecisionValeur field=valeur}}</th>
    <th class="narrow">{{mb_label class=CPrecisionValeur field=actif}}</th>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
  </tr>
  {{foreach from=$precision_valeurs item=_precision_valeur}}
  {{assign var=group value=$_precision_valeur->_ref_group}}
  {{assign var=anesth_perops value=$_precision_valeur->_ref_anesth_perops}}
    <tr class="{{if !$_precision_valeur->actif}}hatching{{/if}}">
      <td class="text">
         {{mb_ditto name=description value=$_precision_valeur->valeur center=true}}
      </td>
      <td class="button">
          {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_precision_valeur
          onComplete="GestePerop.loadlistPrecisionValeurs(`$_precision_valeur->geste_perop_precision_id`)"}}
      </td>
      <td class="button narrow">
        <button type="button"
                onclick="GestePerop.editPrecisionValeur('{{$_precision_valeur->_id}}', '{{$_precision_valeur->geste_perop_precision_id}}');"
                {{if !$_precision_valeur->_edit}}disabled{{/if}}
                title="{{tr}}{{if $_precision_valeur->_edit}}Modify{{else}}CPrecisionValeur-msg-The value is associated with a perop gesture and can not be changed{{/if}}{{/tr}}">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CPrecisionValeur.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
