{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="chooseUfs" method="get">
  <table class="main form">
    <tr>
      <th colspan="6" class="category">Association d'unités fonctionnelles</th>
    </tr>
    {{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
      <tr>
        <td colspan="6">
          {{mb_include module=hospi template=inc_alerte_ufs}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td>
        <fieldset>
          <legend>
            {{me_img src="search.png" icon="search" class="me-primary" onclick="this.up('fieldset').down('tbody').toggle();"}}
            {{mb_label class=CSejour field=uf_hebergement_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=hebergement}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$object  ufs=$uf_sejour_hebergement}}
            {{mb_include template=inc_vw_ufs_object object=$service ufs=$ufs_service.$context}}
            </tbody>

            {{mb_include template=inc_options_ufs_context_form ufs_context=$ufs_hebergement}}
          </table>
        </fieldset>

        <fieldset>
          <legend>
            {{me_img src="search.png" icon="search" class="me-primary" onclick="this.up('fieldset').down('tbody').toggle();"}}
            {{mb_label class=CSejour field=uf_soins_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=soins}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$object  ufs=$uf_sejour_soins}}
            {{mb_include template=inc_vw_ufs_object object=$service ufs=$ufs_service.$context}}
            </tbody>

            {{mb_include template=inc_options_ufs_context_form ufs_context=$ufs_soins}}
          </table>
        </fieldset>

        <fieldset>
          <legend>
            {{me_img src="search.png" icon="search" class="me-primary" onclick="this.up('fieldset').down('tbody').toggle();"}}
            {{mb_label class=CSejour field=uf_medicale_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=medicale}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$object    ufs=$uf_sejour_medicale}}
            {{mb_include template=inc_vw_ufs_object object=$function  ufs=$ufs_function }}
            {{mb_include template=inc_vw_ufs_object object=$function  ufs=$ufs_function_second  uf_secondaire=true}}
            {{mb_include template=inc_vw_ufs_object object=$praticien ufs=$ufs_praticien_sejour name="Praticien séjour"}}
            {{mb_include template=inc_vw_ufs_object object=$praticien ufs=$ufs_praticien_sejour_second uf_secondaire=true}}
            </tbody>
            {{mb_include template=inc_options_ufs_context_form ufs_context=$ufs_medicale}}
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="tick me-primary" type="button"
                onclick="window.parent.PreselectorUfs.applyUfs(this.form); window.parent.Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
