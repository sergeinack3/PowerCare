{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Echange = {
  purge: function(force) {
    var form = getForm('EchangePurge');

	  if (!force && !$V(form.auto)) {
	    return;
	  }
  
    if (!checkForm(form)) {
      return;
    }
    
    var url = new Url('ftp', 'ajax_purge_echange');
    url.addFormData(form);
    url.requestUpdate("purge-echange");
  }
}
</script>

<form name="EchangePurge" action="?" method="get">
  <table class="main form" style="table-layout: fixed;">
    <tr>
      <th>
        <label for="date_max">{{tr}}CEchangeFTP-_date_max{{/tr}}</label>
			</th>
			<td>
        <input class="date notNull" type="hidden" name="date_max" value="" />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('EchangePurge').date_max);
          });
        </script>
      </td>
		</tr>
		<tr>
			<th>
				<label for="delete">Supprimer les échanges antérieurs à 6 mois : </label>
			</th>
			<td>
				<input type="checkbox" name="delete" value="1" />
			</td>
    </tr>
    <tr>
      <th>
        <button type="button" class="change" onclick="Echange.purge(true)">
          {{tr}}CEchangeFTP-purge-search{{/tr}}
        </button>
        <label><input type="checkbox" name="do_purge" /> {{tr}}Purge{{/tr}}</label>
        <label><input type="checkbox" name="auto" /> {{tr}}Auto{{/tr}}</label>
      </th>
      <td id="purge-echange"></td>
    </tr>
  </table>
</form>