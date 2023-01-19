{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="me-autocomplete-mediusers"
     style="border-left: 3px solid #{{$match->_ref_function->color}}; padding-left: 2px; margin: -1px;">
  <div style="background-color: #{{$match->_ref_function->color}};"></div>
  {{if $can->admin && $match->isSecondary()}}
    <i class="fa fa-link" style="float: left;"></i>
  {{/if}} <span class="view"
                {{if !$match->isActive()}}style="text-decoration: line-through;"{{/if}}
                data-spec_cpam="{{if $match->spec_cpam_id}}{{$match->spec_cpam_id}}{{/if}}"
                data-create_sejour_consult="{{$match->_ref_function->create_sejour_consult}}"
                data-rpps="{{$match->rpps}}"
    >{{if $show_view || !$f}}{{$match}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}</span>
  <div style="text-align: right; color: #999; font-size: 0.8em;">
    {{if $match->adeli}}
      <span style="float: left;">
        {{mb_value object=$match field=adeli}}
      </span>
    {{/if}}
    {{$match->_ref_function}}
  </div>
</div>
