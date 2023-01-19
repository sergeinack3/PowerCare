{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=labo script=catalogue ajax=true}}

<script>
  Main.add(function() {
    Catalogue.refreshList();
    PairEffect.initGroup('tree-content');
  });
</script>

<button type="button" class="new" onclick="Catalogue.edit();">{{tr}}CCatalogueLabo.create{{/tr}}</button>

<div id="list_catalogues"></div>
