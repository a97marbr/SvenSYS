$(document).ready(function(){	
//function to change color in the database
	function colorStuff(data, that, color){
		if(that.text() != "")
		$.ajax({
			type: "POST",
			url: "updateDB.php",
			data: data,
			success: function(result) {
				that.removeClass("red green none orange");
				that.addClass(color);
			}
		});
	}
	
//handeling of rightclicks on the hourscells
	$('td.hoursCell').bind('contextmenu', function(e){
		var cpp = $(this).attr("cpp");
		var personID = $(this).attr("personID");
		var that = $(this);
		e.preventDefault();
		$("<div id='dialogstuff' title='Välj en färg'></div>").appendTo("body");//div that will appear in the dialog
		$("#dialogstuff").dialog({buttons:{// four different buttons that only differ in color
		"Ingen färg":function(){
			var data = "color=none&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
			colorStuff(data, that, "none");
			$(this).dialog("close");
			$("#dialogstuff").remove();
		},"Röd": function(){
			var data = "color=red&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
			colorStuff(data, that, "red");
			$(this).dialog("close");
			$("#dialogstuff").remove();
		},"Orange":function(){
			var data = "color=orange&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
			colorStuff(data, that, "orange");
			$(this).dialog("close");
			$("#dialogstuff").remove();
		},"Grön":function(){
			var data = "color=green&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
			colorStuff(data, that, "green");
			$(this).dialog("close");
			$("#dialogstuff").remove();
		}}});
		$('#dialogstuff').bind('dialogclose', function(event) {//to remove the div if the x is clicked
			$("#dialogstuff").remove();
		});
		return false;
	});
	
//		--		Hours	-- 'hoursCell' -- hours_work\hours_int
	//Hours Cells - How many hours someone is budgeted on a specific course.
	$("td.hoursCell").click(function(e){
		//Get ID - contains person and course name.
		var id = $(this).attr("id");
		var cpp = $(this).attr("cpp");
		var personID = $(this).attr("personID");
		var tempArray = id.split(';');
		var year = $(this).attr("year");
		var user = tempArray[0];
		var that = $(this);
		var mainField = $(this).attr("mainField");
		var oldValue = parseInt($(this).html());
		var currentYear = (new Date).getFullYear();
	
		if(year < currentYear){
			return;
		}else{
			if(isNaN(oldValue)){
				oldValue = 0;
			}
	//using textbox for input	
			if($(this).hasClass("hoursCellTextField")){
				if ($(this).children().attr("type") != "text"){
					if(user.match('superadmin') || user.match('organizer')){
						var value = $(this).html();
						var textCheck = $(this).attr("value");
						$(this).html("<input type='text' maxlength='3' id='updateCourseHours' value='" + value + "' style='width:28px; height:10px;'>");	
						$("#updateCourseHours").select();
				
					if (textCheck != "text") {
						$("#updateCourseHours").numeric();
					}	

					//updates DBS
					courseHoursUpdater(cpp, personID, value, mainField);
					}
				}
				
				//else - do nothing.
			}	
	//	using alert functionality to display/modift
			else {
				if(user.match('superadmin') || user.match('organizer')){
					var hours;
					$("<div id='dialogthings' title='"+tempArray[1]+"'><input type='text' id='enText' value='"+$(this).html()+"'></div>").appendTo("body");//div to appear in the dialog
					$("#enText").keyup(function(e){//handeling of enter in the dialog
						if(e.which == 13){
							hours = $("#enText").val();
							imAFunction(that, hours);
							$(this).dialog("close");
							$("#dialogthings").remove();
						}
					});
					$("#dialogthings").dialog({buttons:{//four buttons that only differ in color
					"Ingen färg":function(){
						hours = $("#enText").val();
						imAFunction(that, hours, "none");
						$(this).dialog("close");
						$("#dialogthings").remove();
					},"Röd": function(){
						hours = $("#enText").val();
						imAFunction(that, hours, "red");
						$(this).dialog("close");
						$("#dialogthings").remove();
					},"Orange":function(){
						hours = $("#enText").val();
						imAFunction(that, hours, "orange");
						$(this).dialog("close");
						$("#dialogthings").remove();
					},"Grön":function(){
						hours = $("#enText").val();
						imAFunction(that, hours, "green");
						$(this).dialog("close");
						$("#dialogthings").remove();
					}}});
					$('#dialogthings').bind('dialogclose', function(event) {//to remove the div if the x is clicked
						$("#dialogthings").remove();
					});
					//var hours = prompt(tempArray[1] + "\n\n" + "Nuvarande budgeterade timmar: " +$(this).html());
					// TODO: Run function to update hours
				}
					
				else if(user == 'user'){
						//alert("Nuvarande budgeterad timmar: " + $(this).html());
						//nothing to do here
				}
			}
		}
	});

//function to handel slow-mode editing
	function imAFunction(stuff, hours, color){
		var id = stuff.attr("id");
		var cpp = stuff.attr("cpp");
		var personID = stuff.attr("personID");
		var tempArray = id.split(';');
		var year = stuff.attr("year");
		var user = tempArray[0];
		var availableVal = stuff.attr("available");
		var vacation = stuff.attr("vacation");
		var totalHoursOnCourses = stuff.attr("totalHoursOnCourses");
		var mainField = stuff.attr("mainField");
		var sign = stuff.attr("sign");
		var oldValue = parseInt(stuff.html());
		var currentYear = (new Date).getFullYear();
		
		if(isNaN(oldValue)){
			oldValue = 0;
		}
		
		if(hours != null){
			var originalLength = hours.length;
		}
		else{
			//empty input
			return;
		}	
		// alert("Original length " + originalLength);	
			
		//Non-digits are not acceptable.	
		hours = hours.replace(/[^0-9]/g, "");
		intHours = parseInt(hours);				
		
		
		
		if(hours.length != originalLength){
			alert("Bara positiva heltal accepteras utan andra tecken");
			return;
		}
		//else if(hours.length > 3){
		
		var data;
		
		if(hours.length != 0){
			if(intHours > 999 || intHours < 0){
				alert("Talet bör vara mellan och 999 i storlek!");
				return;
			}
					
			while(hours.charAt(0) == "0" && hours.length > 1){
				hours = hours.substring(1,hours.length);							
			}
			data = "hours=" + hours +"&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toUpdate=hoursWorkOnCourse";
		}
		
		else{
			data = "&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toDelete=hoursWorkOnCourse";
		}
			
		$.ajax({
			type: "POST",
			url: "updateDB.php",
			data: data,
			dataType:"json",
			success: function(result) {
				$("td#Sum" + personID).html(result.hours);
				$("td#NrOfCourses" +personID).html(result.nr_of_courses);
			}
		});	
			
		//Update Dispfield
		var newValue = oldValue - hours;
		if(newValue != 0) {
			//Update value
			availableVal = stuff.attr("available");
			availableVal = parseInt(availableVal) + newValue;
			stuff.attr('available', availableVal);
			$("#" + personID).html(availableVal);
			stuff.html(hours);	

		}
		
		color = color || null;
		
		switch(color){
			case "none":
				var data = "color=none&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
				colorStuff(data, stuff, "none");
				break;
			case "red":
				var data = "color=red&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
				colorStuff(data, stuff, "red");
				break;
			case "orange":
				var data = "color=orange&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
				colorStuff(data, stuff, "orange");
				break;
			case "green":
				var data = "color=green&personID=" + personID + "&coursePerPeriodID=" + cpp + "&toUpdate=color";
				colorStuff(data, stuff, "green");
				break;
			default:
				break;
		}

		if(hours.length == 0){
			stuff.html("");
			stuff.removeClass("red green none orange");
			return;
		}
							
		else{
			stuff.html("" + hours);
		}
		$("td#" +personID).html(availableVal);
			
		//alert(availableVal);
	}
	
	function courseHoursUpdater(cpp, personID, value, mainField){		
		$("#updateCourseHours").focusout(function(){
			var hours = $(this).attr("value");
			var hoursNumber = hours.replace(/[^0-9]/g, "");
			var hoursLength = hours.length;
			
			//in case the column was empty and there is no input, the program should not do anything.
			if(value == "" && $(this).html() == "" && hours == ""){
				
				//alert("branch: [courseBudgetView/clickScript]@courseHoursUpdated: empty colum, empty input");
								
				$('#updateCourseHours').parent().html("" + value);
				$('#updateCourseHours').remove();				
				return;
			};
			
			if(hours.length != hoursNumber.length){
					
					return;
				} else {
					var data;
					if(hours.length != 0){
						data = "hours=" + hours +"&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toUpdate=hoursWorkOnCourse";
					}else{				
						data = "&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toDelete=hoursWorkOnCourse";
						$(this).parent().removeClass("red green none orange");
					}
										
					$.ajax({
						type: "POST",
						url: "updateDB.php",
						data: data,
						dataType:"json",
						success: function(result) {
							$("td#Sum" + personID).html(result.hours);
							$("td#NrOfCourses" +personID).html(result.nr_of_courses);
						}
					});
					//Update Dispfield
					var newValue = value - hours;
					if(newValue != 0) {
						//Update value
						var availableVal = $('#' + personID).html();
						var newVal = parseInt(availableVal) + newValue;						
						$('#' + personID).html(newVal);
					}

					
					if(hours.length == 0){
						$(this).parent().html("");
						
					} 
					else {
						//taking away "0"s from the beginning of the input (while cycle could not be used for this purpose)
						//input in form of 0xy
						if(hours.charAt(0) == "0" && hours.length == 3){
							hours = hours.substring(1,hours.length);	
						}
						//input in form of 0y
						if(hours.charAt(0) == "0" && hours.length == 2){
							hours = hours.substring(1,hours.length);	
						}	
						$(this).parent().html("" + hours);
					}
				}	
		});		
		$("#updateCourseHours").keyup(function(e){		
			if (e.keyCode == 13) {
				var hours = $(this).attr("value");
				var hoursNumber = hours.replace(/[^0-9]/g, "");
				var hoursLength = hours.length;
				//var availableVal = $(this).parent().attr("available");
				if(hours.length != hoursNumber.length){
					return;
				} else {
					var data;
					
					//taking away "0"s from the beginning of the input
					while(hours.charAt(0) == "0" && hours.length > 1){
						hours = hours.substring(1,hours.length);	
					}
					
					if(hours.length != 0){
						data = "hours=" + hours +"&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toUpdate=hoursWorkOnCourse";
					}else{
						data = "&personID=" + personID + "&coursePerPeriodID=" + cpp + "&mainField=" + mainField + "&toDelete=hoursWorkOnCourse";
						hours = 0;
						
					}
					$.ajax({
						type: "POST",
						url: "updateDB.php",
						data: data,
						dataType:"json",
						success: function(result) {
							$("td#Sum" + personID).html(result.hours);
							$("td#NrOfCourses" +personID).html(result.nr_of_courses);
						}
					});			
					
					//Update Dispfield
					var newValue = value - hours;
					if(newValue != 0) {
						//Update value
						//var availableVal = $('#updateCourseHours').parent().attr("available");
						var availableVal = $('#' + personID).html();
						availableVal = parseInt(availableVal) + newValue;
						$('#' + personID).html(availableVal);
					}
					if(hoursLength == 0){
						$(this).parent().removeClass("red green none orange");
						$(this).parent().html("");
					} else {
						$(this).parent().html("" + hours);
					}
					
				}
			}
			else if(e.keyCode == 27){
				$('#updateCourseHours').parent().html("" + value);
				$('#updateCourseHours').remove();
			}
		});	
	}

//	--	Hovereffects!	--

	$("td.courseName, td.courseCode, td.coursePersonSign, td.sortable").hover(function(){
			//hovers to show something can be done
			$(this).css('cursor','pointer');
	});
});