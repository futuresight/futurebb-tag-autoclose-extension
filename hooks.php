<?php
//the hooks file for the Automatic Tag Closing extension
$hooks = array();
$hooks['bbcode_toolbar_bottom'] = array(
	function($args) {
		?>
<script type="text/javascript">
function getAllMatches(str, pattern) {
	//returns all the matches
	var result = [];
	var i = 0;
	do {
		i++;
		curMatch = pattern.exec(str);
		if (curMatch != null) {
			result.push(curMatch[0]);
		}
	} while (curMatch != null);
	return result;
}

var bbcode_tags = ['b','i','u','s','color','colour','url','img','quote','code','list','\\*', 'table', 'tr', 'td', 'th'];
document.getElementById('message').onkeyup = function(event) {
	if (event.key == '/') {
		var msgbox = document.getElementById('message');
		var msg = msgbox.value;
		var len = msgbox.value.length;
		if (msg.charAt(msgbox.selectionStart - 2) == '[') {
			//the user just typed [/ at the end of the message, so let's automatically close the tag
			var regex = new RegExp('\\[/?(' + bbcode_tags.join('|') + ')(=.*?)?\\]', 'gm');
			var matches = getAllMatches(msg.substring(0, msgbox.selectionStart), regex);
			//get all of the matches going forwards to the start of the selection
			var frontStack = [];
			var innerRegex = new RegExp('\\[/?(.*?)(=|\\])');
			for (var key in matches) {
				var curMatch = matches[key];
				var tag = innerRegex.exec(curMatch)[1];
				if (curMatch.indexOf('[/') == 0) {
					//closing tag
					if (tag == frontStack[frontStack.length - 1]) {
						frontStack.pop();
					} else {
						//the syntax is invalid, so forget it
						return;
					}
				} else {
					//opening tag
					frontStack.push(tag);
				}
			}
			//now get all of the matches going backwards towards the selection end
			var backStack = [];
			matches = getAllMatches(msg.substring(msgbox.selectionEnd), regex);
			matches = matches.reverse();
			for (var key in matches) {
				//this works the opposite way as the front stack - you add closing tags on and remove them when you open them
				var curMatch = matches[key];
				var tag = innerRegex.exec(curMatch)[1];
				if (curMatch.indexOf('[/') == 0) {
					//closing tag
					backStack.push(tag);
				} else {
					//opening tag
					if (tag == backStack[backStack.length - 1]) {
						backStack.pop();
					} else {
						//the syntax is invalid, so forget it
						return;
					}
				}
			}
			//cancel any common items (i.e. tags that are opened then closed later
			while (frontStack.length > 0 && backStack.length > 0 && frontStack[0] == backStack[0]) {
				frontStack.shift();
				backStack.shift();
			}
			if (frontStack.length > 0) {
				//this means that there still is a tag to close
				var closingTag = frontStack[frontStack.length - 1];
				var cursorPos = msgbox.selectionStart + closingTag.length + 1;
				msgbox.value = msgbox.value.substr(0, msgbox.selectionStart) + closingTag + ']' + msgbox.value.substr(msgbox.selectionEnd, msgbox.value.length);
				msgbox.selectionStart = cursorPos;
				msgbox.selectionEnd = cursorPos;
			}
		}
	}
}
</script>
<?php
	}
);