define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'stage_type', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'platform',
        column: 'platform_id',
        friendly: 'name'
      }
    },
    name: {
      singular: 'stage type',
      plural: 'stage types',
      possessive: 'stage type\'s'
    },
    columnList: {
      study_phase: {
        column: 'study_phase.name',
        title: 'Study Phase'
      },
      platform: {
        column: 'platform.name',
        title: 'Platform'
      },
      name: { column: 'stage_type.name', title: 'Name' },
      stage_count: { title: 'Stages' }
    },
    defaultOrder: {
      column: 'study_phase.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.name',
      title: 'Study Phase',
      type: 'string',
      constant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      constant: true
    },
    name: {
      column: 'stage_type.name',
      title: 'Name',
      type: 'string',
      constant: true
    },
    duration_low: {
      title: 'Duration Low (seconds)',
      type: 'string',
      format: 'float'
    },
    duration_high: {
      title: 'Duration High (seconds)',
      type: 'string',
      format: 'float'
    },
    stage_count: {
      title: 'Stages',
      type: 'string',
      constant: true
    },
    contraindicated: {
      title: 'Contraindicated',
      type: 'string',
      constant: true
    },
    missing: {
      title: 'Missing',
      type: 'string',
      constant: true
    },
    skip: {
      title: 'Skipped',
      type: 'string',
      constant: true
    }
} );

/* ######################################################################################################## */
cenozo.providers.directive( 'cnStageTypeList', [
  'CnStageTypeModelFactory',
  function( CnStageTypeModelFactory ) {
    return {
      templateUrl: module.getFileUrl( 'list.tpl.html' ),
      restrict: 'E',
      scope: { model: '=?' },
      controller: function( $scope ) {
        if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageTypeModelFactory.root;
      }
    };
  }
] );

/* ######################################################################################################## */
cenozo.providers.directive( 'cnStageTypeView', [
  'CnStageTypeModelFactory',
  function( CnStageTypeModelFactory ) {
    return {
      templateUrl: module.getFileUrl( 'view.tpl.html' ),
      restrict: 'E',
      scope: { model: '=?' },
      controller: function( $scope ) {
        if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageTypeModelFactory.root;
      }
    };
  }
] );

/* ######################################################################################################## */
cenozo.providers.factory( 'CnStageTypeListFactory', [
  'CnBaseListFactory',
  function( CnBaseListFactory ) {
    var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
    return { instance: function( parentModel ) { return new object( parentModel ); } };
  }
] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageTypeViewFactory', [
    'CnBaseViewFactory', 'CnHttpFactory', '$q',
    function( CnBaseViewFactory, CnHttpFactory, $q ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );
        angular.extend( this, {
          outlierLoading: false,
          histogramLoading: false,

          resetPlots: function() {
            this.outlierLoading = true;
            this.histogramLoading = true;

            this.outlier = {
              labels: [],
              series: [],
              data: [],
              options: {
                legend: { display: true },
                tooltips: {
                  callbacks: {
                    label: function( item, data ) {
                      return data.datasets[item.datasetIndex].label +': ' + item.yLabel + '%';
                    }
                  }
                },
                scales: {
                  yAxes: [ {
                    scaleLabel: {
                      display: true,
                      labelString: 'Percent of Interviews',
                      fontFamily: 'sans-serif',
                      fontSize: 15
                    },
                    ticks: {
                      callback: function( value ) { return value + '%'; }
                    }
                  } ]
                }
              }
            };

            this.histogram = {
              labels: [],
              series: [],
              data: [],
              options: {
                legend: { display: true },
                scales: {
                  xAxes: [ {
                    ticks: {
                      autoSkip: true,
                      maxTicksLimit: 40
                    },
                    scaleLabel: {
                      display: true,
                      labelString: 'Stage Duration in Minutes',
                      fontFamily: 'sans-serif',
                      fontSize: 15
                    }
                  } ],
                  yAxes: [ {
                    scaleLabel: {
                      display: true,
                      labelString: 'Number of Interviews',
                      fontFamily: 'sans-serif',
                      fontSize: 15
                    }
                  } ]
                }
              }
            };
          },

          buildPlots: function() {
            var bins = Math.ceil( this.record.duration_high/60 );
            var baseData = [];
            for( var i = 1; i <= bins; i++ ) {
              baseData.push( 0 );
              this.histogram.labels.push( i );
            }
            this.histogram.labels[bins-1] += '+';

            this.outlier.labels = [
              'Low (<' + self.record.duration_low + 's)',
              'On Target',
              'High (>' + self.record.duration_high + 's)'
            ];

            return $q.all( [
              // get all values for the line plot
              CnHttpFactory.instance( {
                path: this.parentModel.getServiceResourcePath() + '/stage?plot=histogram'
              } ).query().then( function( response ) {
                var lastSite = null;
                var dataIndex = -1;
                response.data.forEach( function( row ) {
                  if( row.site != lastSite ) {
                    lastSite = row.site;
                    dataIndex++;
                    self.histogram.series.push( row.site );
                    self.histogram.data.push( angular.copy( baseData ) );
                  }
                  self.histogram.data[dataIndex][row.value-1] = row.count;
                } );
                self.histogramLoading = false;
              } ),

              // get all values for the doughnut plot
              CnHttpFactory.instance( {
                path: this.parentModel.getServiceResourcePath() + '/stage?plot=outlier'
              } ).query().then( function( response ) {
                response.data.forEach( function( row ) {
                  self.outlier.series.push( row.site );
                  self.outlier.data.push( [ (100*row.low).toFixed(1), (100*row.middle).toFixed(1), (100*row.high).toFixed(1) ] );
                } );
                console.log( self.outlier );
                self.outlierLoading = false;
              } )

            ] );
              
          },

          onView: function( force ) {
            this.resetPlots();
            return this.$$onView( force ).then( function() { self.buildPlots(); } );
          },

          onPatch: function( data ) {
            this.resetPlots();
            return this.$$onPatch( data ).then( function() { self.buildPlots(); } );
          }
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageTypeModelFactory', [
    'CnBaseModelFactory', 'CnStageTypeListFactory', 'CnStageTypeViewFactory',
    function( CnBaseModelFactory, CnStageTypeListFactory, CnStageTypeViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnStageTypeListFactory.instance( this );
        this.viewModel = CnStageTypeViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
