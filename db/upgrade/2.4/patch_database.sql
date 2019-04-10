-- Patch to upgrade database to version 2.4

SET AUTOCOMMIT=0;

SOURCE application_type.sql
SOURCE application_type_has_role.sql
SOURCE application.sql
SOURCE application_has_site.sql

SOURCE access.sql
SOURCE service.sql
SOURCE role_has_service.sql
SOURCE setting.sql
SOURCE writelog.sql

SOURCE technician.sql
SOURCE interview.sql
SOURCE stage_type.sql
SOURCE study_data_has_stage_type.sql
SOURCE stage.sql
SOURCE indicator.sql

SOURCE blood_data.sql
SOURCE blood_pressure_data.sql
SOURCE body_composition_data.sql
SOURCE carotid_intima_data.sql
SOURCE cdtt_data.sql
SOURCE chair_rise_data.sql
SOURCE cognition_recording_data.sql
SOURCE cognitive_test_data.sql
SOURCE conclusion_qnaire_data.sql
SOURCE contraindication_qnaire_data.sql
SOURCE deviation_aecrf_data.sql
SOURCE dexa_data.sql
SOURCE dexa_forearm_data.sql
SOURCE dexa_hip_data.sql
SOURCE dexa_lateral_data.sql
SOURCE dexa_qnaire_data.sql
SOURCE dexa_spine_data.sql
SOURCE dexa_whole_body_data.sql
SOURCE disease_qnaire_data.sql
SOURCE ecg_data.sql
SOURCE event_pmt_data.sql
SOURCE fas_data.sql
SOURCE frax_data.sql
SOURCE functional_status_data.sql
SOURCE general_health_data.sql
SOURCE grip_strength_data.sql
SOURCE hearing_data.sql
SOURCE height_weight_data.sql
SOURCE hips_waist_data.sql
SOURCE inhome_1_data.sql
SOURCE inhome_2_data.sql
SOURCE inhome_3_data.sql
SOURCE inhome_4_data.sql
SOURCE inhome_cognition_recording_data.sql
SOURCE inhome_conclusion_qnaire_data.sql
SOURCE inhome_consent_data.sql
SOURCE inhome_id_data.sql
SOURCE inhome_qa_data.sql
SOURCE neuro_battery_data.sql
SOURCE neuro_scoring_data.sql
SOURCE os_data.sql
SOURCE osea_data.sql
SOURCE osipv_data.sql
SOURCE osonly_data.sql
SOURCE retinal_scan_data.sql
SOURCE retinal_scan_left_data.sql
SOURCE retinal_scan_right_data.sql
SOURCE sitting_height_data.sql
SOURCE social_network_data.sql
SOURCE spirometry_data.sql
SOURCE standing_balance_data.sql
SOURCE standing_height_data.sql
SOURCE stroop_data.sql
SOURCE time_based_pmt_data.sql
SOURCE tonometer_data.sql
SOURCE tug_data.sql
SOURCE urine_data.sql
SOURCE vision_acuity_data.sql
SOURCE walk_data.sql
SOURCE weight_data.sql

SOURCE update_version_number.sql

COMMIT;

