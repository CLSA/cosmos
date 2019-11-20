define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'indicator', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage_type',
        column: 'stage_type_id'
      }
    },
    name: {
      singular: 'indicator',
      plural: 'indicators',
      possessive: 'indicator\'s'
    },
    columnList: {
      study_phase: {
        column: 'study_phase.code',
        title: 'Study Phase'
      },
      platform: {
        column: 'platform.name',
        title: 'Platform'
      },
      stage_type: {
        column: 'stage_type.name',
        title: 'Stage Type'
      },
      name: {
        column: 'indicator.name',
        title: 'Name'
      },
      type: {
        title: 'Type'
      },
      minimum: {
        title: 'Minimum'
      },
      maximum: {
        title: 'Maximum'
      },
      outlier_low: {
        title: 'Low Outliers'
      },
      outlier_high: {
        title: 'High Outliers'
      }
    },
    defaultOrder: {
      column: 'indicator.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.code',
      title: 'Study Phase',
      type: 'string',
      isConstant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      isConstant: true
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string',
      isConstant: true
    },
    name: {
      title: 'Name',
      type: 'string',
      isConstant: true
    },
    type: {
      title: 'Type',
      type: 'string',
      isConstant: true
    },
    median: {
      title: 'Median Value',
      type: 'string',
      format: 'float',
      isConstant: true
    },
    minimum: {
      title: 'Minimum Threshold',
      type: 'string',
      format: 'float'
    },
    maximum: {
      title: 'Maximum Threshold',
      type: 'string',
      format: 'float'
    },
    min_date: {
      type: 'date',
      isExcluded: true
    },
    max_date: {
      type: 'date',
      isExcluded: true
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnIndicatorList', [
    'CnIndicatorModelFactory',
    function( CnIndicatorModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnIndicatorModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnIndicatorView', [
    'CnIndicatorModelFactory',
    function( CnIndicatorModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnIndicatorModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorListFactory', [
    'CnBaseListFactory', 'CnHttpFactory',
    function( CnBaseListFactory, CnHttpFactory ) {
      var object = function( parentModel ) {
        var self = this;
        CnBaseListFactory.construct( this, parentModel );
        angular.extend( this, {
          heading: 'Outlier List',
          onSetDateSpan: function() {
            this.parentModel.updateDateSpan();
            this.onList();
          }
        } );
      };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorViewFactory', [
    'CnBaseViewFactory', 'CnPlotHelperFactory',
    function( CnBaseViewFactory, CnPlotHelperFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );

        // use the plot helper to set up an outlier and histogram plot for this indicator
        CnPlotHelperFactory.addPlot( this, {
          getType: function() {
            // determine if the indicator has a special data type
            if( 'file_size' == self.record.name || null != self.record.name.match( /_file$/ ) ) { 
              return 'file';
            } else if( null != self.record.name.match( /_duration$/ ) ) {
              return 'time';
            } else if( null != self.record.name.match( /_complete$/ ) ) {
              return 'percent';
            }
            return '';
          },
          getPath: function() {
            return [
              self.record.study_phase,
              self.record.platform,
              self.record.stage_type,
              'data'
            ].join( '_' ) + '?plot=' + self.record.name;
          },
          getXLabel: function() {
            return ( self.record.study_phase ? self.record.study_phase.toUpperCase() + ': ' : '' ) +
                   [self.record.platform, self.record.stage_type, self.record.name].join( '/' )
          },
          getBinSize: function() { return Math.ceil( ( self.record.maximum - self.record.minimum )/100 ); }
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorModelFactory', [
    'CnBaseModelFactory', 'CnIndicatorListFactory', 'CnIndicatorViewFactory', 'CnSession', 'CnHttpFactory', '$q',
    function( CnBaseModelFactory, CnIndicatorListFactory, CnIndicatorViewFactory, CnSession, CnHttpFactory, $q ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnIndicatorListFactory.instance( this );
        this.viewModel = CnIndicatorViewFactory.instance( this, root );

        this.dateSpan = { low: null, high: null, list: [], lowList: [], highList: [] };

        this.setupBreadcrumbTrail = function() {
          if( angular.isUndefined( this.getParentIdentifier().subject ) && 'indicator' == this.getSubjectFromState() ) {
            CnSession.setBreadcrumbTrail( [ { title: 'Outliers' } ] );
          } else {
            this.$$setupBreadcrumbTrail();
          }
        };

        // when viewing the root list of indicators with no parent show outliers instead
        this.getServiceData = function( type, columnRestrictLists ) {
          var data = this.$$getServiceData( type, columnRestrictLists );
          if( 'list' == type && 'indicator' == this.getSubjectFromState() ) {
            if( angular.isUndefined( data.modifier ) ) data.modifier = {};
            if( angular.isUndefined( data.modifier.where ) ) data.modifier.where = [];
            data.modifier.where.push( {
              bracket: true,
              open: true
            } );
            data.modifier.where.push( {
              column: 'outlier_low',
              operator: '>',
              value: 0
            } );
            data.modifier.where.push( {
              column: 'outlier_high',
              operator: '>',
              value: 0,
              or: true
            } );
            data.modifier.where.push( {
              bracket: true,
              open: false
            } );

            if( this.dateSpan.low && this.dateSpan.high ) {
              data.modifier.where.push( {
                bracket: true,
                open: true
              } );
              data.modifier.where.push( {
                column: 'outlier.date',
                operator: '>=',
                value: this.dateSpan.low
              } );
              data.modifier.where.push( {
                column: 'outlier.date',
                operator: '<=',
                value: this.dateSpan.high
              } );
              data.modifier.where.push( {
                bracket: true,
                open: false
              } );
            }
          }
          return data;
        };

        // updates the plots based on the selected date span
        this.updateDateSpan = function() {
          if( null == this.dateSpan.low ) this.dateSpan.low = this.dateSpan.list[0].value;
          if( null == this.dateSpan.high ) this.dateSpan.high = this.dateSpan.list[this.dateSpan.list.length-1].value;

          // change the low/high lists based on the selected values
          this.dateSpan.lowList = this.dateSpan.list.filter( item => item.value < this.dateSpan.high );
          this.dateSpan.highList = this.dateSpan.list.filter( item => item.value > this.dateSpan.low );
        };

        CnHttpFactory.instance( {
          path: 'interview',
          data: {
            select: {
              column: [
                { column: 'MIN( start_date )', alias: 'min_date', table_prefix: false },
                { column: 'MAX( start_date )', alias: 'max_date', table_prefix: false }
              ]
            }
          }
        } ).query().then( function( response ) {
          // determine the date spans
          var date = moment( new Date( response.data[0].min_date ) );
          date.day( 1 );
          var endDate = moment( new Date( response.data[0].max_date ) );
          endDate.day( 1 );
          self.dateSpan.list = [];
          while( date.isSameOrBefore( endDate ) ) {
            self.dateSpan.list.push( {
              name: date.format( 'MMMM, YYYY' ),
              value: date.format( 'YYYY-MM-01' )
            } );
            date.add( 1, 'month' );
          }
          self.updateDateSpan();
        } );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
