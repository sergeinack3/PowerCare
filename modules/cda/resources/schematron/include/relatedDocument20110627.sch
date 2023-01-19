<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    relatedDocument.sch :
    Contenu :
        La relation avec le document lié est obligatoirement de remplacement 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="relatedDocument">
    <p>
        Si l'élément relatedDocument est présent alors son attribut typeCode doit valoir RPLC 
    </p>
    <rule context="cda:relatedDocument">      
        <assert test="(count(@*) = 1 and name(@*) = 'typeCode' and @* = 'RPLC')">
            Erreur de conformité CI-SIS : ClinicalDocument/relatedDocument ne contient pas l'attribut typeCode avec la seule valeur autorisée : RPLC.
        </assert>
    </rule>
</pattern>