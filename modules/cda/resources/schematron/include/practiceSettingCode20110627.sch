<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    practiceSettingCode.sch :
    Contenu :
        Contrôle du cadre de réalisation d'un acte documenté dans l'en-tête CDA  (nullFlavor non autorisé)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        31/05/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="practiceSettingCode" is-a="dansJeuDeValeurs">
    <p>Conformité du practiceSettingCode de l'exécutant de l'acte documenté au CI-SIS</p>
    <param name="path_jdv" value="$jdv_practiceSettingCode"/>
    <param name="vue_elt" value="'serviceEvent/performer/assignedEntity/representedOrganization/standardIndustryClassCode'"/>
    <param name="xpath_elt" value="cda:serviceEvent/cda:performer/cda:assignedEntity/cda:representedOrganization/cda:standardIndustryClassCode"/>
    <param name="nullFlavor" value="0"/>
</pattern>   