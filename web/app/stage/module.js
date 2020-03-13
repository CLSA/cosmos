define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'stage', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'interview',
        column: 'interview_id'
      }
    },
    name: {
      singular: 'stage',
      plural: 'stages',
      possessive: 'stage\'s'
    },
    columnList: {
      stage_type: {
        column: 'stage_type.name',
        title: 'Stage Type'
      },
      technician: {
        column: 'technician.name',
        title: 'Technician'
      },
      contraindicated: {
        title: 'Contraindicated',
        type: 'boolean'
      },
      missing: {
        title: 'Missing',
        type: 'boolean'
      },
      duration: {
        title: 'Duration',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'stage_type.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.code',
      title: 'Phase',
      type: 'string'
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string'
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string'
    },
    technician: {
      column: 'technician.name',
      title: 'Technician',
      type: 'string'
    },
    contraindicated: {
      title: 'Contraindicated',
      type: 'boolean'
    },
    missing: {
      title: 'Missing',
      type: 'boolean'
    },
    duration: {
      title: 'Duration',
      type: 'string',
      format: 'float'
    },
    data: {
      title: 'Raw Data',
      type: 'text'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageList', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageView', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageModelFactory', [
    'CnBaseModelFactory', 'CnStageListFactory', 'CnStageViewFactory',
    function( CnBaseModelFactory, CnStageListFactory, CnStageViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnStageListFactory.instance( this );
        this.viewModel = CnStageViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
