cenozoApp.defineModule( { name: 'indicator_issue', models: ['list', 'view'], defaultTab: 'indicator_issue_note', create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'indicator issue',
      plural: 'indicator issues',
      possessive: 'indicator issue\'s'
    },
    columnList: {
      site: { column: 'site.name', title: 'Site' },
      technician: { column: 'technician.name', title: 'Technician' },
      study_phase: { column: 'study_phase.code', title: 'Phase' },
      platform: { column: 'platform.name', title: 'Platform' },
      stage_type: { column: 'stage_type.name', title: 'Stage Type' },
      indicator: { column: 'indicator.name', title: 'Indicator' },
      date: { title: 'Month', type: 'yearmonth' },
      stage_count: { title: 'Number of Interviews', type: 'number' },
      closed: { type: 'boolean', title: 'Closed' }
    },
    defaultOrder: {
      column: 'date',
      reverse: true
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
    indicator: {
      column: 'indicator.name',
      title: 'Indicator',
      type: 'string',
      isConstant: true
    },
    value_span: {
      title: 'Value Span',
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
  cenozo.providers.directive( 'cnIndicatorIssueList', [
    'CnIndicatorIssueModelFactory',
    function( CnIndicatorIssueModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?', removeColumns: '@' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnIndicatorIssueModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorIssueViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root, 'indicator_issue_note' );

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

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorIssueModelFactory', [
    'CnBaseModelFactory', 'CnIndicatorIssueListFactory', 'CnIndicatorIssueViewFactory',
    function( CnBaseModelFactory, CnIndicatorIssueListFactory, CnIndicatorIssueViewFactory ) {
      var object = function( root ) {
        CnBaseModelFactory.construct( this, module );

        angular.extend( this, {
          listModel: CnIndicatorIssueListFactory.instance( this ),
          viewModel: CnIndicatorIssueViewFactory.instance( this, root ),
          getServiceCollectionPath: function( ignoreParent ) {
            // ignore the parent if we're on the root view
            return this.$$getServiceCollectionPath( ignoreParent || 'root' == this.getSubjectFromState() );
          },
          getServiceData: function( type, columnRestrictLists ) {
            var data = this.$$getServiceData( type, columnRestrictLists );

            // only show open issues when on the root view
            if( 'root' == this.getSubjectFromState() ) {
              if( angular.isUndefined( data.modifier ) ) data.modifier = {};
              if( angular.isUndefined( data.modifier.where ) ) data.modifier.where = [];
              data.modifier.where.push( { column: 'indicator_issue.closed', operator: '=', value: false } );
            }

            return data;
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
