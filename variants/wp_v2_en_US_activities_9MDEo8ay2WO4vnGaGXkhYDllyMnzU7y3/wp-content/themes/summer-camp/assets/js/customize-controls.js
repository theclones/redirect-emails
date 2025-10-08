( function( api ) {

	// Extends our custom "summer-camp" section.
	api.sectionConstructor['summer-camp'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );