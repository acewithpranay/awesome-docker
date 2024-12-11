var import_export={};
(function ($){ 

	import_export.get_url_vars=function(){
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	import_export.bulk_initialize=function(action,file_slug,format,ladda,active_btn){

		var js_action_msg =$(active_btn).siblings('.js-action-msg');
		var js_progress = $(active_btn).parents('td.actions').find('.js-progress');	
		event.preventDefault();
		selected_items='';
		if(action=='selected'){
			selected_items = jQuery(".export form").serialize();
		}	
		url=ajaxurl+'?action=awesome_export_code&activity='+action+'&format='+format+'&file_slug='+file_slug+'&'+selected_items;
		ladda.stop();	
		window.location.href = url;
		
	}
	
	import_export.ladda_bind=function(target, options ) {
		options = options || {};

		var targets = [];
		
		if( typeof target === 'string' ) {
			nodes=document.querySelectorAll( target );
			for ( var i = 0; i < nodes.length; i++ ) {
				targets.push( nodes[ i ] );
			}
		}
		else if( typeof target === 'object' && typeof target.nodeName === 'string' ) {
			targets = [ target ];
		}
		
		for( var i = 0, len = targets.length; i < len; i++ ) {
			
			(function() {
				var element = targets[i];
				
				// Make sure we're working with a DOM element
				if( typeof element.addEventListener === 'function' ) {
					var instance = Ladda.create( element );
					
					element.addEventListener( 'click', function( event ) {
						instance.startAfter( 1 );
						// Invoke callbacks
						if( typeof options.callback === 'function' ) {
							options.callback.apply(element, [ instance ] );
						}
					}, false );
				}
			})();
		}
	}

	

}) (jQuery);

 jQuery(document).ready(function($){

	import_export.ladda_bind('.js-app-export-button',{
		callback: function( instance ) {
			var active_btn=this;
			
			var action = this.getAttribute('data-action');
			var file_slug = this.getAttribute('data-file-slug');
			var format = this.getAttribute('data-format');
			import_export.bulk_initialize(action,file_slug,format,instance,active_btn);
			
			
		}
	});

	import_export.ladda_bind('.js-import-blocks',{
		callback: function( instance ) {
			var active_btn=this;
			
			event.preventDefault();

			var action = this.getAttribute('data-action');
			var fd = new FormData();
			var files = $('#upload-gt-block')[0].files;
			
			// Check file selected or not
			if(files.length > 0 ){
				fd.append('file',files[0]);
				url=ajaxurl+'?action=awesome_import_gt_code&activity='+action;
				$.ajax({
					url: url,
					type: 'post',
					data: fd,
					contentType: false,
					processData: false,
					success: function(response){
						
						var data = $.parseJSON(response);
						console.log(data);
						$(".js-status-response").append( "<h2>Importing ("+data['no_of_blocks']+") GT Blocks</h2> <ol></ol>" );
						// function to trigger the ajax call
						var ajax_request = function(item,ticket_id) {
							var deferred = $.Deferred();
							var url=ajaxurl+'?action=awesome_import_single_block&ticket_id='+ticket_id;
							$.ajax({
							url: url,
							dataType: "json",
							type: 'GET',
							data: item,
							success: function(data) {
								// do something here
								//console.log(data);
								if(data.status='success')
									$(".js-status-response ol").append( "<li>Imported <strong>"+data.title+" </strong></li>" );
								else
									$(".js-status-response").append( "<li>Failed to import - <em>"+data.title+"</em></li>" );

								// mark the ajax call as completed
								deferred.resolve(data);
							},
							error: function(error) {
								// mark the ajax call as failed
								deferred.reject(error);
							}
							});

							return deferred.promise();
						};

						var looper = $.Deferred().resolve();

						// go through each item and call the ajax function
						$.when.apply($, $.map(data['blocks'], function(item, i) {
							looper = looper.then(function() {
								// trigger ajax call with item data
								return ajax_request(item,data['ticket_id']);
							});

							return looper;
						})).then(function() {
							// run this after all ajax calls have completed
							console.log('Done!');
						});

						instance.stop();
					},
				});
			}   
			
		}
	});

	import_export.ladda_bind('.js-import-htmlzip-blocks',{
		
		callback: function( instance ) {
			
			var active_btn=this;
			
			event.preventDefault();

			
			var fd = new FormData();
			if($('#overwrite').is(":checked")){
				var overwrite = 'true';
			}
			
			var files = $('#upload-htmlzip-block')[0].files;
			
			// Check file selected or not
			if(files.length > 0 ){
				fd.append('file',files[0]);
				fd.append('overwrite',overwrite);
				
				url=ajaxurl+'?action=awesome_import_zip_html';
				$.ajax({
					url: url,
					type: 'post',
					data: fd,
					contentType: false,
					processData: false,
					success: function(response){
						
						var data = $.parseJSON(response);
						console.log(data);
						$(".js-status-htmlzip-response").append( "<h2>Importing file is done.</h2> <ol></ol>" );
						
						instance.stop();
					},
				});
			}   
			
		}
	});
	
	
});