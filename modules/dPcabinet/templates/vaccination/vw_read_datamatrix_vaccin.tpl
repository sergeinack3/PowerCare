{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(() => {
    getForm('read_datamatrix_vaccin').datamatrix_vaccin.focus();
  });
</script>

<form name="read_datamatrix_vaccin" method="post" onsubmit="return false;">
    <table class="form">
        <tr>
            <th>{{tr}}CInjection-datamatrix_vaccin{{/tr}}</th>
            <td>
                <input type="text" name="datamatrix_vaccin" class="barcode"/>
            </td>
        </tr>
        <tr>
            <td class="button" colspan="2">
                <button onclick="dataVacc.readDatamatrix(this.form.datamatrix_vaccin.value, '{{$search}}');"
                        class="search">
                    {{tr}}common-action-Fill{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
