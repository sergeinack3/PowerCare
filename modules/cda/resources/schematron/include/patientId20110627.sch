<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    patientId.sch :
    Contenu :
       Contrôle des id du patient dans l'en-tête CDA  
    Paramètres d'appel :
       Néant
    Historique :
       10/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="patientId">
    <p>
        Vérification de la conformité au CI-SIS :
        l'INS-C doit être une chaîne de 22 chiffres 
    </p>
    <rule context="cda:ClinicalDocument/cda:recordTarget/cda:patientRole/cda:id">
        <assert test="
            (@root = $OIDINS-c and string-length(@extension) = 22 and number(@extension) &gt; 1) 
             or (@root != $OIDINS-c)">
            Erreur de conformité CI-SIS : L'INS-c doit contenir une chaine de 22 chiffres 
            (valeur trouvée dans le document : <value-of select="@extension"/>,
             OID trouvé : <value-of select="@root"/>)
        </assert>
    </rule>
</pattern>