{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez un mode de traitement</option>

    {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:null item=_charge}}
      <option value="{{$_charge->_id}}" {{if $_charge->_id == $value}}selected{{/if}}>{{$_charge}}</option>
    {{/foreach}}
  </select>
{{/if}}