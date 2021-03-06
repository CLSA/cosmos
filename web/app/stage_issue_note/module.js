cenozoApp.defineModule( { name: 'stage_issue_note', models: ['add', 'list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage_issue',
        column: 'stage_issue_id'
      }
    },
    name: {
      singular: 'note',
      plural: 'notes',
      possessive: 'note\'s'
    },
    columnList: {
      user: { column: 'user.name', title: 'Username' },
      datetime: { type: 'datetime', title: 'Date & Time' },
      note: { type: 'text', align: 'left', width: '70%', title: 'Note', limit: null }
    },
    defaultOrder: {
      column: 'datetime',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    user_id: {
      title: 'User',
      type: 'lookup-typeahead',
      typeahead: {
        table: 'user',
        select: 'CONCAT( user.first_name, " ", user.last_name, " (", user.name, ")" )',
        where: [ 'user.first_name', 'user.last_name', 'user.name' ]
      },
      isConstant: true,
      isExcluded: 'add'
    },
    datetime: {
      title: 'Date & Time',
      type: 'datetime',
      isConstant: true,
      isExcluded: 'add'
    },
    note: {
      title: 'Note',
      type: 'text'
    },
    is_most_recent: {
      type: 'boolean',
      isExcluded: true
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageIssueNoteViewFactory', [
    'CnBaseViewFactory', 'CnSession',
    function( CnBaseViewFactory, CnSession ) {
      var object = function( parentModel, root ) {
        CnBaseViewFactory.construct( this, parentModel, root );

        angular.extend( this, {
          onView: async function( force ) {
            await this.$$onView( force );
            
            var self = this;
            angular.extend( this.parentModel, {
              getEditEnabled: function() {
                return angular.isDefined( this.module.actions.edit ) && (
                  3 <= CnSession.role.tier ||
                  ( self.record.user_id == CnSession.user.id && self.record.is_most_recent )
                );
              },
              getDeleteEnabled: function() {
                return angular.isDefined( this.module.actions.delete ) && (
                  3 <= CnSession.role.tier ||
                  ( self.record.user_id == CnSession.user.id && self.record.is_most_recent )
                );
              }
            } );
          }
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

} } );
