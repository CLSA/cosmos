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
      constant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      constant: true
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string',
      constant: true
    },
    name: {
      title: 'Name',
      type: 'string',
      constant: true
    },
    type: {
      title: 'Type',
      type: 'string',
      constant: true
    },
    median: {
      title: 'Median Value',
      type: 'string',
      format: 'float',
      constant: true
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
      exclude: true
    },
    max_date: {
      type: 'date',
      exclude: true
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
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
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
          getPath: function() {
            return [
              self.record.study_phase,
              self.record.platform,
              self.record.stage_type,
              'data'
            ].join( '_' ) + '?plot=' + self.record.name;
          },
          onView: function() {
            self.histogram.options.scales.xAxes[0].scaleLabel.labelString = self.record.name;
          },
          getBinSize: function() {
            return Math.ceil( ( self.record.maximum - self.record.minimum )/100 );
          }
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorModelFactory', [
    'CnBaseModelFactory', 'CnIndicatorListFactory', 'CnIndicatorViewFactory',
    function( CnBaseModelFactory, CnIndicatorListFactory, CnIndicatorViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnIndicatorListFactory.instance( this );
        this.viewModel = CnIndicatorViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
