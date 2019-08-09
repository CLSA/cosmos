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
        if( angular.isUndefined( parameters.getType ) ) parameters.getType = function() { return ''; };
        if( angular.isUndefined( parameters.getXLabel ) ) parameters.getXLabel = function() { return ''; };
        if( angular.isUndefined( parameters.getYLabel ) ) parameters.getYLabel = function() { return 'Number of Interviews'; };
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
                xAxes: [ {
                  scaleLabel: {
                    display: true,
                    labelString: parameters.getXLabel(),
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ],
                yAxes: [ {
                  scaleLabel: {
                    display: true,
                    labelString: parameters.getYLabel(),
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
                    maxTicksLimit: 15
                  },
                  scaleLabel: {
                    display: true,
                    labelString: parameters.getXLabel(),
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ],
                yAxes: [ {
                  scaleLabel: {
                    display: true,
                    labelString: parameters.getYLabel(),
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ]
              }
            }
          },

          // reset and reread all data from the server
          readRawData: function() {
            var self = this;
            this.dataLoading = true;
            return CnHttpFactory.instance( {
              path: parameters.getPath()
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
                  value: row.value
                } );
              } );

              self.dataLoading = false;
            } );
          },

          // updates the plots based on the selected date span
          updateDateSpan: function() {
            var self = this;
            var type = parameters.getType();
            var binSize = parameters.getBinSize();
            if( 0 >= binSize ) binSize = 1;

            if( null == this.dateSpan.low ) this.dateSpan.low = this.dateSpan.list[0].value;
            if( null == this.dateSpan.high ) this.dateSpan.high = this.dateSpan.list[this.dateSpan.list.length-1].value;

            // change the low/high lists based on the selected values
            this.dateSpan.lowList = this.dateSpan.list.filter( item => item.value < self.dateSpan.high );
            this.dateSpan.highList = this.dateSpan.list.filter( item => item.value > self.dateSpan.low );
          },

          // builds the plots (reset will rebuild the plot's details as well)
          buildPlot: function( reset ) {
            var self = this;
            var binSize = parameters.getBinSize();
            if( 0 >= binSize ) binSize = 1;

            var minValue = parseInt( this.record[parameters.minName] );
            var maxValue = parseInt( this.record[parameters.maxName] );
            var minBin = Math.ceil( minValue / binSize ) - 1;
            var maxBin = Math.ceil( maxValue / binSize ) + 1;
            var baseData = [];
            for( var i = minBin; i <= maxBin; i++ ) baseData.push( 0 );

            if( reset === true ) {
              // reset the plot details
              this.histogram.labels = [];
              this.histogram.series = [];
              this.histogram.data = [];
              this.outlier.labels = [];
              this.outlier.series = [];
              this.outlier.data = [];

              // set the labels
              var type = parameters.getType();
              var middleValue = ( minValue + maxValue ) / 2;
              var binLabelSize = binSize;
              var histogramUnit = null;

              if( 'file' == type ) {
                var giga = Math.pow( 2, 30 );
                var mega = Math.pow( 2, 20 );
                var kilo = Math.pow( 2, 10 );

                // outlier low label
                if( minValue > giga ) minValue = ( minValue/giga ).toFixed(1) + ' GB';
                else if( minValue > mega ) minValue = ( minValue/mega ).toFixed(1) + ' MB';
                else if( minValue > kilo ) minValue = ( minValue/kilo ).toFixed(1) + ' KB';
                else minValue = minValue + ' bytes';

                // outlier high label
                if( maxValue > giga ) maxValue = ( maxValue/giga ).toFixed(1) + ' GB';
                else if( maxValue > mega ) maxValue = ( maxValue/mega ).toFixed(1) + ' MB';
                else if( maxValue > kilo ) maxValue = ( maxValue/kilo ).toFixed(1) + ' KB';
                else maxValue = maxValue + ' bytes';

                // histogram bin size label
                if( middleValue > giga ) {
                  binLabelSize = binLabelSize/giga;
                  histogramUnit = 'GB';
                } else if( middleValue > mega ) {
                  binLabelSize = binLabelSize/mega;
                  histogramUnit = 'MB';
                } else if( middleValue > kilo ) {
                  binLabelSize = binLabelSize/kilo;
                  histogramUnit = 'KB';
                } else {
                  histogramUnit = 'bytes';
                }
              } else if( 'time' == type ) {
                var day = 24*60*60;
                var hour = 60*60;
                var minute = 60;

                // outlier low label
                if( minValue > day ) minValue = ( minValue/day ).toFixed(1) + ' days';
                else if( minValue > hour ) minValue = ( minValue/hour ).toFixed(1) + ' hours';
                else if( minValue > minute ) minValue = ( minValue/minute ).toFixed(1) + ' mins';
                else minValue = minValue + ' secs';

                // outlier high label
                if( maxValue > day ) maxValue = ( maxValue/day ).toFixed(1) + ' days';
                else if( maxValue > hour ) maxValue = ( maxValue/hour ).toFixed(1) + ' hours';
                else if( maxValue > minute ) maxValue = ( maxValue/minute ).toFixed(1) + ' mins';
                else maxValue = maxValue + ' secs';

                // histogram bin size label
                if( middleValue > day ) {
                  binLabelSize = binLabelSize/day;
                  histogramUnit = 'days';
                } else if( middleValue > hour ) {
                  binLabelSize = binLabelSize/hour;
                  histogramUnit = 'hours';
                } else if( middleValue > minute ) {
                  binLabelSize = binLabelSize/minute;
                  histogramUnit = 'mins';
                } else {
                  histogramUnit = 'secs';
                }
              }

              this.outlier.labels = [
                'Low (<' + minValue + ')',
                'On Target',
                'High (>' + maxValue + ')'
              ];

              // initialize the outlier series
              this.outlier.series = this.rawData.map( catData => catData.category );

              // set the histogram labels
              for( var i = minBin; i <= maxBin; i++ ) this.histogram.labels.push( (i*binLabelSize).toFixed(1) );
              if( minBin > 0 ) this.histogram.labels[0] = '<' + this.histogram.labels[0];
              this.histogram.labels[maxBin-minBin] = '>' + this.histogram.labels[maxBin-minBin];

              // initialize the histogram series
              this.histogram.series = this.rawData.map( catData => catData.category );

              // set the axis labels
              this.outlier.options.scales.xAxes[0].scaleLabel.labelString = parameters.getXLabel();
              this.outlier.options.scales.yAxes[0].scaleLabel.labelString = parameters.getYLabel();
              this.histogram.options.scales.xAxes[0].scaleLabel.labelString = parameters.getXLabel() +
                ( null != histogramUnit ?  ' (' + histogramUnit + ')' : '' );
              this.histogram.options.scales.yAxes[0].scaleLabel.labelString = parameters.getYLabel();
            }

            // initialize the data
            this.outlier.data = [];
            for( var i = 0; i < this.outlier.series.length; i++ ) this.outlier.data.push( angular.copy( [ 0, 0, 0 ] ) );
            this.histogram.data = [];
            for( var i = 0; i < this.histogram.series.length; i++ ) this.histogram.data.push( angular.copy( baseData ) );

            // put the data into the appropriate bins
            this.rawData.forEach( function( catData, catIndex ) {
              catData.data.filter( datum => self.dateSpan.low <= datum.date && datum.date <= self.dateSpan.high )
                           .forEach( function( datum ) {
                // outlier data
                self.outlier.data[catIndex][minValue > datum.value ? 0 : maxValue < datum.value ? 2 : 1]++;

                // histogram data
                var bin = Math.ceil( datum.value / binSize );
                if( bin < minBin ) bin = minBin;
                else if( bin > maxBin ) bin = maxBin;
                self.histogram.data[catIndex][bin-minBin]++;
              } );
            } );
          },

          onView: function( force ) {
            var self = this;
            return this.$$onView( force ).then( function() {
              parameters.onView();

              // determine the date spans
              var date = moment( new Date( self.record.min_date ) );
              date.day( 1 );
              var endDate = moment( new Date( self.record.max_date ) );
              endDate.day( 1 );
              self.dateSpan.list = [];
              while( date.isSameOrBefore( endDate ) ) {
                self.dateSpan.list.push( {
                  name: date.format( 'MMMM, YYYY' ),
                  value: parseInt( date.format( 'YYYYMM' ) )
                } );
                date.add( 1, 'month' );
              }
              self.updateDateSpan();

              // read the raw plotting data
              return self.readRawData().then( function() {
                self.buildPlot( true );
              } );
            } );
          },

          onSetDateSpan: function() {
            this.updateDateSpan();
            this.buildPlot();
          },

          onPatch: function( data ) {
            var self = this;
            return this.$$onPatch( data ).then( function() { self.buildPlot( true ); } );
          }
        } );
      }
    };
  }
] );
