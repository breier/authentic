	/*******************************************************
	 * custom JavaScript file using jQuery and written in  *
	 * page order, which means, as pages can be accessed.  *
	 *******************************************************/

/* ----- OnLoad / Document Ready - Stuff to always load ----- */
$(function () {
	var currentPageURL = window.location.toString().split('#')[0].split('/');
	var currentPageHref = currentPageURL[(currentPageURL.length-1)].split('&')[0]; // get the "?p=..." stuff in the URL
// --- Sidebar Menu active control --- //
	var activeMenu = $('#sidebar-menu').find('a[href="./'+ currentPageHref +'"]').parent('li');
	activeMenu.parent('ul').slideDown();
	if (activeMenu.parent('ul').parent('li').is('.parent')) activeMenu.parent('ul').parent('li').addClass('current-page active');
	activeMenu.addClass('current-page');
// --- AJaX default error Set --- //
	$(document).ajaxError(function () { alertPNotify('alert-danger', $('script[data-error]').attr('data-error')); });
// --- Sidebar Menu active function --- //
	$('#sidebar-menu').find('li').on('click', function(ev) {
		var link = $('a', this).attr('href');
		if (link) ev.stopPropagation();
		else {
			if ($(this).is('.active')) {
				$(this).removeClass('active');
				$('ul', this).slideUp();
			} else {
				$('#sidebar-menu').find('li').removeClass('active');
				$('#sidebar-menu').find('li ul').slideUp();
				$(this).addClass('active');
				$('ul', this).slideDown();
			}
		}
	});
// --- Menu toggle function by top button "fa-bars" --- //
	$('#menu_toggle').on('click', function() {
		if ($(window).width() > 991) return false;
		$('.container.body .left_col').toggleClass("small-push");
		$('.container.body .right_col').toggleClass("small-push");
	});
// --- Menu toggle function by swiping on touch screens --- //
	$('body').on('touchstart', function (event) { touchStartX = event.changedTouches[0].pageX; });
	$('body').on('touchend', function (event) {
		if (event.changedTouches.length > 1 || $(window).width() > 991) return false;
		touchEndX = event.changedTouches[0].pageX;
		if (touchStartX < 100 && (touchEndX - touchStartX) > 100 && !$('.container.body .left_col').is('.small-push')) {
			$('.container.body .left_col').addClass("small-push");
			$('.container.body .right_col').addClass("small-push");
		} else if (touchStartX > 250 && (touchStartX - touchEndX) > 100 && $('.container.body .left_col').is('.small-push')) {
			$('.container.body .left_col').removeClass("small-push");
			$('.container.body .right_col').removeClass("small-push");
		}
	});
// --- Dropdown slide effect --- //
	$('.dropdown').on('show.bs.dropdown', function(e) { $('.dropdown-menu', this).slideDown(200); });
	$('.dropdown').on('hide.bs.dropdown', function(e) { $('.dropdown-menu', this).slideUp(200); });
// --- Language set function (with _SESSION) --- //
	ajaxFolder = $('script[data-ajax-folder]').attr('data-ajax-folder');
	$('#langset a').on('click', function() {
		$('.dropdown .dropdown-menu').hide(0);
		var post = 'ajax=1&lg='+ $(this).attr('label');
		$.ajax({ url: ajaxFolder +'/session/set.php', type: 'POST', data: post, success: function () { window.location = currentPageHref; } });
	});
// --- Right column height  --- //
	contentHeightHandler();
	$(window).on("resize orientationchange", function () { contentHeightHandler(); });
	$(document).ajaxComplete(function () { contentHeightHandler(); });
// --- Fix Multiple Modal Windows --- //
	$(document).on('show.bs.modal', '.modal', function () {
		var zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function() { $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'); }, 0);
	});
	$(document).on('hidden.bs.modal', '.modal', function () { $('.modal:visible').length && $(document.body).addClass('modal-open'); });
// --- Extend Number Object with PAD function --- //
	Number.prototype.pad = function(size) {
		var s = String(this);
		while (s.length < (size || 2)) { s = "0" + s; }
		return s;
	}
});

/* ----- Define Page Content Height Handler Function ----- */
function contentHeightHandler () {
	$(".right_col").css("min-height", $(window).height());
	$("#sidebar-menu").css("max-height", $(window).height() - 58);
	$("footer").css("margin-top", 0);
	var footerMarginTop = $(window).height() - $("footer").offset().top - $("footer").height() - 1; // -1 for border-top
	if (footerMarginTop < 0) footerMarginTop = 0;
	$("footer").css("margin-top", footerMarginTop);
}

/* ----- Define PNotify Alert Function ----- */
function alertPNotify (classType, message, customDelay, customStack) {
	var customStack = (typeof customStack != 'object') ? ($("div.row")) : (customStack);
	if (typeof rowStack == 'undefined') rowStack = { dir1: 'down', dir2: 'left', push: 'top', context: customStack };
	new PNotify({
		stack: rowStack,
		text: (typeof message == 'undefined') ? (' -- ') : (message),
		addclass: (typeof classType == 'undefined') ? ('alert-info') : (classType),
		delay: (typeof customDelay == 'undefined') ? (2500) : (parseInt(customDelay)),
		animate: { animate: true, in_class: 'slideInDown', out_class: 'slideOutUp' },
		buttons: { closer_hover: false, sticker_hover: false }
	});
	$(".ui-pnotify").one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() { $(this).removeClass("slideInDown"); });
}

/* ----- Define Timeout Function Checker ----- */
function checkTimeout () {
	$.ajax({
		url: ajaxFolder +'/session/timeout.php',
		type: 'POST',
		data: 'ajax=1',
		success: function (response) {
			var responseTime = (isNaN(parseInt(response))) ? (0) : (parseInt(response));
			var tempo = new Date(responseTime * 1000);
			if (tempo.getTime() == 0) window.location = "./?p=off";
			else {
				var minutes = (tempo.getMinutes() < 10) ? ("0"+ tempo.getMinutes()) : (tempo.getMinutes());
				var seconds = (tempo.getSeconds() < 10) ? ("0"+ tempo.getSeconds()) : (tempo.getSeconds());
				$("#timeout_counter").html(minutes +":"+ seconds);
				$("#timeout_counter").attr("value", (tempo.getTime() / 1000));
			}
		}
	});
}

