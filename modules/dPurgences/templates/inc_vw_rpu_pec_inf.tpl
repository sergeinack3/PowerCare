{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="pec_inf_reload" style="display: inline-block;">
  <script>
    HorodatageInf = {
      onSubmit:  function(form) {
        return onSubmitFormAjax(form, HorodatageInf.reload.curry(form));
      },
      reload: function(form) {
        new Url("urgences", "ajax_vw_attente")
          .addParam("rpu_id", $V(form.rpu_id))
          .addParam("pec_inf", 1)
          .requestUpdate('pec_inf_reload');
      }
    }
  </script>

  {{mb_include template=inc_horodatage_field object=$rpu field=pec_inf form="editRPU" type_attente="pec_inf"
               onchange="HorodatageInf.onSubmit(this.form)"}}
</div>
