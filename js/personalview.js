$(document).ready(function(){
	//adds an textfield to the td that you want to update
	$(".editableField").click(function(){
		if ($(this).children().attr("type") != "text") {
			$('#updateInput').remove();
			oldValue = $(this).text();
			var textCheck = $(this).attr("value"); //Check if input should be numeric only
			if ($(this).attr("update") == "employment_note") {
				$(this).html("<input type='text' maxlength='500' id='updateInput' value='" + oldValue + "' style='width:100%; border:0;'>");
			} else {
				$(this).html("<input type='text' maxlength='100' id='updateInput' value='" + oldValue + "' style='width:100%; border:0;'>");
			}
			$("#updateInput").select();
			if (textCheck != "text") {
				$("#updateInput").numeric();
			}
			loadUpdate();
		}
	});
	
	//if a search fails, fade the failtext out
	$("#wrongSearch").fadeOut(4500);
	
	//deletes an extra hour
	$("a.deleteExtraHour").click(function(e) {
		e.preventDefault();
		var answer = confirm("Vill du verkligen radera det här?");
		if (answer == true) {
			var id = $(this).attr("id");
			var toUpdate = "extra_delete";
			var data = "toUpdate=" + toUpdate + "&id=" + id;
			$(this).parent().parent().remove();
			ajaxUpdatePersView(data);
		}
	});
	
	//moves an extrahour
	$("a.moveExtraHour").click(function(e){
		e.preventDefault();
		var id = $(this).attr("id");
		var toUpdate = "extra_move";
		var data = "toUpdate=" + toUpdate + "&id=" + id;
		$.ajax({
			type: "POST",
			url: "updatepersonalview.php",
			data: data,
			success: function(result) {
				//alert(result);
				$("#ajaxUpdateForm").submit();
			}
		});
	});
});

//Have tp use this function so that the updateInput id will get the functions in it
function loadUpdate() {
	$("#updateInput").focusout(function(){
		checkUpdate();
	});

	$("#updateInput").keyup(function(e){
		if (e.keyCode == 13) {
			checkUpdate();
		} else if (e.keyCode == 27){
			$("#updateInput").parent().html(oldValue);
		}
	});
}

function checkUpdate() {
	var newValue = $("#updateInput").attr("value");
	var toUpdate = $("#updateInput").parent().attr("update");
	var updateField = true;
	var personID = $("#personID").attr("value");
	var year = $("#curYear").text();
	
	//controls what is to be updated in the database and creates a string with the variables that will be used by the ajax function later
	switch (toUpdate) {
		case 'allocated_time': //Update allocated time
			if ($.isNumeric(newValue) && newValue >= 0) {
				newValue = parseInt(newValue);
				var data = "toUpdate=" + toUpdate + "&year=" + year + "&personID=" + personID + "&newValue=" + newValue;	
				ajaxUpdatePersView(data);
			} else {
				updateField = false;
			}
			break;
		case 'extra_hours': //Update hours for stuff that arent a course
			if ($.isNumeric(newValue) && newValue >= 0) {
				newValue = parseInt(newValue);
				var id = $("#updateInput").parent().attr("id");
				var data = "toUpdate=" + toUpdate + "&id=" + id + "&newValue=" + newValue;	
				ajaxUpdatePersView(data);
			} else {
				updateField = false;
			}
			break;
		case 'extra_note': //Update the description for stuff that arent a course
			var id = $("#updateInput").parent().attr("id");
			var data = "toUpdate=" + toUpdate + "&id=" + id + "&newValue=" + newValue;
			ajaxUpdatePersView(data);
			break;
		case 'work_hours': //Update hours work on a course
			if ($.isNumeric(newValue) && newValue >= 0) {
				newValue = parseInt(newValue);
				var course_period_id = $("#updateInput").parent().attr("id");
				var data = "toUpdate=" + toUpdate + "&personID=" + personID + "&course_period_id=" + course_period_id + "&newValue=" + newValue;
				ajaxUpdatePersView(data);
			} else {
				updateField = false;
			}
			break;
		case 'work_note': //Update personal description for a course
			var course_period_id = $("#updateInput").parent().attr("id");
			var data = "toUpdate=" + toUpdate + "&personID=" + personID + "&course_period_id=" + course_period_id + "&newValue=" + newValue;
			ajaxUpdatePersView(data);
			break;
		case 'employment_percent': //Update service in percent
			if ($.isNumeric(newValue) && newValue >= 0) {
				newValue = parseInt(newValue);
				if (newValue > 100) {
					newValue = 100;
				}
				var data = "toUpdate=" + toUpdate + "&personID=" + personID + "&year=" + year + "&newValue=" + newValue;
				ajaxUpdatePersView(data);
			} else {
				updateField = false;
			}
			break;
		case 'employment_note': //Update notification for a person
			var data = "toUpdate=" + toUpdate + "&personID=" + personID + "&year=" + year + "&newValue=" + newValue;
			ajaxUpdatePersView(data);
			break;
		case 'extra_title': //Update title for projects/random
			var id = $("#updateInput").parent().attr("id");
			var data = "toUpdate=" + toUpdate + "&id=" + id + "&newValue=" + newValue;
			ajaxUpdatePersView(data);
			break;
		default:
			alert("Nothing to do here.");
	}
	if (updateField == true) {
		$("#updateInput").parent().html(newValue);
	} else {
		$("#updateInput").parent().html(oldValue);
		alert("Du får endast mata in positiva tal i det här fältet.");
	}
}

