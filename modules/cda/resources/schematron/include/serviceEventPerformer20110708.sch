<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    serviceEventperformer.sch :
    Contenu :
        Contrôle de la présence de l'exécutant l'acte principal documenté :
        - 1 seul élément documentationOf/serviceEvent/performer
        - et l'élément frère documentationOf/serviceEvent/effectiveTime doit être renseigné sans nullFlavor
    Paramètres d'appel :
        néant
    Historique :
        08/07/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="serviceEventPerformer">
    <p>
        Vérification de la présence et de la conformité de l'acte principal documenté 
    </p>
    <rule context="cda:ClinicalDocument">
        
        <assert test="count(cda:documentationOf/cda:serviceEvent/cda:performer) = 1">
            Erreur de conformité CI-SIS : l'en-tête CDA doit comporter un et un seul documentationOf/serviceEvent 
            avec un élément fils performer. </assert>
        
    </rule>
    
    <rule context="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:performer">
        
        <assert
            test="not(@nullFlavor)">
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent/performer doit être renseigné sans nullFlavor. </assert>
        
        <assert test="../cda:effectiveTime/cda:low and 
                      not(../cda:effectiveTime[@nullFlavor]) and
                      not(../cda:effectiveTime/cda:low[@nullFlavor])">
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent portant l'acte principal documenté
            doit comporter à la fois un fils performer et un petit-fils effectiveTime/low sans attribut nullFlavor. </assert>
        
        <assert test="cda:assignedEntity/cda:representedOrganization/cda:standardIndustryClassCode">
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent/performer correspondant à l'acte principal documenté, 
            doit comporter un descendant assignedEntity/representedOrganization/standardIndustryClassCode. </assert>
        
    </rule>
</pattern>