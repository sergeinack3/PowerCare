{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Echange = {
    purge: function(force) {
      form = getForm('EchangePurge');

    if (!force && !$V(form.auto)) {
    return;
    }
    
      if (!checkForm(form)) {
        return;
      }
      
      var url = new Url('hprimxml', 'ajax_purge_echange');
      url.addElement(form.date_max);
      url.addElement(form.do_purge);  
      url.requestUpdate("purge-echange");
    }
  }

</script>

<table class="main">
  <tr>
    <td class="button">
      <form name="EchangePurge" action="?" method="get">
        <table class="form">
          <tr>
            <td colspan="2">
             <label for="date_max">{{tr}}CEchangeHprim-_date_max{{/tr}}</label> : 
             <input class="date notNull" type="hidden" name="date_max" value="" />
              <script type="text/javascript">
                Main.add(function () {
                  Calendar.regField(getForm('EchangePurge').date_max);
                });
              </script>
            </td>
          </tr>
          <tr>
            <td>
              <button type="button" class="change" onclick="Echange.purge(true)">
                {{tr}}CEchangeHprim-purge-search{{/tr}}
              </button>
              <label><input type="checkbox" name="do_purge" />{{tr}}Purge{{/tr}}</label>
              <label><input type="checkbox" name="auto" />{{tr}}Auto{{/tr}}</label>
            </td>
            <td id="purge-echange"></td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>