(function(w, d) {
    var DMK = w.DMK || {};
    DMK.DevLog = DMK.DevLog || {};
    DMK.JSON = DMK.JSON || {};
    DMK.JSON.highlight = function(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="json-' + cls + '">' + match + '</span>';
        });
    }
    DMK.DevLog.toggleData = function(id) {
        var data = d.getElementById("log-toggle-" + id + "-data");

        if (data.className.search("json-parsed") < 0) {
            data.innerHTML = DMK.JSON.highlight(
                JSON.stringify(
                    JSON.parse(data.innerHTML),
                    undefined,
                    2
                )
            );
            data.className = "json-parsed";
        }

        var dataRow = data.closest('tr');
        if (dataRow.className.search("log-hidden") < 0) {
            dataRow.className = "log-hidden";
        } else {
            dataRow.className = "";
        }
    };

    let toggleElements = d.getElementsByClassName('log-toggle-link');
    Array.from(toggleElements).forEach(function(element) {
        element.addEventListener('click', function(event) {
            let resultUid = event.target.getAttribute("data-resultUid");
            DMK.DevLog.toggleData(resultUid);
        })
    })

    w.DMK = DMK;
})(window, document);
