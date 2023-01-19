{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=0}}
{{assign var=create_antecedent_only_prat value=0}}
{{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
  !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  {{assign var=create_antecedent_only_prat value=1}}
{{/if}}

<ul>
  {{if $dossier_medical->_ref_prescription->_ref_prescription_lines|@count == 0 && $dossier_medical->_ref_traitements|@count == 0}}
    {{if $dossier_medical->absence_traitement}}
      <li class="empty">{{tr}}CDossierMedical-absence_traitement{{/tr}}</li>
    {{else}}
      <li class="empty">{{tr}}CTraitement-none{{/tr}}</li>
    {{/if}}
  {{/if}}
  {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line}}
    <li>
      {{if $edit && $_line->creator_id == $app->user_id && !$create_antecedent_only_prat}}
        <form name="delLine{{$_line->_id}}" method="post">
          <input type="hidden" name="m" value="mpm" />
          <input type="hidden" name="dosql" value="do_prescription_line_medicament_aed" />
          {{mb_key object=$_line}}
          <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}"
                  onclick="confirmDeletion(this.form, {
                    typeName: $T('CTraitement-name_tp_to_delete'),
                    objName: '{{$_line->_ref_produit->ucd_view|JSAttribute}}'
                    },
                    refreshTp)"></button>
        </form>
      {{/if}}
      {{mb_include module=system template=inc_interval_date from=$_line->debut to=$_line->fin}}

      <a href="#1" onclick="Prescription.viewProduit(null,'{{$_line->code_ucd}}','{{$_line->code_cis}}');">
        {{$_line->_ucd_view}}
      </a>

      <span class="compact" style="display: inline;">
        {{$_line->commentaire}}
        {{if $_line->_ref_prises|@count}}
          <br />
          ({{foreach from=`$_line->_ref_prises` item=_prise name=foreach_prise}}
          {{$_prise}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
        {{/foreach}})
        {{/if}}

        {{if $_line->conditionnel}}
          ({{mb_label class=CPrescriptionLineMedicament field=conditionnel}})
        {{/if}}

        {{if $_line->long_cours}}
          ({{mb_label class=CPrescriptionLineMedicament field=long_cours}})
        {{/if}}
      </span>
    </li>
  {{/foreach}}

  {{foreach from=$dossier_medical->_ref_traitements item=_traitement}}
    <li>
      {{if $edit && $_traitement->owner_id == $app->user_id && !$create_antecedent_only_prat}}
        <form name="delAtcd{{$_traitement->_id}}" method="post">
          {{mb_class object=$_traitement}}
          {{mb_key object=$_traitement}}
          <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}"
                  onclick="confirmDeletion(this.form, {
                    typeName: $T('CTraitement-name_tp_to_delete'),
                    objName: '{{$_traitement->traitement|JSAttribute}}'
                    }, refreshTp);"></button>
        </form>
      {{/if}}
      {{mb_include module=system template=inc_interval_date_progressive object=$_traitement from_field=debut to_field=fin}}

      {{$_traitement->traitement|nl2br}}
    </li>
  {{/foreach}}
</ul>
