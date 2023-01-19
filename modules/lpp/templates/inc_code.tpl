{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    selectPricing = function (date) {
        new Url('lpp', 'viewCodePricing')
            .addParam('code', '{{$code->code}}')
            .addParam('date', date)
            .requestUpdate('selected_pricing');
    };
</script>

<div id="general_infos">
    <fieldset>
        <legend>{{tr}}CLPPCode-title-general_infos{{/tr}}</legend>
        <table class="form" style="">
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=code}}</th>
                <td>{{mb_value object=$code field=code}}</td>
            </tr>
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=name}}</th>
                <td class="text compact">{{mb_value object=$code field=name}}</td>
            </tr>
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=_parent_id}}</th>
                <td>{{$code->_parent->rank}} - {{$code->_parent->name}}</td>
            </tr>
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=prestation_type}}</th>
                <td>{{mb_value object=$code field=prestation_type}}</td>
            </tr>
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=end_date}}</th>
                <td>
                    {{if $code->end_date}}
                        {{mb_value object=$code field=end_date}}
                    {{else}}
                        <span class="empty">{{tr}}None{{/tr}}</span>
                    {{/if}}
                </td>
            </tr>
            <tr>
                <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPCode field=max_age}}</th>
                <td>
                    {{if $code->max_age}}
                        {{mb_value object=$code field=max_age}} {{tr}}years{{/tr}}
                    {{else}}
                        <span class="empty">{{tr}}None{{/tr}}</span>
                    {{/if}}
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div id="pricings">
    <fieldset>
        <legend>{{tr}}CLPPCode-title-pricings{{/tr}}</legend>
        <table class="form">
            <tr>
                <th style="font-weight: bold; width: 120px;">{{tr}}CLPPDatedPricing-title-act_and_jo_dates{{/tr}}</th>
                <td>
                    <select name="selectPricing" onchange="selectPricing($V(this));">
                        {{foreach from=$code->_pricings item=_pricing}}
                            <option value="{{$_pricing->begin_date}}"
                                    {{if $_pricing->begin_date == $code->_last_pricing->begin_date}}selected="selected"{{/if}}>
                                {{mb_value object=$_pricing field=act_date}}
                                &mdash; {{mb_value object=$_pricing field=jo_date}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
            <tbody id="selected_pricing">
            {{mb_include module=lpp template=inc_pricing pricing=$code->_last_pricing}}
            </tbody>
        </table>
    </fieldset>
</div>
<div>
    <table class="layout" style="width: 100%;">
        <tr>
            <td id="compatibilities" class="halfPane" style="vertical-align: top; text-align: left;">
                <fieldset style="width: 95%; display: inline;">
                    <legend>{{tr}}CLPPCode-title-compatibilities{{/tr}} ({{$code->_compatibilities|@count}})</legend>
                    <ul style="list-style: none">
                        {{foreach from=$code->_compatibilities item=_code}}
                            <li><strong>{{$_code->code}}</strong><span class="text compact">{{$_code->name}}</span></li>
                            {{foreachelse}}
                            <li class="empty">{{tr}}CLPPCode-_compatibilities.none{{/tr}}</li>
                        {{/foreach}}
                    </ul>
                </fieldset>
            </td>
            <td id="incompatibilities" class="halfPane" style="vertical-align: top; text-align: right;">
                <fieldset style="width: 95%; display: inline;">
                    <legend>{{tr}}CLPPCode-title-incompatibilities{{/tr}} ({{$code->_incompatibilities|@count}})
                    </legend>
                    <ul style="list-style: none">
                        {{foreach from=$code->_incompatibilities item=_code}}
                            <li style="text-align: left;"><strong>{{$_code->code}}</strong><span class="text compact"
                                                                                                 style="padding-left: 5px;">{{$_code->name}}</span>
                            </li>
                            {{foreachelse}}
                            <li class="empty"
                                style="text-align: left;">{{tr}}CLPPCode-_incompatibilities.none{{/tr}}</li>
                        {{/foreach}}
                    </ul>
                </fieldset>
            </td>
        </tr>
    </table>


</div>
