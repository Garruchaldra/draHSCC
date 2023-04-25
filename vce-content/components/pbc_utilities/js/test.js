<script>
    $(document).ready(function() {

        function update_users_$dl() {
            var selected = $('#group-members-$dl li');
            var ids = new Array();
            $.each(selected, function (index, value) {
                var thisId = $(value).attr('user_id');
                ids.push(thisId);
            });
            //create json object which includes datalist identifier, dl id and selected user ids in one hidden input
            $('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + ids.join('|') + '"}');
        }
        

        //create json object from selected users when page has loaded
       update_users_$dl();
 

        // arguments: reference to select list, callback function (optional)
function getSelectedOptions(sel, fn) {
    var opts = [], opt;
    
    // loop through options in select list
    for (var i=0, len=sel.options.length; i<len; i++) {
        opt = sel.options[i];
        
        // check if selected
        if ( opt.selected ) {
            // add to array of option elements to return from this function
            opts.push(opt);
            
        }
    }
    var jason = '{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + opts.join('|') + '"}';
    // invoke optional callback function if provided
    if (fn) {
        fn(jason);
    }
    // return array containing references to selected option elements
    return opts;
}


// example callback function (selected options passed one by one)
function callback(jason) {
    // display in textarea for this example
    var display = document.getElementById('display');

    display.innerHTML = jason;

    // can access properties of opt, such as...
    //alert( opt.value )
    //alert( opt.text )
    //alert( opt.form )
};

// anonymous function onchange for select list with id demoSel
document.getElementById('$dl').onchange = function(e) {
    // get reference to display textarea
    var display = document.getElementById('display');
    display.innerHTML = ''; // reset
    
    // callback fn handles selected options
    getSelectedOptions(this, callback);
    
    // remove ', ' at end of string
    var str = display.innerHTML.slice(0, -2);
    display.innerHTML = str;
};




    $('.cancel-button').on('click', function() {
        window.location.reload(1);
    });
    


    });
</script>	