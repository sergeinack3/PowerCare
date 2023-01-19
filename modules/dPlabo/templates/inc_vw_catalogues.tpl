{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  PairEffect.initGroup('tree-content');
</script>

<form name="editCatalogue" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{mb_class object=$catalogue}}
  {{mb_key   object=$catalogue}}
  <input type="hidden" name="del" value="0" />
</form>

{{assign var="catalogue_id" value=$catalogue->_id}}
{{foreach from=$listCatalogues item="_catalogue"}}
  {{mb_include module=labo template=tree_catalogues}}
{{/foreach}}