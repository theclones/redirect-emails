( function( api ) {

	// Extends our custom "forest-jungle-safari" section.
	api.sectionConstructor['forest-jungle-safari'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );