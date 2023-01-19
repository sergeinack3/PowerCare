{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$listPrat item=_prat}}
  <div style="display: inline-block" class="me-margin-right-8">
    <label>
      <input type="checkbox" name="prats_selected[{{$_prat->_id}}]" value="{{$_prat->_id}}" class="prats"
             {{if $prats_selected && in_array($_prat->_id, $prats_selected)}}checked{{/if}}>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}
    </label>
  </div>
{{/foreach}}
