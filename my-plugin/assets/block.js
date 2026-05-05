( function( blocks, element ) {
    var el = element.createElement;

    blocks.registerBlockType( 'my-plugin/my-block', {
        title: 'My Plugin Block',
        icon: 'smiley',
        category: 'widgets',
        edit: function() {
            return el( 'p', null, 'working plugin (editor)' );
        },
        save: function() {
            return null; // server-rendered
        }
    } );
} )( window.wp.blocks, window.wp.element );
