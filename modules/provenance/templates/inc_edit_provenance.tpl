{{*
* @package Mediboard\Provenance
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=provenance script=provenance}}

<form name="editProvenance" method="post">
  <input type="hidden" name="m" value="provenance"/>
  <input type="hidden" name="del" value="0"/>
  {{mb_class object=$provenance}}
  {{mb_key object=$provenance}}
  {{mb_field object=$provenance field=group_id value=$group_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$provenance show_notes=false}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$provenance mb_field=libelle}}
      {{mb_field object=$provenance field=libelle}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$provenance mb_field=desc}}
      {{mb_field object=$provenance field=desc rows=3 class="noresize"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$provenance mb_field=actif}}
      {{mb_field object=$provenance field=actif}}
      {{/me_form_bool}}
    </tr>
    {{mb_include module=system template=inc_form_table_footer object=$provenance
    options="{typeName: '', objName: '`$provenance`'}" options_ajax="function(){Control.Modal.close();Provenance.listProvenances()}"}}
  </table>
</form>