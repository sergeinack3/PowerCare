{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
	function changePage(page) {
  $V(getForm('recherche').page,page);
}
	
</script>
<form action="?" name="recherche" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
	<input type="hidden" name="plage_id" value="" />
	<input type="hidden" name="page" value="{{$page}}" onchange="this.form.submit()"/>

  <table class="form">
    <tr>
      <th  colspan="4" class="title">{{tr}}CPlageConge-user-search{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="user_id"}}</th>
      <td colspan="3">
      	<select name="user_id">
      		<option value="">{{tr}}CMediusers.all{{/tr}}</option>
        {{mb_include module=mediusers template=inc_options_mediuser list=$mediusers selected=$filter->user_id}}
				</select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="date_debut"}}</th>
      <td>{{mb_field object=$filter field="date_debut" form="recherche" register="true"}}</td>
		</tr>
		<tr>
      <th>{{mb_label object=$filter field="date_fin"}}</th>
      <td>{{mb_field object=$filter field="date_fin" form="recherche" register="true"}}</td>
    </tr>
    <tr>
      <td colspan="4" style="text-align: center">
        <button type="submit" class="search">
          {{tr}}Filter{{/tr}}
        </button>
				<button type="button" onclick = "raz(this.form)" class="cancel">
          {{tr}}Reset{{/tr}}
        </button>
      </td>
    </tr>
  </table>
	  {{if $nbusers != 0}}
       {{mb_include module=system template=inc_pagination total=$nbusers current=$page change_page='changePage'}}
    {{/if}}
      
</form>