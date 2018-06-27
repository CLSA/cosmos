<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

class retinal_scan_generator extends table_generator
{
  public function set_statistic($_stat)
  {
    $this->statistic = $_stat;
  }

  public function set_standard_deviation_scale($_stdev)
  {
    $this->standard_deviation_scale = $_stdev;
  }

  protected function build_data()
  {
    global $db;

    $filesize_min=0;
    $filesize_max=0;
    if('mode' == $this->statistic)
    {
      $minsz=0;
      $maxsz=0;
      $mode=0;
      if(1 == $this->rank)
      {
        $sql=
          'select fsz, count(fsz) as freq from '.
          '('.
          '  ( '.
          '    select '.
          '      round( '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=1'.
          '    and qcdata is not null'.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round(trim( "}" from '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", -1 ), ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0 '.
          'group by fsz order by freq desc, fsz desc limit 1';

        $res = $db->get_row( $sql );
        $mode = $res['fsz'];

        $sql=
          'select min(fsz) as minsz, max(fsz) as maxsz from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round(trim( "}" from '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", -1 ), ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0';

        $res = $db->get_row( $sql );
        $minsz = $res['minsz'];
        $maxsz = $res['maxsz'];
      }
      else
      {
        $sql = sprintf(
          'select fsz, count(fsz) as freq from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1))/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_left" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0 '.
          'group by fsz order by freq desc, fsz desc limit 1', $this->rank, $this->rank);

        $res = $db->get_row( $sql );
        $mode = $res['fsz'];

        $sql = sprintf(
          'select min(fsz) as minsz, max(fsz) as maxsz from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1))/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_left" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0', $this->rank, $this->rank);

        $res = $db->get_row( $sql );
        $minsz = $res['minsz'];
        $maxsz = $res['maxsz'];
      }
      $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*1024),0);
      $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*1024);
    }
    else
    {
      $avg=0;
      $stdev=0;
      if(1 == $this->rank)
      {
        $sql=
          'select avg(fsz) as favg from '.
          '('.
          '  ( '.
          '    select '.
          '      round( '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=1'.
          '    and qcdata is not null'.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round(trim( "}" from '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", -1 ), ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0';

        $res = $db->get_row( $sql );
        $avg = $res['favg'];

        $sql =
          'select stddev(fsz) as fstd from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round(trim( "}" from '.
          '        substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", -1 ), ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0';

        $res = $db->get_row( $sql );
        $stdev = $res['fstd'];
      }
      else
      {
        $sql = sprintf(
          'select avg(fsz) as favg from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1))/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_left" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0 ', $this->rank, $this->rank);

        $res = $db->get_row( $sql );
        $avg = $res['favg'];

        $sql = sprintf(
          'select stddev(fsz) as fstd from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1))/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_left" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) )/1024.0,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0', $this->rank, $this->rank);

        $res = $db->get_row( $sql );
        $stdev = $res['fstd'];
      }

      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*1024),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev)*1024);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    if( 1 < $this->rank )
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(qcdata,":",-1))<%d,1,0))) as total_filesize_sub, ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(qcdata,":",-1)) between %d and %d,1,0))) as total_filesize_par, ',
         $filesize_min,$filesize_max);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(qcdata,":",-1))>%d,1,0))) as total_filesize_sup, ',$filesize_max);

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(s.name like "retinal_scan_left" and trim("}" from substring_index(qcdata,":",-1))>0,1,0))) as total_left_eye, ';

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(s.name like "retinal_scan_right" and trim("}" from substring_index(qcdata,":",-1))>0,1,0))) as total_right_eye, ';
    }
    else
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(substring_index(substring_index(qcdata,",",1),":",-1)<%d,1,0))) + ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1))<%d,1,0))) as total_filesize_sub, ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(substring_index(substring_index(qcdata,",",1),":",-1) between %d and %d,1,0))) + ',
         $filesize_min,$filesize_max);
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1)) between %d and %d,1,0))) as total_filesize_par, ',
         $filesize_min,$filesize_max);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(substring_index(substring_index(qcdata,",",1),":",-1)>%d,1,0))) + ',$filesize_max);
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1))>%d,1,0))) as total_filesize_sup, ',$filesize_max);

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(substring_index(substring_index(qcdata,",",1),":",-1)>0,1,0))) as total_left_eye, ';

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1))>0,1,0))) as total_right_eye, ';
    }

    $sql .= sprintf(
      'sum(case when strcmp(skip,"TechnicalProblem")=0 then 1 else 0 end) as total_skip_technical, '.
      'sum(case when strcmp(skip,"ParticipantDecision")=0 then 1 else 0 end) as total_skip_participant, '.
      'sum(case when strcmp(skip,"InterviewerDecision")=0 then 1 else 0 end) as total_skip_interviewer, '.
      'sum(case when strcmp(skip,"ModifiedVisit")=0 then 1 else 0 end) as total_skip_modified_visit, '.
      'sum(case when strcmp(skip,"SeeComment")=0 then 1 else 0 end) as total_skip_other, '.
      'sum(!isnull(skip)) as total_skip, '.
      'sum(if(missing,isnull(skip),0)) as total_unexplained_missing, '.
      'sum(contraindicated) as total_contraindicated, '.
      'sum(!isnull(t.name)) as total_tech, '.
      'sum(1) as total_interview '.
      'FROM interview i '.
      'join stage s on i.id=s.interview_id '.
      'join site on site.id=i.site_id '.
      'left join technician t on t.id=s.technician_id and t.site_id=site.id '.
      'where (start_date between "%s" and "%s") '.
      'and rank=%d '.
      'and s.name '.
      ( 1 == $this->rank ? '="retinal_scan" ' : 'in ("retinal_scan_left","retinal_scan_right") ').
      'group by site,tech', $this->begin_date, $this->end_date, $this->rank);

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation = array();
    if('mode'==$this->statistic)
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (min + 0.5 x (mode - min))', $filesize_min);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mode + 0.5 x (max - mode))', $filesize_max);
    }
    else
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (mean - %s x SD)', $filesize_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mean + %s x SD)', $filesize_max, $this->standard_deviation_scale);
    }
    $this->page_explanation[]='total number of left eye scans';
    $this->page_explanation[]='total number of right eye scans';
  }

  private $statistic;

  private $standard_deviation_scale;
}

$retinal_scan = new retinal_scan_generator(
  'retinal_scan',$rank,$begin_date,$end_date);

$qc_keys=array('total_filesize_sub','total_filesize_par','total_filesize_sup');
$percent_keys = array('total_left_eye','total_right_eye','total_skip','total_unexplained_missing','total_contraindicated');

$retinal_scan->set_indicator_keys($qc_keys);
$retinal_scan->set_percent_keys($percent_keys);
$retinal_scan->set_statistic($stat);
$retinal_scan->set_standard_deviation_scale(2);

$retinal_scan->build_table_data();

$retinal_scan->set_page_stage('RETINAL SCAN');

$qc_keys=array('total_filesize_sub','total_filesize_par','total_filesize_sup',
  'total_left_eye','total_right_eye');
$retinal_scan->set_indicator_keys($qc_keys);
echo $retinal_scan->build_table_html();
