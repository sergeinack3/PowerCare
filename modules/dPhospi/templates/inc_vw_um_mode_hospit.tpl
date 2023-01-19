{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="type_autorisation_mode_hospi">
  <option value="">{{tr}}Choose{{/tr}}</option>
  {{foreach from=$um->_mode_hospitalisation item=_um_mode_hospit}}
    <option value="{{$_um_mode_hospit}}"
            {{if $uf->type_autorisation_mode_hospi == $_um_mode_hospit}}selected{{/if}} >{{$_um_mode_hospit}}</option>
  {{/foreach}}
</select>
