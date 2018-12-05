<?php
require_once (dirname(__FILE__).'/../settings.ini.php');
require_once $SETTINGS['path']['APPLICATION'].'/web/scripts/common.php';

$min_rank=1;
$max_rank=3;
$sql =
  'select '.
  'min(start_date) as min_date, '.
  'max(start_date) as max_date, '.
  'rank '.
  'from interview '.
  'group by rank';
$date_ranges = json_encode($db->get_all( $sql ));
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CLSA-&Eacute;LCV QAC</title>
    <link rel="stylesheet" type="text/css" href="css/qac.css">
    <link rel="stylesheet" type="text/css" href="css/duration-picker.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="scripts/duration-picker.min.js"></script>
    <script>
      var stat_stages =[
        'scripts/bloodTable.php',
        'scripts/body_composition_weightTable.php',
        'scripts/bone_density_questionnaireTable.php',
        'scripts/carotid_intima_cineloopTable.php',
        'scripts/carotid_intima_srTable.php',
        'scripts/carotid_intima_stillTable.php',
        'scripts/cdttTable.php',
        'scripts/cognitive_testTable.php',
        'scripts/cognition_recordingTable.php',
        'scripts/contraindicationsTable.php',
        'scripts/dual_hip_bone_densityTable.php',
        'scripts/deviation_aecrfTable.php',
        'scripts/disease_symptomsTable.php',
        'scripts/ecgTable.php',
        'scripts/event_pmtTable.php',
        'scripts/forearm_bone_densityTable.php',
        'scripts/fraxTable.php',
        'scripts/functional_statusTable.php',
        'scripts/general_healthTable.php',
        'scripts/inhome_cognition_recordingTable.php',
        'scripts/lateral_bone_densityTable.php',
        'scripts/osipvTable.php',
        'scripts/quality_assurance_inhomeTable.php',
        'scripts/retinal_scan_leftTable.php',
        'scripts/retinal_scan_rightTable.php',
        'scripts/retinal_scanTable.php',
        'scripts/social_networkTable.php',
        'scripts/spine_bone_densityTable.php',
        'scripts/stroop_fasTable.php',
        'scripts/time_based_pmtTable.php',
        'scripts/urineTable.php',
        'scripts/whole_body_bone_densityTable.php'];
      var date_json = <?php echo $date_ranges; ?>;
      var date_ranges = [];
      date_json.forEach(function(obj){
        date_ranges[obj.rank]={'min_date':obj.min_date,'max_date':obj.max_date};
      });
      $(function(){
        var dp1 = $('#stage-dur-min').durationPicker();
        var dp2 = $('#stage-dur-max').durationPicker();
        var dp3 = $('#module-dur-min').durationPicker();
        var dp4 = $('#module-dur-max').durationPicker();

        $('#stage').change( function() {
          $('#qacform').attr('action', $(this).val());
          console.log('changing qac action to : '+$(this).val());
          if(stat_stages.indexOf($(this).val()) > -1) {
            $('#stat-option').prop('disabled',false).change();
          } else {
            $('#stat-option').prop('disabled',true).change();
          }
        }).change();

        $('#duration').change( function() {
          $('#qacform').attr('action', $(this).val());
          console.log('changing duration action to : '+$(this).val());
          var defdur = {hours: 0, minutes: 0, seconds: 0};
          dp1.setvalues(defdur);
          dp2.setvalues(defdur);
          dp3.setvalues(defdur);
          dp4.setvalues(defdur);
        }).change();

        $('#from').datepicker({
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          showAnim: 'blind',
          onSelect: function(dateText, inst){
            var newDate = new Date(dateText);
            $('#to').datepicker('option','minDate', newDate);
          }
        });

        $('#to').datepicker({
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          showAnim: 'blind',
          onSelect: function(dateText, inst){
            var newDate = new Date(dateText);
            $('#from').datepicker('option','maxDate', newDate);
          }
        });

        $('#rank').bind('keyup change', function(e) {
          var num = $(this).val();
          console.log('new wave: '+num);
          $('#to, #from').datepicker('setDate',null);
          $('#to, #from').datepicker('option','minDate',date_ranges[num].min_date);
          $('#to, #from').datepicker('option','maxDate',date_ranges[num].max_date);
          $('#from').datepicker('option','defaultDate',date_ranges[num].min_date);
          $('#to').datepicker('option','defaultDate',date_ranges[num].max_date);
          $('#stage').val('scripts/bloodTable.php');
          $('#duration').val('scripts/bloodDuration.php');

          if(3==num) {

            $('#stage option[value="scripts/cdttTable.php"]').show();
            $('#stage option[value="scripts/deviation_aecrfTable.php"]').show();
            $('#stage option[value="scripts/fraxTable.php"]').show();
            $('#stage option[value="scripts/functional_statusTable.php"]').hide();
            $('#stage option[value="scripts/general_healthTable.php"]').show();
            $('#stage option[value="scripts/inhome_idTable.php"]').hide();
            $('#stage option[value="scripts/inhome_4Table.php"]').show();
            $('#stage option[value="scripts/oseaTable.php"]').hide();
            $('#stage option[value="scripts/oseaTable.php"]').hide();
            $('#stage option[value="scripts/osonlyTable.php"]').hide();
            $('#stage option[value="scripts/quality_assurance_inhomeTable.php"]').show();
            $('#stage option[value="scripts/retinal_scan_leftTable.php"]').show();
            $('#stage option[value="scripts/retinal_scan_rightTable.php"]').show();
            $('#stage option[value="scripts/retinal_scanTable.php"]').hide();
            $('#stage option[value="scripts/sitting_heightTable.php"]').show();
            $('#stage option[value="scripts/social_networkTable.php"]').show();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').show();
            $('#stage option[value="scripts/stroop_fasTable.php"]').show();

            $('#duration option[value="scripts/body_composition_weightDuration.php"]').hide();
            $('#duration option[value="scripts/cdttDuration.php"]').show();
            $('#duration option[value="scripts/deviation_aecrfDuration.php"]').show();
            $('#duration option[value="scripts/fraxDuration.php"]').show();
            $('#duration option[value="scripts/functional_statusDuration.php"]').hide();
            $('#duration option[value="scripts/general_healthDuration.php"]').show();
            $('#duration option[value="scripts/inhome_idDuration.php"]').hide();
            $('#duration option[value="scripts/inhome_4Duration.php"]').show();
            $('#duration option[value="scripts/neuropsychological_batteryDuration.php"]').hide();
            $('#duration option[value="scripts/oseaDuration.php"]').hide();
            $('#duration option[value="scripts/osipvDuration.php"]').show();
            $('#duration option[value="scripts/osonlyDuration.php"]').hide();
            $('#duration option[value="scripts/quality_assurance_inhomeDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scan_leftDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scan_rightDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scanDuration.php"]').hide();
            $('#duration option[value="scripts/sitting_heightDuration.php"]').show();
            $('#duration option[value="scripts/social_networkDuration.php"]').show();
            $('#duration option[value="scripts/spine_bone_densityDuration.php"]').show();
            $('#duration option[value="scripts/stroop_fasDuration.php"]').show();
          } else if(2==num) {

            $('#stage option[value="scripts/cdttTable.php"]').hide();
            $('#stage option[value="scripts/deviation_aecrfTable.php"]').hide();
            $('#stage option[value="scripts/fraxTable.php"]').show();
            $('#stage option[value="scripts/functional_statusTable.php"]').show();
            $('#stage option[value="scripts/general_healthTable.php"]').show();
            $('#stage option[value="scripts/inhome_idTable.php"]').hide();
            $('#stage option[value="scripts/inhome_4Table.php"]').show();
            $('#stage option[value="scripts/oseaTable.php"]').show();
            $('#stage option[value="scripts/osipvTable.php"]').hide();
            $('#stage option[value="scripts/osonlyTable.php"]').show();
            $('#stage option[value="scripts/quality_assurance_inhomeTable.php"]').show();
            $('#stage option[value="scripts/retinal_scan_leftTable.php"]').show();
            $('#stage option[value="scripts/retinal_scan_rightTable.php"]').show();
            $('#stage option[value="scripts/retinal_scanTable.php"]').hide();
            $('#stage option[value="scripts/sitting_heightTable.php"]').hide();
            $('#stage option[value="scripts/social_networkTable.php"]').hide();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').show();
            $('#stage option[value="scripts/stroop_fasTable.php"]').hide();

            $('#duration option[value="scripts/body_composition_weightDuration.php"]').show();
            $('#duration option[value="scripts/cdttDuration.php"]').hide();
            $('#duration option[value="scripts/deviation_aecrfDuration.php"]').hide();
            $('#duration option[value="scripts/fraxDuration.php"]').show();
            $('#duration option[value="scripts/functional_statusDuration.php"]').show();
            $('#duration option[value="scripts/general_healthDuration.php"]').show();
            $('#duration option[value="scripts/inhome_idDuration.php"]').hide();
            $('#duration option[value="scripts/inhome_4Duration.php"]').show();
            $('#duration option[value="scripts/neuropsychological_batteryDuration.php"]').show();
            $('#duration option[value="scripts/osipvDuration.php"]').hide();
            $('#duration option[value="scripts/oseaDuration.php"]').show();
            $('#duration option[value="scripts/osonlyDuration.php"]').show();
            $('#duration option[value="scripts/quality_assurance_inhomeDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scan_leftDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scan_rightDuration.php"]').show();
            $('#duration option[value="scripts/retinal_scanDuration.php"]').hide();
            $('#duration option[value="scripts/sitting_heightDuration.php"]').hide();
            $('#duration option[value="scripts/social_networkDuration.php"]').hide();
            $('#duration option[value="scripts/spine_bone_densityDuration.php"]').show();
            $('#duration option[value="scripts/stroop_fasDuration.php"]').hide();
          } else if(1==num) {

            $('#stage option[value="scripts/cdttTable.php"]').hide();
            $('#stage option[value="scripts/deviation_aecrfTable.php"]').hide();
            $('#stage option[value="scripts/fraxTable.php"]').hide();
            $('#stage option[value="scripts/general_healthTable.php"]').hide();
            $('#stage option[value="scripts/inhome_idTable.php"]').show();
            $('#stage option[value="scripts/inhome_4Table.php"]').hide();
            $('#stage option[value="scripts/osipvTable.php"]').hide();
            $('#stage option[value="scripts/oseaTable.php"]').hide();
            $('#stage option[value="scripts/osipvTable.php"]').hide();
            $('#stage option[value="scripts/osonlyTable.php"]').hide();
            $('#stage option[value="scripts/quality_assurance_inhomeTable.php"]').hide();
            $('#stage option[value="scripts/retinal_scan_leftTable.php"]').hide();
            $('#stage option[value="scripts/retinal_scan_rightTable.php"]').hide();
            $('#stage option[value="scripts/retinal_scanTable.php"]').show();
            $('#stage option[value="scripts/sitting_heightTable.php"]').hide();
            $('#stage option[value="scripts/social_networkTable.php"]').hide();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').hide();
            $('#stage option[value="scripts/stroop_fasTable.php"]').hide();

            $('#duration option[value="scripts/body_composition_weightDuration.php"]').hide();
            $('#duration option[value="scripts/cdttDuration.php"]').hide();
            $('#duration option[value="scripts/deviation_aecrfDuration.php"]').hide();
            $('#duration option[value="scripts/fraxDuration.php"]').hide();
            $('#duration option[value="scripts/general_healthDuration.php"]').hide();
            $('#duration option[value="scripts/inhome_idDuration.php"]').show();
            $('#duration option[value="scripts/inhome_4Duration.php"]').hide();
            $('#duration option[value="scripts/neuropsychological_batteryDuration.php"]').show();
            $('#duration option[value="scripts/osipvDuration.php"]').hide();
            $('#duration option[value="scripts/oseaDuration.php"]').hide();
            $('#duration option[value="scripts/osonlyDuration.php"]').hide();
            $('#duration option[value="scripts/quality_assurance_inhomeDuration.php"]').hide();
            $('#duration option[value="scripts/retinal_scan_leftDuration.php"]').hide();
            $('#duration option[value="scripts/retinal_scan_rightDuration.php"]').hide();
            $('#duration option[value="scripts/retinal_scanDuration.php"]').show();
            $('#duration option[value="scripts/sitting_heightDuration.php"]').show();
            $('#duration option[value="scripts/social_networkDuration.php"]').hide();
            $('#duration option[value="scripts/spine_bone_densityDuration.php"]').hide();
            $('#duration option[value="scripts/stroop_fasDuration.php"]').hide();
          }
          $('#from').val(date_ranges[num].min_date);
          $('#to').val(date_ranges[num].max_date);
          console.log('date range: ' + date_ranges[num].min_date + ' -> ' + date_ranges[num].max_date);
        });
      });
    </script>
  </head>
  <body>
    <div>
      <h1 class="title">CLSA-&Eacute;LCV QAC</h1>
    </div>
    <div class="view">
      <span class="help">
        <ul>
          <li>Please select the wave of interest (eg., 1 = baseline)</li>
          <li>Enter a start date and an end date for the report</li>
          <li>Select an interview stage</li>
        </ul>
      </span>
      <form id="qacform" action="scripts/bloodTable.php" method="POST">
        <div>
          <label for="rank">Wave:</label>
          <input type="number" id="rank" name="rank"
            <?php echo "min={$min_rank} max={$max_rank} step=\"1\" value={$min_rank}"; ?>>
        </div>
        <div>
          <label for="from">Start Date:</label>
          <input type="text" id="from" name="from">
        </div>
        <div>
          <label for="to">End Date:</label>
          <input type="text" id="to" name="to">
        </div>
        <div>
          <label for="stage">Stage QAC:</label>
          <select name="stage" id="stage">
            <option value="scripts/bloodTable.php">Blood Qnaire</option>
            <option value="scripts/blood_pressureTable.php">Blood Pressure</option>
            <option value="scripts/body_composition_weightTable.php">Body Composition Weight</option>
            <option value="scripts/cdttTable.php">CDTT</option>
            <option value="scripts/chair_riseTable.php">Chair Rise</option>
            <option value="scripts/cognition_recordingTable.php">Cognition Recording</option>
            <option value="scripts/cognitive_testTable.php">Cognitive Test</option>
            <option value="scripts/conclusion_questionnaireTable.php">Conclusion Qnaire</option>
            <option value="scripts/contraindicationsTable.php">ContraIndications Qnaire</option>
            <option value="scripts/carotid_intima_stillTable.php">Carotid Intima Still</option>
            <option value="scripts/carotid_intima_cineloopTable.php">Carotid Intima Cineloop</option>
            <option value="scripts/carotid_intima_srTable.php">Carotid Intima SR</option>
            <option value="scripts/deviation_aecrfTable.php">Deviation AE CRF Qnaire</option>
            <option value="scripts/disease_symptomsTable.php">Disease Symptoms Qnaire</option>
            <option value="scripts/bone_density_questionnaireTable.php">DEXA Bone Density Qnaire</option>
            <option value="scripts/dual_hip_bone_densityTable.php">DEXA Dual Hip</option>
            <option value="scripts/forearm_bone_densityTable.php">DEXA Forearm</option>
            <option value="scripts/lateral_bone_densityTable.php">DEXA Lateral</option>
            <option value="scripts/spine_bone_densityTable.php">DEXA Spine</option>
            <option value="scripts/whole_body_bone_densityTable.php">DEXA Whole Body</option>
            <option value="scripts/fraxTable.php">DEXA Frax</option>
            <option value="scripts/ecgTable.php">ECG</option>
            <option value="scripts/event_pmtTable.php">Event PMT Qnaire</option>
            <option value="scripts/four_metre_walkTable.php">4 m Walk</option>
            <option value="scripts/functional_statusTable.php">Functional Status Qnaire</option>
            <option value="scripts/general_healthTable.php">General Health Qnaire</option>
            <option value="scripts/grip_strengthTable.php">Grip Strength</option>
            <option value="scripts/hearingTable.php">Hearing</option>
            <option value="scripts/hips_waistTable.php">Hips Waist</option>
            <option value="scripts/inhome_idTable.php">Inhome id Qnaire</option>
            <option value="scripts/inhome_1Table.php">Inhome 1 Qnaire</option>
            <option value="scripts/inhome_2Table.php">Inhome 2 Qnaire</option>
            <option value="scripts/inhome_3Table.php">Inhome 3 Qnaire</option>
            <option value="scripts/inhome_4Table.php">Inhome 4 Qnaire</option>
            <option value="scripts/inhome_cognition_recordingTable.php">InHome Cognition Recording</option>
            <option value="scripts/oseaTable.php">OSEA Qnaire</option>
            <option value="scripts/osipvTable.php">OSIPV Qnaire</option>
            <option value="scripts/osonlyTable.php">OSOnly Qnaire</option>
            <option value="scripts/quality_assurance_inhomeTable.php">Quality Assurance IH Qnaire</option>
            <option value="scripts/retinal_scan_leftTable.php">Retinal Scan Left</option>
            <option value="scripts/retinal_scan_rightTable.php">Retinal Scan Right</option>
            <option value="scripts/retinal_scanTable.php">Retinal Scan</option>
            <option value="scripts/sitting_heightTable.php">Sitting Height</option>
            <option value="scripts/social_networkTable.php">Social Network Qnaire</option>
            <option value="scripts/spirometryTable.php">Spirometry</option>
            <option value="scripts/standing_balanceTable.php">Standing Balance</option>
            <option value="scripts/standing_heightTable.php">Standing Height</option>
            <option value="scripts/stroop_fasTable.php">Stroop FAS</option>
            <option value="scripts/time_based_pmtTable.php">Time Based PMT Qnaire</option>
            <option value="scripts/tonometerTable.php">Tonometer</option>
            <option value="scripts/tugTable.php">TUG</option>
            <option value="scripts/urineTable.php">Urine Qnaire</option>
            <option value="scripts/vision_acuityTable.php">Vision Acuity</option>
            <option value="scripts/weightTable.php">Weight</option>
          </select>
        </div>
        <div class="stat-option">
          <label for="stat-option">Statistic:</label>
          <select name="stat-option" id="stat-option">
            <option value="mode">Mode Calculation</option>
            <option value="mean">Mean Calculation</option>
          </select>
        </div>
        <div>
          <label for="par-qac-min">Par min:</label>
          <input type="text" id="par-qac-min" name="par-qac-min"><br>
          <label for="par-qac-max">Par max:</label>
          <input type="text" id="par-qac-max" name="par-qac-max"><br>
        </div>
        <div class="button">
          <button type="submit" name="button_stage" value="stage">Request Data</button>
        </div>
        <div>
          <label for="duration">Stage Duration:</label>
          <select name="duration" id="duration">
            <option value="scripts/bloodDuration.php">Blood Qnaire</option>
            <option value="scripts/blood_pressureDuration.php">Blood Pressure</option>
            <option value="scripts/body_composition_weightDuration.php">Body Composition Weight</option>
            <option value="scripts/cdttDuration.php">CDTT</option>
            <option value="scripts/chair_riseDuration.php">Chair Rise</option>
            <option value="scripts/cognition_recordingDuration.php">Cognition Recording</option>
            <option value="scripts/cognitive_testDuration.php">Cognitive Test</option>
            <option value="scripts/conclusion_questionnaireDuration.php">Conclusion Qnaire</option>
            <option value="scripts/contraindicationsDuration.php">ContraIndications Qnaire</option>
            <option value="scripts/carotid_intimaDuration.php">Carotid Intima</option>
            <option value="scripts/deviation_aecrfDuration.php">Deviation AE CRF Qnaire</option>
            <option value="scripts/disease_symptomsDuration.php">Disease Symptoms Qnaire</option>
            <option value="scripts/bone_density_questionnaireDuration.php">DEXA Bone Density Qnaire</option>
            <option value="scripts/dual_hip_bone_densityDuration.php">DEXA Dual Hip</option>
            <option value="scripts/forearm_bone_densityDuration.php">DEXA Forearm</option>
            <option value="scripts/lateral_bone_densityDuration.php">DEXA Lateral</option>
            <option value="scripts/spine_bone_densityDuration.php">DEXA Spine</option>
            <option value="scripts/whole_body_bone_densityDuration.php">DEXA Whole Body</option>
            <option value="scripts/fraxDuration.php">DEXA Frax</option>
            <option value="scripts/ecgDuration.php">ECG</option>
            <option value="scripts/event_pmtDuration.php">Event PMT Qnaire</option>
            <option value="scripts/four_metre_walkDuration.php">4 m Walk</option>
            <option value="scripts/functional_statusDuration.php">Functional Status Qnaire</option>
            <option value="scripts/general_healthDuration.php">General Health Qnaire</option>
            <option value="scripts/grip_strengthDuration.php">Grip Strength</option>
            <option value="scripts/hearingDuration.php">Hearing</option>
            <option value="scripts/hips_waistDuration.php">Hips Waist</option>
            <option value="scripts/inhome_idDuration.php">InHome id Qnaire</option>
            <option value="scripts/inhome_1Duration.php">InHome 1 Qnaire</option>
            <option value="scripts/inhome_2Duration.php">InHome 2 Qnaire</option>
            <option value="scripts/inhome_3Duration.php">InHome 3 Qnaire</option>
            <option value="scripts/inhome_4Duration.php">InHome 4 Qnaire</option>
            <option value="scripts/inhome_cognition_recordingDuration.php">InHome Cognition Recording</option>
            <option value="scripts/neuropsychological_batteryDuration.php">Neuropsychological Battery</option>
            <option value="scripts/oseaDuration.php">OSEA Qnaire</option>
            <option value="scripts/osipvDuration.php">OSIPV Qnaire</option>
            <option value="scripts/osonlyDuration.php">OSOnly Qnaire</option>
            <option value="scripts/quality_assurance_inhomeDuration.php">Quality Assurance IH Qnaire</option>
            <option value="scripts/retinal_scanDuration.php">Retinal Scan</option>
            <option value="scripts/retinal_scan_leftDuration.php">Retinal Scan Left</option>
            <option value="scripts/retinal_scan_rightDuration.php">Retinal Scan Right</option>
            <option value="scripts/sitting_heightDuration.php">Sitting Height</option>
            <option value="scripts/social_networkDuration.php">Social Network Qnaire</option>
            <option value="scripts/spirometryDuration.php">Spirometry</option>
            <option value="scripts/standing_balanceDuration.php">Standing Balance</option>
            <option value="scripts/standing_heightDuration.php">Standing Height</option>
            <option value="scripts/stroop_fasDuration.php">Stroop FAS</option>
            <option value="scripts/time_based_pmtDuration.php">Time Based PMT Qnaire</option>
            <option value="scripts/tonometerDuration.php">Tonometer</option>
            <option value="scripts/tugDuration.php">TUG</option>
            <option value="scripts/urineDuration.php">Urine Qnaire</option>
            <option value="scripts/vision_acuityDuration.php">Vision Acuity</option>
            <option value="scripts/weightDuration.php">Weight</option>
          </select>
        </div>
        <div>
          <label for="stage-dur-min">Stage par min:</label>
          <input type="text" id="stage-dur-min" name="stage-dur-min"><br>
          <label for="stage-dur-max">Stage par max:</label>
          <input type="text" id="stage-dur-max" name="stage-dur-max"><br>
          <label for="module-dur-min">Module par min:</label>
          <input type="text" id="module-dur-min" name="module-dur-min"><br>
          <label for="module-dur-max">Module par max:</label>
          <input type="text" id="module-dur-max" name="module-dur-max"><br>
        </div>
        <div class="button">
          <button type="submit" name="button_duration" value="duration">Request Data</button>
        </div>
      <form>
    </div>
  </body>
</html>
