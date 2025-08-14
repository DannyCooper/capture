/**
 * WP Capture Admin Subscribers Page JavaScript
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle select all checkbox.
		$('#cb-select-all').on('change', function() {
			$('input[name="subscriber_ids[]"]').prop('checked', this.checked);
		});
	});

})(jQuery);
