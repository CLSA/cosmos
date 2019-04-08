define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'interview', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'interview',
      plural: 'interviews',
      possessive: 'interview\'s'
    },
    columnList: {
      start_date: {
        title: 'Date',
        type: 'date'
      }
    },
    defaultOrder: {
      column: 'start_date',
      reverse: true
    }
  } );

  module.addInputGroup( '', {
    start_date: {
      title: 'Date',
      type: 'date'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnInterviewAdd', [
    'CnInterviewModelFactory',
    function( CnInterviewModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'add.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnInterviewModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnInterviewList', [
    'CnInterviewModelFactory',
    function( CnInterviewModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnInterviewModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnInterviewView', [
    'CnInterviewModelFactory',
    function( CnInterviewModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnInterviewModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewAddFactory', [
    'CnBaseAddFactory',
    function( CnBaseAddFactory ) {
      var object = function( parentModel ) { CnBaseAddFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewModelFactory', [
    'CnBaseModelFactory', 'CnInterviewAddFactory', 'CnInterviewListFactory', 'CnInterviewViewFactory',
    function( CnBaseModelFactory, CnInterviewAddFactory, CnInterviewListFactory, CnInterviewViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.addModel = CnInterviewAddFactory.instance( this );
        this.listModel = CnInterviewListFactory.instance( this );
        this.viewModel = CnInterviewViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
