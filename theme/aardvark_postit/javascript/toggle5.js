function toggle5(showHideDiv, switchTextDiv) {
	var ele = document.getElementById(showHideDiv);
	var text = document.getElementById(switchTextDiv);
	if(ele.style.display == "block") {
			ele.style.display = "none";
			text.innerHTML = '&#9660;';
	}else{
			ele.style.display = "block";
			text.innerHTML = '&#9650;';
	}
}