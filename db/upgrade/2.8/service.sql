SELECT "Adding new services" AS "";

DELETE FROM service WHERE subject RLIKE "^(bl|f1|f2|f3)_*" OR subject = "setting";

INSERT IGNORE INTO service ( subject, method, resource, restricted ) VALUES
( 'comment', 'GET', 0, 0 ),
( 'comment', 'GET', 1, 0 ),
( 'opal_view', 'DELETE', 1, 1 ),
( 'opal_view', 'GET', 0, 1 ),
( 'opal_view', 'GET', 1, 1 ),
( 'opal_view', 'PATCH', 1, 1 ),
( 'opal_view', 'POST', 0, 1 ),
( 'region', 'GET', 0, 0 ),
( 'region', 'GET', 1, 0 ),
( 'indicator_issue', 'GET', 0, 1 ),
( 'indicator_issue', 'GET', 1, 1 ),
( 'indicator_issue', 'PATCH', 1, 1 ),
( 'indicator_issue_note', 'DELETE', 1, 1 ),
( 'indicator_issue_note', 'GET', 0, 1 ),
( 'indicator_issue_note', 'GET', 1, 1 ),
( 'indicator_issue_note', 'PATCH', 1, 1 ),
( 'indicator_issue_note', 'POST', 0, 1 ),
( 'stage_issue', 'GET', 0, 1 ),
( 'stage_issue', 'GET', 1, 1 ),
( 'stage_issue', 'PATCH', 1, 1 ),
( 'stage_issue_note', 'DELETE', 1, 1 ),
( 'stage_issue_note', 'GET', 0, 1 ),
( 'stage_issue_note', 'GET', 1, 1 ),
( 'stage_issue_note', 'PATCH', 1, 1 ),
( 'stage_issue_note', 'POST', 0, 1 ),
( 'study_phase', 'GET', 0, 0 ),
( 'study_phase', 'GET', 1, 0 ),
( 'stage_type', 'PATCH', 1, 1 );
