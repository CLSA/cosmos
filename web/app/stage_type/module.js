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
      name: { column: 'stage_type.name', title: 'Name' }
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
    },
    min_date: {
      type: 'date',
      exclude: true
    },
    max_date: {
      type: 'date',
      exclude: true
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
          rawData: [],
          dataLoading: false,
          dateSpan: { low: null, high: null, list: [], lowList: [], highList: [] },
          outlier: {
            labels: [],
            series: [],
            data: [],
            colors: [
              '#FFAA44', '#FF44AA', '#AAFF44', '#AA44FF', '#44FFAA', '#44AAFF',
              '#FF4444', '#44FF44', '#4444FF', '#AAAA44', '#FF44FF', '#44FFFF',
              '#884444', '#448844', '#444488', '#666644', '#884488', '#448888'
            ],
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
          },
          histogram: {
            labels: [],
            series: [],
            data: [],
            colors: [
              '#FFAA44', '#FF44AA', '#AAFF44', '#AA44FF', '#44FFAA', '#44AAFF',
              '#FF4444', '#44FF44', '#4444FF', '#AAAA44', '#FF44FF', '#44FFFF',
              '#884444', '#448844', '#444488', '#666644', '#884488', '#448888'
            ],
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
          },

          resetPlots: function() {
            this.histogram.labels = [];
            this.histogram.series = [];
            this.histogram.data = [];
            this.outlier.labels = [];
            this.outlier.series = [];
            this.outlier.data = [];
          },

          readRawData: function() {
            this.dataLoading = true;
            return CnHttpFactory.instance( {
                path: this.parentModel.getServiceResourcePath() + '/stage?plot=1'
            } ).query().then( function( response ) {
              // group data into categories (cats or technicians)
              var lastSite = null;
              var dataIndex = -1;
              self.rawData = [];
              response.data.forEach( function( row ) {
                if( row.category != lastSite ) {
                  lastSite = row.category;
                  dataIndex++;
                  self.rawData.push( { category: row.category, data: [] } );
                }
                self.rawData[dataIndex].data.push( {
                  date: parseInt( moment( new Date( row.date ) ).format( 'YYYYMM' ) ),
                  value: row.duration
                } );
              } );

              self.buildPlots( true );
              self.dataLoading = false;
            } );
          },

          buildPlots: function( reset ) {
            var minBin = Math.ceil( this.record.duration_low/60 );
            var maxBin = Math.ceil( this.record.duration_high/60 ) + 1;
            console.log( minBin, maxBin );
            var baseData = [];
            for( var i = minBin; i <= maxBin; i++ ) baseData.push( 0 );

            if( true === reset ) {
              this.resetPlots();

              // set the outlier labels
              this.outlier.labels = [
                'Low (<' + self.record.duration_low + 's)',
                'On Target',
                'High (>' + self.record.duration_high + 's)'
              ];

              // initialize the outlier series
              self.outlier.series = self.rawData.map( catData => catData.category );

              // set the histogram labels
              for( var i = minBin; i <= maxBin; i++ ) this.histogram.labels.push( i );
              if( minBin > 0 ) this.histogram.labels[0] += '-';
              this.histogram.labels[maxBin-minBin] += '+';

              // initialize the histogram series
              self.histogram.series = self.rawData.map( catData => catData.category );
            }

            // initialize the data
            self.outlier.data = [];
            for( var i = 0; i < self.outlier.series.length; i++ ) self.outlier.data.push( angular.copy( [ 0, 0, 0 ] ) );
            self.histogram.data = [];
            for( var i = 0; i < self.histogram.series.length; i++ ) self.histogram.data.push( angular.copy( baseData ) );

            // put the data into the appropriate bins
            self.rawData.forEach( function( catData, catIndex ) {
              catData.data.filter( datum => self.dateSpan.low <= datum.date && datum.date <= self.dateSpan.high )
                           .forEach( function( datum ) {
                // outlier data
                self.outlier.data[catIndex][
                  self.record.duration_low > datum.value ? 0 : self.record.duration_high < datum.value ? 2 : 1
                ]++;

                // histogram data
                var bin = Math.ceil( datum.value/60 );
                if( bin < minBin ) bin = minBin;
                else if( bin > maxBin ) bin = maxBin;
                self.histogram.data[catIndex][bin-minBin]++;
              } );
            } );
          },

          updateDateSpan: function() {
            // change the low/high lists based on the selected values
            this.dateSpan.lowList = this.dateSpan.list.filter( item => item.value < self.dateSpan.high );
            this.dateSpan.highList = this.dateSpan.list.filter( item => item.value > self.dateSpan.low );

            // rebuild the plots since the date span has changed
            this.buildPlots();
          },

          onView: function( force ) {
            return this.$$onView( force ).then( function() {
              // determine the date spans
              var date = moment( new Date( self.record.min_date ) );
              date.day( 1 );
              var endDate = moment( new Date( self.record.max_date ) );
              endDate.day( 1 );
              while( date.isSameOrBefore( endDate ) )
              {
                self.dateSpan.list.push( {
                  name: date.format( 'MMMM, YYYY' ),
                  value: parseInt( date.format( 'YYYYMM' ) )
                } );
                date.add( 1, 'month' );
              }
              self.dateSpan.lowList = angular.copy( self.dateSpan.list );
              self.dateSpan.lowList.pop();
              self.dateSpan.low = self.dateSpan.list[0].value;
              self.dateSpan.highList = angular.copy( self.dateSpan.list );
              self.dateSpan.highList.shift();
              self.dateSpan.high = self.dateSpan.list[self.dateSpan.list.length-1].value;

              // read the raw plotting data
              self.readRawData();
            } );
          },

          onPatch: function( data ) {
            return this.$$onPatch( data ).then( function() { self.buildPlots( true ); } );
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
