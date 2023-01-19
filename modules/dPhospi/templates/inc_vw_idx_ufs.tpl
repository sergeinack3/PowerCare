{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('display-ufs');
    form.onsubmit();
  });
</script>

<form name="display-ufs" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-ufs')">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="a" value="ajax_display_ufs" />

</form>

<div id="result-ufs"></div>