//updates the database
function ajaxUpdatePersView(data) {
	$.ajax({
		type: "POST",
		url: "updatepersonalview.php",
		data: data,
		success: function(result) {
			//alert(result);
			$("#datelastchange").text(result);
			updateFields();
		}
	});
}

//Update all fields in the personalview after a change has been made
function updateFields() {
	var allocated_time = parseInt($("#allocated_time").text());
	//Update totalWorkHours
	var vacation = parseInt($(".vacation").text());
	if (!$.isNumeric(vacation)) {
		vacation = 0;
	}
	var totalWorkHours = allocated_time - vacation;
	$("#totalWorkHours").text(totalWorkHours);
	//Update the coursepercent
	var totalCourseHours = 0;
	var mainfields = new Array();
	$(".courseHours").each(function(){
		totalCourseHours += parseInt($(this).text());
		var newPercent = Math.round(parseFloat(($(this).text() / allocated_time) * 100));
		$(this).next().text(newPercent + "%");
		var mainfield = $(this).attr("mainfield");
		if (!mainfields[mainfield]) {
			mainfields[mainfield] = parseInt($(this).text());
		} else {
			mainfields[mainfield] += parseInt($(this).text());
		}
	});
	var totalCoursePercent = Math.round(parseFloat((totalCourseHours / allocated_time) * 100));
	$("#totalCourseHours").text(totalCourseHours);
	$("#totalCoursePercent").text(totalCoursePercent + "%");
	//Update lowerfield
	var lowerFieldHours = 0;
	$(".lowerField").each(function(){
		lowerFieldHours += parseInt($(this).text());
		var newPercent = Math.round(parseFloat(($(this).text() / allocated_time) * 100));
		$(this).next().text(newPercent + "%");
	});
	var totalLowerPercent = Math.round(parseFloat((lowerFieldHours / allocated_time) * 100));
	$("#totalLowerHours").text(lowerFieldHours);
	$("#totalLowerPercent").text(totalLowerPercent + "%");
	//Update upperfield
	var upperFieldHours = 0;
	$(".upperField").each(function(){
		upperFieldHours += parseInt($(this).text());
	});
	$("#totalUpperField").text(upperFieldHours);
	//Update time for unit
	var unitTime = totalWorkHours - upperFieldHours;
	$("#unitTime").text(unitTime);
	//Update planned time
	var plannedTime = upperFieldHours + lowerFieldHours;
	$("#plannedTime").text(plannedTime);
	//Update disp time
	var dispTime = totalWorkHours - plannedTime - totalCourseHours;
	$("#dispTime").text(dispTime);
	//Update mainfields
	totalMainfield = 0;
	for (mainfield in mainfields) {
		$("#" + mainfield).text(mainfields[mainfield]);
		totalMainfield += mainfields[mainfield];
	}
	$("#totalMainfield").text(totalMainfield);
	updateLpRow(allocated_time);
}

//Updates LP in the personal view
function updateLpRow(allocated_time) {
	var personID = $("#personID").attr("value");
	var year = $("#curYear").text();
	data = "toUpdate=update_lp&personID=" + personID + "&year=" + year + "&allocated_time=" + allocated_time;
	$.ajax({
		type: "post",
		url: "updatepersonalview.php",
		data: data,
		success: function(result) {
			//alert(result);
			$(".LP_ROW").remove();
			$("#courseTotalRow").before(result);
		}
	});
}

//create a new extrahour
function createExtraHour() {
	var typeID = $("#newExtraHour").attr("value");
	var personID = $("#personID").attr("value");
	var display = $("#newExtraHourDisplay").attr("value");
	var year = $("#curYear").text();
	$.ajax({
		type: "POST",
		url: "createExtraHour.php",
		data: "typeID=" + typeID + "&personID=" + personID + "&year=" + year + "&display=" + display,
		success: function(result) {
			//alert(result);
			$("#ajaxUpdateForm").submit();
		}
	});
}