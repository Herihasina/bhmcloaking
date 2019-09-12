jQuery(document).ready(function(){
	active_category();

	jQuery('.cbox-cat').on('change', function(){
		active_category();
	});
	jQuery('.parent').on('change', function(){
		console.log( "alert" );
		active_category();
	});

	function active_category() {
		jQuery('.cbox-cat').each(function(){
			if ( jQuery(this).prop('checked')) {
				jQuery('.parent').prop('checked', true);
			}
		});
	}

	jQuery('input[type=radio][name=hcloak]').change(function() {
	    if (this.value != 'cloak_category') {
	        jQuery('.cbox-cat').prop('checked', false);
	    }
	});

	jQuery(window).load(function(){
		jQuery('.overlay-h').addClass('hide');
	});
});
