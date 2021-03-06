cenozoApp.defineModule( { name: 'indicator', models: ['list', 'view'], create: module => {

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
      study_phase: { column: 'study_phase.code', title: 'Study Phase' },
      platform: { column: 'platform.name', title: 'Platform' },
      stage_type: { column: 'stage_type.name', title: 'Stage Type' },
      name: { column: 'indicator.name', title: 'Name' },
      type: { title: 'Type' },
      minimum: { title: 'Minimum', type: 'number' },
      maximum: { title: 'Maximum', type: 'number' },
      outlier_low: { title: 'Low Outliers' },
      outlier_high: { title: 'High Outliers' }
    },
    defaultOrder: {
      column: 'indicator.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.code',
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
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string',
      isConstant: true
    },
    name: {
      title: 'Name',
      type: 'string',
      isConstant: true
    },
    type: {
      title: 'Type',
      type: 'string',
      isConstant: true
    },
    median: {
      title: 'Median Value',
      type: 'string',
      format: 'float',
      isConstant: true,
      isExcluded: function( $state, model ) {
        return angular.isDefined( model.viewModel.record.type ) && !['float', 'integer'].includes( model.viewModel.record.type );
      }
    },
    minimum: {
      title: 'Minimum Threshold',
      type: 'string',
      format: 'float',
      isExcluded: function( $state, model ) {
        return angular.isDefined( model.viewModel.record.type ) && !['float', 'integer'].includes( model.viewModel.record.type );
      }
    },
    maximum: {
      title: 'Maximum Threshold',
      type: 'string',
      format: 'float',
      isExcluded: function( $state, model ) {
        return angular.isDefined( model.viewModel.record.type ) && !['float', 'integer'].includes( model.viewModel.record.type );
      }
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

  module.addExtraOperation( 'view', {
    title: 'Recalculate Boundaries',
    isIncluded: function( $state, model ) {
      return angular.isDefined( model.viewModel.record.type ) && ['float', 'integer'].includes( model.viewModel.record.type );
    },
    isDisabled: function( $state, model ) { return model.viewModel.recalculatingBoundaries; },
    operation: async function( $state, model ) { await model.viewModel.recalculateBoundaries(); }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorListFactory', [
    'CnBaseListFactory', 'CnHttpFactory',
    function( CnBaseListFactory, CnHttpFactory ) {
      var object = function( parentModel ) {
        CnBaseListFactory.construct( this, parentModel );
        angular.extend( this, {
          heading: 'Outlier List',
          onSetDateSpan: function() {
            this.parentModel.updateDateSpan();
            this.onList();
          }
        } );
      };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorViewFactory', [
    'CnBaseViewFactory', 'CnPlotHelperFactory', 'CnModalMessageFactory', 'CnHttpFactory',
    function( CnBaseViewFactory, CnPlotHelperFactory, CnModalMessageFactory, CnHttpFactory ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root );

        angular.extend( this, {
          recalculatingBoundaries: false,
          recalculateBoundaries: async () => {
            var modal = CnModalMessageFactory.instance( {
              title: 'Please Wait',
              message: 'Please wait while indicator boundaries are calculated.',
              block: true
            } );

            try {
              this.recalculatingBoundaires = true;
              modal.show();
              var response = await CnHttpFactory.instance( {
                path: this.parentModel.getServiceResourcePath() + '?recalculate_boundaries=1'
              } ).get();
              this.record.minimum = response.data.minimum;
              this.record.maximum = response.data.maximum;
              await this.onView();

            } finally {
              modal.close();
              this.recalculatingBoundaires = false;
            }
          }
        } );

        // use the plot helper to set up an outlier and histogram plot for this indicator
        var self = this;
        CnPlotHelperFactory.addPlot( this, {
          isExcluded: function( $state, model ) {
            return !['float', 'integer'].includes( model.viewModel.record.type );
          },
          getType: function() {
            // determine if the indicator has a special data type
            if( null != self.record.name.match( /^[a-zA-Z0-9_]+_size\./ ) ) { 
              return 'file';
            } else if( null != self.record.name.match( /[a-zA-Z0-9_]+_duration\./ ) ) {
              return 'time';
            } else if( null != self.record.name.match( /[a-zA-Z0-9_]+_complete\./ ) ) {
              return 'percent';
            }
            return '';
          },
          getPath: function() {
            return 'stage_type/' + self.record.stage_type_id + '/stage?plot=' + self.record.name;
          },
          getXLabel: function() {
            return ( self.record.study_phase ? self.record.study_phase.toUpperCase() + ': ' : '' ) +
                   [self.record.platform, self.record.stage_type, self.record.name].join( '/' );
          },
          getBinSize: function() { return Math.ceil( ( self.record.maximum - self.record.minimum )/100 ); }
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorModelFactory', [
    'CnBaseModelFactory', 'CnIndicatorListFactory', 'CnIndicatorViewFactory', 'CnSession', 'CnHttpFactory', '$q',
    function( CnBaseModelFactory, CnIndicatorListFactory, CnIndicatorViewFactory, CnSession, CnHttpFactory, $q ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );

        angular.extend( this, {
          listModel: CnIndicatorListFactory.instance( this ),
          viewModel: CnIndicatorViewFactory.instance( this, root ),
          dateSpan: { low: null, high: null, list: [], lowList: [], highList: [] },

          setupBreadcrumbTrail: function() {
            if( angular.isUndefined( this.getParentIdentifier().subject ) && 'indicator' == this.getSubjectFromState() ) {
              CnSession.setBreadcrumbTrail( [ { title: 'Outliers' } ] );
            } else {
              this.$$setupBreadcrumbTrail();
            }
          },

          // when viewing the root list of indicators with no parent show outliers instead
          getServiceData: function( type, columnRestrictLists ) {
            var data = this.$$getServiceData( type, columnRestrictLists );
            if( 'list' == type && 'indicator' == this.getSubjectFromState() ) {
              if( angular.isUndefined( data.modifier ) ) data.modifier = {};
              if( angular.isUndefined( data.modifier.where ) ) data.modifier.where = [];
              data.modifier.where.push( {
                bracket: true,
                open: true
              } );
              data.modifier.where.push( {
                column: 'outlier_low',
                operator: '>',
                value: 0
              } );
              data.modifier.where.push( {
                column: 'outlier_high',
                operator: '>',
                value: 0,
                or: true
              } );
              data.modifier.where.push( {
                bracket: true,
                open: false
              } );

              if( this.dateSpan.low && this.dateSpan.high ) {
                data.modifier.where.push( {
                  bracket: true,
                  open: true
                } );
                data.modifier.where.push( {
                  column: 'outlier.date',
                  operator: '>=',
                  value: this.dateSpan.low
                } );
                data.modifier.where.push( {
                  column: 'outlier.date',
                  operator: '<=',
                  value: this.dateSpan.high
                } );
                data.modifier.where.push( {
                  bracket: true,
                  open: false
                } );
              }
            }
            return data;
          },

          // updates the plots based on the selected date span
          updateDateSpan: function() {
            if( null == this.dateSpan.low ) this.dateSpan.low = this.dateSpan.list[0].value;
            if( null == this.dateSpan.high ) this.dateSpan.high = this.dateSpan.list[this.dateSpan.list.length-1].value;

            // change the low/high lists based on the selected values
            this.dateSpan.lowList = this.dateSpan.list.filter( item => item.value < this.dateSpan.high );
            this.dateSpan.highList = this.dateSpan.list.filter( item => item.value > this.dateSpan.low );
          }
        } );

        async function init( object ) {
          var response = await CnHttpFactory.instance( {
            path: 'interview',
            data: {
              select: {
                column: [
                  { column: 'MIN( start_date )', alias: 'min_date', table_prefix: false },
                  { column: 'MAX( start_date )', alias: 'max_date', table_prefix: false }
                ]
              }
            }
          } ).query();

          // determine the date spans
          var date = moment( new Date( response.data[0].min_date ) );
          date.day( 1 );
          var endDate = moment( new Date( response.data[0].max_date ) );
          endDate.day( 1 );
          object.dateSpan.list = [];
          while( date.isSameOrBefore( endDate ) ) {
            object.dateSpan.list.push( {
              name: date.format( 'MMMM, YYYY' ),
              value: date.format( 'YYYY-MM-01' )
            } );
            date.add( 1, 'month' );
          }
          object.updateDateSpan();
        }

        init( this );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} } );
