define( function() {
  'use strict';

  try { var module = cenozoApp.module( '<SUBJECT>', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: '<NAME> data',
      plural: '<NAME> data',
      possessive: '<NAME> data\'s'
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
<COLUMN_LIST>
      stage_id: { isIncluded: function() { return false; } }
    },
    defaultOrder: {
      column: 'id',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
<INPUT_LIST>
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cn<SUBJECT_CAMEL>List', [
    'Cn<SUBJECT_CAMEL>ModelFactory',
    function( Cn<SUBJECT_CAMEL>ModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = Cn<SUBJECT_CAMEL>ModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cn<SUBJECT_CAMEL>View', [
    'Cn<SUBJECT_CAMEL>ModelFactory',
    function( Cn<SUBJECT_CAMEL>ModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = Cn<SUBJECT_CAMEL>ModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'Cn<SUBJECT_CAMEL>ListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'Cn<SUBJECT_CAMEL>ViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'Cn<SUBJECT_CAMEL>ModelFactory', [
    'CnBaseModelFactory', 'Cn<SUBJECT_CAMEL>ListFactory', 'Cn<SUBJECT_CAMEL>ViewFactory', '$state',
    function( CnBaseModelFactory, Cn<SUBJECT_CAMEL>ListFactory, Cn<SUBJECT_CAMEL>ViewFactory, $state ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = Cn<SUBJECT_CAMEL>ListFactory.instance( this );
        this.viewModel = Cn<SUBJECT_CAMEL>ViewFactory.instance( this, root );

        this.getServiceResourcePath = function( resource ) {
          return self.module.subject.snake + '/stage_id=' + $state.params.identifier;
        };

        this.transitionToViewState = function( record ) {
          $state.go( 'stage.view', { identifier: record.stage_id } );
        };
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
