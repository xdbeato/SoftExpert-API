<?php namespace App\Traits;

use DB;
trait FormQueryExecution
{
    public function executeQuery($formulary_id)
    {
      
        $query = "SELECT
        FM.*                                    
    FROM
        (    
          SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
          
            CASE                                                                                                                                     
                WHEN EFM.FGTYPE = '1' THEN 'LISTA'                                                                                                                                     
                WHEN EFM.FGTYPE = '2' THEN 'CHECK'                                                                                                                                     
                WHEN EFM.FGTYPE = '3' THEN 'INPUT'                                                                                                                                     
                WHEN EFM.FGTYPE = '6' THEN 'RADIOBOT'                                                                                                                                     
                WHEN EFM.FGTYPE = '7' THEN 'GRUPO'                                                                                                                                     
                WHEN EFM.FGTYPE = '14' THEN 'FECHA'                                                                                                                                     
                WHEN EFM.FGTYPE = '15' THEN 'HORA'                                                                                                                                     
                WHEN EFM.FGTYPE = '17' THEN 'INPUT4000'                                                                                                                                     
                ELSE 'INPUT'                                                                                                     
            END TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,

            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
          (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
         
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            ATT.IDNAME CAMPO,
            ATT.NMLABEL NOMBRECAMPO,
            EM.IDNAME ENTIDAD,
            'QRY LV' QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                                    
        FROM
            EFREVISIONFORM RFM                                                                                    
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                                                            
                ON EFM.OIDREVISIONFORM = RFM.OID                                                                                     
        JOIN
            EMATTRMODEL ATT                                                                                                                                                                            
                ON ATT.OID = EFM.OIDATTRIBUTEMODEL                                                                                    
        JOIN
            EMENTITYMODEL EM                                                                                                                                                                            
                ON EM.OID = ATT.OIDENTITY                                                                                    
        WHERE
            RFM.FGCURRENT = '1'                                                                                                                       
            AND EFM.FGHIDDENMOBILE = '2'                                                                                
        UNION
              
          SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
            'LISTA' TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,

            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
            (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            EFM.IDSTRUCT CAMPO,
            EFM.NMLABEL NOMBRECAMPO,
            'ENTIDAD' ENTIDAD,
            'txt.txdata' QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                
        FROM
            EFREVISIONFORM RFM                                                                  
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                          
                ON EFM.OIDREVISIONFORM = RFM.OID                                                              
        WHERE
            RFM.FGCURRENT = '1'                                                                                              
            AND EFM.FGTYPE = '1'                                                                                           
            AND EFM.FGHIDDENMOBILE = '2'                     
            and rfm.idform = 'mescytsollegform'          
        UNION
             
          SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
            'LISTA' TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,

            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
            (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and  fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            EFM.IDSTRUCT CAMPO,
            EFM.NMLABEL NOMBRECAMPO,
            'ENTIDAD' ENTIDAD,
            txt.txdata QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                
        FROM
            EFREVISIONFORM RFM                                                                  
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                          
                ON EFM.OIDREVISIONFORM = RFM.OID          
        join
            emdatasetmodel dsm                  
                on dsm.oid = efm.oiddatasetmodel           
        join
            setext txt                  
                on txt.oid = dsm.oidcommand                                                                  
        WHERE
            RFM.FGCURRENT = '1'                                                                                              
            AND EFM.FGTYPE = '18'                                                                                           
            AND EFM.FGHIDDENMOBILE = '2'      
        UNION
        
          SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
            CASE                                                                                                                                     
                WHEN left(EFM.IDSTRUCT,3) = 'grp' THEN 'GRUPO'                                                                                                                                     
                ELSE 'SECCION' 
            END TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,
            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
            (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            EFM.IDSTRUCT CAMPO,
            EFM.NMLABEL NOMBRECAMPO,
            'ENTIDAD' ENTIDAD,
            'txt.txdata' QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                
        FROM
            EFREVISIONFORM RFM                                                                  
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                          
                ON EFM.OIDREVISIONFORM = RFM.OID          
        WHERE
            RFM.FGCURRENT = '1'                                                                                              
            AND EFM.FGTYPE = '7'                                                                                           
            AND EFM.FGHIDDENMOBILE = '2'                 

        UNION
              
          SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
            CASE                                                                                                                                     
                WHEN EFM.FGTYPE = '10' THEN 'TITULO'                                                                                                                                     
                WHEN EFM.FGTYPE = '16' THEN 'GRID'                                                                                                                                     
                WHEN EFM.FGTYPE = '24' THEN 'ARCHIVO'                                                                                                                                     
                ELSE 'INPUT'                                                                                                     
            END TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,

            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
            (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            EFM.IDSTRUCT CAMPO,
            EFM.NMLABEL NOMBRECAMPO,
            'ENTIDAD' ENTIDAD,
            'txt.txdata' QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                
        FROM
            EFREVISIONFORM RFM                                                                  
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                          
                ON EFM.OIDREVISIONFORM = RFM.OID          
        WHERE
            RFM.FGCURRENT = '1'                                                                                              
            AND EFM.FGTYPE in (
                '10', '16', '24'
            )                                                                                           
            AND EFM.FGHIDDENMOBILE = '2'    
UNION

                  SELECT
            RFM.IDFORM IDFORMULARIO,
            RFM.NMFORM FORMULARIO,
            'BOTON' TIPOELEMENTOFM,
            EFM.NMLABEL ETIQUETAEFM,
            EFM.IDSTRUCT IDELEMENTOFM,
            EFM.FGREQUIRED REQUERIDO ,
            EFM.FGENABLED DISPONIBLE,
            EFM.FGHIDDEN VISIBLE,

            Concat(floor(100000+EFM.vlposy), floor(100000+EFM.vlposx)) as ORDEN,
            (Select
                left(min(fm.idstruct),5)              
            from
                efstructform fm              
            where
                FM.OIDREVISIONFORM = RFM.OID                 
                and  fm.fgtype = '7'                  
                and fm.idstruct like 'grp%'                  
                and efm.vlposx between fm.vlposx and (
                    fm.vlposx + fm.vlwidth                 
                )                  
                and efm.vlposy between fm.vlposy and (
                    fm.vlposy + fm.vlheight                 
                )) GRUPO,
            EFM.FGHIDDENMOBILE MOBILE_DISP,
            EFM.IDSTRUCT CAMPO,
            EFM.NMLABEL NOMBRECAMPO,
            'ENTIDAD' ENTIDAD,
            txt.txdata QRY_LISTAVALOR,
            'REGLA' REGLA,
            EFM.OIDREVISIONFORM 'OIDREVISIONFORM'                                                                
        FROM
            EFREVISIONFORM RFM                                                                  
        JOIN
            EFSTRUCTFORM EFM                                                                                                                                          
                ON EFM.OIDREVISIONFORM = RFM.OID          
        join
            emdatasetmodel dsm                  
                on dsm.oid = efm.oiddatasetmodel           
        join
            setext txt                  
                on txt.oid = dsm.oidcommand                                                                  
        WHERE
            RFM.FGCURRENT = '1'                                                                                              
            AND EFM.FGTYPE = '23'                                                                                           
            AND EFM.FGHIDDENMOBILE = '2'  
        ) FM           
    WHERE
        IDFORMULARIO = '$formulary_id'         
    ORDER BY
        IDFORMULARIO,
        GRUPO,
        ORDEN";


    $result =  DB::connection('sqlsrv')->select($query);  
 
    return $result;
    }

    


    public function GetRules($formulary_id)
    {
        $query = "SELECT rules FROM dynreglasportal where idform ='$formulary_id' ";
        $result =  DB::connection('sqlsrv')->select($query);
        return $result;
    }
   
}