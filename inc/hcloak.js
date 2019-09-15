jQuery(document).ready(function(){
	active_category();
	check_catogry_cloak();

	jQuery('.cbox-cat').on('change', function(){
		active_category();
	});
	jQuery('.parent').on('change', function(){
		active_category();
	});	

	jQuery('input[type=radio][name=hcloak]').change(function() {
	    if (this.value != 'cloak_category') {
	        jQuery('.cbox-cat').prop('checked', false);
	    }
	    check_catogry_cloak();
	});

	jQuery(window).load(function(){
		jQuery('.overlay-h').addClass('hide');
	});

	function active_category() {
		jQuery('.cbox-cat').each(function(){
			if ( jQuery(this).prop('checked')) {
				jQuery('.parent').prop('checked', true);
			}
		});
	}

	function check_catogry_cloak(){
		if ( jQuery('input[type=radio][name=hcloak]:checked').val() == 'cloak_category' ) {
			jQuery('#block_all')
				.removeClass('disabled')
				.prop('disabled', false);
			jQuery('.block_all_label').removeClass('disabled');
		}else{
			jQuery('#block_all')
				.addClass('disabled')
				.prop('disabled', true);
			jQuery('.block_all_label').addClass('disabled');
		}
	}

});
