( function( api ) {

	// Extends our custom "accommodation-rental" section.
	api.sectionConstructor['accommodation-rental'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );