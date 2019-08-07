'use strict';

var cenozo = angular.module( 'cenozo' );

cenozo.controller( 'HeaderCtrl', [
  '$scope', 'CnBaseHeader',
  function( $scope, CnBaseHeader ) {
    // copy all properties from the base header
    CnBaseHeader.construct( $scope );
  }
] );

cenozo.service( 'CnPlotHelperFactory', [
  'CnHttpFactory',
  function( CnHttpFactory ) {
    return {
      addPlot: function( model, parameters ) {
        if( !parameters ) parameters = {};
        if( angular.isUndefined( parameters.xAxesLabel ) ) parameters.xAxesLabel = '';
        if( angular.isUndefined( parameters.yAxesLabel ) ) parameters.yAxesLabel = 'Number of Interviews';
        if( angular.isUndefined( parameters.getPath ) ) console.error( 'CnPlotHelper.addPlot(): Missing getPath() function.' );
        if( angular.isUndefined( parameters.minName ) ) parameters.minName = 'minimum';
        if( angular.isUndefined( parameters.maxName ) ) parameters.maxName = 'maximum';
        if( angular.isUndefined( parameters.getBinSize ) ) parameters.getBinSize = function() { return 60; };
        if( angular.isUndefined( parameters.onView ) ) parameters.onView = function() {};

        angular.extend( model, {
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
              scales: {
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
                    labelString: parameters.xAxesLabel,
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ],
                yAxes: [ {
                  scaleLabel: {
                    display: true,
                    labelString: parameters.yAxesLabel,
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
              path: parameters.getPath()
            } ).query().then( function( response ) {
              // group data into categories (cats or technicians)
              var lastSite = null;
              var dataIndex = -1;
              model.rawData = [];
              response.data.forEach( function( row ) {
                if( row.category != lastSite ) {
                  lastSite = row.category;
                  dataIndex++;
                  model.rawData.push( { category: row.category, data: [] } );
                }
                model.rawData[dataIndex].data.push( {
                  date: parseInt( moment( new Date( row.date ) ).format( 'YYYYMM' ) ),
                  value: row.value
                } );
              } );

              model.buildPlots( true );
              model.dataLoading = false;
            } );
          },

          buildPlots: function( reset ) {
            var binSize = parameters.getBinSize();
            if( 0 >= binSize ) binSize = 1;

            var minBin = Math.ceil( this.record[parameters.minName] / binSize ) - 1;
            var maxBin = Math.ceil( this.record[parameters.maxName] / binSize ) + 1;
            var baseData = [];
            for( var i = minBin; i <= maxBin; i++ ) baseData.push( 0 );

            if( true === reset ) {
              this.resetPlots();

              // set the outlier labels
              this.outlier.labels = [
                'Low (<' + model.record[parameters.minName] + 's)',
                'On Target',
                'High (>' + model.record[parameters.maxName] + 's)'
              ];

              // initialize the outlier series
              model.outlier.series = model.rawData.map( catData => catData.category );

              // set the histogram labels
              for( var i = minBin; i <= maxBin; i++ ) this.histogram.labels.push( i * binSize );
              if( minBin > 0 ) this.histogram.labels[0] += '-';
              this.histogram.labels[maxBin-minBin] += '+';

              // initialize the histogram series
              model.histogram.series = model.rawData.map( catData => catData.category );
            }

            // initialize the data
            model.outlier.data = [];
            for( var i = 0; i < model.outlier.series.length; i++ ) model.outlier.data.push( angular.copy( [ 0, 0, 0 ] ) );
            model.histogram.data = [];
            for( var i = 0; i < model.histogram.series.length; i++ ) model.histogram.data.push( angular.copy( baseData ) );

            // put the data into the appropriate bins
            model.rawData.forEach( function( catData, catIndex ) {
              catData.data.filter( datum => model.dateSpan.low <= datum.date && datum.date <= model.dateSpan.high )
                           .forEach( function( datum ) {
                // outlier data
                model.outlier.data[catIndex][
                  model.record[parameters.minName] > datum.value ? 0 : model.record[parameters.maxName] < datum.value ? 2 : 1
                ]++;

                // histogram data
                var bin = Math.ceil( datum.value / binSize );
                if( bin < minBin ) bin = minBin;
                else if( bin > maxBin ) bin = maxBin;
                model.histogram.data[catIndex][bin-minBin]++;
              } );
            } );
          },

          updateDateSpan: function() {
            // change the low/high lists based on the selected values
            this.dateSpan.lowList = this.dateSpan.list.filter( item => item.value < model.dateSpan.high );
            this.dateSpan.highList = this.dateSpan.list.filter( item => item.value > model.dateSpan.low );

            // rebuild the plots since the date span has changed
            this.buildPlots();
          },

          onView: function( force ) {
            return this.$$onView( force ).then( function() {
              parameters.onView();

              // determine the date spans
              var date = moment( new Date( model.record.min_date ) );
              date.day( 1 );
              var endDate = moment( new Date( model.record.max_date ) );
              endDate.day( 1 );
              while( date.isSameOrBefore( endDate ) )
              {
                model.dateSpan.list.push( {
                  name: date.format( 'MMMM, YYYY' ),
                  value: parseInt( date.format( 'YYYYMM' ) )
                } );
                date.add( 1, 'month' );
              }
              model.dateSpan.lowList = angular.copy( model.dateSpan.list );
              model.dateSpan.lowList.pop();
              model.dateSpan.low = model.dateSpan.list[0].value;
              model.dateSpan.highList = angular.copy( model.dateSpan.list );
              model.dateSpan.highList.shift();
              model.dateSpan.high = model.dateSpan.list[model.dateSpan.list.length-1].value;

              // read the raw plotting data
              model.readRawData();
            } );
          },

          onPatch: function( data ) {
            return this.$$onPatch( data ).then( function() { model.buildPlots( true ); } );
          }
        } );
      }
    };
  }
] );
