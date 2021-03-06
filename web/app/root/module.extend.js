cenozoApp.extendModule( { name: 'root', dependencies: ['indicator_issue', 'stage_issue'], create: module => {

  var indicatorIssueModule = cenozoApp.module( 'indicator_issue' );
  var stageIssueModule = cenozoApp.module( 'stage_issue' );

  // extend the view factory
  cenozo.providers.decorator( 'cnHomeDirective', [
    '$delegate', '$compile', 'CnSession', 'CnHttpFactory', 'CnIndicatorIssueModelFactory', 'CnStageIssueModelFactory',
    function( $delegate, $compile, CnSession, CnHttpFactory, CnIndicatorIssueModelFactory, CnStageIssueModelFactory ) {

      var oldController = $delegate[0].controller;
      var oldLink = $delegate[0].link;

      if( [ 'coordinator' ].includes( CnSession.role.name ) ) {
        // show coordinators the list of open issues
        angular.extend( $delegate[0], {
          compile: function() {
            return function( scope, element, attrs ) {
              if( angular.isFunction( oldLink ) ) oldLink( scope, element, attrs );
              angular.element( element[0].querySelector( '.inner-view-frame div' ) ).append(
                '<div class="vertical-spacer">' +
                  '<cn-stage-issue-list model="stageIssueModel" remove-columns="closed" class=""></cn-stage-issue-list>' +
                '</div>' +
                '<div class="vertical-spacer">' +
                  '<cn-indicator-issue-list model="indicatorIssueModel" remove-columns="closed" class=""></cn-indicator-issue-list>' +
                '</div>'
              );
              $compile( element.contents() )( scope );
            };
          },
          controller: function( $scope ) {
            oldController( $scope );
            $scope.indicatorIssueModel = CnIndicatorIssueModelFactory.instance();
            $scope.indicatorIssueModel.listModel.heading = 'Open ' + indicatorIssueModule.name.singular.ucWords() + ' List';
            $scope.indicatorIssueModel
            $scope.stageIssueModel = CnStageIssueModelFactory.instance();
            $scope.stageIssueModel.listModel.heading = 'Open ' + stageIssueModule.name.singular.ucWords() + ' List';
            $scope.stageIssueModel
          }
        } );
      }

      return $delegate;
    }
  ] );

} } );
