{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=medicament script=medicament_selector ajax=true}}

{{mb_default var=edit value=0}}

{{unique_id var=addform}}

{{assign var=gestion_tp value=""}}
{{assign var=_is_anesth value=""}}
{{assign var=type_see value=""}}
{{assign var=reload value=""}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=callback value="refreshTp"}}
{{assign var=callback_texte_libre value="refreshTp"}}
{{assign var=userSel value=$app->_ref_user}}

<script>
  refreshTp = DossierMater.refreshTP.curry("{{$patient->_id}}", 1, "list_tp");
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <fieldset>
        <legend>
          Base de données de médicaments
        </legend>
        {{mb_include module=cabinet template=inc_antecedent_bdm}}
      </fieldset>

      <br />

      <fieldset>
        <legend>
          Texte libre
        </legend>
        {{mb_include module=cabinet template=inc_traitement_texte_libre}}
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>
          Traitements personnels
        </legend>
        <div>
          {{assign var=display value="none"}}
          {{if $dossier_medical->_ref_prescription->_ref_prescription_lines|@count == 0 && $dossier_medical->_ref_traitements|@count == 0}}
            {{assign var=display value="inline"}}
          {{elseif $dossier_medical->absence_traitement}}
            <script>
              Main.add(function () {
                var form = getForm("save_absence_tp");
                $V(form.absence_traitement, "0");
              });
            </script>
          {{/if}}
          <form name="save_absence_tp" action="?" method="post" onsubmit="return onSubmitFormAjax(this);"
                style="float: right;display: {{$display}}">
            {{mb_key   object=$dossier_medical}}
            <input type="hidden" name="m" value="patients" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
            <input type="hidden" name="object_id" value="{{$patient->_id}}" />
            <input type="hidden" name="object_class" value="{{$patient->_class}}" />
            {{mb_label object=$dossier_medical field=absence_traitement}}
            {{mb_field object=$dossier_medical field=absence_traitement typeEnum=checkbox onchange="return onSubmitFormAjax(this.form);"}}
          </form>
        </div>
        <div id="list_tp" style="height: 250px; overflow-y: auto;">
          {{mb_include module=maternite template=inc_list_tp edit=1}}
        </div>
      </fieldset>
    </td>
  </tr>
</table>