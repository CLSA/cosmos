define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'carotid_intima_data', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'carotid intima data',
      plural: 'carotid intima data',
      possessive: 'carotid intima data\'s'
    },
    columnList: {
      uid: {
        column: 'participant.uid',
        title: 'UID'
      },
      study_phase: {
        column: 'study_phase.code',
        title: 'Phase'
      },
      still_image_1_left: {
        title: 'Still Image 1 Left',
        type: 'number'
      },
      still_image_1_right: {
        title: 'Still Image 1 Right',
        type: 'number'
      },
      still_image_2_left: {
        title: 'Still Image 2 Left',
        type: 'number'
      },
      still_image_2_right: {
        title: 'Still Image 2 Right',
        type: 'number'
      },
      still_image_3_left: {
        title: 'Still Image 3 Left',
        type: 'number'
      },
      still_image_3_right: {
        title: 'Still Image 3 Right',
        type: 'number'
      },
      cineloop_1_left: {
        title: 'Cineloop 1 Left',
        type: 'number'
      },
      cineloop_1_right: {
        title: 'Cineloop 1 Right',
        type: 'number'
      },
      structured_report_1_left: {
        title: 'Structured Report 1 Left',
        type: 'number'
      },
      structured_report_1_right: {
        title: 'Structured Report 1 Right',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'participant.uid',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    still_image_1_left: {
      title: 'Still Image 1 Left',
      type: 'string',
      format: 'integer'
    },
    still_image_1_right: {
      title: 'Still Image 1 Right',
      type: 'string',
      format: 'integer'
    },
    still_image_2_left: {
      title: 'Still Image 2 Left',
      type: 'string',
      format: 'integer'
    },
    still_image_2_right: {
      title: 'Still Image 2 Right',
      type: 'string',
      format: 'integer'
    },
    still_image_3_left: {
      title: 'Still Image 3 Left',
      type: 'string',
      format: 'integer'
    },
    still_image_3_right: {
      title: 'Still Image 3 Right',
      type: 'string',
      format: 'integer'
    },
    cineloop_1_left: {
      title: 'Cineloop 1 Left',
      type: 'string',
      format: 'integer'
    },
    cineloop_1_right: {
      title: 'Cineloop 1 Right',
      type: 'string',
      format: 'integer'
    },
    structured_report_1_left: {
      title: 'Structured Report 1 Left',
      type: 'string',
      format: 'integer'
    },
    structured_report_1_right: {
      title: 'Structured Report 1 Right',
      type: 'string',
      format: 'integer'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCarotidIntimaDataList', [
    'CnCarotidIntimaDataModelFactory',
    function( CnCarotidIntimaDataModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnCarotidIntimaDataModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCarotidIntimaDataView', [
    'CnCarotidIntimaDataModelFactory',
    function( CnCarotidIntimaDataModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnCarotidIntimaDataModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCarotidIntimaDataListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCarotidIntimaDataViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCarotidIntimaDataModelFactory', [
    'CnBaseModelFactory', 'CnCarotidIntimaDataListFactory', 'CnCarotidIntimaDataViewFactory', '$state',
    function( CnBaseModelFactory, CnCarotidIntimaDataListFactory, CnCarotidIntimaDataViewFactory, $state ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnCarotidIntimaDataListFactory.instance( this );
        this.viewModel = CnCarotidIntimaDataViewFactory.instance( this, root );

        this.getServiceResourcePath = function( resource ) {
          return self.module.subject.snake + '/stage_id=' + $state.params.identifier;
        };
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
