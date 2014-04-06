function hasClicked() 
{
	var x = document.getElementById("submitBTN");
	x.setAttribute('disabled','true');
}

function spinMeRightRound()
{	// options for styling the spinner
	var opts = { lines: 9, length: 15, width: 13, radius: 27, corners: 1, rotate: 30, direction: 1, color: "#06C", speed: 2.2, trail: 60, shadow: false, hwaccel: true, className: "spinner", zIndex: 2e9, top: "35%", left: "50%"};
    // target refers to the element where the spinner is appended
	var target = document.getElementById("content");

	
	var studentBOX = document.getElementById("studentidBOX");
	var studentValue = studentBOX.value;
	// Make sure none of the expected fields are empty before displaying the spinner onClick
	if (!(studentValue == null || studentValue ==""))
	{
		setTimeout(hasClicked, 1); ; // the submit button has been clicked and no fields are empty, so disable it.
		var spinner = new Spinner(opts).spin(target);
	}
	
}
