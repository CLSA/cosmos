cenozoApp.defineModule( { name: 'technician', models: ['add', 'list', 'view'], create: module => {

  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'technician',
      plural: 'technicians',
      possessive: 'technician\'s'
    },
    columnList: {
      name: { title: 'Name' }
    },
    defaultOrder: {
      column: 'name',
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
