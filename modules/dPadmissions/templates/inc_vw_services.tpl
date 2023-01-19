{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
if(!self.changeServiceId) {
  changeServiceId = function(oForm) {return false;}
}
</script>

{{if $services|@count}}
	<select name="service_sortie_id" onchange="changeServiceId(this.form);">
		<option value="">&mdash; Serv. de mutation</option>
		{{foreach from=$services item="_service"}}
		<option value="{{$_service->_id}}" {{if $_service->_id == $service}}selected="selected"{{/if}}>
		  {{$_service}}
		</option>
		{{/foreach}}
	</select>
{{/if}}