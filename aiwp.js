/**
 * Theme cards for displaying excerpt and post content.
 */
jQuery(document).ready(function( $ ) {

	// initiate AppearIn SDK
	var aiwp = new AppearIn();

	// check if webRTC compatible
	var aiwpCompatible = aiwp.isWebRtcCompatible();

	// get roomname from URI if provided
	var aiRoom = lookToURI( 'room' );

	if ( aiwpCompatible ) {
		$('#webrtc-compatability-tester').hide();
	} else {
	    $("#aiwp-container:not('.aiwp-ios') #appearin-incompatibility").show();
        $("#aiwp-container:not('.aiwp-ios') #aiwp-room-type-selection").hide();
	}
   
    if ( '' != aiRoom ) {
    	$( window ).on( 'load', function() {
			window.location.hash = 'appearin-room';
		});

    	// hide room type selection
    	$("#aiwp-container:not('.aiwp-ios') #aiwp-room-type-selection").hide();

    	// launch room
    	if ( $('.aiwp-ios').length ) {
    		window.location.replace('http://'+aiRoom.replace('?lite',''));
    	} else {
    		launchAppearInRoom( aiRoom );
    	}
    } // end if

    var roomURL = window.location.host + window.location.pathname;
    var roomName = $('#appearin-room').attr('data-room-name');

    // Handle Room Assignment on iOS
	$('#aiwp-container.aiwp-ios #aiwp-room-type-selection #aiwp-post').attr('href','http://appear.in/'+roomURL);
	$('#aiwp-container.aiwp-ios #aiwp-room-type-selection #aiwp-public').attr('href','http://appear.in/'+roomName);
	aiwp.getRandomRoomName().then(function(randroomName) {
		$('#aiwp-container.aiwp-ios #aiwp-room-type-selection #aiwp-private').attr('href','http://appear.in/'+randroomName);
	});

	// Handle Room Selection
	$("#aiwp-container:not('.aiwp-ios') #aiwp-select-public-room,#aiwp-container:not('.aiwp-ios') #aiwp-select-private-room,#aiwp-container:not('.aiwp-ios') #aiwp-select-post-room").click( function () {
			
		var roomType = $(this).data('room-type');

		if ( 'post' == roomType ) {
			
			launchAppearInRoom( roomURL );		
		} else if ( 'public' == roomType ) {

			launchAppearInRoom( roomName );
		} else if ( 'private' == roomType ) {
			aiwp.getRandomRoomName().then(function(roomName) {
				launchAppearInRoom( roomName.replace('/','') );
			});
		}

	});

	function launchAppearInRoom( randomString ) {
		if ( randomString.indexOf('appear.in') >= 0 ) {
			var roomName = randomString.replace('?lite','');
		} else {
			var roomName = 'appear.in/' + randomString;
		}
		var roomNameLite = roomName + '?lite';
		if ( '/' != window.location.pathname ) {
			var roomURL = window.location.host + window.location.pathname;
		} else if ( '' != window.location.search ) {
			var roomURL = window.location.host + window.location.search;
			var n = roomURL.indexOf('?room=');
			roomURL = roomURL.substring(0, n != -1 ? n : roomURL.length);
		} else {
			var roomURL = window.location.host
		}
		// set the iframe source to load the room
		var iframe = document.getElementById('appearin-room');
		iframe.setAttribute('src', window.location.protocol + "//" + roomNameLite);

		var container = $('#aiwp-container');
		container.addClass('aiwp-room-threshold');
		if ( 'bottom' == container.data('position') || 'left' == container.data('position') ) {
			$('body').after(container);
		}
		$('body').append($('#aiwp-maximize'));

		$('#aiwp-room-type-selection').hide();
		$('#aiwp-controls').show();
		$('#aiwp-invite-facebook').attr('href','https://facebook.com/sharer.php?u='+roomURL+'?room='+roomNameLite);
		$('#aiwp-invite-twitter').attr('href','https://twitter.com/intent/tweet?url='+window.location.protocol+'//'+roomURL+'?room='+roomNameLite+'&text=Join%20me%20in%20an%20%23appear_inWP%20video%20chat%20at');
		$('#aiwp-invite-email').attr('href','mailto:?subject=You\'ve%20been%20invited%20to%20appear.in%20a%20video%20chat&body='+roomURL+'?room='+roomNameLite);
		
		if ( 'bottom' != container.data('position') && 'left' != container.data('position') ) {
			$('#appearin-room-label').html(roomURL+'?room='+roomNameLite);
		}

		if ( 'left' == container.data('position') ) {
			$('body').css('margin-left', '380px' );
		} else {
			container.css('height',container.data('room-height'));
		}

		if ( 'bottom' == container.data('position') ) {
			$('body').css('margin-bottom',container.data('room-height'));
		}

		$('#appearin-room').css('height',container.height()-40);

		window.onbeforeunload = function(){
		    return 'Active sessions at ' + roomName + ' will be ended.'; 
		}

	}

	function lookToURI(name){
		if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search)) {
			return decodeURIComponent(name[1]);
		} else {
			return '';
		}
	}

	$('#aiwp-minimize').click( function() {
		$('#aiwp-container').slideUp();
		$('body').css('margin-bottom',0);
		if ( 'left' == $('#aiwp-container').data('position') ) {
			$('body').animate({marginLeft: '-=380px' }, 600);
		}
		$('#aiwp-maximize').delay(400).show(200);
	});

	$('#aiwp-maximize').click( function() {
		$('#aiwp-container').slideDown();
		$('body').css('margin-bottom',$('#aiwp-container').data('room-height'));
		if ( 'left' == $('#aiwp-container').data('position') ) {
			$('body').animate({marginLeft: '+=380px' }, 600);
		}
		$('#aiwp-maximize').hide();
	});

	$('#aiwp-move-bottom').click( function() {
		$('body').css('margin-bottom',$('#aiwp-container').data('room-height'));
		if ( 'left' == $('#aiwp-container').data('position') ) {
			$('body').animate({marginLeft: '-=380px' }, 600);
			$('#aiwp-container').animate({height: '275px',width:'100%'}, 600);
			$('#appearin-room').animate({height: '235px'}, 600);
		}
		$(this).hide();
		$('#aiwp-move-left').show();
		$('#aiwp-container').data('position','bottom');
	});

	$('#aiwp-move-left').click( function() {
		$('body').css('margin-bottom','-='+$('#aiwp-container').data('room-height'));
		if ( 'bottom' == $('#aiwp-container').data('position') ) {
			$('body').animate({marginLeft: '+=380px' }, 600);
			if ( $('body').hasClass('logged-in') ) {
				$('#aiwp-container').animate({height: $(window).height()-32,width:'380px'}, 600);
				$('#appearin-room').animate({height: $(window).height()-72}, 600);
			} else {
				$('#aiwp-container').animate({height: $(window).height(),width:'380px'}, 600);
				$('#appearin-room').animate({height: $(window).height()-40}, 600);
			}
		}
		$(this).hide();
		$('#aiwp-move-bottom').show();
		$('#aiwp-container').data('position','left');
	});

});
