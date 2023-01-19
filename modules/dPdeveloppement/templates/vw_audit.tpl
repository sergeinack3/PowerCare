{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('audit-data');

    Calendar.regField(form.elements.start_date);
    Calendar.regField(form.elements.end_date);
  });

  submitAuditForm = function(form) {
    var url = new Url('developpement', 'ajax_audit');
    url.addFormData(form);

    url.requestUpdate('audit-result', {method: 'post', getParameters: {m: 'developpement', a: 'ajax_audit'}});

    return false;
  }
</script>

<form name="audit-data" method="post" onsubmit="return submitAuditForm(this);">
  <table class="main form">
    <tr>
      <td class="narrow" style="text-align: left;">
        <label>
          Hôtes

          <input type="text" name="host1" class="notNull" value="{{$host1}}" size="35" placeholder="user[:password]@hostname/db[:port]" />
          <input type="text" name="host2" class="notNull" value="{{$host2}}" size="35" placeholder="user[:password]@hostname/db[:port]" />
        </label>
      </td>

      <td rowspan="2" style="text-align: left; vertical-align: middle;">
        <button type="submit" class="lookup">
          {{tr}}common-action-Audit{{/tr}}
        </button>
      </td>
    </tr>

    <tr>
      <td style="text-align: left;">
        <label>
          {{tr}}common-Date{{/tr}}

          <input type="hidden" name="start_date" class="dateTime notNull" value="{{$start_date}}" />
          &raquo;
          <input type="hidden" name="end_date" class="dateTime notNull" value="{{$end_date}}" />
        </label>
      </td>
    </tr>
  </table>
</form>

<div id="audit-result"></div>