/* ----- Define Edit Profile Function ----- */
function editProfile (userID) {
	alertPNotify('alert-info', "append and show modal with "+ userID);
}

/* ----- Define Smart Help Function ----- */
function smartHelp () {
	alertPNotify('alert-info', "append and show modal with "+ location.href);
}

/* ----- Define Input Password Toggle (show / hide) ----- */
function register_togglePassword (addonObject) {
	var currentType = $(addonObject).prev("input").attr('type');
	$(addonObject).find('i').toggleClass('fa-eye-slash fa-eye');
	$(addonObject).prev("input").attr('type', (currentType == 'text') ? ('password') : ('text'));
}

/* ----- Define AJaX request for List Users ----- */
function list_fillUsersTable (type, searchString, pageNumber, sortOrder) {
	if (typeof type == 'undefined') var type = '';
	if (typeof searchString == 'undefined') var searchString = '';
	if (typeof pageNumber == 'undefined') var pageNumber = 1;
	if (typeof sortOrder == 'undefined') var sortOrder = '1a';
	$.ajax({
		url: ajaxFolder +'/list/search.php',
		type: 'POST',
		data: 'ajax=1&type='+ type +'&search='+ searchString +'&page='+ pageNumber +'&sort='+ sortOrder,
		success: function (response) {
			if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
			else {
				var rows_content = JSON.parse(response);
				$("#users_table tbody").html("");
				$("#total_result").html(rows_content[0]['total'].pad());
				for (var i=1; i<rows_content.length; i++) {
					$("#users_table tbody").append("<tr>");
					for (var j=0; j<rows_content[i].length; j++) $("#users_table tbody tr:last").append("<td>"+ rows_content[i][j] +"</td>");
				} if (!rows_content[0]['total']) {
					$("#users_table tbody").append("<tr>");
					$("#users_table tbody tr").append("<td>"+ $("#users_table").attr("data-empty") +"</td>");
					$("#users_table tbody tr td").attr("colspan", 5).css("padding", "8px");
				} $("#users_table tbody tr:has(a.disabled)").addClass('disabled');
				// Deal with sort style
				$("#users_table thead th:nth("+ (sortOrder[0] - 1) +") i").addClass("active");
				$("#users_table thead th:not(:nth("+ (sortOrder[0] - 1) +")) i").removeClass("active");
				if ($("#users_table thead th:nth("+ (sortOrder[0] - 1) +") i").hasClass("fa-sort-amount-asc") && sortOrder[1] == 'd')
					$("#users_table thead th:nth("+ (sortOrder[0] - 1) +") i").toggleClass("fa-sort-amount-asc fa-sort-amount-desc");
				else if ($("#users_table thead th:nth("+ (sortOrder[0] - 1) +") i").hasClass("fa-sort-amount-desc") && sortOrder[1] == 'a')
					$("#users_table thead th:nth("+ (sortOrder[0] - 1) +") i").toggleClass("fa-sort-amount-asc fa-sort-amount-desc");
				// Deal with pagination
				if (rows_content[0]['total'] > 10) {
					$("#pagination").prev("button").attr("disabled", (pageNumber == 1));
					$("#pagination").empty();
					var listPages = Math.ceil(rows_content[0]['total'] / 10);
					for (var i=1; i <= listPages; i++) {
						$("#pagination").append("<button>"+ i +"</button>");
						$("#pagination button:last").addClass("btn btn-default").on("click", null, "input", list_paginate);
						if (pageNumber == i) $("#pagination button:last").addClass("active");
					} if (listPages > 5) {
						$("#pagination button").each(function (index, element) {
							if (index == 0 || index == (listPages - 1) || $(element).hasClass("active")) return true;
							$(element).attr("data-remove", true);
						});
						$("#pagination button[data-remove]").remove();
						if ($("#pagination button.active").html() > 2) {
							$("#pagination button.active").before("<button>...</button>");
							$("#pagination button.active").prev("button").addClass("btn btn-default");
						} if ($("#pagination button.active").html() < (listPages - 1)) {
							$("#pagination button.active").after("<button>...</button>");
							$("#pagination button.active").next("button").addClass("btn btn-default");
						}
					} $("#pagination").next("button").attr("disabled", (pageNumber == listPages));
				} else {
					$("#pagination").prev("button").attr("disabled", true);
					$("#pagination").html("<button>1</button>");
					$("#pagination button").addClass("btn btn-default active");
					$("#pagination").next("button").attr("disabled", true);
				}
			}
		}
	});
}

/* ----- Define AJaX search funtion with delay for List Users ----- */
function list_searchTable (event, searchLength) {
	if (typeof searchLength == 'undefined')
		return setTimeout('list_searchTable(false, ' + $(event.target).val().length + ')', 500);
	else {
		if ($("#search").val().length != searchLength) return false;
		else var searchText = $("#search").val();
	} var searchArray = new String(window.location.href).split('#');
	var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
	window.location = '#'+ searchText +'#1#'+ sortOrder;
}

/* ----- Define AJaX sort table function for List Users ----- */
function list_sortTable (column) {
	var column = (typeof column == 'undefined') ? (1) : (parseInt(column));
	var searchArray = new String(window.location.href).split('#');
	var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
	var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
	var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);

	if (sortOrder[0] > 5 || sortOrder[0] < 1) sortOrder = '1a';
	var sort = (sortOrder[0] == column && sortOrder[1] == 'a') ? ('d') : ('a');
	window.location = '#'+ searchString +'#'+ pageNumber +'#'+ column + sort;
}

/* ----- Define AJaX pagination function for List Users ----- */
function list_paginate (number) {
	var searchArray = new String(window.location.href).split('#');
	var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
	var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
	var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);

	if (typeof number == 'number') {
		if (number == -1) window.location = searchArray[0] +'#'+ searchString +'#'+ (parseInt(pageNumber) - 1) +'#'+ sortOrder;
		else window.location = searchArray[0] +'#'+ searchString +'#'+ (parseInt(pageNumber) + 1) +'#'+ sortOrder;
	} else if (typeof number == 'object') {
		window.location = searchArray[0] +'#'+ searchString +'#'+ parseInt(number.target.innerHTML) +'#'+ sortOrder;
	}
}

