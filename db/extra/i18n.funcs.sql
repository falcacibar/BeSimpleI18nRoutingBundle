-- @info funcion i18n_trad_id_por_campo traer el id por campo ya traducido

DELIMITER ;;;;

DROP FUNCTION IF EXISTS `i18n_trad_id_por_campo`;;;;
CREATE FUNCTION `i18n_trad_id_por_campo`(`context` VARCHAR(256), `field` varchar(256), `locale` varchar(9), `content` text)
    RETURNS text
    LANGUAGE SQL
    NOT DETERMINISTIC
    READS SQL DATA
    SQL SECURITY INVOKER
COMMENT ''
BEGIN
    RETURN IFNULL((
                    SELECT tr.foreign_key
                    FROM ext_translations tr
                    WHERE tr.`field` = `field` AND tr.object_class LIKE CONCAT('%', context)
                    AND tr.locale = locale AND tr.content = content
                ), IF(
                    LENGTH(locale) > 2
                    , (
                        SELECT tr.foreign_key
                        FROM ext_translations tr
                        WHERE tr.`field` = `field` AND tr.object_class LIKE CONCAT('%', context)
                        AND tr.locale = SUBSTRING_INDEX(locale, '_', 1) AND tr.content = content
                    ) , NULL
               )
    );
END ;;;;

/*
DROP FUNCTION IF EXISTS `i18n_trad_campo`;;;;
CREATE OR REPLACE DEFINER=`root`@`localhost` FUNCTION `i18n_trad_campo`(`context` VARCHAR(256), `field` varchar(256), `locale` varchar(9), `content` text, `if_null` varchar(2048))

    RETURNS varchar(256)
    LANGUAGE SQL
    NOT DETERMINISTIC
    READS SQL DATA
    SQL SECURITY INVOKER
    COMMENT ''
BEGIN
    RETURN IFNULL((
                SELECT content
                FROM   ext_translations 
                WHERE  ext_translations.locale = locale
                       AND ext_translations.object_classi LIKE CONCAT('%', context)
                       AND ext_translations.`field`= `field`
                       AND ext_translations.id = id
                ), IF(
                        LENGTH(locale) > 2
                        , IFNULL(( 
                          SELECT content
                          FROM   ext_translations 
                          WHERE  ext_translations.locale = SUBSTRING_INDEX(locale, '_', 1)
                                 AND ext_translations.object_classLIKE CONCAT('%', context)
                                 AND ext_translations.`field`= `field`
                                 AND ext_translations .id= id
                        ), `if_null`
                ), if_null
    ));
END ;;;;
*/

DELIMITER ;
