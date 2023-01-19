<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    legalAuthenticator.sch :
    Contenu :
        Contrôle du legalAuthenticator dans l'en-tête CDA  
    Paramètres d'appel :
        Néant
    Historique :
        31/05/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="legalAuthenticator">
    <rule context="cda:ClinicalDocument/cda:legalAuthenticator/cda:assignedEntity">
        <assert test="./cda:id and not(./cda:id[@nullFlavor])">
            Erreur de conformité CI-SIS : L'élément "id" doit être présent sous legalAuthenticator/assignedEntity, sans nullFlavor.
        </assert>
        <assert test="./cda:assignedPerson and not(./cda:assignedPerson[@nullFlavor])">
            Erreur de conformité CI-SIS : L'élément "assignedPerson" doit être présent sous legalAuthenticator/assignedEntity, 
            sans nullFlavor.
        </assert>
        <assert test="./cda:assignedPerson/cda:name and not(./cda:assignedPerson/cda:name[@nullFlavor])">
            Erreur de conformité CI-SIS : L'élément "name" doit être présent sous legalAuthenticator/assignedEntity/assignedPerson, 
            sans nullFlavor.
        </assert>        
    </rule>
</pattern>   