/* ----- Define AJaX Modal Details for List Users / Helpdesk ----- */
function list_details (userID) {
	$(document).focus();
	$.ajaxSetup({ global: false });
	$.ajax({
		url: ajaxFolder +'/list/search.php',
		type: 'POST',
		data: 'ajax=1&type='+ $('#type').val() +'&user_id='+ userID,
		success: function (response) {
			$('#modal_details #details_id').val(userID);
			$('#modal_details .modal-body').html(response);
			$('#modal_details #details_ticket').attr("href", './?p=33&cid='+ userID);
			$('#modal_details #details_delete').attr("href", 'javascript:list_delete('+ userID +');');
			$('#modal_details #details_disable').attr("href", 'javascript:list_disableEnable('+ userID +');');
			if ($('#modal_details #user_disabled').length) {
				$('#modal_details #details_disable span').html($('#modal_details #details_disable').attr("data-enable"));
				$('#modal_details #details_disable i.fa').removeClass("fa-lock").addClass("fa-unlock");
			} else {
				$('#modal_details #details_disable span').html($('#modal_details #details_disable').attr("data-disable"));
				$('#modal_details #details_disable i.fa').removeClass("fa-unlock").addClass("fa-lock");
			} if ($("#modal_details .modal-body").hasClass("edit")) list_detailsEdit();
			$("#modal_details").modal("show");
			$('#modal_details .modal-body .selectpicker').selectpicker();
			list_handleDuplicates("#modal_details .modal-body");
		},
		complete: function () { $.ajaxSetup({ global: true }); }
	});
}

/* ----- Define Handle Duplicated Form Fields (sequence) for List Details ----- */
function list_handleDuplicates (parentObject) {
	var labelElement = ($(parentObject).is(".modal-body")) ? ("strong") : ("label");
	$(parentObject).find(".duplicated").each(function (index, element) {
		if($(element).prev("div").hasClass("duplicated")) return false;
		var firstElementTitle = $(element).prev("div").find(labelElement).html();
		var secondElementTitle = $(element).find(labelElement).html();
		var firstElementInput = $(element).prev("div").find("input");
		var secondElementInput = $(element).find("input");
		var firstElementClasses = $(element).prev("div").attr("class");
		$(element).prev("div").before($("<div>", {"class": firstElementClasses +" control"}));
		$(secondElementInput).attr("disabled", true);
		var controlElement = $(element).prev("div").prev("div");
		if (!$(parentObject).is(".modal-body")) {
			$(controlElement).append($("<label>", {"class": $(element).prev("div").find("label").attr("class")}));
			$(controlElement).append($("<div>", {"class": $(element).prev("div").find("div:first").attr("class")}));
		} else $(controlElement).append($("<div>", {"class": $(element).prev("div").find("div:first").attr("class")}));
		$(controlElement).find("div").append($("<input>", {"id": "control_1-"+ index, "name": "control_"+ index, "type": "radio", "value": $(firstElementInput).attr("name")}));
		$(controlElement).find("div").append($("<label>", {"for": "control_1-"+ index}));
		$(controlElement).find("div label").html(firstElementTitle);
		$(controlElement).find("div").append($("<input>", {"id": "control_2-"+ index, "name": "control_"+ index, "type": "radio", "value": $(secondElementInput).attr("name")}));
		$(controlElement).find("div").append($("<label>", {"for": "control_2-"+ index}));
		$(controlElement).find("div label:last").html(secondElementTitle);
		$(controlElement).find("div input").on("change", function () {
			var firstElement = $(this).parent("div").parent("div").next("div");
			var secondElement = $(this).parent("div").parent("div").next("div").next("div");
			if($(this).val() == $(firstElement).find("input").attr("name")) {
				$(firstElement).removeClass("duplicated").addClass("flipInX animated").find("input").attr("disabled", false).focus();
				$(secondElement).addClass("duplicated").removeClass("flipInX animated").find("input").attr("disabled", true);
			} else {
				$(firstElement).addClass("duplicated").removeClass("flipInX animated").find("input").attr("disabled", true);
				$(secondElement).removeClass("duplicated").addClass("flipInX animated").find("input").attr("disabled", false).focus();
			}
		});
		if ($(parentObject).is(".modal-body")) {
			if ($(firstElementInput).val().length > $(secondElementInput).val().length)
				$(controlElement).find("div input:first").attr("checked", true);
			else $(controlElement).find("div input:last").attr("checked", true).change();
		} else $(controlElement).find("div input:first").attr("checked", true);
	});
}

/* ----- Define Modal Details Edit Button Function ----- */
function list_detailsEdit () {
	$("#modal_details .modal-body>div>div>span").toggleClass("fadeIn animated");
	$("#modal_details .modal-footer").toggleClass("edit").find(".form-edit").toggleClass("fadeIn animated");
	$("#modal_details .modal-body").toggleClass("edit").find(".form-edit").toggleClass("fadeIn animated");
	$("#modal_details #details_edit").toggleClass("active");
}

/* ----- Define Modal Details Form Submit Function ----- */
function list_detailsEditSend () {
	$("#modal_details .modal-body input").each(function (index, element) {
		if ($(element).prop('defaultValue') != $(element).val()) $(element).attr("data-changed", true);
	});
	$("#modal_details .modal-body select").each(function (index, element) {
		$(element).find('option').each(function (idx, elm) {
			if ($(elm).prop('defaultSelected') && !$(elm).prop('selected')) $(element).attr("data-changed", true);
		});
	});
	if ($("#modal_details .modal-body *[data-changed]").length) {
		var dataSend = {"ajax": 1, "type": $("#type").val(), "id": $("#details_id").val()};
		$("#modal_details .modal-body *[data-changed]").each(function (index, element) { dataSend[$(element).attr("name")] = $(element).val(); });
		$.ajax({
			url: ajaxFolder +'/list/edit.php',
			type: 'POST',
			data: dataSend,
			success: function (response) {
				$("#modal_details").modal("hide");
				if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
				else {
					var responseArray = JSON.parse(response);
					var searchArray = new String(window.location.href).split('#');
					var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
					var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
					var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
					alertPNotify(responseArray[0], responseArray[1]);
					list_fillUsersTable($("#type").val(), searchString, pageNumber, sortOrder);
				}
			}
		});
	}
}

