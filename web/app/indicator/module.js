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
    minimum: {
      title: 'Minimum',
      type: 'string',
      format: 'integer'
    },
    maximum: {
      title: 'Maximum',
      type: 'string',
      format: 'integer'
    },
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
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
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
