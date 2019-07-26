define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'comment', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage',
        column: 'stage_id'
      }
    },
    name: {
      singular: 'comment',
      plural: 'comment',
      possessive: 'comment\'s'
    },
    columnList: {
      type: { title: 'Type' },
      note: { title: 'Note', align: 'left' }
    },
    defaultOrder: {
      column: 'type',
      reverse: false
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCommentList', [
    'CnCommentModelFactory',
    function( CnCommentModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnCommentModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCommentListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCommentModelFactory', [
    'CnBaseModelFactory', 'CnCommentListFactory', '$state',
    function( CnBaseModelFactory, CnCommentListFactory, $state ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnCommentListFactory.instance( this );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