/* ----- Define Modal Details Customer / User - Disable / Enable Function ----- */
function list_disableEnable (userID) {
	if (typeof userID == 'undefined') return false;
	var manageAction = ($('#modal_details #user_disabled').length) ? ('enable') : ('disable');
	$.ajax({
		url: ajaxFolder +'/list/actions.php',
		type: 'POST',
		data: 'ajax=1&action='+ manageAction + '&id='+ userID,
		success: function (response) {
			$("#modal_details").modal("hide");
			if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
			else {
				var responseArray = JSON.parse(response);
				var searchArray = new String(window.location.href).split('#');
				var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
				var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
				var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
				alertPNotify(responseArray[0], responseArray[1]);
				list_fillUsersTable($("#type").val(), searchString, pageNumber, sortOrder);
				list_resetUser(userID);
			}
		}
	});
}

/* ----- Define Customer / User Reset Connection Function ----- */
function list_resetUser (userID, userName) {
	if (typeof userID == 'undefined') return false;
	var postDeletion = (typeof userName != 'undefined') ? ('&username='+ userName) : ('');
	$.ajax({
		url: ajaxFolder +'/list/actions.php',
		type: 'POST',
		data: 'ajax=1&action=reset&id='+ userID + postDeletion,
		success: function (response) {
			if (response[0]=='[') {
				var responseArray = JSON.parse(response);
				alertPNotify(responseArray[0], responseArray[1]);
			}
		}
	});
}

/* ----- Define Modal Details Customer / User / Equipment Delete Function ----- */
function list_delete (userID) {
	if (typeof userID == 'undefined') {
		var userID = $("#modal_delete .modal-body input[name='delete_id']").val();
		$.ajax({
			url: ajaxFolder +'/list/actions.php',
			type: 'POST',
			data: 'ajax=1&action=delete&type='+ $("#type").val() +'&id='+ userID,
			success: function (response) {
				$("#modal_delete").modal("hide");
				$("#modal_details").modal("hide");
				if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
				else {
					var responseArray = JSON.parse(response);
					var searchArray = new String(window.location.href).split('#');
					var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
					var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
					var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
					alertPNotify(responseArray[0], responseArray[1]);
					list_fillUsersTable($("#type").val(), searchString, pageNumber, sortOrder);
					if (typeof responseArray[2] != 'undefined') list_resetUser(userID, responseArray[2]);
				}
			}
		});
	} var deleteName = ($("#type").val() == 'equipment') ? ($("#modal_details input[name='equipment_name']").val()) : ($("#modal_details input[name='name']").val());
	$("#modal_delete .modal-body span.strong").html(deleteName);
	$("#modal_delete .modal-body input[name='delete_id']").val(userID);
	$("#modal_delete").modal("show");
}

/* ----- Define Check Before Submit for Register Customers / Users ----- */
function register_checkSendUser () {
	var currentStep = parseInt($(".wizard_steps .selected").attr('href').toString().split('#')[1]);
	switch(currentStep) {
		case 1:
			if ($("#mac_address").length) {
				if (!$("#mac_address").val().match(/(([\da-f]{2}\:){5}[\da-f]{2})/i)) return register_alert('#mac_address');
			} if ($("#name").val().split(" ").length < 2 || $("#name").val().length < 6) return register_alert('#name');
			if ($("#username").val().length < 4) return register_alert('#username');
			if ($("#password").val().length < 6) return register_alert('#password');
			register_formNavigate(1, 2);
			return false;
		break;
		case 2:
			var validateInputs = $("#2 input[data-validate]:not([disabled])");
			for (var i=0; i<validateInputs.length; i++) {
				var validateRegExp = new RegExp(validateInputs[i].getAttribute('data-validate'));
				if (!validateInputs[i].value.match(validateRegExp)) return register_alert('#'+ validateInputs[i].id);
			} var validateInputs = $("#2 input:not([disabled])").filter(function() { return !this.value; });
			if (validateInputs.length) {
				$("#modal_confirm").modal("show");
				return false;
			} else {
				register_formNavigate(2, 3);
				return false;
			}
		break;
		default:
			if (!$("#confirm").is(":checked")) return register_alert('#confirm');
			$("#mac_address").attr('disabled', false);
			$("#connection").attr('disabled', false);
			$("#username").attr('disabled', false);
			$("#2 .control input").attr('disabled', true);
		break;
	} return true;
}

/* ----- Define Form Alert for Register ----- */
function register_alert (objectId, dataError) {
	if (typeof objectId == 'undefined') return false;
	if (typeof dataError == 'undefined') var dataError = "data-error";
	alertPNotify('alert-danger', $(objectId).attr(dataError));
	$(objectId).addClass("shake animated").focus();
	$(objectId).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
		$(this).removeClass("shake animated");
	}); return false;
}

/* ----- Define Form Wizard Pagination for Register ----- */
function register_formNavigate (currentStep, nextStep) {
	$("#"+ currentStep).hide("slide");
	$(".wizard_steps .selected").removeClass("selected").addClass("done");
	if ($("#password").attr('type') != 'password') $("#password").next("span").click();
	if (nextStep == 2 && $("#1 #brand").length) register_fillEquipment();
	if (nextStep == 3) {
		$("#check_username").val($("#username").val());
		$("#2 .duplicated input").each(function (index, element) {
			$("#3 input#check_"+ element.id).parent("div").parent("div").addClass("duplicated");
		});
	} $("#"+ nextStep).show("slide");
	$(".wizard_steps a[href='#"+ nextStep +"']").removeClass("done disabled").addClass("selected");
	if (nextStep == 2) $("#2 input[type='text']:first").focus();
	return false;
}

