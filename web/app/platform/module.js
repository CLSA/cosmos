cenozoApp.defineModule( { name: 'platform', models: ['list', 'view'], create: module => {

  angular.extend( module, {
    identifier: { column: 'name' },
    name: {
      singular: 'platform',
      plural: 'platforms',
      possessive: 'platform\'s'
    },
    columnList: {
      name: { title: 'Name' }
    },
    defaultOrder: {
      column: 'platform.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    name: {
      title: 'Name',
      type: 'string'
    }
  } );

} } );
