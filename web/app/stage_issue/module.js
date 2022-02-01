cenozoApp.defineModule( { name: 'stage_issue', models: ['list', 'view'], defaultTab: 'stage_issue_note', create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'stage issue',
      plural: 'stage issues',
      possessive: 'stage issue\'s'
    },
    columnList: {
      technician: { column: 'technician.name', title: 'Technician' },
      study_phase: { column: 'study_phase.code', title: 'Phase' },
      platform: { column: 'platform.name', title: 'Platform' },
      stage_type: { column: 'stage_type.name', title: 'Stage Type' },
      date: { title: 'Month', type: 'yearmonth' },
      stage_count: { title: 'Number of Interviews' },
      closed: { type: 'boolean', title: 'Closed' }
    },
    defaultOrder: {
      column: 'stage_issue.date',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    technician: {
      column: 'technician.name',
      title: 'Technician',
      type: 'string',
      isConstant: true
    },
    study_phase: {
      column: 'study_phase.code',
      title: 'Phase',
      type: 'string',
      isConstant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      isConstant: true
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string',
      isConstant: true
    },
    duration_span: {
      title: 'Duration Span',
      type: 'string',
      isConstant: true
    },
    date_string: {
      title: 'Month',
      type: 'string',
      isConstant: true
    },
    stage_count: {
      title: 'Number of Interviews',
      type: 'string',
      isConstant: true
    },
    closed: {
      title: 'Closed',
      type: 'boolean'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageIssueViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root, 'stage_issue_note' );

        async function init( object ) {
          // do not allow stages to be added/removed
          await object.deferred.promise;

          if( angular.isDefined( object.stageModel ) ) object.stageModel.getChooseEnabled = function() { return false; };
        }

        init( this );
      };
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );
} } );
