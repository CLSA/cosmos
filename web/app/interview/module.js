cenozoApp.defineModule( { name: 'interview', models: ['list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'interview',
      plural: 'interviews',
      possessive: 'interview\'s'
    },
    columnList: {
      uid: { column: 'participant.uid', title: 'UID' },
      study_phase: { column: 'study_phase.name', title: 'Study Phase' },
      platform: { column: 'platform.name', title: 'Platform' },
      site: { column: 'site.name', title: 'Site' },
      start_date: { title: 'Date', type: 'date' },
      barcode: { title: 'Barcode' },
      duration: { title: 'Duration', type: 'number' },
      total_stage_duration: { title: 'Total Stage Duration', type: 'number' }
    },
    defaultOrder: {
      column: 'start_date',
      reverse: true
    }
  } );

  module.addInputGroup( '', {
    uid: {
      column: 'participant.uid',
      title: 'UID',
      type: 'string'
    },
    study_phase: {
      column: 'study_phase.name',
      title: 'Study Phase',
      type: 'string'
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string'
    },
    site: {
      column: 'site.name',
      title: 'Site',
      type: 'string'
    },
    start_date: {
      title: 'Date',
      type: 'date'
    },
    barcode: {
      title: 'Barcode',
      type: 'string'
    },
    duration: {
      title: 'Duration',
      type: 'string'
    },
    total_stage_duration: {
      title: 'Total Stage Duration',
      type: 'string'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewModelFactory', [
    'CnBaseModelFactory', 'CnInterviewListFactory', 'CnInterviewViewFactory',
    function( CnBaseModelFactory, CnInterviewListFactory, CnInterviewViewFactory ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );

        angular.extend( this, {
          listModel: CnInterviewListFactory.instance( this ),
          viewModel: CnInterviewViewFactory.instance( this, root ),
          // override the edit functionality (it's used to update data in a nightly script only)
          getEditEnabled: function() { return false; }
        } );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} } );
