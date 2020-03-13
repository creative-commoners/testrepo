(function( factory ) {
	if ( typeof define === "function" && define.amd ) {
		define( ["jquery", "../jquery.validate"], factory );
	} else {
		factory( jQuery );
	}
}(function( $ ) {

/*
 * Localized default methods for the jQuery validation plugin.
 * Locale: NL
 */
$.extend($.validator.methods, {
	date: function(value, element) {
		return this.optional(element) || /^[\d]{1,4}\-[0-1][0-9]\-[0-3][0-9]?$/.test(value);
	}
});

}));