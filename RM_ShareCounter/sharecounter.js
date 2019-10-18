console.log("RM_ShareCounter Ajax"); 
//Future enhancement:  put postID var using  wp_localize_script

//Start
// Set up our HTTP request
var xhr = new XMLHttpRequest();
var postID = document.querySelector('.status-publish').getAttribute('id').replace("post-", "");
var params = 'postID='+postID;

// Setup our listener to process completed requests
xhr.onload = function () {
    // Process our return data
    if (xhr.status >= 200 && xhr.status < 300) {
        // What do when the request is successful
        //console.log('success!', xhr.response);
        //Update Counter
        var sharecounters = document.getElementsByClassName("results_share");
        for (let index = 0; index < sharecounters.length; index++) {
            const element = sharecounters[index];
            element.innerHTML = xhr.response;
        }

    } else {
        // What do when the request fails
        //console.log('The request failed!');
    }
    // Code that should run regardless of the request status
    //console.log('This always runs...');
};
xhr.open('POST', '/rm-shareupdater/',true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.send(params);

