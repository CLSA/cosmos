cenozoApp.defineModule( { name: 'stage', models: ['list', 'view'], dependencies: 'interview', create: module => {

  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'interview',
        column: 'interview_id'
      }
    },
    name: {
      singular: 'stage',
      plural: 'stages',
      possessive: 'stage\'s'
    },
    columnList: {
      uid: { column: 'participant.uid', title: 'UID' },
      barcode: { column: 'interview.barcode', title: 'Barcode' },
      stage_type: { column: 'stage_type.name', title: 'Stage Type' },
      technician: { column: 'technician.name', title: 'Technician' },
      contraindicated: { title: 'Contraindicated', type: 'boolean' },
      missing: { title: 'Missing', type: 'boolean' },
      skip: { title: 'Skip', type: 'string' },
      duration: { title: 'Duration', type: 'seconds' }
    },
    defaultOrder: {
      column: 'stage_type.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.code',
      title: 'Phase',
      type: 'string'
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string'
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string'
    },
    technician: {
      column: 'technician.name',
      title: 'Technician',
      type: 'string'
    },
    contraindicated: {
      title: 'Contraindicated',
      type: 'boolean'
    },
    missing: {
      title: 'Missing',
      type: 'boolean'
    },
    skip: {
      title: 'Skip',
      type: 'string'
    },
    duration: {
      title: 'Duration',
      type: 'string',
      format: 'float'
    },
    data: {
      title: 'Raw Data',
      type: 'text'
    }
  } );

} } );