/* ----- Define Page Wizard Navigation for Register ----- */
function register_pageNavigate (object) {
	var currentStep = parseInt($(".wizard_steps .selected").attr('href').toString().split('#')[1]);
	if (object.tagName.toString().toLowerCase()=='a')
		var nextStep = parseInt(object.getAttribute('href').toString().split('#')[1]);
	else var nextStep = ($(object).hasClass('btn-info')) ? (currentStep - 1) : (currentStep + 1);
	if (nextStep == currentStep) return false;
	if ((nextStep-currentStep) > 1 && !$(".wizard_steps a[href='#"+ nextStep +"']").hasClass("done")) return false;
	if (nextStep > currentStep) {
		if ($("#1 #brand").length) return register_checkSendEquipment();
		else return register_checkSendUser();
	} else return register_formNavigate(currentStep, nextStep);
}

/* ----- Define Safe Username Fill from Full Name for Register ----- */
function register_usernameTip (fullName, useDomain) {
	if (typeof useDomain == 'undefined') var useDomain = true;
	if (typeof fullName != 'string') return false;
	if (fullName.length<4) return false;
	var nameArray = fullName.split(' ');
	if (nameArray.length < 2) return false;
	var userName = register_str2ascii(nameArray[0].toLowerCase() +'.'+ nameArray[(nameArray.length-1)].toLowerCase());
	if (userName[0] == '.' || userName[(userName.length-1)] == '.') return false;
	if (useDomain) userName += '@'+ document.domain.replace(/^www\./,''); // what about another subdomain ???
	return userName;
}

/* ----- Define String to ASCII for Register / Settings ----- */
function register_str2ascii (string) {
// Checking input
	if (typeof string != 'string') return false;
	var output = new String(string).toLowerCase();
// Replacing Special Chars
	var output = output.replace(/[óòôöõº°]/g, 'o');
	var output = output.replace(/[áàâäãª]/g, 'a');
	var output = output.replace(/[éèêë]/g, 'e');
	var output = output.replace(/[íìîï]/g, 'i');
	var output = output.replace(/[úùûü]/g, 'u');
	var output = output.replace(/[ -]/g, '_');
	var output = output.replace(/ç/g, 'c');
	var output = output.replace(/ñ/g, 'n');
// Returning The Lower Case ASCII string
	return output.replace(/[^a-z0-9_.]/g, '');
}

/* ----- Define Check if Username is Already Registered for Register ----- */
function register_checkUsername (lastLength) {
	if (typeof lastLength == 'undefined') var lastLength = 0;
	var currentLength = $("#username").val().length;
	if (currentLength < 4) return false;
	if (currentLength == lastLength) {
		$.ajax({
			url: ajaxFolder +'/register/check_username.php',
			type: 'POST',
			data: 'ajax=1&username='+ $("#username").val(),
			success: function (response) { if (response != 'OK') register_alert("#username", "data-taken"); }
		});
	}
}

/* ----- Define Upload Resized Picture Using HTML5 for Register ----- */
function register_fileSelect (event, object) {
	if (!window.File || !window.FileReader || !window.FileList || !window.Blob) return alertPNotify('alert-danger', $(object).attr('data-error-html5'));
	if (!event.target.files[0].type.match($(object).attr('accept'))) return alertPNotify('alert-danger', $(object).attr('data-error-format'));
	uploadedFile = event.target.files[0];
	var fileReader = new FileReader();
	fileReader.onloadend = function () {
		var image = new Image();
		image.src = fileReader.result;
		image.onload = function () {
			var imageWidth = image.width;
			var imageHeight = image.height;
		//Set maximum image size to 320 x 320
			if (imageWidth > imageHeight) {
				if (imageWidth > 320) {
					imageHeight *= 320 / imageWidth;
					imageWidth = 320;
				}
			} else {
				if (imageHeight > 320) {
					imageWidth *= 320 / imageHeight;
					imageHeight = 320;
				}
			}
		//Create a sized canvas to the JPEG
			var imageCanvas = document.createElement('canvas');
			imageCanvas.width = imageWidth;
			imageCanvas.height = imageHeight;
			var canvasContext = imageCanvas.getContext("2d");
			canvasContext.drawImage(this, 0, 0, imageWidth, imageHeight);
			$("#picture").next("input").next("button").find("span").html("["+ uploadedFile.name +"]");
		//Send image through ajax jQuery
			$.ajax({
				url: ajaxFolder +'/register/file_upload.php',
				type: 'POST',
				data: 'ajax=1&name='+ uploadedFile.name +'&image='+ imageCanvas.toDataURL("image/jpeg"),
				success: function (response) {
					if (response[0] != '2') alertPNotify('alert-danger', response); // 2 stands for year 2000
					else $("#picture").next("input").val(response);
				}
			});
		};
	};
	fileReader.readAsDataURL(uploadedFile);
}

/* ----- Define Check Before Submit for Register Equipment ----- */
function register_checkSendEquipment () {
	var currentStep = parseInt($(".wizard_steps .selected").attr('href').toString().split('#')[1]);
	switch(currentStep) {
		case 1:
			if ($("#brand").val().length < 2) return register_alert('#brand');
			if ($("#ip_address").val().length < 7) return register_alert('#ip_address');
			if ($("#ssh_port").val().length < 1) return register_alert('#ssh_port');
			if ($("#username").val().length < 4) return register_alert('#username');
			if ($("#password").val().length < 6) return register_alert('#password');
			register_formNavigate(1, 2);
			return false;
		break;
		case 2:
			var validateInputs = $("#2 input[data-validate]:not([disabled])");
			for (var i=0; i<validateInputs.length; i++) {
				var validateRegExp = new RegExp(validateInputs[i].getAttribute('data-validate'));
				if (!validateInputs[i].value.match(validateRegExp)) return register_alert('#'+ validateInputs[i].id);
			} var validateInputs = $("#2 input:not([disabled])").filter(function() { return !this.value; });
			if (validateInputs.length) {
				$("#modal_confirm").modal("show");
				return false;
			} else {
				register_formNavigate(2, 3);
				return false;
			}
		break;
		default:
			if (!$("#confirm").is(":checked")) return register_alert('#confirm');
			//$("#mac_address").attr('disabled', false);
			//$("#connection").attr('disabled', false);
			//$("#username").attr('disabled', false);
			$("#2 .control input").attr('disabled', true);
		break;
	} return true;
}

