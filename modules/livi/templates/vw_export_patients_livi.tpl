{{*
 * @package Mediboard\livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    var form = getForm("uploadLivi");
    Calendar.regField(form.date_debut);
    Calendar.regField(form.date_fin);
  });
</script>
<form name="uploadLivi" method="post" enctype="multipart/form-data"
      action="?m=livi&raw=importCsvPatientsLivi" target="livi_pdf">
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000"/>
  <table class="form">
    <tr>
      <th>
        {{tr}}common-Start date{{/tr}}
      </th>
      <td>
        <input type="hidden" name="date_debut" value="{{$date_debut}}" class="date notNull"/>
      </td>
      <th>
        {{tr}}common-End date{{/tr}}
      </th>
      <td>
        <input type="hidden" name="date_fin" value="{{$date_fin}}" class="date notNull"/>
      </td>
    </tr>
    <tr>
      <td colspan="4">
        {{mb_include module=system template=inc_inline_upload lite=true paste=false extensions='csv'}}
      </td>
    </tr>
    <tr>
      <td colspan="4" class="button">
        <button id="submit_livi" type="submit" class="button submit">
          {{tr}}Import{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
<div name="livi_pdf" class="me-no-display"></div>
