{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="list-style: none;">
    <li>
        <div class="small-info">
            Nombre de {{tr}}CPlageconsult{{/tr}} cr��es: {{$results.CPlageconsult}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CPatient{{/tr}} cr��s: {{$results.CPatient}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfsePatient{{/tr}} cr��s: {{$results.CJfsePatient}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CConsultation{{/tr}} cr��es: {{$results.CConsultation}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfseInvoice{{/tr}} cr��es: {{$results.CJfseInvoice}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CActeNGAP{{/tr}} cr��s: {{$results.CActeNGAP}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CACteCCAM{{/tr}} cr��s: {{$results.CActeCCAM}}
        </div>
    </li>
    <li>
        <div class="small-info">
            Nombre de {{tr}}CJfeAct{{/tr}} cr��es: {{$results.CJfseAct}}
        </div>
    </li>
</ul>

{{if $results.errors}}
    <div class="small-error">
        Nombre d'erreurs rencontr�es: {{$results.errors|count}}
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
