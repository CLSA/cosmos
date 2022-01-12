SELECT "Adding new services" AS "";

DELETE FROM service WHERE subject RLIKE "^(bl|f1|f2|f3)_*";

INSERT IGNORE INTO service ( subject, method, resource, restricted ) VALUES
( 'opal_view', 'DELETE', 1, 1 ),
( 'opal_view', 'GET', 0, 1 ),
( 'opal_view', 'GET', 1, 1 ),
( 'opal_view', 'PATCH', 1, 1 ),
( 'opal_view', 'POST', 0, 1 ),
( 'study_phase', 'GET', 0, 0 ),
( 'study_phase', 'GET', 1, 0 );

