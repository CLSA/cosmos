cenozoApp.defineModule( { name: 'opal_view', models: ['add', 'list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'opal view',
      plural: 'opal views',
      possessive: 'opal view\'s'
    },
    columnList: {
      study: { column: 'study.name', title: 'Study' },
      study_phase: { column: 'study_phase.name', title: 'Phase' },
      platform: { column: 'platform.name', title: 'Platform', },
      keep_updated: { title: 'Keep Updated', type: 'boolean' },
      total: { title: 'Total Entries' }
    },
    defaultOrder: {
      column: 'platform',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    platform_id: {
      title: 'Platform',
      type: 'enum'
    },
    study_phase_id: {
      title: 'Study Phase',
      type: 'enum'
    },
    keep_updated: {
      title: 'Keep Updated',
      type: 'boolean'
    },
    total: {
      title: 'Total Entries',
      type: 'string',
      isExcluded: 'add'
    }
  } );

  module.addExtraOperation( 'view', {
    title: 'Upload Entries',
    operation: async function( $state, model ) {
      await $state.go( 'opal_view.upload', { identifier: model.viewModel.record.getIdentifier() } );
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnOpalViewUpload', [
    'CnOpalViewUploadFactory', 'CnSession', '$state',
    function( CnOpalViewUploadFactory, CnSession, $state ) {
      return {
        templateUrl: module.getFileUrl( 'upload.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: async function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnOpalViewUploadFactory.instance();

          await $scope.model.promise;
          CnSession.setBreadcrumbTrail( [ {
            title: 'Opal Views',
            go: async function() { await $state.go( 'opal_view.list' ); }
          }, {
            title: $scope.model.title,
            go: async function() {
              await $state.go( 'opal_view.view', { identifier: $state.params.identifier } );
            }
          }, {
            title: 'Upload'
          } ] );
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOpalViewUploadFactory', [
    'CnHttpFactory', '$state',
    function( CnHttpFactory, $state ) {
      var object = function() {
        angular.extend( this, {
          working: false,
          title: null,
          file: null,

          cancel: async function() { await $state.go( 'opal_view.view', { identifier: $state.params.identifier } ); },

          upload: async function() {
            var data = new FormData();
            data.append( 'file', this.file );
            var fileDetails = data.get( 'file' );

            try {
              this.working = true;
              var response = await CnHttpFactory.instance( {
                path: 'opal_view/' + $state.params.identifier + '?upload=1',
                data: this.file,
                format: 'csv'
              } ).patch();
              await $state.go( 'opal_view.view', { identifier: $state.params.identifier } );
            } finally {
              this.working = false;
            }
          }
        } );

        async function init( object ) {
          var response = await CnHttpFactory.instance( {
            path: 'opal_view/' + $state.params.identifier,
            data: {
              select: {
                column: [
                  { table: 'study', column: 'name', alias: 'study' },
                  { table: 'study_phase', column: 'name', alias: 'study_phase' },
                  { table: 'platform', column: 'name', alias: 'platform' }
                ]
              }
            },
            redirectOnError: true
          } ).get();

          object.title = response.data.study + ' ' + response.data.study_phase + ': ' + response.data.platform;
        }

        this.promise = init( this );
      }
      return { instance: function() { return new object(); } };
    }
  ] );
  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOpalViewModelFactory', [
    'CnBaseModelFactory', 'CnOpalViewListFactory', 'CnOpalViewAddFactory', 'CnOpalViewViewFactory', 'CnHttpFactory',
    function( CnBaseModelFactory, CnOpalViewListFactory, CnOpalViewAddFactory, CnOpalViewViewFactory, CnHttpFactory ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );

        angular.extend( this, {
          addModel: CnOpalViewAddFactory.instance( this ),
          listModel: CnOpalViewListFactory.instance( this ),
          viewModel: CnOpalViewViewFactory.instance( this, root ),

          // never allow patching (it is only allowed for uploading entries)
          getEditEnabled: function() { return false; },

          // extend getMetadata
          getMetadata: async function() {
            await this.$$getMetadata();

            var [platformResponse, studyPhaseResponse] = await Promise.all( [
              CnHttpFactory.instance( {
                path: 'platform',
                data: {
                  select: { column: [ 'id', 'name' ] },
                  modifier: { order: 'name', limit: 1000 }
                }
              } ).query(),

              CnHttpFactory.instance( {
                path: 'study_phase',
                data: {
                  select: { column: [ 'id', 'name', { table: 'study', column: 'name', alias: 'study' } ] },
                  modifier: { order: ['study.name', 'study_phase.rank'], limit: 1000 }
                }
              } ).query()
            ] );

            this.metadata.columnList.platform_id.enumList = platformResponse.data.reduce( ( list, item ) => {
              list.push( { value: item.id, name: item.name } );
              return list;
            }, [] );

            this.metadata.columnList.study_phase_id.enumList = studyPhaseResponse.data.reduce( ( list, item ) => {
              list.push( { value: item.id, name: item.study + ': ' + item.name } );
              return list;
            }, [] );
          }
        } );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );
} } );
