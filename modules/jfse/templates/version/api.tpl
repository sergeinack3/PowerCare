{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1>{{tr}}SSV{{/tr}}</h1>
{{assign var=ssv value=$api_version->getSsv()}}

{{assign var=computer value=$ssv->getComputer()}}
<table class="tbl">
    <tr><th colspan="2" class="title">{{tr}}Computer{{/tr}}</th></tr>
    <tr><th>{{tr}}Computer-Group{{/tr}}</th><td>{{$computer->getGroup()}}</td></tr>
    <tr><th>{{tr}}Computer-SSV Version{{/tr}}</th><td>{{$computer->getSsvVersion()}}</td></tr>
    <tr><th>{{tr}}Computer-GALSS Version{{/tr}}</th><td>{{$computer->getGalssVersion()}}</td></tr>
    <tr><th>{{tr}}Computer-PSS Version{{/tr}}</th><td>{{$computer->getPssVersion()}}</td></tr>
</table>

<table class="tbl">
    {{foreach from=$ssv->getCardReaderConfigs() item=_config}}
        <tr><th class="title" colspan="2">{{$_config->getReaderConstructorName()}}</th></tr>
        <tr><th>{{tr}}CardConfigReader-Reader type{{/tr}}</th><td>{{$_config->getReaderType()}}</td></tr>
        <tr><th>{{tr}}CardConfigReader-Serial number{{/tr}}</th><td>{{$_config->getSerialNumber()}}</td></tr>
        <tr><th>{{tr}}CardConfigReader-OS{{/tr}}</th><td>{{$_config->getOsReader()}}</td></tr>
        <tr><th>{{tr}}CardConfigReader-Amount of softwares{{/tr}}</th><td>{{$_config->getReaderAmountSoftwares()}}</td></tr>
        <tr>
            <th>{{tr}}CardConfigReader-Software|pl{{/tr}}</th>
            <td>
                <ul>
                    {{foreach from=$_config->getSoftwares() item=_soft}}
                        <li>
                            {{$_soft->getName()}} ({{$_soft->getVersionNumber}}) - {{$_soft->getDateTimeString()}}<br>
                            <em>{{tr}}CardConfigReader-Checksum{{/tr}}: {{$_soft->getChecksum()}}</em>
                        </li>
                    {{/foreach}}
                </ul>
            </td>
        </tr>
    {{/foreach}}
</table>

<table class="tbl">
    <tr><th colspan="2" class="title">{{tr}}PcscReader|pl{{/tr}}</th></tr>
    {{foreach from=$ssv->getPcscReaders() item=_pcsc}}
        <tr><th colspan="2" class="me-text-align-center">{{$_pcsc->getName()}}</th></tr>
        <tr><th>{{tr}}PcscReader-Group{{/tr}}</th><td>{{$_pcsc->getGroup()}}</td></tr>
        <tr><th>{{tr}}PcscReader-Card type{{/tr}}</th><td>{{$_pcsc->getCardType()}}</td></tr>
    {{/foreach}}
</table>

<table class="tbl">
    <tr><th colspan="2" class="title">{{tr}}SesamVitalComponent{{/tr}}</th></tr>
    {{foreach from=$ssv->getSesamVitaleComponents() item=_component}}
        <tr><th colspan="2" class="me-text-align-center">{{$_component->getLabel()}}</th></tr>
        <tr><th>{{tr}}SesamVitalComponent-Group{{/tr}}</th><td>{{$_component->getGroup()}}</td></tr>
        <tr><th>{{tr}}SesamVitalComponent-Id{{/tr}}</th><td>{{$_component->getId()}}</td></tr>
        <tr><th>{{tr}}SesamVitalComponent-Version number{{/tr}}</th><td>{{$_component->getVersionNumber()}}</td></tr>
    {{/foreach}}
</table>

<h1>{{tr}}SRT{{/tr}}</h1>
{{assign var=srt value=$api_version->getSrt()}}
<table class="tbl">
    <tr><th>{{tr}}SRT-Referential{{/tr}}</th><td>{{$srt->getReferential()}}</td></tr>
    <tr><th>{{tr}}SRT-Referential server{{/tr}}</th><td>{{$srt->getReferentialServer()}}</td></tr>
    <tr><th>{{tr}}SRT-Referent revision{{/tr}}</th><td>{{$srt->getReferentialRevision()}}</td></tr>
    <tr><th>{{tr}}SRT-Referential variant{{/tr}}</th><td>{{$srt->getReferentialVariant()}}</td></tr>
    <tr><th>{{tr}}SRT-Ccam db{{/tr}}</th><td>{{$srt->getCcamDb()}}</td></tr>
    <tr><th>{{tr}}SRT-Ccam server{{/tr}}</th><td>{{$srt->getCcamDbServer()}}</td></tr>
    <tr><th>{{tr}}SRT-Modification date{{/tr}}</th><td>{{$srt->getModificationDateString()}}</td></tr>
    <tr><th>{{tr}}SRT-Comment{{/tr}}</th><td>{{$srt->getComment()}}</td></tr>
    <tr><th>{{tr}}SRT-Software version{{/tr}}</th><td>{{$srt->getSoftwareVersion()}}</td></tr>
</table>

<h1>{{tr}}STS{{/tr}}</h1>
<table class="tbl">
    {{assign var=sts value=$api_version->getSts()}}
    {{foreach from=$sts->getDetails() item=_detail}}
        <tr><th>{{tr}}STS-Group{{/tr}}</th><td>{{$_detail->getGroup()}}</td></tr>
        <tr><th>{{tr}}STS-Module identification{{/tr}}</th><td>{{$_detail->getModuleIdentification()}}</td></tr>
        <tr><th>{{tr}}STS-Module identification label{{/tr}}</th><td>{{$_detail->getModuleIdentificationLabel()}}</td></tr>
        <tr><th>{{tr}}STS-Module version{{/tr}}</th><td>{{$_detail->getModuleVersion()}}</td></tr>
        <tr><th>{{tr}}STS-External tables version{{/tr}}</th><td>{{$_detail->getExternalTablesVersion()}}</td></tr>
        <tr><th>{{tr}}STS-Variant{{/tr}}</th><td>{{$_detail->getVariant()}}</td></tr>
        <tr><th>{{tr}}STS-Comment{{/tr}}</th><td>{{$_detail->getComment()}}</td></tr>
    {{/foreach}}
</table>
