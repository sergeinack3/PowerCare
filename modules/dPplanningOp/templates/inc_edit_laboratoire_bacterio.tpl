{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editLaboratoireBacterio" method="post" onsubmit="return LaboBacterio.submit(this);">
  {{mb_class object=$laboratoire_bacterio}}
  {{mb_key object=$laboratoire_bacterio}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$laboratoire_bacterio}}
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=libelle nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=libelle}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_bool mb_object=$laboratoire_bacterio mb_field=actif nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=actif}}
      {{/me_form_bool}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=adresse nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=adresse}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=cp nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=cp}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=ville nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=ville}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=tel nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=tel}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=fax nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=fax}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{me_form_field mb_object=$laboratoire_bacterio mb_field=mail nb_cells=2}}
      {{mb_field object=$laboratoire_bacterio field=mail}}
      {{/me_form_field}}
    </tr>
    
    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$laboratoire_bacterio
      options="{typeName: '', objName: `$laboratoire_bacterio->_view`}"
      options_ajax="Control.Modal.close"}}
    </tr>
  </table>
</form>
