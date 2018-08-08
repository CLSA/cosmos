<?php
require_once 'table_generator.class.php';

class retinal_scan_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_filesize_sub','total_filesize_par','total_filesize_sup');
    $this->standard_deviation_scale = 2;  // default
    $this->statistic = 'mean';            // default
    $this->file_scale = 1024.0;
  }

  public function set_statistic($_stat)
  {
    $this->statistic = $_stat;
  }

  public function set_standard_deviation_scale($_stdev)
  {
    $this->standard_deviation_scale = $_stdev;
  }

  public function set_file_scale( $_scale )
  {
    if( 0 < $_scale )
      $this->file_scale = $_scale;
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
        $sql=sprintf(
          'select fsz, count(fsz) as freq from '.
          '('.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1) as unsigned)/%s,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=1'.
          '    and qcdata is not null'.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim( "}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", -1 ), ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0 '.
          'group by fsz order by freq desc, fsz desc limit 1', $this->file_scale);

        $res = $db->get_row( $sql );
        $mode = $res['fsz'];

        $sql=sprintf(
          'select min(fsz) as minsz, max(fsz) as maxsz from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim( "}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", -1 ), ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0', $this->file_scale);

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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1) ) as unsigned)/%s,0) as fsz '.
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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0 '.
          'group by fsz order by freq desc, fsz desc limit 1',
          $this->file_scale, $this->rank,
          $this->file_scale, $this->rank);

        $res = $db->get_row( $sql );
        $mode = $res['fsz'];

        $sql = sprintf(
          'select min(fsz) as minsz, max(fsz) as maxsz from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1) ) as unsigned)/%s,0) as fsz '.
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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0',
          $this->file_scale, $this->rank,
          $this->file_scale, $this->rank);

        $res = $db->get_row( $sql );
        $minsz = $res['minsz'];
        $maxsz = $res['maxsz'];
      }
      $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*$this->file_scale),0);
      $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*$this->file_scale);
    }
    else
    {
      $avg=0;
      $stdev=0;
      if(1 == $this->rank)
      {
        $sql=sprintf(
          'select avg(fsz) as favg from '.
          '('.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1) as unsigned)/%s,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=1'.
          '    and qcdata is not null'.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim( "}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", -1 ), ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0',
          $this->file_scale, $this->file_scale);

        $res = $db->get_row( $sql );
        $avg = $res['favg'];

        $sql =sprintf(
          'select stddev(fsz) as fstd from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( substring_index( '.
          '          substring_index( '.
          '            qcdata, ",", 1 ), ":", -1) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          '  union all '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim( "}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", -1 ), ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=1 '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan" '.
          '  ) '.
          ') as t '.
          'where fsz>0',
          $this->file_scale, $this->file_scale);

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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1) ) as unsigned)/%s,0) as fsz '.
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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0',
          $this->file_scale, $this->rank,
          $this->file_scale, $this->rank);

        $res = $db->get_row( $sql );
        $avg = $res['favg'];

        $sql = sprintf(
          'select stddev(fsz) as fstd from '.
          '( '.
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1) ) as unsigned)/%s,0) as fsz '.
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
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            qcdata, ":", -1 ) ) as unsigned)/%s,0) as fsz '.
          '    from interview i '.
          '    join stage s on i.id=s.interview_id '.
          '    where rank=%d '.
          '    and qcdata is not null '.
          '    and s.name="retinal_scan_right" '.
          '  ) '.
          ') as t '.
          'where fsz>0',
          $this->file_scale, $this->rank,
          $this->file_scale, $this->rank);

        $res = $db->get_row( $sql );
        $stdev = $res['fstd'];
      }

      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*$this->file_scale),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev)*$this->file_scale);
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
        'if(cast(trim("}" from substring_index(qcdata,":",-1)) as unsigned)<%d,1,0))) as total_filesize_sub, ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(qcdata,":",-1)) as unsigned) between %d and %d,1,0))) as total_filesize_par, ',
         $filesize_min,$filesize_max);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(qcdata,":",-1)) as unsigned)>%d,1,0))) as total_filesize_sup, ',$filesize_max);

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(s.name like "retinal_scan_left" and cast(trim("}" from substring_index(qcdata,":",-1)) as signed)>0,1,0))) as total_left_eye, ';

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(s.name like "retinal_scan_right" and cast(trim("}" from substring_index(qcdata,":",-1)) as signed)>0,1,0))) as total_right_eye, ';
    }
    else
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as unsigned)<%d,1,0))) + ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1)) as unsigned)<%d,1,0))) as total_filesize_sub, ',$filesize_min);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as unsigned) between %d and %d,1,0))) + ',
         $filesize_min,$filesize_max);
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1)) as unsigned) between %d and %d,1,0))) as total_filesize_par, ',
         $filesize_min,$filesize_max);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as unsigned)>%d,1,0))) + ',$filesize_max);
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1)) as unsigned)>%d,1,0))) as total_filesize_sup, ',$filesize_max);

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)>0,1,0))) as total_left_eye, ';

      $sql .=
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",-1),":",-1)) as signed)>0,1,0))) as total_right_eye, ';
    }

    $sql .= sprintf(
      'sum(case when strcmp(skip,"TechnicalProblem")=0 then 1 else 0 end) as total_skip_technical, '.
      'sum(case when strcmp(skip,"ParticipantDecision")=0 then 1 else 0 end) as total_skip_participant, '.
      'sum(case when strcmp(skip,"InterviewerDecision")=0 then 1 else 0 end) as total_skip_interviewer, '.
      'sum(case when strcmp(skip,"ModifiedVisit")=0 then 1 else 0 end) as total_skip_modified_visit, '.
      'sum(case when strcmp(skip,"SeeComment")=0 then 1 else 0 end) as total_skip_other, '.
      'sum(!isnull(skip)) as total_skip, '.
      'sum(missing) as total_unexplained_missing, '.
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

  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[]='total_left_file';
    $this->indicator_keys[]='total_right_file';
  }

  private $statistic;

  private $standard_deviation_scale;

  private $file_scale;
}
