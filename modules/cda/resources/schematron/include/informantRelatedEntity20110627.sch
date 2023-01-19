<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    informantRelatedEntity.sch :
    Contenu :
        Contrôle d'un proche du patient  
    Paramètres d'appel :
        Néant
    Historique :
    05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="informantRelatedEntity">
    <rule context="cda:informant/cda:relatedEntity">
        <assert test="((name(@*) = 'classCode') and 
                        (@* = 'ECON' or @* = 'GUARD' or @* = 'POLHOLD' or @* = 'CON' or @* = 'QUAL')
                    )">
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit avoir un attribut classCode dont la valeur est dans l'ensemble :
            (ECON, GUARD, POLHOLD, CON, QUAL).
        </assert>
        <assert test="./cda:addr">
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit comporter une adresse géographique (nullFlavor autorisé)
        </assert>
        <assert test="./cda:telecom">
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit comporter une adresse telecom (nullFlavor autorisé)
        </assert>
    </rule>
</pattern>