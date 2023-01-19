<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    serviceEventEffectiveTime.sch :
    Contenu :
        Règles de contrôle de la période de production de l'acte documenté 
    Paramètres d'appel :
        néant
    Historique :
        02/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="serviceEventEffectiveTime" is-a="IVL_TS">
    <p>Conformité de la période de réalisation de l'acte documenté, nullFlavor autorisé</p>
    <param name="elt" value="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime"/>
    <param name="vue_elt" value="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
    <param name="nullFlavor" value="1"/>
</pattern>