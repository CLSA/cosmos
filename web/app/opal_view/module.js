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
      platform: { column: 'platform.name', title: 'Platform', },
      study_phase: { column: 'study_phase.name', title: 'Phase' },
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
    total: {
      title: 'Total Entries',
      type: 'string',
      isExcluded: 'add'
    }
  } );

  module.addExtraOperation( 'view', {
    title: 'Upload Entries',
    operation: async function( $state, model ) {
      await $state.go( 'opal_view.upload', { identifier: model.viewMOdel.record.getIdentifier() } );
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOpalViewModelFactory', [
    'CnBaseModelFactory', 'CnOpalViewListFactory', 'CnOpalViewAddFactory', 'CnOpalViewViewFactory', 'CnHttpFactory',
    function( CnBaseModelFactory, CnOpalViewListFactory, CnOpalViewAddFactory, CnOpalViewViewFactory, CnHttpFactory ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );
        this.addModel = CnOpalViewAddFactory.instance( this );
        this.listModel = CnOpalViewListFactory.instance( this );
        this.viewModel = CnOpalViewViewFactory.instance( this, root );

        // extend getMetadata
        this.getMetadata = async function() {
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
        };
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );
} } );
