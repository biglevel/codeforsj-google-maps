<p>
    <a href="/admin">Maps</a> | <a href="/admin/map">Add Map</a> | <a href="/admin/candidates">Generate Candidate JSON</a>
</p>
<p>
    Columns:
</p>
<ol>
    <li>Type: primary, runoff, pac</li>
    <li>Candidate Name</li>
    <li>Zip</li>
    <li>Amount</li>
</ol>
<p>
    Format Example:
</p>
<!--
Candidates:
- Nguyen
- Licarrdo
- Oliverio
- Cortese
- Herrera
-->
<pre>primary,Nguyen,10012,250.00
primary,Licarrdo,19711,1000.00
primary,Oliverio,11414,1434.60
primary,Cortese,94086,1350.00
primary,Herrera,20007,1000.00
runoff,Licarrdo,19711,1000.00
runoff,Cortese,94086,1350.00
pac,Licarrdo,19711,1000.00
pac,Cortese,94086,1350.00
</pre>
<form name="contributions" action="#" method="post">
    <fieldset>
        <legend>Generate JSON from Candidate data</legend>
        <textarea name="data" style="width:940px; height: 280px;"></textarea>
        <button name="submit">Generate JSON</button>
    </fieldset>
    <fieldset id="results" style="display: none;">
        <legend>Results</legend>
        <textarea name="results" style="width:940px; height: 280px;"></textarea>
    </fieldset>
</form>

<script>
$(document).ready(function () {
    $("form[name=contributions]").submit(function(e){
        e.preventDefault();
        var data = $("textarea[name=data]").val();
        var lines = data.split("\n");
        var results = {};
        $.each(lines, function(i, line) {
            line = line.trim();
            var columns = line.split(",");
            if (columns.length==4) {
                var param = {
                    type: columns[0].trim().toLowerCase(),
                    candidate: columns[1].trim().toLowerCase(),
                    zip: columns[2].trim().toLowerCase(),
                    amount: columns[3].trim().toLowerCase()
                };
                if (param.type.length > 0 && param.candidate.length > 0 && param.zip.length > 0 && param.amount.length > 0) {
                    if (typeof results[param.type] == 'undefined') {
                        results[param.type] = {};
                    }
                    if (typeof results[param.type][param.candidate] == 'undefined') {
                        results[param.type][param.candidate] = {};
                    }
                    if (typeof results[param.type][param.candidate][param.zip] == 'undefined') {
                        results[param.type][param.candidate][param.zip] = 0;
                    }
                    results[param.type][param.candidate][param.zip] = parseFloat(param.amount);
                    console.log(param.zip + " " + parseFloat(param.amount));

                }
            }
        });
        console.log(results);
        var json = JSON.stringify(results);
        console.log(json);
        $("textarea[name=results]").val( json );
        $("#results").show();
    });

});
</script>