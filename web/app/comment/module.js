cenozoApp.defineModule( { name: 'comment', models: ['list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage',
        column: 'stage_id'
      }
    },
    name: {
      singular: 'comment',
      plural: 'comments',
      possessive: 'comment\'s'
    },
    columnList: {
      rank: { type: 'rank', title: 'Rank' },
      type: { type: 'string', title: 'Type' },
      note: { type: 'text', align: 'left', width: '70%', title: 'Comment', limit: null }
    },
    defaultOrder: {
      column: 'rank',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    rank: {
      title: 'Rank',
      type: 'rank'
    },
    type: {
      title: 'Type',
      type: 'string'
    },
    note: {
      title: 'Comment',
      type: 'text'
    }
  } );

} } );
