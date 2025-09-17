( function( $, api ) {

	api.controlConstructor['lawyer-consultant-toggle'] = api.Control.extend( {

		ready: function() {
			var control = this;

			this.container.on( 'change', 'input:checkbox', function() {
				value = this.checked ? true : false;
				control.setting.set( value );
			} );
		}
   
	} );

} )( jQuery, wp.customize );