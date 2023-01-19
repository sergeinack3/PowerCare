{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}
{{mb_default var=order_col value='code'}}
{{mb_default var=order_way value='ASC'}}
{{mb_default var=target value='listActesNGAP'}}
{{mb_default var=display value=null}}

<tr>
    <th class="category">{{mb_title class=CActeNGAP field=quantite}}</th>
    <th class="category">
        {{mb_colonne class=CActeNGAP field=code order_col=$order_col order_way=$order_way function="ActesNGAP.refreshList.curry('$target')"}}
    </th>
    <th class="category">{{mb_title class=CActeNGAP field=coefficient}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=demi}}</th>
    {{if !$object->_coded || $display == 'pmsi'}}
        {{if $can->edit || $display == 'pmsi'}}
            <th class="category">{{mb_title class=CActeNGAP field=montant_base}}</th>
            <th class="category">{{mb_title class=CActeNGAP field=montant_depassement}}</th>
        {{/if}}
    {{else}}
        <th class="category">{{mb_title class=CActeNGAP field=montant_base}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=montant_depassement}}</th>
    {{/if}}
    <th class="category">{{mb_title class=CActeNGAP field=complement}}</th>

    <th class="category">{{mb_title class=CActeNGAP field=gratuit}}</th>
    {{if $object->_class == "CConsultation" || $object->_class == 'CModelCodage'}}
        <th class="category">{{mb_title class=CActeNGAP field=lieu}}</th>
    {{/if}}

    <th class="category">{{mb_title class=CActeNGAP field=qualif_depense}}</th>

    {{if $conf.ref_pays != "2" && ($object->_ref_patient->ald || ($object->_class == 'CConsultation' && $object->concerne_ALD))}}
        <th class="category">{{mb_title class=CActeNGAP field=ald}}</th>
    {{/if}}

    <th class="category">{{mb_title class=CActeNGAP field=exoneration}}</th>

    {{if $_is_dentiste}}
        <th class="category">{{mb_title class=CActeNGAP field=numero_dent}}</th>
    {{/if}}

    <th class="category">{{mb_title class=CActeNGAP field=accord_prealable}}</th>

    <th class="category">
        {{mb_colonne class=CActeNGAP field=execution order_col=$order_col order_way=$order_way function="ActesNGAP.refreshList.curry('$target')"}}
    </th>

    <th class="category">
        {{mb_colonne class=CActeNGAP field=executant_id order_col=$order_col order_way=$order_way function="ActesNGAP.refreshList.curry('$target')"}}
    </th>
    {{if !$object->_coded || $display == 'pmsi'}}
        {{if (!$readonly && $can->edit) || $display == 'pmsi'}}
            <th class="category">{{tr}}Action{{/tr}}</th>
        {{/if}}
    {{/if}}
    <th class="category" colspan="2"></th>
</tr>
