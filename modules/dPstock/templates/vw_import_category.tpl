{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<iframe id="upload_iframe" name="upload_iframe" src="about:blank" style="width: 100%; height: 600px;"></iframe>

<form name="upload_form" action="?m=stock&raw=do_import_product_category_xml"
      enctype="multipart/form-data" method="post" target="upload_iframe" onsubmit="if (checkForm(this)){this.submit()
}">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="dosql" value="do_import_product_category_xml" />
  <input type="hidden" name="MAX_FILE_SIZE" value="67108864" /><!-- 64MB -->

  <input type="file" name="import" class="notNull" size="60" />
  <button type="button" class="tick" onclick="this.form.onsubmit()">Importer le fichier XML</button>
</form>
