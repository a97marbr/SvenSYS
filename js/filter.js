$(document).ready(function(){
//arrays to hold everything that matters with the filtering
	var peopleInView = [];//all the people that can be shown
	var coursesInView = [];//all the courses that can is shown
	var noTimeInView = [];//all the people that haven't got any time
	var noDispInView = []//all the people that haven't got any disp.-time left
	var optionsInView = [];//all the different options
	
	if($("#filterCourses").val()){//if the value in the .php is set then fill the array with the value
		coursesInView = $("#filterCourses").val().split(",");
	}else{//if nothing is set then enter in everything
		$("tr.courseAndHoursRow").each(function(){
			enterIn(coursesInView, $(this).attr('id'));
		});
	}
	if($("#filterPeople").val()){//if the value in the .php is set then fill the array with the value
		peopleInView = $("#filterPeople").val().split(",");
	}else{//if nothing is set then enter in everyone
		$("td.coursePersonSign").each(function(){
			var classArray = $(this).attr('class').split(" ");
			enterIn(peopleInView, classArray[1]);
		});
	}
	if($("#filterNoTime").val()){//if the value in the .php is set then fill the array with the value
		noTimeInView = $("#filterNoTime").val().split(",");
	}else{//if nothing is set then go with the current ones
		hideNoTime();
	}
	if($("#filterNoDisp").val()){//if the value in the .php is set then fill the array with the value
		noDispInView = $("#filterNoDisp").val().split(",");
	}else{//if nothing is set then go with the current ones
		hideNoDispTime();
	}
	if($("#filterOptions").val()){//if the value in the .php is set then fill the array with the value
		optionsInView = $("#filterOptions").val().split(",");
		if(isIn(optionsInView, "superfluous")){//hide the superfluous things in the table
			$("td.courseCode, td.courseLevel, td.courseCredits, td.emptyGrey, td.courseSpeed, td.courseBstud, td.courseBudget, td.courseExaminator, td.courseAdmin, td.superfluous").hide();
			$("td.courseBigCell").attr({colspan:'2'});
			$("#takeAwayButtonOfDeath").attr('checked', true);
		}
		if(isIn(optionsInView, "edit")){//set the mode to speed-edit
			$("td.hoursCell").addClass("hoursCellTextField");
			$("#changeHowInputWorksOnCourseHours").attr('checked', true);
		}
		if(isIn(optionsInView, "noTime")){
			$("#showThemAll").attr('checked', true);
		}
		if(isIn(optionsInView, "noDisp")){
			$("#dispFilter").attr('checked', true);
		}
	}else{//the standard values are set, superfluous stuff is hidden, people with no time are hidden and speed-edit is set
		enterIn(optionsInView, "superfluous");
		$("#takeAwayButtonOfDeath").attr('checked', true);
		$("td.courseCode, td.courseLevel, td.courseCredits, td.emptyGrey, td.courseSpeed, td.courseBstud, td.courseBudget, td.courseExaminator, td.courseAdmin, td.superfluous").hide();
		$("td.courseBigCell").attr({colspan:'2'});
		enterIn(optionsInView, "noTime");
		$("#showThemAll").attr('checked', true);
		enterIn(optionsInView, "edit");
		$("td.hoursCell").addClass("hoursCellTextField");
		$("#changeHowInputWorksOnCourseHours").attr('checked', true);
	}
	keepUpdated();//update the view
	if($("#personSearch").val()){//if the user have searched for something it will be shown
		$("td."+$("#personSearch").val()).show();
	}
	
	function updateForm(){//used to update the php
		$("#filterCourses").val(coursesInView);
		$("#filterPeople").val(peopleInView);
		$("#filterNoTime").val(noTimeInView);
		$("#filterNoDisp").val(noDispInView);
		$("#filterOptions").val(optionsInView);
	}
	
	function isIn(filterArray, value){//used to check if the value is in the array
		for(var i = 0; i < filterArray.length; i++){
			if(filterArray[i] == value){
				return true;
			}
		}
		return false;
	}
	
	function enterIn(filterArray, value){//used to enter in things into the array
		if(!isIn(filterArray, value)){
			filterArray.push(value);
		}
	}
	
	function removeFrom(filterArray, value){//used to remove things from the array
		var temp = filterArray.indexOf(value);
		if(temp != -1){
			filterArray.splice(temp, 1);
		}
	}
	
	function filter(){//used to filter out what should be shown in the view
		$("tr.courseAndHoursRow").each(function(){//shows or hides courses depending if they are in the array for courses
			if(isIn(coursesInView, $(this).attr('id'))){
				$(this).show();
			}else{
				$(this).hide();
			}
		});
		$("td.coursePersonSign").each(function(){//shows or hides peopel depending if they are in the array for courses, if any specified options is in place and so on
			var classArray = $(this).attr('class').split(" ");
			if(!isIn(peopleInView, classArray[1]) ||
			(isIn(optionsInView, "noTime") && isIn(noTimeInView, classArray[1])) ||
			(isIn(optionsInView, "noDisp") && isIn(noDispInView, classArray[1]))){
				$("."+classArray[1]).each(function(){
					$(this).hide();
				});
			}else{
				$("."+classArray[1]).each(function(){
					$(this).show();
				});
			}
		});
	}
	
	function showAllCourses(){//used to show all the courses and highlight the previous shown
		$("td.courseName").each(function(){
			if(isIn(coursesInView, $(this).parent().attr('id'))){
				$(this).toggleClass("highlightRow");
				$(this).siblings().each(function(){
					$(this).toggleClass("highlightRow");
					if($(this).hasClass("highlightColumn")){
						$(this).toggleClass("doubleHigh");
					}
				});
			}
			enterIn(coursesInView, $(this).parent().attr('id'));
		});
	}
	
	function showAllPeople(){//used to show all the people and highlight the previous shown
		$("td.coursePersonSign").each(function(){
			var classArray = $(this).attr('class').split(" ");
			if(isIn(peopleInView, classArray[1])){
				var classArray = $(this).attr('class').split(" ");
				$("."+classArray[1]).each(function(){
					$(this).toggleClass("highlightColumn");
					if($(this).hasClass("highlightRow")){
						$(this).toggleClass("doubleHigh");
					}
				});
			}
			enterIn(peopleInView, classArray[1]);
		});
	}
	
	function checkHighlight(){
		var highlightRowCount = 0; //holds the number of highlighted rows
		var highlightColumnCount = 0; //holds the number of highlighted columns
		$("td.courseName").each(function(){ //to count up the 'highlightRowCount'
			if($(this).hasClass("highlightRow")){
				highlightRowCount = highlightRowCount + 1;
			};
		});
		$("td.coursePersonSign").each(function(){ //to count up the 'highlightColumnCount'
			var classArray = $(this).attr('class').split(" "); //split used to get the sign from the classes
			$("."+classArray[1]).each(function(){
				if($(this).hasClass("highlightColumn")){
					highlightColumnCount = highlightColumnCount + 1;
				}
			});
		});
		if(highlightRowCount > 0){ //if some things in the rows is highlighted then hide everything else in the rows
			coursesInView = [];
			$("td.courseName").each(function(){
				if($(this).hasClass("highlightRow")){
					$(this).toggleClass("highlightRow");
					$(this).siblings().each(function(){
						$(this).toggleClass("highlightRow");
						if($(this).hasClass("highlightColumn")){
							$(this).toggleClass("doubleHigh");
						}
					});
					enterIn(coursesInView ,$(this).parent().attr('id'));
				}
			});
			enterIn(optionsInView, "fCou");
		}
		if(highlightColumnCount > 0){ //if some things in the columns is highlighted then hide everything else in the columns
			peopleInView = [];
			$("td.coursePersonSign").each(function(){
				var isThere = false;
				var classArray = $(this).attr('class').split(" ");
				$("."+classArray[1]).each(function(){
					if($(this).hasClass("highlightColumn")){
						$(this).toggleClass("highlightColumn");
						isThere = true;
					}
				});
				if(isThere){
					enterIn(peopleInView ,classArray[1]);
				}
			});
			enterIn(optionsInView, "fPep");
		}
		updateForm();
	}
	
	function hideNoTime(){//used to decide which people have no time in any of the courses shown
		$("td.coursePersonSign").each(function(){
			var haveNumber = false;
			var classArray = $(this).attr('class').split(" ");
			$("tr.courseAndHoursRow ."+classArray[1]).each(function(){//if any of the cells have a time in a course the set a variable
				var temp = $(this).text();
				if(!temp=="" && isIn(coursesInView, $(this).parent().attr('id'))){
					haveNumber = true;
					return false;
				}
			});
			if(!haveNumber){//if the variable isn't set then the person doesn't have any time in the view
				enterIn(noTimeInView, classArray[1]);
			}else{
				removeFrom(noTimeInView, classArray[1]);
			}
		});
		updateForm();
	}
	
	function hideSuperfluous(){//used to hide or show the superfluous stuff in the view
		if($("td.courseCode").is(":visible")){//to hide
			enterIn(optionsInView, "superfluous")
			$("td.courseCode, td.courseLevel, td.courseCredits, td.emptyGrey, td.courseSpeed, td.courseBstud, td.courseBudget, td.courseExaminator, td.courseAdmin, td.superfluous").hide();
			$("td.courseBigCell").attr({colspan:'2'});
		}else{//to show
			removeFrom(optionsInView, "superfluous");
			$("td.courseCode, td.courseLevel, td.courseCredits, td.emptyGrey, td.courseSpeed, td.courseBstud, td.courseBudget, td.courseExaminator, td.courseAdmin, td.superfluous").show();
			$("td.courseBigCell").attr({colspan:'10'});
		}
		updateForm();
	}
	
	function hideNoDispTime(){//used to pick out the people that doesn't have any disp.-time left
		$("td.coursePersonSign").each(function(){
			var haveNumber = false;
			var classArray = $(this).attr('class').split(" ");
			$("tr.imATree ."+classArray[1]).each(function(){
				var temp = parseInt($(this).text());
				if(temp <= 0){
					enterIn(noDispInView, classArray[1]);
					return false;
				}else{
					removeFrom(noDispInView, classArray[1]);
				}
			});
		});
		updateForm();
	}
	
	function showOrHideOne(){//used to show or hide one person or course
		var who = prompt("Ange en 'sign' eller 'kurskod'");
		var isThere = false;
		$("td.coursePersonSign").each(function(){//show or hide person
			var classArray = $(this).attr('class').split(" ");
			if(who.toLowerCase() == classArray[1]){
				isThere = true;
				if(isIn(peopleInView, classArray[1]) && isIn(noTimeInView, classArray[1])){
				}else if(isIn(peopleInView, classArray[1])){
					removeFrom(peopleInView, classArray[1]);
				}else{
					enterIn(peopleInView, classArray[1]);
				}
				removeFrom(noTimeInView, classArray[1]);
				removeFrom(noDispInView, classArray[1]);
			}
		});
		$("td.courseCode").each(function(){//show or hide course
			if(who.toLowerCase() == $(this).text().toLowerCase()){
				isThere = true;
				var thisisatemp = false;
				if(isIn(coursesInView, $(this).parent().attr('id'))){
					removeFrom(coursesInView, $(this).parent().attr('id'));
				}else{
					enterIn(coursesInView, $(this).parent().attr('id'));
				}
			}
		});
		if(!isThere){//incase that no match was found in the arrays
			alert("Hittade ingen matchande sign eller kurskod");
		}
		updateForm();
	}
	
	function toggleButtons(){//used to disable/enable buttons
		if(!isIn(optionsInView, "fCou")){//if there aren't any hidden courses the disable the unfilter-button for the courses
			$("#unfilterCoursesButtonOfDoom").attr("disabled", "disabled");
		}else{
			$("#unfilterCoursesButtonOfDoom").removeAttr("disabled");
		}
		if(!isIn(optionsInView, "fPep")){//if there aren't any hidden people the disable the unfilter-button for the people
			$("#unfilterPeopleButtonOfDoom").attr("disabled", "disabled");
		}else{
			$("#unfilterPeopleButtonOfDoom").removeAttr("disabled");
		}
		var hidden = 0;
		var highlight = 0;
		$("td.courseName").each(function(){//if there are any hidden courses
			if(!isIn(coursesInView, $(this).parent().attr('id'))){
				hidden = hidden + 1;
			};
			if($(this).hasClass("highlightRow")){//if there are any highlighted rows
				highlight = highlight + 1;
			}
		});
		$("td.coursePersonSign").each(function(){//if there are any hidden people
			var classArray = $(this).attr('class').split(" ");
			if(!isIn(peopleInView, classArray[1])){
				hidden = hidden + 1;
			}
			if($(this).hasClass("highlightColumn")){//if there are any highlighted columns
					highlight = highlight + 1;
			}
		});
		if(hidden == 0){//if there are no hidden things then disable the unfilter-button for all
			$("#unfilterAllButtonOfDoom").attr("disabled", "disabled");
		}else{
			$("#unfilterAllButtonOfDoom").removeAttr("disabled");
		}
		if(highlight > 0){//if there are no highlights then disable the buttons for filtering and the button to remove highlihgting
			$("#filterButtonOfDoom").removeAttr("disabled");
			$("#somethingPretty").removeAttr("disabled");
		}else{
			$("#filterButtonOfDoom").attr("disabled", "disabled");
			$("#somethingPretty").attr("disabled", "disabled");
		}
	}
	
	function keepUpdated(){//used to make sure that the view is up to date
		hideNoDispTime();
		hideNoTime();
		filter();
		toggleButtons();
	}
	
	$("td.courseName, td.courseCode, td.courseLevel, td.courseCredits, td.coursePeriod, td.courseSpeed, td.courseBstud, td.courseBudget, td.courseExaminator, td.courseAdmin").click(function(){//when a user clicks a course then highlight that row
		$(this).toggleClass("highlightRow");
		$(this).siblings().each(function(){
			$(this).toggleClass("highlightRow");
			if($(this).hasClass("highlightColumn")){
				$(this).toggleClass("doubleHigh");
			}
		});
		toggleButtons();
	});
	
	$("td.coursePersonSign").click(function(){//when a user clicks a person then highlight that coulmn
		var classArray = $(this).attr('class').split(" ");
		$("."+classArray[1]).each(function(){
			$(this).toggleClass("highlightColumn");
			if($(this).hasClass("highlightRow")){
				$(this).toggleClass("doubleHigh");
			}
		});
		toggleButtons();
	});
	
	$("#takeAwayButtonOfDeath").click(function(){//when the user clicks the checkbox to show/hide superfluous stuff then run the function to show/hide them, called "Dölj informationsfält" in the view
	if(isIn(optionsInView, "superfluous")){
			removeFrom(optionsInView, "superfluous");
		}else{
			enterIn(optionsInView, "superfluous");
		}
		hideSuperfluous();
		keepUpdated();
	});
	
	$("#dispFilter").click(function(){//when the user clicks the checkbox to show/hide people with no disp. left then run the function to show/hide them, called "Dölj negativ Disponib." in the view
		if(isIn(optionsInView, "noDisp")){
			removeFrom(optionsInView, "noDisp");
		}else{
			enterIn(optionsInView, "noDisp");
		}
		keepUpdated();
	});
	
	$("#showThemAll").click(function(){//when the user clicks the checkbox to show/hide people with no time left in the view then run the function to show/hide them, called "Dölj ej allokerade personer" in the view
		if(isIn(optionsInView, "noTime")){
			removeFrom(optionsInView, "noTime");
		}else{
			enterIn(optionsInView, "noTime");
		}
		keepUpdated();
	});
	
	$("#showOne").click(function(){//when the user clicks the button to show/hide one person or course then run the function to show/hide them, called "Visa/göm person/kurs" in the view
		showOrHideOne();
		filter();
	});
	
	$("#filterButtonOfDoom").click(function(){//when the user clicks the button to filter run the function to filter, called "Filtrera markerade" in the view
		checkHighlight();
		keepUpdated();
	});
	
	$("#unfilterCoursesButtonOfDoom").click(function(){//when the user clicks the button to unfilter courses run the function to unfilter them, called "Avfiltrera kurser" in the view
		showAllCourses();
		removeFrom(optionsInView, "fCou");
		keepUpdated();
	});
	
	$("#unfilterPeopleButtonOfDoom").click(function(){//when the user clicks the button to unfilter people run the function to unfilter them, called "Avfiltrera personer" in the view
		showAllPeople();
		removeFrom(optionsInView, "fPep");
		keepUpdated();
	});
	
	$("#unfilterAllButtonOfDoom").click(function(){//when the user clicks the button to unfilter all stuff run the function to unfilter them, called "Avfiltrera allt" in the view
		if(isIn(optionsInView, "fCou")){
			showAllCourses();
		}
		if(isIn(optionsInView, "fPep")){
			showAllPeople();
		}
		removeFrom(optionsInView, "fCou");
		removeFrom(optionsInView, "fPep");
		keepUpdated();
	});
	
	$("#somethingPretty").click(function(){//when the user clicks the button to remove the highlights then run the function to remove them, called "Ta bort markeringar" in the view
		$(".highlightRow").each(function(){
			$(this).removeClass("highlightRow");
		});
		$(".highlightColumn").each(function(){
			$(this).removeClass("highlightColumn");
		});
		$(".doubleHigh").each(function(){
			$(this).removeClass("doubleHigh");
		});
		toggleButtons();
	});
	
	//changing functionality on input on courseHours
	$("#changeHowInputWorksOnCourseHours").click(function(){//when the user clicks the checkbox to change the edit-mode then change it, called "Snabbredigering" in the view
		if(isIn(optionsInView, "edit")){
			removeFrom(optionsInView, "edit");
			$("td.hoursCell").removeClass("hoursCellTextField");
		}else{
			enterIn(optionsInView, "edit");
			$("td.hoursCell").addClass("hoursCellTextField");
		}
		keepUpdated();
	});
});