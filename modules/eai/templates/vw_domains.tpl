{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=domain}}

<script>
  window.checkedMerge = [];
  checkOnlyTwoSelected = function (checkbox) {
    checkedMerge = checkedMerge.without(checkbox);

    if (checkbox.checked) {
      checkedMerge.push(checkbox);
    }

    if (checkedMerge.length > 2) {
      checkedMerge.shift().checked = false;
    }
  };

  Main.add(function() {
    Domain.refreshListDomains();
  })
</script>

<table class="main">
  <tr>
    <td style="width: 60%">
      <a href="#" onclick="Domain.showDomain()" class="button new">
        {{tr}}CDomain-title-create{{/tr}}
      </a>

      <a href="#" onclick="Domain.createDomainWithIdexTag()" class="button new">
        {{tr}}CDomain-title-create-with-idex-tag{{/tr}}
      </a>
    </td>
  </tr>
  <tr>
    <td id="vw_list_domains"></td>
  </tr>
</table>