{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math assign=spacing equation="(x +1) * 5" x=$criterion->spacing}}

<li class="criterion"{{if $criterion->ponderation_id < 7}} data-criterion_id="{{$criterion->criterion_id}}" data-ponderation="{{$criterion->ponderation_id}}"
    data-level="{{$criterion->spacing_id}}" data-parent="{{$criterion->parent_id}}" style="padding-left: {{$spacing}}px;" onclick="DRC.toggleCriterion('{{$criterion->criterion_id}}');"{{/if}}>
  {{$criterion->ponderation}} <span class="criterion_title">{{$criterion->title|smarty:nodefaults|ucfirst}}</span>
</li>