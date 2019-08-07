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
    'CnBaseViewFactory', 'CnPlotHelperFactory',
    function( CnBaseViewFactory, CnPlotHelperFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );

        // use the plot helper to set up an outlier and histogram plot for this indicator
        CnPlotHelperFactory.addPlot( this, {
          getPath: function() {
            return self.parentModel.getServiceResourcePath() + '/stage?plot=1'
          },
          xAxesLabel: 'Stage Duration in Seconds',
          minName: 'duration_low',
          maxName: 'duration_high',
          getBinSize: function() { return 30; }
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
