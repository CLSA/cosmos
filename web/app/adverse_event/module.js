cenozoApp.defineModule( { name: 'adverse_event', models: ['list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage',
        column: 'stage_id'
      }
    },
    name: {
      singular: 'adverse event',
      plural: 'adverse events',
      possessive: 'adverse event\'s'
    },
    columnList: {
      uid: { column: 'participant.uid', title: 'UID' },
      study_phase: {
        column: 'study_phase.name',
        title: 'Study Phase',
        isIncluded: function( $state, model ) { return 'adverse_effect' == model.getSubjectFromState(); }
      },
      platform: {
        column: 'platform.name',
        title: 'Platform',
        isIncluded: function( $state, model ) { return 'adverse_effect' == model.getSubjectFromState(); }
      },
      site: { column: 'site.name', title: 'Site' },
      technician: {
        column: 'technician.name',
        title: 'Technician',
        isIncluded: function( $state, model ) { return 'adverse_effect' == model.getSubjectFromState(); }
      },
      start_date: { column: 'interview.start_date', title: 'Date', type: 'date' },
      barcode: { column: 'interview.barcode', title: 'Barcode' },
      type: { type: 'string', title: 'Type' },
      followup: { type: 'string', title: 'Follow-Up' }
    },
    defaultOrder: {
      column: 'start_date',
      reverse: true
    }
  } );

  module.addInputGroup( '', {
    uid: { column: 'participant.uid', title: 'UID', type: 'string' },
    study_phase: { column: 'study_phase.name', title: 'Study Phase', type: 'string' },
    platform: { column: 'platform.name', title: 'Platform', type: 'string' },
    site: { column: 'site.name', title: 'Site', type: 'string' },
    technician: { column: 'technician.name', title: 'Technician', type: 'string' },
    start_date: { column: 'interview.start_date', title: 'Date', type: 'date' },
    barcode: { column: 'interview.barcode', title: 'Barcode', type: 'string' },
    type: { type: 'string', title: 'Type', type: 'string' },
    followup: { type: 'string', title: 'Follow-Up', type: 'string' }
  } );

} } );
