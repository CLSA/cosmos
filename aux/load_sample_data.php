<?php
/**
 * Loads the sample data in sample_data.csv into a PHP array which can be used for testing
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

$file = file_get_contents( __DIR__.'/sample_data.csv' );

// split the file into lines
$csv_data = array_map( 'str_getcsv', explode( "\n", $file ) );

// get the column names from the first line
$columns = array_shift( $csv_data );

// parse the data from the remaining lines
$sample_data = array_reduce(
  $csv_data,
  function( $carry, $line ) {
    global $columns;
    $row = array();
    if( $line[0] ) // only add data if it has an identifier
    {
      foreach( $line as $index => $cell ) $row[ $columns[$index] ] = $cell;
      $carry[$line[0]] = $row;
    }
    return $carry;
  },
  array()
);
