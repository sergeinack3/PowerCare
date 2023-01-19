{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="factory_{{$sens}}" onchange="testCR('{{$sens}}');">
  <option value="wkhtmltopdf_amd64">
    WkHtmlToPdf 0.12.3
  </option>
  <option value="CDomPDFConverter">
    domPDF
  </option>
</select>