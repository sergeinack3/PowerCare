{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$activite_csarr->_ref_modulateurs item=_modulator}}
  <label title="{{$_modulator->_libelle}}">
    <input type="checkbox" class="modulateur" value="{{$_modulator->modulateur}}" onclick="Modulators.toggle(this.value, this.checked)"/>
    {{$_modulator->modulateur}}
  </label>
{{/foreach}}