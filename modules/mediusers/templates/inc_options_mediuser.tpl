{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected value=null}}
{{mb_default var=disabled value=null}}
{{mb_default var=show_type value=0}}

{{foreach from=$list item=_mediuser}}
  <option class="mediuser" 
          style="border-color: #{{$_mediuser->_color}};"
          value="{{$_mediuser->_id}}"
          data-activite="{{$_mediuser->activite}}"
          {{if $_mediuser->_ref_uf_medicale}}
            data-uf_medicale_id="{{$_mediuser->_ref_uf_medicale->_id}}"
          {{/if}}
          data-uf-medicale-mandatory="{{$_mediuser->_uf_medicale_mandatory}}"
          {{if $selected == $_mediuser->_id}}selected{{/if}}
          {{if $disabled == $_mediuser->_id}}disabled{{/if}}>
    {{$_mediuser}}{{if $show_type}} &mdash; {{$_mediuser->_user_type_view}}{{/if}}
    {{if $_mediuser->adeli && ($_mediuser->isSecondary() || $_mediuser->_ref_secondary_users|@count)}}
      &mdash; {{mb_value object=$_mediuser field=adeli}}
    {{/if}}
  </option>
{{foreachelse}}
  {{if @$showEmptyList}}
  <option disabled>{{tr}}CMediuser.none{{/tr}}</option>
  {{/if}}
{{/foreach}}
