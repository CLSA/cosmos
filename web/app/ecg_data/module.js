define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'ecg_data', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'ecg data',
      plural: 'ecg data',
      possessive: 'ecg data\'s'
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
      intrinsic_poor_quality: {
        title: 'Intrinsic Poor Quality',
        type: 'number'
      },
      xml_file_size: {
        title: 'XML File Size',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'id',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    intrinsic_poor_quality: {
      title: 'Intrinsic Poor Quality',
      type: 'string',
      format: 'integer'
    },
    xml_file_size: {
      title: 'XML File Size',
      type: 'string',
      format: 'integer'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnEcgDataList', [
    'CnEcgDataModelFactory',
    function( CnEcgDataModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnEcgDataModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnEcgDataView', [
    'CnEcgDataModelFactory',
    function( CnEcgDataModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnEcgDataModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnEcgDataListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnEcgDataViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnEcgDataModelFactory', [
    'CnBaseModelFactory', 'CnEcgDataListFactory', 'CnEcgDataViewFactory', '$state',
    function( CnBaseModelFactory, CnEcgDataListFactory, CnEcgDataViewFactory, $state ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnEcgDataListFactory.instance( this );
        this.viewModel = CnEcgDataViewFactory.instance( this, root );

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