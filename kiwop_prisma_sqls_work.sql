
SELECT COUNT(*) FROM wp_posts WHERE post_type='video'

SELECT DISTINCT taxonomy FROM wp_term_taxonomy

UPDATE `wp_kpr_scrappeddata` SET sistema = 'android'


SELECT COUNT(ID) AS total_by_source 
FROM `wp_kpr_scrappeddata`
GROUP BY `source`
ORDER BY `source`

SELECT COUNT(ID) AS total_by_source, `post_type`
FROM `wp_kpr_scrappeddata`
GROUP BY `post_type`
ORDER BY `post_type`

SELECT    
    DATE(created_at),
    COUNT(*) AS total_records
FROM
    wp_kpr_scrappeddata
GROUP BY
    DATE(created_at)
ORDER BY
    DATE(created_at) DESC;

DELETE FROM `wp_posts` WHERE id >= 5728;

DELETE FROM `wp_kpr_typologyvalues`;
DELETE FROM `wp_kpr_typologies`;
DELETE FROM `wp_kpr_scrappeddata`;

SELECT * FROM wp_kpr_scrappeddata 
            WHERE 
                source='apliense' AND description IS NOT NULL
                
UPDATE wp_kpr_scrappeddata SET `extra_data_json` = `tipologias_json` 
WHERE extra_data_json IS NULL AND `tipologias_json` IS NOT NULL


SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(url_import, '/', -1), '&', 1) AS id_recurs FROM wp_kpr_scrappeddata WHERE 
source = 'merli'
ORDER BY updated_at


AND 
FROM 
 
 
 SELECT COUNT(id) FROM wp_kpr_scrappeddata 
WHERE updated_at < '2024-01-01 00:00:00' 
 AND `source` = 'merli' 
ORDER BY updated_at

 WHERE 
   `source` = 'merli' 
 
 
SELECT s.*
FROM wp_kpr_scrappeddata s
WHERE JSON_CONTAINS(s.extra_data_json, 'Escrit', '$.tipo_recurso') 
 AND s.extra_data_json IS NOT `wp_kpr_scrappeddata`NULL
 
 
 SELECT extra_data_json->'$.Recurs educatiu:' AS tipo_recurso, COUNT(id) AS total FROM wp_kpr_scrappeddata
 WHERE source = 'merli'
 GROUP BY extra_data_json->'$.Recurs educatiu:'
 ORDER BY extra_data_json->'$.Recurs educatiu:'

 
 SELECT extra_data_json->'$.tipo_recurso' AS tipo_recurso, COUNT(id) AS total FROM wp_kpr_scrappeddata
 WHERE source = 'merli'
 GROUP BY extra_data_json->'$.tipo_recurso'
 ORDER BY extra_data_json->'$.tipo_recurso'

SELECT JSON_EXTRACT(extra_data_json, '$."Recurs educatiu:"') AS Recurs_educatiu,
       COUNT(id) AS total
FROM wp_kpr_scrappeddata
WHERE source = 'merli'
GROUP BY Recurs_educatiu
ORDER BY Recurs_educatiu;

SELECT id FROM wp_kpr_scrappeddata WHERE 
`url_import` = "https://merli.xtec.cat/merli/cerca/fitxaRecurs.jsp?idRecurs=/134084&sheetId=null&nomUsuari=null&inxtec=0" 
AND `source` = 'merli'  
 
SELECT scr.* 
FROM wp_kpr_scrappeddata scr 
WHERE 1 = 1  
  AND scr.extra_data_json->'$.app-competence' LIKE '%audiovisual'  
  AND scr.source = 'toolbox'  
ORDER BY scr.created_at  
DESC LIMIT 0, 10 

      SELECT scr.* 
            FROM wp_kpr_scrappeddata scr 
            WHERE 1 = 1  
         AND JSON_UNQUOTE(JSON_EXTRACT(scr.extra_data_json, '$."app-area"')) LIKE '%Matemàtiques%'  AND scr.source = 'toolbox'  ORDER BY scr.created_at  DESC LIMIT 0, 10 
 
 
 
 
SELECT COUNT(SUBSTRING_INDEX(SUBSTRING_INDEX(url_import, '/', -1), '&', 1)) AS id_recurs 
FROM wp_kpr_scrappeddata 
WHERE source = 'merli' AND updated_at > DATE_SUB(NOW(), INTERVAL 100 MINUTE);


SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(url_import, '/', -1), '&', 1) AS id_recurs 
FROM wp_kpr_scrappeddata 
WHERE source = 'merli'
 AND updated_at >= DATE_SUB(NOW(), INTERVAL 5 HOUR)
ORDER BY updated_at DESC

UPDATE wp_kpr_scrappeddata
SET description = SUBSTRING_INDEX(description, '<!-- Relacions -->', 1)
WHERE description LIKE '%<!-- Relacions -->%'
AND id = 62550
 
 
 
             SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(url_import, '/', -1), '&', 1) AS id_recurs 
            FROM wp_kpr_scrappeddata 
            WHERE source = 'merli'
            AND updated_at > '2024-01-01 00:00:00' 
            ORDER BY updated_at DESC    