document.onreadystatechange = function() {
    var max_chars = 160;
    var char_counts = document.getElementsByClassName('sendsms-char-count');
    if (document.readyState === "complete") {
        for (var i = 0; i < char_counts.length; i++) {
            var msgTxt = document.createElement('p');
            // find textfield
            var char_textfield = char_counts[i].parentNode.getElementsByClassName('sendsms-char-count')[0];
            // set max length
            // char_textfield.setAttribute('maxlength', max_chars);
            char_textfield.after(msgTxt);
            // count remaining characters
            if (char_textfield.value.length !== typeof undefined) {
                msgTxt.innerHTML = lenghtCounter(char_textfield.value);
            }
            // add event
            char_textfield.onkeyup = function() {
                this.nextSibling.innerHTML = lenghtCounter(this.value);
            };
        }
    }

    function lenghtCounter(message) {
        var lenght = message.length;
        var messages = lenght / 160 + 1;
        if (lenght > 0) {
            if (lenght % 160 === 0) {
                messages--;
            }
            return "Number of messages: " + Math.floor(messages) + " (" + lenght + ")";
        } else {
            return "The field is empty";
        }
    }
};