/* ----- Define Fill Equipment Information for Register Equipment ----- */
function register_fillEquipment () {
	var postObject = { 'ajax': 1,
		'brand': $("#brand").val(),
		'ip_address': $("#ip_address").val(),
		'ssh_port': $("#ssh_port").val(),
		'username': $("#username").val(),
		'password': $("#password").val()
	};
	$.ajax({
		url: ajaxFolder +'/register/equipment_info.php',
		type: 'POST',
		data: postObject,
		success: function (response) {
			//if (response[0] != '2') alertPNotify('alert-danger', response); // 2 stands for year 2000
			//else $("#picture").next("input").val(response);
			alertPNotify('alert-info', response);
		}
	});
}
/**************************************************************/
/* ----- Form Send Equipment Authentication Information ----- */
function form_seai () {
	if (isNaN($("#port").val())) return register_alert('#port');
	if ($("#usrnm").val().length < 4) return register_alert('#usrnm');
	if ($("#pass").val().length < 4) return register_alert('#pass');
	var psit = 'ajax=1&usnm='+ $("#usrnm").val() +'&pass='+ $("#pass").val();
	psit += '&srvc='+ $("#srvc").val() +'&port='+ $("#port").val();
	$.ajax({url: ajaxFolder +'/reg_info.php',
					type: 'POST',
					data: psit,
					success: function (htrs) { $('.x_content').html(htrs); }
	});
}
/********************************************************************************/
/* ----- Define ONT Autofind Function ----- */
function tools_ontAutofind () {
	$.ajax({
		url: ajaxFolder +'/tools/olt-proxy.php',
		type: 'POST',
		data: 'ajax=1&action=autofind',
		success: function (response) {
			if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
			else {
				var responseArray = JSON.parse(response);
				$("#onts tbody").html("");
				for (var i=0; i<responseArray.length; i++) {
					$("#onts tbody").append("<tr>");
					$("#onts tbody tr:last").append("<td>"+ responseArray[i]['input'] +"</td>");
					$("#onts tbody tr:last").append("<td>"+ responseArray[i]['port'] +"</td>");
					$("#onts tbody tr:last").append("<td>"+ responseArray[i]['sn'] +"</td>");
				} $("#result").html("").parent("div").hide();
			}
		}
	});
}

/* ----- Define ONT Select Function ----- */
function tools_ontSelect (ontInfo) {
	if (typeof ontInfo == 'undefined') return false;
	$("#customer").attr("disabled", false).selectpicker("refresh");
	$("#building").attr("disabled", false);
	var ontInfoArray = ontInfo.split(':');
	$("#gpon_slot").val(ontInfoArray[0]);
	$("#gpon_port").val(ontInfoArray[1]);
	$("#ont_sn").val(ontInfoArray[2]);
}

/* ----- Define ONT Customer Select Function ----- */
function tools_ontCustomerSelect (ontCustomerInfo) {
	if (typeof ontCustomerInfo == 'undefined') return false;
	$("#activate_pppoe").attr("disabled", ((ontCustomerInfo)?(false):(true)));
	$("#activate_bridge").attr("disabled", ((ontCustomerInfo)?(false):(true)));
	var ontCustomerInfoArray = ontCustomerInfo.split(':');
	$("#customer_id").val(ontCustomerInfoArray[0]);
	$("#customer_description").val(ontCustomerInfoArray[1]);
}

/* ----- Define ONT Building Input Function ----- */
function tools_ontBuildingSelect (ontBuildingName, nameLength) {
	if (typeof ontBuildingName == 'undefined') return false;
	if (typeof nameLength == 'undefined')
		return setTimeout('tools_ontBuildingSelect(\''+ ontBuildingName +'\', '+ ontBuildingName.length +')', 500);
	else {
		if ($("#building").val().length != nameLength) return false;
		else var ontBuildingName = register_str2ascii(ontBuildingName);
	} $("#activate_pppoe").attr("disabled", true);
	$("#activate_bridge").attr("disabled", ((ontBuildingName.length < 4)?(true):(false)));
	if (ontBuildingName.length < 4) return register_alert('#building');
	$("#customer_description").val(ontBuildingName);
	$("#customer_id").val(0);
}

/* ----- Define ONT Activation AJaX Function ----- */
function tools_ontActivate (activationType) {
	if (typeof activationType == 'undefined') return false;
	var postData = {
		'ajax': 1, 'action': 'activate', 'type': activationType,
		'gpon_slot': $("#gpon_slot").val(), 'gpon_port': $("#gpon_port").val(), 'ont_sn': $("#ont_sn").val(),
		'customer_id': $("#customer_id").val(), 'customer_description': $("#customer_description").val()
	};
	$.ajax({
		url: ajaxFolder +'/tools/olt-proxy.php',
		type: 'POST',
		data: postData,
		success: function (response) {
			if (response[0]!='<') alertPNotify ('alert-danger', response, 5000);
			else {
				$("#result").html(response).parent("div").show();
				$("#onts tbody").html("");
				$("#customer").attr("disabled", true);
				$("#activate_pppoe").attr("disabled", true);
				$("#activate_bridge").attr("disabled", true);
			}
		}
	});
}

/* ----- Define Confirm and Delete Modal for Manage Plans ----- */
function tools_deletePlan (planID) {
	var plansArray = JSON.parse($("#customers_per_plan").val());
	var planName = $(".x_content form[data-plan="+ planID +"] input[name=plan]").prop("defaultValue");
	if (typeof plansArray[planName] != 'undefined') {
		if (plansArray[planName]['count']) {
			$("#modal_delete .modal-body p strong").html(plansArray[planName]['count']);
			$("#modal_delete .modal-footer button:last").attr("disabled", true);
			$("#modal_delete .modal-body div[cannot-delete]").show();
			$("#modal_delete .modal-body div[can-delete]").hide();
		} else {
			if (planID == undefined) return $("#modal_delete form").submit();
			$("#modal_delete .modal-footer button:last").attr("disabled", false);
			$("#modal_delete .modal-body form input[name=id]").val(planID);
			$("#modal_delete .modal-body div[cannot-delete]").hide();
			$("#modal_delete .modal-body div[can-delete]").show();
		} $("#modal_delete .modal-body span.strong").html(planName);
		$("#modal_delete").modal("show");
	} else return alert("Something has gone wrong!");
}

/* ----- Define Ticket Tag and Tooltips Mod for Helpdesk Calendar ----- */
function tools_ticketCalendar () {
	$('.form-ticket .tooltip[role="tooltip"]').remove(); // --- Fix Phantom Tooltips
	for (var k in ticketCalendar) {
		var ticketYear = k.split('-')[0];
		var ticketMonth = parseInt(k.split('-')[1]) - 1;
		var ticketDay = k.split('-')[2];
		var ticketSelector = ".xdsoft_calendar .xdsoft_date[data-date="+ ticketDay +"][data-month=";
		ticketSelector += ticketMonth +"][data-year="+ ticketYear +"]";
		if($(ticketSelector).length) $(ticketSelector).addClass("has-tickets"); // --- Add date tag
	} // --- Get calendar selected date
	var ticketsCurrentYear = $(".xdsoft_calendar .xdsoft_date.xdsoft_current").attr("data-year");
	var ticketsCurrentMonth = parseInt($(".xdsoft_calendar .xdsoft_date.xdsoft_current").attr("data-month")) + 1;
	var ticketsCurrentDay = $(".xdsoft_calendar .xdsoft_date.xdsoft_current").attr("data-date");
	var ticketsCurrentDate = ticketsCurrentYear +'-'+ ticketsCurrentMonth +'-'+ ticketsCurrentDay;
	if (typeof ticketCalendar[ticketsCurrentDate] == 'object') {
		for (var k in ticketCalendar[ticketsCurrentDate]) {
			var ticketHour = parseInt(k.split('-')[0]);
			var ticketMinutes = parseInt(k.split('-')[1]);
			var ticketSelector = ".xdsoft_timepicker .xdsoft_time[data-hour="+ ticketHour +"][data-minute="+ ticketMinutes +"]";
			if($(ticketSelector).length) $(ticketSelector).addClass("has-tickets").attr("title", ticketCalendar[ticketsCurrentDate][k]);
		} $(".xdsoft_timepicker .xdsoft_time.has-tickets").tooltip({ container: ".form-ticket", html: true }); // --- Add hour tag with HTML tooltip
	}
}

/* ----- Define Edit Ticket Messages for Helpdesk ----- */
function tools_ticketEdit (ticketID) {
	if (typeof ticketID == 'undefined') return false;
	if (!$(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]").length) return false;
	if ($(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]>textarea").length) {
		var initialString = $(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]>textarea").attr("data-initial");
		$(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]").html(initialString);
		return false;
	} $(".list-group-item[data-id] .list-group-item-text>span[data-message]>textarea").each(function (index, element) {
		var initialString = $(element).attr("data-initial");
		$(element).parent("span").html(initialString);
	});
	var ticketMessageObject = $(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]");
	var ticketMessageString = $(ticketMessageObject).html();
	$(ticketMessageObject).prev("span").css("display", "inline-block");
	var ticketMessageSubtractWidth = $(ticketMessageObject).prev("span").width() + 4;
	if (ticketMessageSubtractWidth < 70) ticketMessageSubtractWidth = 70; // Minimum width to fit the Save button
	$(ticketMessageObject).prev("span").css("display", "initial").css("vertical-align", "top");
	$(ticketMessageObject).html($("<textarea>", {"class": "form-control", "data-initial": ticketMessageString}));
	$(ticketMessageObject).find("textarea").css("width", "calc(100% - "+ ticketMessageSubtractWidth +"px)");
	$(ticketMessageObject).find("textarea").css("resize", "none").css("display", "inline-block").html(ticketMessageString).focus();
	$(ticketMessageObject).append($("<button>", {"class": "btn btn-info ticket-edit"}));
	$(ticketMessageObject).find("button").html($(".form-ticket button[type='submit']:last").html());
	$(ticketMessageObject).find("button").on("click", function () {
		if ($(ticketMessageObject).find("textarea").val() == $(ticketMessageObject).find("textarea").attr("data-initial")) {
			$(ticketMessageObject).html($(ticketMessageObject).find("textarea").attr("data-initial"));
		} else {
			$.ajax({
				url: ajaxFolder +'/tools/ticket_edit.php',
				type: 'POST',
				data: 'ajax=1&id='+ ticketID +'&message='+ $(ticketMessageObject).find("textarea").val(),
				success: function (response) { $(ticketMessageObject).html(response); }
			});
		}
	});
}

/* ----- Define Delete Ticket Messages for Helpdesk ----- */
function tools_ticketDelete (ticketID, confirmation) {
	if (typeof ticketID == 'undefined') return false;
	if (!$(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]").length) return false;
	$(".list-group-item[data-id] .list-group-item-text>span[data-message]>textarea").each(function (index, element) {
		var initialString = $(element).attr("data-initial");
		$(element).parent("span").html(initialString);
	});
	if (typeof confirmation == 'undefined') confirmation = false;
	if (confirmation) {
		$.ajax({
			url: ajaxFolder +'/tools/ticket_edit.php',
			type: 'POST',
			data: 'ajax=1&id='+ ticketID +'&delete=true',
			success: function (response) {
				if (response == 'DELETED') {
					$(".list-group-item[data-id="+ ticketID +"]").remove();
					if (!$(".list-group-item").length) $(".x_panel>.x_title>button").click();
				} else alertPNotify('alert-danger', response);
			}
		});
	} else {
		var ticketMessageString = $(".list-group-item[data-id="+ ticketID +"] .list-group-item-text>span[data-message]").html();
		$("#modal_ticket_message_delete .modal-body p>span").html(ticketMessageString);
		$("#modal_ticket_message_delete .modal-body input").val(ticketID);
		$("#modal_ticket_message_delete").modal("show");
	}
}

/* ----- Define Save Single Configurations for Settings ----- */
function settings_saveSingle (form_object) {
	var form_selector = (typeof form_object == 'object') ? ($(form_object)) : ($('.x_content form'));
	form_selector.find("input[type=text]").each(function (index, element) {
		if ($(element).val() != $(element).prop("defaultValue")) $(element).attr("data-changed", true);
	});
	form_selector.find("select").each(function (index, element) {
		$(element).find("option").each(function (idx, elm) {
			if ($(elm).is(":selected")) { if (!$(elm).prop("defaultSelected")) $(element).attr("data-changed", true); }
			else { if ($(elm).prop("defaultSelected")) $(element).attr("data-changed", true); }
		});
	});
	if (!form_selector.find("*[data-changed]").length) $("#modal_nochange").modal("show");
	else {
		var finalValidation = true;
		form_selector.find('input[data-changed]').each(function (index, element) {
			if ($(element).attr("name") == 'Session Timeout[]' || $(element).attr("name") == 'Data Points[]') {
				if (isNaN($(element).val())) finalValidation = false;
				else {
					if ($(element).attr("name") == 'Session Timeout[]' && ($(element).val() < 0 || $(element).val() > 3599)) finalValidation = false;
					if ($(element).attr("name") == 'Data Points[]' && ($(element).val() < 2 || $(element).val() > 64)) finalValidation = false;
				} if (!finalValidation) {
					alertPNotify("alert-danger", $(element).attr("data-error"));
					$(element).addClass("shake animated").focus();
					$(element).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() { $(this).removeClass("shake animated"); });
					return false;
				}
			}
		});
		if (!finalValidation) return false;
		form_selector.find("input:not([data-changed]):not([data-required]), select").attr('disabled', true);
		form_selector.find('input[data-changed]').each(function (index, element) {
			form_selector.find('input[name="'+ $(element).attr("name") +'"]').attr('disabled', false);
		});
		form_selector.find('select[data-changed]').each(function (index, element) { // Fix select label array (not multiple select)
			form_selector.append($('<input>', {'type': "hidden", 'name': $(element).attr("name"), 'value': $(element).attr("name").replace('[]', '')}));
			form_selector.append($('<input>', {'type': "hidden", 'name': $(element).attr("name"), 'value': $(element).val()}));
		});
		return true;
	} return false;
}

/* ----- Define Check and Save Form Field for Settings ----- */
function settings_saveFormField (label) {
	if (label == undefined) return false;
	$(".x_content form[data-label='"+ label +"'] input").each(function (index, element) {
		if ($(element).val() != $(element).prop("defaultValue")) $(element).attr("data-changed", true);
	});
	if (!$(".x_content form[data-label='"+ label +"'] input[data-changed]").length) $("#modal_nochange").modal("show");
	else {
		var passRequired = true;
		$(".x_content form[data-label='"+ label +"'] input[required]").each(function (index, element) {
			if (!passRequired) return;
			if (!$(element).val().length) {
				alertPNotify("alert-danger", $(element).attr("data-error"));
				$(element).addClass("shake animated").focus();
				$(element).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() { $(this).removeClass("shake animated"); });
				passRequired = false;
			}
		});
		if (passRequired) $(".x_content form[data-label='"+ label +"']").submit();
	}
}

/* ----- Define Confirm and Delete for Settings ----- */
function settings_delete (label) {
	if (label == undefined) return $("#modal_delete form").submit();
	$("#modal_delete .modal-body span.strong").html($(".x_content form input[name='"+ label +"[]']:last").val());
	$("#modal_delete .modal-body form input[name=label]").val(label);
	if ($(".x_content form input[name='"+ label +"[]']:first").parent("div").length)
		var settingsCategory = $(".x_content form input[name='"+ label +"[]']:first").parent("div").parent("form").find("input[name='settings_category']").val();
	else var settingsCategory = 'default';
	$("#modal_delete .modal-body form input[name='settings_category']").val(settingsCategory);
	$("#modal_delete").modal("show");
}

/* ----- Define Add MAC Prefix for Settings -> Provisioning ----- */
function settings_addMACPrefix (addonObject) {
	if ($(addonObject).prev("input").val().length < 8) return $(addonObject).prev("input").focus();
	var thisContainer = $(addonObject).parent("div").parent("div");
	var formContainer = thisContainer.parent("form");
	var thisLabel = thisContainer.prev("label");
	var clearFix = thisContainer.next("div");
	thisContainer.after(thisLabel.clone().hide());
	thisContainer.next("label").after(thisContainer.clone().hide());
	formContainer.append(clearFix);
	formContainer.find("label:last").html("&nbsp;").slideDown(400);
	formContainer.find("label:last + div").slideDown(400).find("input").val("").focus();
	$(addonObject).attr("onclick", "settings_deleteMACPrefix(this);").find("i").toggleClass("fa-plus fa-minus");
}

/* ----- Define Delete MAC Prefix for Settings -> Provisioning ----- */
function settings_deleteMACPrefix (addonObject) {
	var thisContainer = $(addonObject).parent("div").parent("div");
	var thisLabel = thisContainer.prev("label");
	if (thisLabel.html() != "&nbsp;") thisContainer.next("label").slideUp(400, function () { $(this).prev("div").remove(); this.remove(); });
	else thisLabel.slideUp(400, function () { $(this).next("div").remove(); this.remove(); });
	thisContainer.slideUp(400).parent("form").find("input[name=label]").attr("data-changed", true);
}

/* ----- Define Modal Add Ticket Priority / Category for Settings -> Helpdesk ----- */
function settings_modalAdd (settingsCategory) {
	$("#modal_single_add .modal-header>span").html($(".x_content form input[value='"+ settingsCategory +"']").parent("form").find("h4").html());
	$("#modal_single_add .modal-body form input[name='settings_category']").val(settingsCategory);
	if (settingsCategory == 'ticket_priority') $("#modal_single_add .modal-body form label[for='set_color']").show().next("div").show();
	else $("#modal_single_add .modal-body form label[for='set_color']").hide().next("div").hide();
	$("#modal_single_add").modal("show");
}
