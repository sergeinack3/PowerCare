{{*
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="addTypeDocDoc" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  {{mb_class object=$document_item}}
  {{mb_key object=$document_item}}
  <fieldset>
    <legend>Sélectionnez le type du document/fichier :</legend>
    <br />
    {{mb_label object=$document_item field=type_doc_dmp}}
    {{mb_field object=$document_item field=type_doc_dmp class="notNull" emptyLabel="Choose"
    onchange="this.form.onsubmit();"}}
    <br />
  </fieldset>
</form>