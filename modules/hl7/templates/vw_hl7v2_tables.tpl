{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    getForm('listFilter').onsubmit();
  });
</script>

{{mb_script module=hl7 script=tables_hl7v2 ajax=true}}

<table class="main">
  <tr>
    <td colspan="2">
      <button class="new" onclick="Tables_hl7v2.editTableDescription(0)"> {{tr}}CHL7v2TableDescription-title-create{{/tr}} </button>
    </td>
  </tr>
  <tr>
    <td>
      <form name="listFilter" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'tables-hl7v2');">
        <input type="hidden" name="m" value="hl7" />
        <input type="hidden" name="a" value="ajax_refresh_tables" />
        <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()" />
        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>
            <td>
              <table class="form">
                <tr>
                  <th style="width: 8%"> Mots clés :</th>
                  <td>
                    <input type="text" name="keywords" value="{{$keywords}}" onchange="$V(this.form.page, 0)" />
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>

<div id="tables-hl7v2"></div>