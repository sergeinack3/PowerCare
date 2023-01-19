{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<option value="">&mdash; {{tr}}Choose{{/tr}}</option>
{{foreach from=$components item=componentsByOwner key=owner}}
<optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
  {{foreach from=$componentsByOwner item=_component}}
  <option value="{{$_component->_id}}">{{$_component->nom}}</option>
  {{foreachelse}}
  <option value="" disabled>{{tr}}None{{/tr}}</option>
  {{/foreach}}
</optgroup>
{{/foreach}}