SELECT "Adding new services" AS "";

INSERT IGNORE INTO service ( subject, method, resource, restricted ) VALUES
( 'opal_view', 'DELETE', 1, 1 ),
( 'opal_view', 'GET', 0, 1 ),
( 'opal_view', 'GET', 1, 1 ),
( 'opal_view', 'PATCH', 1, 1 ),
( 'opal_view', 'POST', 0, 1 ),
( 'study_phase', 'GET', 0, 0 ),
( 'study_phase', 'GET', 1, 0 );
