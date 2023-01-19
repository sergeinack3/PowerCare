{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editWhiteList" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$whitelist}}
  {{mb_key   object=$whitelist}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$whitelist show_notes=false}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$whitelist mb_field=email}}
        {{mb_field object=$whitelist field=email}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$whitelist mb_field=actif}}
        {{mb_field object=$whitelist field=actif typeEnum=checkbox}}
      {{/me_form_bool}}
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$whitelist
                 options="{typeName: \$T('CWhiteList'), objName: '`$whitelist->_view`'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>