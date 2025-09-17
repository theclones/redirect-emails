( function( api ) {

	// Extends our custom "industrial-technology" section.
	api.sectionConstructor['industrial-technology'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );