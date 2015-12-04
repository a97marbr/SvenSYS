var triggerToolTip = false;

function checkB() {
	
	oneNotChecked = false;
	cString="";
	$('#filterForm :checkbox').each(function(index) {
		var id = $(this).attr('id');
		if(id != "checkAll") {
			if(!($("#"+id).is(':checked'))) {
				oneNotChecked = true;
				//cString += "S"+$(this).attr('id')+",";
			}else{
				cString += $(this).attr('id')+",";
			}
		}
	});
	if(oneNotChecked) {
		$("#checkAll").attr('checked', false);
	}
	else {
		$("#checkAll").attr('checked', true);
	}
	
	$(".hiddenCheckString").val(cString); //Passes on the checkboxes values
}

function checkAllBoxes() {
	if($('#checkAll').is(':checked')) {
		$(':checkbox').each(function(index) {
			$("."+$(this).attr('id')).show();
			$(this).attr('checked', true);
		});
	}
	else {
		$(':checkbox').each(function(index) {
			$("."+$(this).attr('id')).hide();
			$(this).attr('checked', false);
		});
	}
}

/*

function setConferenceValues(confname, longname, regstart, regend, beforetext, aftertext, confdate){
	$("#confname").val(confname);
	$("#longname").val(longname);
	$("#confdate").val(confdate);
	$("#regstart").val(regstart);
	$("#regend").val(regend);
	$("#beforetext").val(beforetext);
	$("#aftertext").val(aftertext);
	if(confname != ""){
		triggerToolTip = true;
	}
}

function userExists(firstname, lastname, persemail, pw, conferences, userkind){
	$("#userform input[name=firstname]").val(firstname);
	$("#userform input[name=lastname]").val(lastname);
	$("#userform input[name=persemail]").val(persemail);
	$("#userform input[name=pw]").val(pw);
	$("#userform input[name=confmultiselect]").val(conferences);
	$("#kind option[value='"+userkind+"']").attr('selected', 'selected');
	if (persemail != ""){
		triggerToolTip = true;
	}
}


function removeUser(user){
	$.post("conferencefunctions.php", { userID: user, funcCall: "removeUser"},
	function(data) {
		if(data = "User_removed"){
			$("#selectedRow").hide();
			$("#userform input[name=firstname]").val("");
			$("#userform input[name=lastname]").val("");
			$("#userform input[name=persemail]").val("");
			$("#userform input[name=pw]").val("");
			$("#userform input[name=confmultiselect]").val("");
			tb_remove();
			$("#removeButton").hide();
		}
		else{
			alert("Error!")
		}
	});
}


function removeCard(card){
	$.post("conferencefunctions.php", { cardID: card, funcCall: "removeCard"},
	function(data) {
		if(data = "Card_removed"){
			$("#cardform input[name=width]").val("");
			$("#cardform input[name=height]").val("");
			$("#cardform input[name=rowcount]").val("");
			$("#cardform input[name=columncount]").val("");
			$("#cardform input[name=marginwidth]").val("");
			$("#cardform input[name=borderwidth]").val("");
			$("#cardform input[name=bordercolor]").val("");
			tb_remove();
			$("#selectedRow").hide();
			$("#removeButton").hide();
		}
		else{
			alert("Error!")
		}
	});
}


function removeConf(){
	$.post("conferencefunctions.php", { confID: $("#confname").val(), funcCall: "removeConf"},
	function(data) {
		if(data = "Conf_removed"){
			$("#selectedRow").hide();
			setConferenceValues("", "", "", "", "", "", "")
			tb_remove();
			$("#removeButton").hide();
		}
		else{
			alert("Error!")
		}
	});
}


function copyCard($card) {	
	$.post("conferencefunctions.php", { cardnumber: $card, funcCall: "copyCard"},
	function(data) {
		$("#background").val($(data).find("background").text());
		$("#width").val($(data).find("width").text());
		$("#height").val($(data).find("height").text());
		$("#columncount").val($(data).find("columncount").text());
		$("#rowcount").val($(data).find("rowcount").text());
		$("#borderwidth").val($(data).find("borderwidth").text());
		$("#marginwidth").val($(data).find("marginwidth").text());
		$("#borderColor").val($(data).find("bordercolor").text());
		$("#borderkind").val($(data).find("borderkind").text());
	});
}


function copyForm($conference) {	
	$.post("conferencefunctions.php", { conference: $conference, funcCall: "copyForm"},
	function(data) {
		$("#longname").val($(data).find("longname").text());
		$("#confdate").val($(data).find("confdate").text());
		$("#regstart").val($(data).find("regstart").text());
		$("#regend").val($(data).find("regend").text());
		$("#beforetext").val($(data).find("beforetext").text());
		$("#aftertext").val($(data).find("aftertext").text());
	});
}

function submitForm() {
	document.filterForm.submit();
}



function markPaid(lineno, conferencefilter) {
	$.post("conferencefunctions.php", { conferencefilter: conferencefilter, lineno: lineno, funcCall: "markPaid"},
	function(data) {
		if(data == "1") {
			tb_remove();
			$("#state_"+lineno).html('Paid by invoice');
			$("#tr_"+lineno).animate({backgroundColor: "#5CC46C", color: "#000"}, 800, 'linear', function() {});
			disableButton("button", lineno);
			disableButton("suspButton", lineno);
			
			$("#costField_"+lineno).fadeOut(800);
			$("#addCostButton_"+lineno).fadeOut(800, function() {
			$.post("conferencefunctions.php", { conferencefilter: conferencefilter, lineno: lineno, funcCall: "getAddcost"},
				function(data) {
					if(data) {
						$('#td_AddCost_'+lineno).html(""+data);
					}
				});	
			});
		}
		else {
			alert(data);
			tb_remove(); 
		}
	});
}

function addAdditional(lineno, conferencefilter) {
	var colo = "#fff";
	var colo2 = "#000";
	var addCost = $('#costField_'+lineno).val();
	var cost = $('#cost_'+lineno).text();
	//$("#mailText").remove();
	$.post("conferencefunctions.php", { conferencefilter: conferencefilter, addCost: addCost, cost: cost, lineno: lineno, funcCall: "additionalCostAdjustment"},
	function(data) {
		//$("#TB_ajaxContent").empty();
		tb_show("Mail message", "#TB_inline?height=400&width=400&inlineId=addCostMessage_"+lineno, "");
		//$("#TB_ajaxContent").replaceWith("<div id='#TB_ajaxContent' style='margin: 0px auto;'>"+$("#TB_ajaxContent").append("#addCostMessage_"+lineno)+"</div>");
		if(data.indexOf("Error") == -1) {
				$("#costField_"+lineno).animate({color: colo}, 500, 'linear', function() {
				$('#costField_'+lineno).val(data);
				$("#costField_"+lineno).animate({color: colo2}, 500, 'linear', function() {});
			});
		}
		else{
			alert(data);
			tb_remove(); 
		}
		$.post("conferencefunctions.php", { conferencefilter: conferencefilter, addCost: addCost, cost: cost, lineno: lineno, funcCall: "sendMail"},
		function(data) {
			$("#TB_ajaxContent").replaceWith("<div id='TB_ajaxContent' style='margin: 0px auto;'></div>");
			//$("#TB_ajaxWindowTitle").append("Mail message");
			$("#wrapper").prepend(data);
			//$("#mailText").hide();
		})
		.complete( function(){
			tb_show("Mail message", "#TB_inline?height=400&width=400&inlineId=mailText", "");
		});
	});
}

function fixAddCostMessage(lineno) {
	tb_show("Mail message", "#TB_inline?height=400&width=400&inlineId=mailText", "");
	$("#TB_ajaxContent").replaceWith("<div id='TB_ajaxContent' style='margin: 0px auto;'></div>");
	var addCost = new Number($('#costField_'+lineno).val());
	var cost = new Number($('#cost_'+lineno).text());
	
	if(!isNaN(addCost)) {
		if((cost + addCost) >= 0) {
	    	if (addCost < 0) {
				$("#okButton_"+lineno).show();
		        $("#addCostMessage_"+lineno).html("<strong>Are you sure you want to set the addititional costs value to the <span style='color: red; text-decoration: underline;'>negative</span> value below?</strong>");
		        $("#costDiv_"+lineno).html("<span style='color: red;'>"+addCost+"</span>");
	   		}
	   		else if(addCost == 0) {
	   			$("#okButton_"+lineno).show();
	   			$("#addCostMessage_"+lineno).html("<strong>Are you sure you want to reset the additional cost to 0?</strong>");		
	   			$("#costDiv_"+lineno).html(""+addCost+"");
	   		}
	    	else {
				$("#okButton_"+lineno).show();
	    		$("#addCostMessage_"+lineno).html("<strong>Are you sure you want to set the addititional costs value to the <span style='color: green; text-decoration: underline;'>positive</span> value below?</strong>");
	    		$("#costDiv_"+lineno).html("<span style='color: green;'>"+addCost+"</span>");
	    	}
	    }
	    else {
	    	$("#okButton_"+lineno).hide();
	    	$("#addCostMessage_"+lineno).html("<span style='color: red; text-align: center'><strong>The additional cost you have entered brings the total cost below 0. This is not permitted.</strong></span>");
	    	$("#costDiv_"+lineno).html("&nbsp;");
	    }
	} 
	else {
		$("#okButton_"+lineno).hide();
    	$("#addCostMessage_"+lineno).html("<span style='color: red; text-align: center'><strong>This is not a valid number!</strong></span>");
    	$("#costDiv_"+lineno).html("&nbsp;");	
	}
}

function suspState(sub) {
	if($("#suspStateCbox").is(':checked'))
		$(".hiddenSuspiciousState").val(1);
	else
		$(".hiddenSuspiciousState").val(0);
		
	if(sub == true)
		$("#filterForm").submit();
}

function markSuspicious(lineno, conferencefilter, suspicious) {
	$.post("conferencefunctions.php", { conferencefilter: conferencefilter, lineno: lineno, susp: suspicious, funcCall: "markSusp"},
	function(data) {
		if(data == 1) {
			$("#state_"+lineno).html('Suspicious');
			$("#suspButton_"+lineno).val("Mark Unsuspicious");
			$("#tr_"+lineno).animate({backgroundColor: "#3F708F", color: "#fff"}, 800, 'linear', function() {});
			$("#costField_"+lineno).fadeOut(800);
			disableButton("button", lineno);
			disableButton("sendReminderButton", lineno);
			
			if($("#suspButton_"+lineno).attr('onclick') != null)
				$("#suspButton_"+lineno).removeAttr('onclick');
			else
				$("#suspButton_"+lineno).unbind('click');
		
			$('#suspButton_'+lineno).bind('click', function() {
				markSuspicious(lineno, conferencefilter, 0);
			});
			
			$("#addCostButton_"+lineno).fadeOut(800, function() {
			$.post("conferencefunctions.php", { conferencefilter: conferencefilter, lineno: lineno, funcCall: "getAddcost"},
				function(data) {
					if(data) {
						$('#td_AddCost_'+lineno).html(""+data);
					}
				});	
			});
		}
		else if(data == "2") {
			$("#suspButton_"+lineno).val("Mark Suspicious");
			$("#state_"+lineno).html('Unpaid');
			$("#tr_"+lineno).animate({backgroundColor: "#fff", color: "#000"}, 800, 'linear', function() {});
			$("#costField_"+lineno).fadeOut(800);
			enableButton("button", lineno);
			if($("#suspButton_"+lineno).attr('onclick') != null) {
				$("#suspButton_"+lineno).removeAttr('onclick');
			}
			else
				$("#suspButton_"+lineno).unbind('click');
				
			enableButton("sendReminderButton", lineno);
				
			$('#suspButton_'+lineno).bind('click', function() {
				markSuspicious(lineno, conferencefilter, 1);
			});
		}
		else {
			alert(data);
		}
	});
}

function sendReminder(lineno, conferencefilter) {
	tb_remove();
	$.post("conferencefunctions.php", { conferencefilter: conferencefilter, lineno: lineno, funcCall: "sendReminder"},
	function(data) {
		if(data) {
			setTimeout('tb_show("", "#TB_inline?height=200&width=300&inlineId=remindedSentBox", "");',400);
			if(!(data == 2 || data == 3)) {
				$("#tr_"+lineno).animate({backgroundColor: "#C24E4E", color: "#fff"}, 800, 'linear', function() {});
				disableButton("sendReminderButton", lineno);
				$("#state_"+lineno).html('Reminder Sent');
				enableButton("button", lineno);
				disableButton("suspButton", lineno);
				$("#sentBoxP").html("<p style='color: green'>The reminder has been sent to "+data+"</p>");
			}
			if(data == 2) {
				$("#sentBoxP").html("<p style='color: red'>Due to an internal server error no notice message has been sent. Please try again later.</p>");
			}
			else if(data == 3) {
				$("#sentBoxP").html("<p style='color: red'>An invalid email is registered with this user.</p>");
			}
		}
		else {
			alert(data);
		}
	});
}

function disableButton(element, lineno) {
	$("#"+element+"_"+lineno).fadeTo(800, 0.6);
	$("#"+element+"_"+lineno).css('cursor', 'default');
	$("#"+element+"_"+lineno).attr('disabled', 'true');
}

function enableButton(element, lineno) {
	$("#"+element+"_"+lineno).fadeTo(800, 1.0);
	$("#"+element+"_"+lineno).css('cursor', 'pointer');
	$("#"+element+"_"+lineno).removeAttr("disabled");
}

function ajaxFunctionReady() {
	checkB();
	suspState(false);
	if($("#contentTable").width() > $(window).width()){
		var padding = $(window).width() * 0.02;
		//$('#wrapper').width();
		
		$('#content').css({'padding-left' : padding+"px", 'padding-right' : padding+"px"});
		$('header').css({'padding-left' : padding+"px", 'padding-right' : padding+"px"});
		$('nav').css({'padding-left' : padding+"px", 'padding-right' : padding+"px"});
		
		var wrapWidth = $("#contentTable").width() + padding;
		$('#wrapper').width(wrapWidth);
		$('#content').width(wrapWidth);
		$('header').width(wrapWidth);
		$('nav').width(wrapWidth);	
	}
}
function colorCards(){
	$('#color').ColorPicker({
		eventName: 'focus',
		color: '#ffffff',
		livePreview: true,
		onChange: function(hsb, hex, rbg, el){
			$('#color').val("#" + hex);
		},
		onSubmit: function(hsb, hex, rbg, el){
			$('#color').val("#" + hex);
			$('#color').ColorPickerHide();
		}
	});
}
function colorCardsClose(){
	$('#color').ColorPickerHide();
}

*/