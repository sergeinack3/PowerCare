{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=modele_etiquette ajax=1}}

<script>
  Main.add(ModeleEtiquette.refreshList);
</script>

<table class="main">
  <tr>
    <td style="width: 50%;">
      {{mb_include template=inc_filter_etiquettes}}
      <div id="list_etiq"></div>
    </td>
  </tr>
</table>