<script src="codemirror/lib/codemirror.js"></script>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link rel="stylesheet" href="codemirror/lib/codemirror.css">
<script src="codemirror/mode/javascript/javascript.js"></script>
<script
        src="https://code.jquery.com/jquery-3.5.1.js"
        integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
        crossorigin="anonymous"></script>

<body>
<div id="codemirror"></div>
<div id="info"></div>
<button class="btn btn-success" onclick="send_data()">Submit</button>
            <table class="table" id="LITERAL"></table>
            <table class="table" id="IDENTIFIER"></table>
            <table class="table" id="KEYWORD"></table>
            <table class="table" id="OPERATION"></table>
</body>

<script>
    var editor = CodeMirror(document.getElementById('codemirror'), {
        tabSize: 5,
        mode: 'c',
        lineNumbers: true,
    });

    function send_data() {
        var lines = editor.display.view;
        var line_arr = [];
        lines.forEach(function (item, index) {
            line_arr[index] = item.line.text.trim();
        });
        $.ajax({
            type: "POST",
            url: "translator_process.php",
            async: false,
            data:  {'data':line_arr},
        }).done(function( msg ) {
            console.log(msg);
            msg = JSON.parse(msg);
            print_lexem(msg);
        });
    }

    function print_lexem(msg) {

        let classes = ['DELIMITER', 'IDENTIFIER', 'KEYWORD', 'OPERATION', 'LITERAL'];
        $('#lexem').empty();

        $(classes).each(function (index, key) {
            $('table#'+key).empty();
            $('table#'+key).append('<tr>' +
                '<th>'+key+'</th>' +
                '<th>Index</th>' +
                '</tr>');
            $(msg[key]).each(function (ind, value) {
                $('table#'+key).append('' +
                    '<tr>' +
                    '<td>'+value+'</td>' +
                    '<td>'+ind+'</td>' +
                    '</tr>');
            });

            $('#lexem').append('<br><hr><br>');
        });
    }

</script>
