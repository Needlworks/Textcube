CREATE OR REPLACE FUNCTION from_unixtime(integer) RETURNS timestamp AS '
SELECT
$1::abstime::timestamp without time zone AS result
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION unix_timestamp() RETURNS integer AS '
SELECT
ROUND(EXTRACT( EPOCH FROM abstime(now()) ))::int4 AS result;
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION unix_timestamp(timestamp with time zone) RETURNS integer AS '
SELECT
ROUND(EXTRACT( EPOCH FROM ABSTIME($1) ))::int4 AS result;
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION dayofmonth(timestamp without time zone) RETURNS numeric AS '
SELECT
to_number(to_char($1, \'DD\'),\'99\') AS result;
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION year(timestamp without time zone) RETURNS numeric AS '
SELECT 
to_number(to_char($1, \'YYYY\'),\'99\') AS result;
' LANGUAGE 'SQL';
CREATE OR REPLACE FUNCTION month(timestamp without time zone) RETURNS numeric AS '
SELECT 
to_number(to_char($1, \'MM\'),\'99\') AS result;
' LANGUAGE 'SQL';