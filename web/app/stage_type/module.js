cenozoApp.defineModule( { name: 'stage_type', models: ['list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'stage type',
      plural: 'stage types',
      possessive: 'stage type\'s'
    },
    columnList: {
      study_phase: { column: 'study_phase.name', title: 'Study Phase' },
      platform: { column: 'platform.name', title: 'Platform' },
      name: { column: 'stage_type.name', title: 'Name' }
    },
    defaultOrder: {
      column: 'study_phase.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.name',
      title: 'Study Phase',
      type: 'string',
      isConstant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      isConstant: true
    },
    name: {
      column: 'stage_type.name',
      title: 'Name',
      type: 'string',
      isConstant: true
    },
    duration_low: {
      title: 'Duration Low (seconds)',
      type: 'string',
      format: 'float'
    },
    duration_high: {
      title: 'Duration High (seconds)',
      type: 'string',
      format: 'float'
    },
    contraindicated: {
      title: 'Contraindicated',
      type: 'string',
      isConstant: true
    },
    missing: {
      title: 'Missing',
      type: 'string',
      isConstant: true
    },
    min_date: {
      type: 'date',
      isExcluded: true
    },
    max_date: {
      type: 'date',
      isExcluded: true
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageTypeViewFactory', [
    'CnBaseViewFactory', 'CnPlotHelperFactory',
    function( CnBaseViewFactory, CnPlotHelperFactory ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root );

        // use the plot helper to set up an outlier and histogram plot for this indicator
        var self = this;
        CnPlotHelperFactory.addPlot( this, {
          getType: function() { return 'time'; },
          getPath: function() { return self.parentModel.getServiceResourcePath() + '/stage?plot=duration' },
          getXLabel: function() { return 'Stage Duration'; },
          getBinSize: function() { return 30; },
          minName: 'duration_low',
          maxName: 'duration_high'
        } );

        async function init( object ) {
          await object.deferred.promise;
          if( angular.isDefined( object.indicatorModel ) ) object.indicatorModel.listModel.heading = 'Indicator List';
        }

        init( this );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

} } );
