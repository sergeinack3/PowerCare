{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$_astreinte->libelle}}{{$_astreinte->type}}{{else}}{{$_astreinte->libelle}}{{/if}}<br/>
{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_astreinte->_ref_user}}<br/>
<span><i class="me-icon phone me-primary"></i>{{mb_value object=$_astreinte field=phone_astreinte}}</span><br/>
<a href="#astreinte_{{$_astreinte->_id}}" onclick="PlageAstreinte.modal('{{$_astreinte->_id}}')">{{tr}}Edit{{/tr}}</a>
{{if ($mode == "day") || ($mode=="week")}}
{{if $_astreinte->start|date_format:"%H:%M" != "00:00"}}<span class="startTime incline">{{$_astreinte->start|date_format:$conf.time}}</span>{{else}}<span class="startTime"><</span>{{/if}}
{{if $_astreinte->end|date_format:"%H:%M" != "23:59"}}<span class="endTime incline">{{$_astreinte->end|date_format:$conf.time}}</span>{{else}}<span class="endTime">></span>{{/if}}
{{/if}}
