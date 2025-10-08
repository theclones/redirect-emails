( function( api ) {

	// Extends our custom "catering-services" section.
	api.sectionConstructor['catering-services'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );