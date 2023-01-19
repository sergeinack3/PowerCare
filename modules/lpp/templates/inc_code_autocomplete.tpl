{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$codes item=_code}}
        <li data-code="{{$_code->code}}" data-code_prestation="{{$_code->_last_pricing->prestation_code}}"
            data-type_prestation="{{$_code->prestation_type}}" data-montant_base="{{$_code->_last_pricing->price}}"
            data-dep="{{$_code->_last_pricing->dep}}"
            data-qualif_depense="{{'|'|implode:$_code->_unauthorized_expense_qualifying}}">
      <span class="compact" style="float: right;">
        ({{mb_value object=$_code->_last_pricing field=price}})
        {{mb_value object=$_code field=prestation_type}}
      </span>
            <strong>{{mb_value object=$_code field=code}}</strong>
            <div class="text compact"
                 style="width: 100%; overflow: hidden; text-overflow: ellipsis;">{{$_code->name}}</div>
        </li>
        {{foreachelse}}
        <li class="empty" style="text-align: center;">{{tr}}CLPPCode.none{{/tr}}</li>
    {{/foreach}}
</ul>
