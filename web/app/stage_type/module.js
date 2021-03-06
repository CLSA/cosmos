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
      column: 'study_phase',
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
    median: {
      title: 'Median Duration',
      type: 'string',
      format: 'float',
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
    contraindicated_count: {
      title: 'Total Contraindicated',
      type: 'string',
      isConstant: true
    },
    missing_count: {
      title: 'Total Missing',
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

  module.addExtraOperation( 'list', {
    title: 'Recalculate All Boundaries',
    isDisabled: function( $state, model ) {
      return !model.listModel.isMasterUser() || model.listModel.recalculatingBoundaries;
    },
    operation: async function( $state, model ) {
      await model.listModel.recalculateAllBoundaries();
    },
    help: 'Developer access only'
  } );

  module.addExtraOperation( 'view', {
    title: 'Recalculate Boundaries',
    isDisabled: function( $state, model ) {
      return model.viewModel.recalculatingBoundaries;
    },
    operation: async function( $state, model ) {
      await model.viewModel.recalculateBoundaries();
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageTypeListFactory', [
    'CnBaseListFactory', 'CnModalConfirmFactory', 'CnModalMessageFactory', 'CnHttpFactory', 'CnSession',
    function( CnBaseListFactory, CnModalConfirmFactory, CnModalMessageFactory, CnHttpFactory, CnSession ) {
      var object = function( parentModel ) {
        CnBaseListFactory.construct( this, parentModel );
        angular.extend( this, {
          isMasterUser: function() { return 1 == CnSession.user.id; },
          recalculatingBoundaries: false,
          recalculateAllBoundaries: async () => {
            var response = await CnModalConfirmFactory.instance( {
              title: 'Please Confirm',
              message: 'Recalculating all stage type and indicator boundaries is time consuming and will cause ' +
                       'significant impact to the database, possibly slowing down this and other applications.\n\n' +
                       'Are you sure you wish to proceed?'
            } ).show();

            if( response ) {
              var modal = CnModalMessageFactory.instance( {
                title: 'Please Wait',
                message: 'Please wait while all stage type duration and indicator boundaries are calculated. ' +
                         'This may take a while.',
                block: true
              } );

              try {
                this.recalculatingBoundaries = true;
                modal.show();
                await CnHttpFactory.instance( {
                  path: this.parentModel.getServiceCollectionPath() + '?recalculate_boundaries=1'
                } ).get();
              } finally {
                modal.close();
                this.recalculatingBoundaries = false;
              }
            }
          }
        } );
      };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageTypeViewFactory', [
    'CnBaseViewFactory', 'CnPlotHelperFactory', 'CnModalMessageFactory', 'CnHttpFactory',
    function( CnBaseViewFactory, CnPlotHelperFactory, CnModalMessageFactory, CnHttpFactory ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root );

        angular.extend( this, {
          recalculatingBoundaries: false,
          recalculateBoundaries: async () => {
            var modal = CnModalMessageFactory.instance( {
              title: 'Please Wait',
              message: 'Please wait while stage type duration boundaries are calculated.',
              block: true
            } );

            try {
              this.recalculatingBoundaires = true;
              modal.show();
              var response = await CnHttpFactory.instance( {
                path: this.parentModel.getServiceResourcePath() + '?recalculate_boundaries=1'
              } ).get();
              this.record.duration_low = response.data.duration_low;
              this.record.duration_high = response.data.duration_high;
            } finally {
              modal.close();
              this.recalculatingBoundaires = false;
            }
          }
        } );

        // use the plot helper to set up an outlier and histogram plot for this indicator
        var self = this;
        CnPlotHelperFactory.addPlot( this, {
          getType: function() { return 'time'; },
          getPath: function() { return self.parentModel.getServiceResourcePath() + '/stage?plot=duration' },
          getXLabel: function() { return 'Stage Duration'; },
          getBinSize: function() { return Math.ceil( ( self.record.duration_high - self.record.duration_low )/100 ); },
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
