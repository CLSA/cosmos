cenozoApp.extendModule( { name: 'root', dependencies: ['stage_issue'], create: module => {

  var stageIssueModule = cenozoApp.module( 'stage_issue' );

  // extend the view factory
  cenozo.providers.decorator( 'cnHomeDirective', [
    '$delegate', '$compile', 'CnSession', 'CnHttpFactory', 'CnStageIssueModelFactory',
    function( $delegate, $compile, CnSession, CnHttpFactory, CnStageIssueModelFactory ) {

      var oldController = $delegate[0].controller;
      var oldLink = $delegate[0].link;

      if( [ 'coordinator' ].includes( CnSession.role.name ) ) {
        // show coordinators the list of open issues
        angular.extend( $delegate[0], {
          compile: function() {
            return function( scope, element, attrs ) {
              if( angular.isFunction( oldLink ) ) oldLink( scope, element, attrs );
              angular.element( element[0].querySelector( '.inner-view-frame div' ) ).append(
                '<cn-stage-issue-list model="stageIssueModel" remove-columns="closed"></cn-stage-issue-list>'
              );
              $compile( element.contents() )( scope );
            };
          },
          controller: function( $scope ) {
            oldController( $scope );
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
