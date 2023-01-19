{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="list-style: none;">
    <li>
        <div class="small-info">
            Nombre de {{tr}}CPlageconsult{{/tr}} créées: {{$results.CPlageconsult}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CPatient{{/tr}} créés: {{$results.CPatient}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfsePatient{{/tr}} créés: {{$results.CJfsePatient}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CConsultation{{/tr}} créées: {{$results.CConsultation}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfseInvoice{{/tr}} créées: {{$results.CJfseInvoice}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CActeNGAP{{/tr}} créés: {{$results.CActeNGAP}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CACteCCAM{{/tr}} créés: {{$results.CActeCCAM}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfeAct{{/tr}} créées: {{$results.CJfseAct}}
        </div>
    </li>
</ul>

{{if $results.errors}}
    <div class="small-error">
        Nombre d'erreurs rencontrées: {{$results.errors|count}}
    </div>
    <ul>
        {{foreach from=$results.errors item=error}}
            <li>
                <div class="small-error">
                    {{$error}}
                </div>
            </li>
        {{/foreach}}
    </ul>
{{/if}}
