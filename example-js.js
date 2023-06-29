$(document).ready(function() {

	var regular = { "mac" : /\b([0-9a-fA-F]{2}([:-]|$)){6}$|([0-9a-fA-F]{4}([.]|$)){3}\b/ };


	var mac		= $(".mac").data("mac");
	var status	= {"mac" : mac};
	var vendor	= {"vendor" : mac};
		
	if (mac.search(regular.mac) == -1) {
		$(".status").text("N/A");
	} else {
			$.post("users_status", status, function(request) {
				response = $.parseJSON(request);

				if (response.error) {
					$(".status").addClass("label label-inverse").text("ERROR");
				} else {

					$(".status").data("placement", "top").data("title", response.note).tooltip();
					//$(".mac").data("placement", "top").data("title", response.vendor).tooltip();

					if (response.active == "wan") {
						if (response.sec == 0) {
							$(".status").addClass("label").text("RENEW");
						} else if (response.sec < 1805) {
							$(".status").addClass("label label-success").text("ONLINE");
						} else if (response.sec < 3600) {
							$(".status").addClass("label label-warning").text("ONLINE");
						} else {
							$(".status").addClass("label label-important").text("OFFLINE");
						}
						
						if (response.sec < 3600) {
							$(".ping").data("ip", response.ip).addClass("label label-inverse").text("PING");
						}
					} else if (response.active == "lan") {
						if (response.sec == 0) {
							$(".status").addClass("label").text("RENEW");
						} else if (response.sec < 300) {
							$(".status").addClass("label").text("ONLINE");
						} else {
							$(".status").addClass("label").text("OFFLINE");
						}
						
						if (response.sec < 300) {
							$(".ping").data("ip", response.ip).addClass("label label-inverse").text("PING");	
						}
					} else {
						$(".status").addClass("label").text("UPDATE");
					}
				}
			});

			$.post("users_status", vendor, function(request) {
				response = $.parseJSON(request);

				if (response.error) {
					$(".vendor").data("placement", "top").data("title", response.error).tooltip();
				} else {
					$(".vendor").data("placement", "top").data("title", response.vendor).tooltip();
				}
			});
		}
	
	$('.ping').click(function(){
		var ip = $(this).data("ip");		
		window.open('ping?ip='+ip, '', 'toolbar=1, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=0, width=560, height=280');
	});
	
	$('[rel="tooltip"]').tooltip();

	$.fn.editable.defaults.url = '/users_edit';
	$.fn.editable.defaults.highlight = false;
	$.fn.editable.defaults.emptytext = "N/A";

	$.fn.editable.defaults.ajaxOptions = {
		error: function(response, newValue) {
			if(response.status === 500) {
				return 'Service unavailable. Please try later.';
			} else {
				return response.responseText;
			}
		}
	}
	
	$('.enable').click(function() {
		$('#user .editable').editable('toggleDisabled');
	});

	$('#surname').editable();
	$('#name').editable();
	$('#patronymic').editable();
	
	$('#phone_primary').editable();
	$('#phone_secondary').editable();

	$('#comments').editable({
		type: 'textarea',
		rows: 10
	});

	// $('#birthday').editable({
	// 	type: 'combodate',
	// 	format: 'YYYY-MM-DD',
	// 	template: 'DD / MM / YYYY',
	// 	combodate: {
	// 		minYear: 1930
	// 	}
	// });
	
	$('#passwd').editable({
		savenochange: true
	});

	$('#state').editable({
		type: 'select',
		source: '/users_edit?act=state',
	});

	$('#credit').editable();
	$('#discount').editable();

	$('#tariffication').editable({
		type: 'select',
		source: '/users_edit?act=tariffication',
	});

	$('#tariff').editable({
		type: 'select',
	});
	
	$('#mac').editable();
	
	$('#dns').editable({
		type: 'select',
		source: '/users_edit?act=dns',
	});

	$('#catv_state').editable({
		type: 'select',
		source: '/users_edit?act=catv',
	});
	
	$('#stock').editable({
		type: 'select',
		source: '/users_edit?act=stock',
	});

	// PON
	$('#pon_state').editable({
		type: 'select',
		source: '/users_edit?act=pon',
	});

	$('#pon_time_next_removal').editable({
		type: 'combodate',
		format: 'YYYY-MM-DD',
		template: 'DD / MM / YYYY',
		combodate: {
			minYear: new Date().getFullYear(),
			maxYear: new Date().getFullYear() + 10
		}
	});

	$('#pon_comments').editable({
		type: 'textarea',
		rows: 10
	});

	// TV
	$('#tv_state').editable({
		type: 'select',
		source: '/users_edit?act=tv',
	});

	$('#tv_time_next_removal').editable({
		type: 'combodate',
		format: 'YYYY-MM-DD',
		template: 'DD / MM / YYYY',
		combodate: {
			minYear: new Date().getFullYear(),
			maxYear: new Date().getFullYear() + 10
		}
	});

	$('#tv_comments').editable({
		type: 'textarea',
		rows: 10
	});

	// Отключаем редактирование по умолчанию
	$('.editable').editable('disable');

});