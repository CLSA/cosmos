var dataModuleList = [ 'bl_dcs_auxiliary_data', 'bl_dcs_carotid_intima_data', 'bl_dcs_ecg_data', 'bl_dcs_interview_data', 'bl_inhome_inhome_1_data', 'bl_inhome_inhome_2_data', 'bl_inhome_inhome_3_data', 'bl_inhome_inhome_cognition_recording_data', 'bl_inhome_inhome_conclusion_qnaire_data', 'bl_inhome_inhome_id_data', 'bl_inhome_inhome_scoring_data', 'bl_inhome_interview_data', 'f1_dcs_auxiliary_data', 'f1_dcs_carotid_intima_data', 'f1_dcs_ecg_data', 'f1_dcs_interview_data', 'f1_dcs_home_blood_pressure_data', 'f1_dcs_home_body_composition_data', 'f1_dcs_home_contraindication_qnaire_data', 'f1_dcs_home_disease_qnaire_data', 'f1_dcs_home_event_pmt_data', 'f1_dcs_home_functional_status_data', 'f1_dcs_home_general_health_data', 'f1_dcs_home_grip_strength_data', 'f1_dcs_home_hips_waist_data', 'f1_dcs_home_interview_data', 'f1_dcs_home_neuro_scoring_data', 'f1_dcs_home_osonly_data', 'f1_dcs_home_spirometry_data', 'f1_dcs_home_stroop_fas_data', 'f1_dcs_home_time_based_pmt_data', 'f1_dcs_phone_contraindication_qnaire_data', 'f1_dcs_phone_disease_qnaire_data', 'f1_dcs_phone_functional_status_data', 'f1_dcs_phone_general_health_data', 'f1_dcs_phone_height_weight_data', 'f1_dcs_phone_interview_data', 'f1_dcs_phone_osea_data', 'f1_dcs_phone_stroop_fas_data', 'f1_inhome_inhome_1_data', 'f1_inhome_inhome_2_data', 'f1_inhome_inhome_3_data', 'f1_inhome_inhome_4_data', 'f1_inhome_inhome_cognition_recording_data', 'f1_inhome_inhome_conclusion_qnaire_data', 'f1_inhome_interview_data', 'f2_dcs_auxiliary_data', 'f2_dcs_carotid_intima_data', 'f2_dcs_ecg_data', 'f2_dcs_interview_data', 'f2_dcs_phone_contraindication_qnaire_data', 'f2_dcs_phone_disease_qnaire_data', 'f2_dcs_phone_general_health_data', 'f2_dcs_phone_height_weight_data', 'f2_dcs_phone_interview_data', 'f2_dcs_phone_os_data', 'f2_dcs_phone_social_network_data', 'f2_inhome_inhome_1_data', 'f2_inhome_inhome_2_data', 'f2_inhome_inhome_3_data', 'f2_inhome_inhome_4_data', 'f2_inhome_inhome_cognition_recording_data', 'f2_inhome_inhome_conclusion_qnaire_data', 'f2_inhome_interview_data' ];
define( dataModuleList.reduce( function( list, name ) {
  return list.concat( cenozoApp.module( name ).getRequiredFiles() );
}, [] ), function () {
  'use strict';

  try { var module = cenozoApp.module( 'stage', true ); } catch( err ) { console.warn( err ); return; }
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
      uid: {
        column: 'participant.uid',
        title: 'UID'
      },
      study_phase: {
        column: 'study_phase.code',
        title: 'Phase'
      },
      stage_type: {
        column: 'stage_type.name',
        title: 'Stage Type'
      },
      technician: {
        column: 'technician.name',
        title: 'Technician'
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
        title: 'Skip'
      },
      duration: {
        title: 'Duration',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'stage_type.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
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
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageList', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageView', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  var injectionList = [ 'CnBaseViewFactory' ];
  injectionList = injectionList.concat( dataModuleList.map( dataModule => 'Cn' + dataModule.snakeToCamel( true ) + 'ModelFactory' ) );
  cenozo.providers.factory( 'CnStageViewFactory',
    injectionList.concat( function( ...injected ) {
      var object = function( parentModel, root ) {
        var self = this;
        this.indicatorList = [];
        injected[0].construct( this, parentModel, root );

        this.onView = function( force ) {
          self.dataModel = null;
          return this.$$onView( force ).then( function() {
            self.dataModel = injected[ dataModuleList.indexOf( self.record.stage_type ) ].root;
            self.dataModel.viewModel.onView( true );
          } );
        };
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    } )
  );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageModelFactory', [
    'CnBaseModelFactory', 'CnStageListFactory', 'CnStageViewFactory',
    function( CnBaseModelFactory, CnStageListFactory, CnStageViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnStageListFactory.instance( this );
        this.viewModel = CnStageViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
