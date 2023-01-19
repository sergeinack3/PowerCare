{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=labo script=pack ajax=true}}

<script>
  Main.add(function() {
    Pack.refreshList();
  });
</script>

<button type="button" class="new" onclick="Pack.edit();">{{tr}}CPackExamensLabo.create{{/tr}}</button>

<div id="list_packs"></div>